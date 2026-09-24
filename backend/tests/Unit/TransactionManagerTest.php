<?php

namespace Tests\Unit;

use App\Services\TransactionManager;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class TransactionManagerTest extends TestCase
{
    public function test_record_deposit_rejects_zero_or_negative_amount(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Deposit amount must be greater than zero.');

        TransactionManager::recordDeposit(1, 0.0);
    }

    public function test_record_withdrawal_rejects_negative_amount(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Withdrawal amount must be greater than zero.');

        TransactionManager::recordWithdrawal(1, -50.0);
    }

    public function test_record_loan_disbursement_rejects_zero_amount(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Disbursed amount must be greater than zero.');

        TransactionManager::recordLoanDisbursement(1, 0.0);
    }

    public function test_record_emi_payment_rejects_zero_amount(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('EMI payment amount must be greater than zero.');

        TransactionManager::recordEmiPayment(1, 0.0);
    }

    public function test_bank_transfer_rejects_invalid_type(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported bank transfer type: invalid_type');

        TransactionManager::recordBankTransfer('invalid_type', 1000.0);
    }

    public function test_bank_transfer_requires_target_for_cash_to_bank(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Target bank account is required.');

        TransactionManager::recordBankTransfer('cash_to_bank', 5000.0, null, null);
    }

    public function test_bank_to_bank_transfer_rejects_identical_accounts(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Distinct source and target bank accounts are required.');

        TransactionManager::recordBankTransfer('bank_to_bank', 5000.0, 1, 1);
    }
}
