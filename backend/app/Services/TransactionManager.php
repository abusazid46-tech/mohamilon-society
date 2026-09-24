<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class TransactionManager
{
    /**
     * Records a Member Deposit (Daily or Monthly) and propagates across all modules (Sections 3 & 13).
     */
    public static function recordDeposit(
        int $accountId,
        float $amount,
        string $paymentMode = 'cash',
        ?int $bankAccountId = null,
        ?string $note = null,
        ?int $staffId = null
    ): array {
        if ($amount <= 0) {
            throw new InvalidArgumentException('Deposit amount must be greater than zero.');
        }

        return DB::transaction(function () use ($accountId, $amount, $paymentMode, $bankAccountId, $note, $staffId) {
            $account = DB::table('accounts')->where('id', $accountId)->lockForUpdate()->firstOrFail();
            $member = DB::table('members')->where('id', $account->member_id)->firstOrFail();

            // 1. Update Account balance
            DB::table('accounts')->where('id', $accountId)->increment('balance', $amount, ['updated_at' => now()]);

            // 2. Member Ledger Entry
            $txRef = SequenceService::nextTransactionRef();
            $ledgerId = DB::table('ledger_entries')->insertGetId([
                'reference' => $txRef,
                'account_id' => $accountId,
                'direction' => 'credit',
                'amount' => $amount,
                'description' => $note ?: "Deposit to account {$account->account_no}",
                'status' => 'posted',
                'posted_at' => now(),
                'created_by' => $staffId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 3. Cash or Bank Ledger
            $location = in_array($paymentMode, ['bank', 'upi'], true) ? 'bank' : 'treasury';
            FinanceLedger::cash(
                $location,
                'credit',
                'deposit',
                $amount,
                "Deposit to {$account->account_no}",
                $staffId,
                'account',
                $accountId
            );

            if ($location === 'bank' && $bankAccountId) {
                DB::table('bank_accounts')->where('id', $bankAccountId)->increment('current_balance', $amount, ['updated_at' => now()]);
            }

            // 4. Generate Official Receipt (Section 18)
            $receipt = ReceiptService::generate(
                $member->id,
                $amount,
                'deposit',
                $paymentMode,
                $accountId,
                null,
                $txRef,
                $note,
                $staffId
            );

            // 5. Send Notification
            MemberNotifier::send(
                $member->user_id,
                'deposit',
                'Deposit Received',
                "Deposit of ₹" . number_format($amount, 2) . " credited to account {$account->account_no}. Receipt: {$receipt->receipt_no}",
                'deposit-' . $ledgerId,
                '/accounts'
            );

            // 6. Recalculate Credit Score
            $score = CreditScore::recalculate($member->id);

            return [
                'transaction_ref' => $txRef,
                'receipt_no' => $receipt->receipt_no,
                'new_balance' => round((float) $account->balance + $amount, 2),
                'credit_score' => $score,
            ];
        });
    }

    /**
     * Records a Member Withdrawal with balance validation and overdraft protection (Section 10).
     */
    public static function recordWithdrawal(
        int $accountId,
        float $amount,
        string $paymentMode = 'cash',
        ?int $bankAccountId = null,
        ?string $reason = null,
        ?int $staffId = null
    ): array {
        if ($amount <= 0) {
            throw new InvalidArgumentException('Withdrawal amount must be greater than zero.');
        }

        return DB::transaction(function () use ($accountId, $amount, $paymentMode, $bankAccountId, $reason, $staffId) {
            $account = DB::table('accounts')->where('id', $accountId)->lockForUpdate()->firstOrFail();
            $member = DB::table('members')->where('id', $account->member_id)->firstOrFail();

            if ((float) $account->balance < $amount) {
                throw new InvalidArgumentException("Insufficient balance. Available: ₹" . number_format((float) $account->balance, 2));
            }

            // 1. Deduct Account Balance
            DB::table('accounts')->where('id', $accountId)->decrement('balance', $amount, ['updated_at' => now()]);

            // 2. Member Ledger Entry (Debit)
            $txRef = SequenceService::nextTransactionRef();
            $ledgerId = DB::table('ledger_entries')->insertGetId([
                'reference' => $txRef,
                'account_id' => $accountId,
                'direction' => 'debit',
                'amount' => $amount,
                'description' => $reason ? "Withdrawal: {$reason}" : "Withdrawal from {$account->account_no}",
                'status' => 'posted',
                'posted_at' => now(),
                'created_by' => $staffId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 3. Cash or Bank Ledger Deduction
            $location = in_array($paymentMode, ['bank', 'upi'], true) ? 'bank' : 'treasury';
            FinanceLedger::cash(
                $location,
                'debit',
                'withdrawal',
                $amount,
                "Withdrawal from {$account->account_no}",
                $staffId,
                'account',
                $accountId
            );

            if ($location === 'bank' && $bankAccountId) {
                DB::table('bank_accounts')->where('id', $bankAccountId)->decrement('current_balance', $amount, ['updated_at' => now()]);
            }

            // 4. Generate Withdrawal Receipt
            $receipt = ReceiptService::generate(
                $member->id,
                $amount,
                'withdrawal',
                $paymentMode,
                $accountId,
                null,
                $txRef,
                $reason,
                $staffId
            );

            // 5. Send Notification
            MemberNotifier::send(
                $member->user_id,
                'withdrawal',
                'Withdrawal Debited',
                "Withdrawal of ₹" . number_format($amount, 2) . " debited from account {$account->account_no}. Receipt: {$receipt->receipt_no}",
                'withdrawal-' . $ledgerId,
                '/accounts'
            );

            return [
                'transaction_ref' => $txRef,
                'receipt_no' => $receipt->receipt_no,
                'new_balance' => round((float) $account->balance - $amount, 2),
            ];
        });
    }

    /**
     * Executes Loan Disbursement from Cash or Bank and generates the EMI schedule (Section 7).
     */
    public static function recordLoanDisbursement(
        int $loanId,
        float $disbursedAmount,
        string $paymentMode = 'cash',
        ?int $bankAccountId = null,
        ?int $staffId = null
    ): array {
        if ($disbursedAmount <= 0) {
            throw new InvalidArgumentException('Disbursed amount must be greater than zero.');
        }

        return DB::transaction(function () use ($loanId, $disbursedAmount, $paymentMode, $bankAccountId, $staffId) {
            $loan = DB::table('loan_applications')->where('id', $loanId)->lockForUpdate()->firstOrFail();

            $rate = (float) ($loan->annual_interest_rate ?: LoanSchedule::defaultRate($loan->loan_type));

            // 1. Generate Repayment/EMI Schedule
            LoanSchedule::generate($loan->id, $disbursedAmount, $rate);

            // 2. Mark Loan Disbursed
            DB::table('loan_applications')->where('id', $loanId)->update([
                'status' => 'disbursed',
                'sanctioned_amount' => $disbursedAmount,
                'updated_at' => now(),
            ]);

            // 3. Cash or Bank Deduction
            $location = in_array($paymentMode, ['bank', 'upi'], true) ? 'bank' : 'treasury';
            FinanceLedger::cash(
                $location,
                'debit',
                'loan_disbursement',
                $disbursedAmount,
                "Disbursement for loan {$loan->application_no}",
                $staffId,
                'loan_application',
                $loanId
            );

            if ($location === 'bank' && $bankAccountId) {
                DB::table('bank_accounts')->where('id', $bankAccountId)->decrement('current_balance', $disbursedAmount, ['updated_at' => now()]);
            }

            // Member Statement / Ledger Entry (if member has an active account)
            $memberAccount = DB::table('accounts')->where('member_id', $loan->member_id)->where('status', 'active')->first();
            if ($memberAccount) {
                DB::table('ledger_entries')->insert([
                    'reference' => SequenceService::nextTransactionRef(),
                    'account_id' => $memberAccount->id,
                    'direction' => 'credit',
                    'amount' => $disbursedAmount,
                    'description' => "Loan Disbursement: {$loan->application_no} ({$paymentMode})",
                    'status' => 'posted',
                    'created_by' => $staffId,
                    'posted_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // 4. Generate Disbursement Receipt
            $receipt = ReceiptService::generate(
                $loan->member_id,
                $disbursedAmount,
                'loan_disbursement',
                $paymentMode,
                null,
                $loanId,
                null,
                'Loan disbursement processed',
                $staffId
            );

            // 5. Member Notification
            $userId = MemberNotifier::memberUserId($loan->member_id);
            MemberNotifier::send(
                $userId,
                'loan',
                'Loan Disbursed',
                "Your loan {$loan->application_no} of ₹" . number_format($disbursedAmount, 2) . " has been disbursed. Receipt: {$receipt->receipt_no}",
                'loan-disbursed-' . $loanId,
                '/loan'
            );

            return [
                'loan_id' => $loanId,
                'application_no' => $loan->application_no,
                'disbursed_amount' => $disbursedAmount,
                'receipt_no' => $receipt->receipt_no,
            ];
        });
    }

    /**
     * Records an EMI Payment with waterfall allocation and auto-updates all books (Sections 8 & 13).
     */
    public static function recordEmiPayment(
        int $loanId,
        float $amount,
        string $paymentMode = 'cash',
        ?int $bankAccountId = null,
        ?string $utr = null,
        ?int $staffId = null
    ): array {
        if ($amount <= 0) {
            throw new InvalidArgumentException('EMI payment amount must be greater than zero.');
        }

        return DB::transaction(function () use ($loanId, $amount, $paymentMode, $bankAccountId, $utr, $staffId) {
            $loan = DB::table('loan_applications')->where('id', $loanId)->lockForUpdate()->firstOrFail();

            // 1. Waterfall Repayment: Penalty -> Interest -> Principal
            $allocation = LoanSchedule::applyRepayment($loanId, $amount);

            // 2. Persist in loan_repayments
            $txRef = SequenceService::nextTransactionRef();
            $repaymentId = DB::table('loan_repayments')->insertGetId([
                'reference' => 'LR' . now()->format('ymd') . str_pad((string) random_int(1, 999999), 6, '0', STR_PAD_LEFT),
                'loan_id' => $loanId,
                'amount' => $amount,
                'principal_component' => $allocation['principal'],
                'interest_component' => $allocation['interest'],
                'penalty_component' => $allocation['penalty'],
                'utr' => $utr ?: $txRef,
                'status' => 'accepted',
                'submitted_by' => $staffId ?: 1,
                'submitted_at' => now(),
                'reviewed_by' => $staffId,
                'reviewed_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 3. Cash or Bank Inflow
            $location = in_array($paymentMode, ['bank', 'upi'], true) ? 'bank' : 'treasury';
            FinanceLedger::cash(
                $location,
                'credit',
                'emi_collection',
                $amount,
                "EMI payment for loan {$loan->application_no}",
                $staffId,
                'loan_repayment',
                $repaymentId
            );

            if ($location === 'bank' && $bankAccountId) {
                DB::table('bank_accounts')->where('id', $bankAccountId)->increment('current_balance', $amount, ['updated_at' => now()]);
            }

            // 4. Profit & Loss Income for Interest & Penalty
            if ($allocation['interest'] > 0) {
                FinanceLedger::profitLoss(
                    'income',
                    'loan_interest',
                    $allocation['interest'],
                    "Interest received on loan {$loan->application_no}",
                    $staffId,
                    'loan_repayment',
                    $repaymentId
                );
            }
            if ($allocation['penalty'] > 0) {
                FinanceLedger::profitLoss(
                    'income',
                    'loan_penalty',
                    $allocation['penalty'],
                    "Penalty fee received on loan {$loan->application_no}",
                    $staffId,
                    'loan_repayment',
                    $repaymentId
                );
            }

            // 5. Generate Official Receipt
            $receipt = ReceiptService::generate(
                $loan->member_id,
                $amount,
                'emi_payment',
                $paymentMode,
                null,
                $loanId,
                $txRef,
                "Principal: ₹{$allocation['principal']}, Interest: ₹{$allocation['interest']}, Penalty: ₹{$allocation['penalty']}",
                $staffId
            );

            // 6. Recalculate Credit Score & Send Notification
            $score = CreditScore::recalculate($loan->member_id);
            $userId = MemberNotifier::memberUserId($loan->member_id);
            MemberNotifier::send(
                $userId,
                'loan',
                'EMI Payment Received',
                "EMI payment of ₹" . number_format($amount, 2) . " accepted for loan {$loan->application_no}. Receipt: {$receipt->receipt_no}",
                'emi-paid-' . $repaymentId,
                '/loan'
            );

            return [
                'repayment_id' => $repaymentId,
                'receipt_no' => $receipt->receipt_no,
                'allocated' => $allocation,
                'credit_score' => $score,
            ];
        });
    }

    /**
     * Records Bank Contra Transfers: Cash to Bank, Bank to Cash, and Inter-Bank (Section 12).
     */
    public static function recordBankTransfer(
        string $type,
        float $amount,
        ?int $sourceBankId = null,
        ?int $targetBankId = null,
        ?string $note = null,
        ?int $staffId = null
    ): array {
        if ($amount <= 0) {
            throw new InvalidArgumentException('Transfer amount must be greater than zero.');
        }

        return DB::transaction(function () use ($type, $amount, $sourceBankId, $targetBankId, $note, $staffId) {
            if ($type === 'cash_to_bank') {
                if (!$targetBankId) throw new InvalidArgumentException('Target bank account is required.');
                FinanceLedger::cash('treasury', 'debit', 'cash_deposit_to_bank', $amount, $note ?: 'Cash deposited to bank', $staffId);
                FinanceLedger::cash('bank', 'credit', 'cash_deposit_to_bank', $amount, $note ?: 'Cash deposited to bank', $staffId, 'bank_account', $targetBankId);
                DB::table('bank_accounts')->where('id', $targetBankId)->increment('current_balance', $amount, ['updated_at' => now()]);
            } elseif ($type === 'bank_to_cash') {
                if (!$sourceBankId) throw new InvalidArgumentException('Source bank account is required.');
                $sourceBank = DB::table('bank_accounts')->where('id', $sourceBankId)->lockForUpdate()->firstOrFail();
                if ((float) $sourceBank->current_balance < $amount) {
                    throw new InvalidArgumentException("Insufficient bank balance in {$sourceBank->bank_name}. Available: ₹" . number_format((float) $sourceBank->current_balance, 2));
                }
                FinanceLedger::cash('bank', 'debit', 'bank_withdrawal_to_cash', $amount, $note ?: 'Cash withdrawn from bank', $staffId, 'bank_account', $sourceBankId);
                FinanceLedger::cash('treasury', 'credit', 'bank_withdrawal_to_cash', $amount, $note ?: 'Cash withdrawn from bank', $staffId);
                DB::table('bank_accounts')->where('id', $sourceBankId)->decrement('current_balance', $amount, ['updated_at' => now()]);
            } elseif ($type === 'bank_to_bank') {
                if (!$sourceBankId || !$targetBankId || $sourceBankId === $targetBankId) {
                    throw new InvalidArgumentException('Distinct source and target bank accounts are required.');
                }
                $sourceBank = DB::table('bank_accounts')->where('id', $sourceBankId)->lockForUpdate()->firstOrFail();
                if ((float) $sourceBank->current_balance < $amount) {
                    throw new InvalidArgumentException("Insufficient bank balance in {$sourceBank->bank_name}. Available: ₹" . number_format((float) $sourceBank->current_balance, 2));
                }
                DB::table('bank_accounts')->where('id', $sourceBankId)->decrement('current_balance', $amount, ['updated_at' => now()]);
                DB::table('bank_accounts')->where('id', $targetBankId)->increment('current_balance', $amount, ['updated_at' => now()]);
                FinanceLedger::cash('bank', 'debit', 'interbank_transfer', $amount, "Transfer to Bank #{$targetBankId}: {$note}", $staffId, 'bank_account', $sourceBankId);
                FinanceLedger::cash('bank', 'credit', 'interbank_transfer', $amount, "Transfer from Bank #{$sourceBankId}: {$note}", $staffId, 'bank_account', $targetBankId);
            } else {
                throw new InvalidArgumentException("Unsupported bank transfer type: {$type}");
            }

            return ['type' => $type, 'amount' => $amount, 'success' => true];
        });
    }

    /**
     * Records a Society Expense with Bill attachment (Section 16).
     */
    public static function recordExpense(
        string $category,
        float $amount,
        string $paymentMode = 'cash',
        ?int $bankAccountId = null,
        ?string $billDisk = null,
        ?string $billPath = null,
        ?string $note = null,
        ?int $staffId = null
    ): int {
        if ($amount <= 0) {
            throw new InvalidArgumentException('Expense amount must be greater than zero.');
        }

        return DB::transaction(function () use ($category, $amount, $paymentMode, $bankAccountId, $billDisk, $billPath, $note, $staffId) {
            $location = in_array($paymentMode, ['bank', 'upi'], true) ? 'bank' : 'treasury';

            // 1. Record in P&L entries
            $pnlId = DB::table('profit_loss_entries')->insertGetId([
                'reference' => SequenceService::nextTransactionRef(),
                'entry_type' => 'expense',
                'category' => $category,
                'amount' => round($amount, 2),
                'bill_disk' => $billDisk,
                'bill_path' => $billPath,
                'note' => $note ?: "Expense: {$category}",
                'occurred_on' => today()->toDateString(),
                'created_by' => $staffId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 2. Deduct from Cash or Bank
            FinanceLedger::cash(
                $location,
                'debit',
                $category,
                $amount,
                $note ?: "Expense: {$category}",
                $staffId,
                'profit_loss_entry',
                $pnlId
            );

            if ($location === 'bank' && $bankAccountId) {
                DB::table('bank_accounts')->where('id', $bankAccountId)->decrement('current_balance', $amount, ['updated_at' => now()]);
            }

            return $pnlId;
        });
    }

    /**
     * Opens an FD Account, creates ledger credit, updates cash/bank, and generates receipt (Section 4).
     */
    public static function openFdAccount(
        int $memberId,
        int $schemeId,
        float $principal,
        float $annualRate,
        int $tenureMonths,
        string $paymentMode = 'cash',
        ?int $bankAccountId = null,
        ?string $openingDate = null,
        ?int $staffId = null
    ): array {
        return DB::transaction(function () use ($memberId, $schemeId, $principal, $annualRate, $tenureMonths, $paymentMode, $bankAccountId, $openingDate, $staffId) {
            $fdCalc = DepositCalculator::calculateFd($principal, $annualRate, $tenureMonths, $openingDate);
            $certNo = SequenceService::nextCertificateNo();

            // Generate account number
            do {
                $accountNo = 'AC' . now()->format('ymd') . str_pad((string) random_int(1, 999999), 6, '0', STR_PAD_LEFT);
            } while (DB::table('accounts')->where('account_no', $accountNo)->exists());

            $accountId = DB::table('accounts')->insertGetId([
                'account_no' => $accountNo,
                'certificate_no' => $certNo,
                'member_id' => $memberId,
                'scheme_id' => $schemeId,
                'principal' => $principal,
                'balance' => $principal,
                'custom_interest_rate' => $annualRate,
                'maturity_amount' => $fdCalc['maturity_amount'],
                'status' => 'active',
                'onboarding_status' => 'active',
                'opened_on' => $fdCalc['opened_on'],
                'matures_on' => $fdCalc['matures_on'],
                'last_interest_posted_on' => $fdCalc['opened_on'],
                'approved_by' => $staffId,
                'approved_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Initial Credit Ledger Entry
            $txRef = SequenceService::nextTransactionRef();
            DB::table('ledger_entries')->insert([
                'reference' => $txRef,
                'account_id' => $accountId,
                'direction' => 'credit',
                'amount' => $principal,
                'description' => "Fixed Deposit Opening: {$certNo}",
                'status' => 'posted',
                'posted_at' => now(),
                'created_by' => $staffId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Cash or Bank Inflow
            $location = in_array($paymentMode, ['bank', 'upi'], true) ? 'bank' : 'treasury';
            FinanceLedger::cash($location, 'credit', 'fd_deposit', $principal, "FD Opening {$accountNo}", $staffId, 'account', $accountId);

            if ($location === 'bank' && $bankAccountId) {
                DB::table('bank_accounts')->where('id', $bankAccountId)->increment('current_balance', $principal, ['updated_at' => now()]);
            }

            // Receipt
            $receipt = ReceiptService::generate(
                $memberId,
                $principal,
                'fd_deposit',
                $paymentMode,
                $accountId,
                null,
                $txRef,
                "Certificate: {$certNo}, Matures on: {$fdCalc['matures_on']}",
                $staffId
            );

            return [
                'account_id' => $accountId,
                'account_no' => $accountNo,
                'certificate_no' => $certNo,
                'maturity_amount' => $fdCalc['maturity_amount'],
                'matures_on' => $fdCalc['matures_on'],
                'receipt_no' => $receipt->receipt_no,
            ];
        });
    }

    /**
     * Opens an RD Account, initializes installment schedule, and processes initial deposit (Section 5).
     */
    public static function openRdAccount(
        int $memberId,
        int $schemeId,
        float $monthlyAmount,
        float $annualRate,
        int $tenureMonths,
        string $paymentMode = 'cash',
        ?int $bankAccountId = null,
        ?string $startDate = null,
        ?int $staffId = null,
        ?string $frequency = null,
        ?int $tenureDays = null
    ): array {
        return DB::transaction(function () use ($memberId, $schemeId, $monthlyAmount, $annualRate, $tenureMonths, $paymentMode, $bankAccountId, $startDate, $staffId, $frequency, $tenureDays) {
            $scheme = DB::table('schemes')->where('id', $schemeId)->firstOrFail();
            $freq = $frequency ?: ($scheme->deposit_frequency ?: 'monthly');

            do {
                $accountNo = 'AC' . now()->format('ymd') . str_pad((string) random_int(1, 999999), 6, '0', STR_PAD_LEFT);
            } while (DB::table('accounts')->where('account_no', $accountNo)->exists());

            $accountId = DB::table('accounts')->insertGetId([
                'account_no' => $accountNo,
                'member_id' => $memberId,
                'scheme_id' => $schemeId,
                'principal' => 0,
                'balance' => 0,
                'custom_interest_rate' => $annualRate,
                'monthly_commitment' => $monthlyAmount,
                'status' => 'active',
                'onboarding_status' => 'active',
                'approved_by' => $staffId,
                'approved_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Generate RD Schedule (Daily or Monthly)
            $rdSchedule = DepositCalculator::generateRdSchedule(
                $accountId,
                $monthlyAmount,
                $tenureMonths,
                $annualRate,
                $startDate,
                $freq,
                $tenureDays
            );

            // Record initial installment payment
            $firstInstallment = DB::table('rd_installments')->where('account_id', $accountId)->where('installment_no', 1)->first();
            if ($firstInstallment) {
                self::payRdInstallment($firstInstallment->id, $paymentMode, $bankAccountId, $staffId, $startDate);
            }

            return [
                'account_id' => $accountId,
                'account_no' => $accountNo,
                'tenure_months' => $tenureMonths,
                'frequency' => $freq,
                'maturity_amount' => $rdSchedule['maturity_amount'],
                'matures_on' => $rdSchedule['matures_on'],
            ];
        });
    }

    /**
     * Pays an RD Installment and marks it paid (Section 5).
     */
    public static function payRdInstallment(
        int $installmentId,
        string $paymentMode = 'cash',
        ?int $bankAccountId = null,
        ?int $staffId = null,
        ?string $paymentDate = null
    ): array {
        return DB::transaction(function () use ($installmentId, $paymentMode, $bankAccountId, $staffId, $paymentDate) {
            $installment = DB::table('rd_installments')->where('id', $installmentId)->lockForUpdate()->firstOrFail();
            $account = DB::table('accounts')->where('id', $installment->account_id)->lockForUpdate()->firstOrFail();
            $member = DB::table('members')->where('id', $account->member_id)->firstOrFail();

            $amount = (float) $installment->amount_due;
            $paidDate = $paymentDate ? \Carbon\Carbon::parse($paymentDate)->startOfDay() : now();

            // 1. Mark installment paid
            DB::table('rd_installments')->where('id', $installmentId)->update([
                'paid_amount' => $amount,
                'status' => 'paid',
                'paid_at' => $paidDate,
                'updated_at' => now(),
            ]);

            // 2. Increment Account balance
            DB::table('accounts')->where('id', $account->id)->increment('balance', $amount, ['updated_at' => now()]);

            // 3. Ledger entry
            $txRef = SequenceService::nextTransactionRef();
            DB::table('ledger_entries')->insert([
                'reference' => $txRef,
                'account_id' => $account->id,
                'direction' => 'credit',
                'amount' => $amount,
                'description' => "RD Installment #{$installment->installment_no} for {$account->account_no}",
                'status' => 'posted',
                'posted_at' => $paidDate,
                'created_by' => $staffId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 4. Cash or Bank Inflow
            $location = in_array($paymentMode, ['bank', 'upi'], true) ? 'bank' : 'treasury';
            FinanceLedger::cash($location, 'credit', 'rd_installment', $amount, "RD Installment {$account->account_no}", $staffId, 'account', $account->id);

            if ($location === 'bank' && $bankAccountId) {
                DB::table('bank_accounts')->where('id', $bankAccountId)->increment('current_balance', $amount, ['updated_at' => now()]);
            }

            // 5. Generate Receipt
            $receipt = ReceiptService::generate(
                $member->id,
                $amount,
                'rd_installment',
                $paymentMode,
                $account->id,
                null,
                $txRef,
                "RD Installment #{$installment->installment_no}",
                $staffId
            );

            // 6. Recalculate Credit Score
            $score = CreditScore::recalculate($member->id);

            return [
                'installment_id' => $installmentId,
                'receipt_no' => $receipt->receipt_no,
                'receipt_id' => $receipt->id,
                'credit_score' => $score,
            ];
        });
    }

    /**
     * Pays multiple consecutive RD installments in bulk (e.g. advance payment for next 10 days)
     */
    public static function payBulkRdInstallments(
        int $accountId,
        int $installmentCount,
        string $paymentMode = 'cash',
        ?int $bankAccountId = null,
        ?int $staffId = null,
        ?string $paymentDate = null
    ): array {
        return DB::transaction(function () use ($accountId, $installmentCount, $paymentMode, $bankAccountId, $staffId, $paymentDate) {
            $account = DB::table('accounts')->where('id', $accountId)->lockForUpdate()->firstOrFail();
            $member = DB::table('members')->where('id', $account->member_id)->firstOrFail();
            $scheme = DB::table('schemes')->where('id', $account->scheme_id)->first();
            $isDaily = ($scheme->deposit_frequency ?? '') === 'daily';

            // Find next unpaid/partially paid installments
            $installments = DB::table('rd_installments')
                ->where('account_id', $accountId)
                ->whereIn('status', ['scheduled', 'due', 'overdue', 'partial'])
                ->orderBy('installment_no', 'asc')
                ->limit($installmentCount)
                ->lockForUpdate()
                ->get();

            if ($installments->isEmpty()) {
                throw new \InvalidArgumentException("No unpaid installments found to pay for this account.");
            }

            $totalAmount = 0.0;
            $installmentNos = [];
            $dueDates = [];
            $paidDate = $paymentDate ? \Carbon\Carbon::parse($paymentDate)->startOfDay() : now();

            foreach ($installments as $inst) {
                $due = (float) $inst->amount_due + (float) $inst->penalty_due - (float) $inst->paid_amount;
                $totalAmount += $due;
                $installmentNos[] = $inst->installment_no;
                $dueDates[] = $inst->due_on;

                DB::table('rd_installments')->where('id', $inst->id)->update([
                    'paid_amount' => $inst->amount_due,
                    'status' => 'paid',
                    'paid_at' => $paidDate,
                    'updated_at' => now(),
                ]);
            }

            $count = count($installments);
            $firstNo = min($installmentNos);
            $lastNo = max($installmentNos);
            $firstDue = min($dueDates);
            $lastDue = max($dueDates);
            $rangeDesc = $count > 1 ? "#{$firstNo} to #{$lastNo}" : "#{$firstNo}";
            $unitLabel = $isDaily ? 'days' : 'installments';

            // 1. Increment Account balance
            DB::table('accounts')->where('id', $account->id)->increment('balance', $totalAmount, ['updated_at' => now()]);

            // 2. Ledger entry
            $txRef = SequenceService::nextTransactionRef();
            DB::table('ledger_entries')->insert([
                'reference' => $txRef,
                'account_id' => $account->id,
                'direction' => 'credit',
                'amount' => $totalAmount,
                'description' => "RD Collection ({$count} {$unitLabel}: {$rangeDesc}) for {$account->account_no}",
                'status' => 'posted',
                'posted_at' => $paidDate,
                'created_by' => $staffId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 3. Cash or Bank Inflow
            $location = in_array($paymentMode, ['bank', 'upi'], true) ? 'bank' : 'treasury';
            FinanceLedger::cash($location, 'credit', 'rd_installment', $totalAmount, "RD {$count} {$unitLabel} ({$rangeDesc}) {$account->account_no}", $staffId, 'account', $account->id);

            if ($location === 'bank' && $bankAccountId) {
                DB::table('bank_accounts')->where('id', $bankAccountId)->increment('current_balance', $totalAmount, ['updated_at' => now()]);
            }

            // 4. Generate Receipt
            $receipt = ReceiptService::generate(
                $member->id,
                $totalAmount,
                'rd_installment',
                $paymentMode,
                $account->id,
                null,
                $txRef,
                "Advance RD collection for {$count} {$unitLabel} ({$rangeDesc}, Due: {$firstDue} to {$lastDue})",
                $staffId
            );

            // 5. Recalculate Credit Score
            $score = CreditScore::recalculate($member->id);

            return [
                'account_id' => $accountId,
                'account_no' => $account->account_no,
                'count' => $count,
                'range' => $rangeDesc,
                'total_amount' => $totalAmount,
                'first_due' => $firstDue,
                'last_due' => $lastDue,
                'receipt_no' => $receipt->receipt_no,
                'receipt_id' => $receipt->id,
                'credit_score' => $score,
            ];
        });
    }
}
