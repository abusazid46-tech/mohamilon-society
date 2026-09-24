<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

final class ReceiptService
{
    /**
     * Issues and persists a receipt automatically following a payment event (Section 18).
     */
    public static function generate(
        int $memberId,
        float $amount,
        string $category,
        string $paymentMode = 'cash',
        ?int $accountId = null,
        ?int $loanId = null,
        ?string $transactionRef = null,
        ?string $notes = null,
        ?int $issuedBy = null
    ): object {
        $receiptNo = SequenceService::nextReceiptNo();
        $member = DB::table('members')->join('users', 'users.id', '=', 'members.user_id')
            ->where('members.id', $memberId)
            ->select('members.member_no', 'users.name')
            ->first();

        $payerName = $member ? $member->name : 'Society Member';

        $id = DB::table('receipts')->insertGetId([
            'receipt_no' => $receiptNo,
            'member_id' => $memberId,
            'account_id' => $accountId,
            'loan_id' => $loanId,
            'transaction_reference' => $transactionRef,
            'category' => $category,
            'amount' => round($amount, 2),
            'payment_mode' => in_array($paymentMode, ['cash', 'bank', 'upi'], true) ? $paymentMode : 'cash',
            'payer_name' => $payerName,
            'notes' => $notes,
            'issued_by' => $issuedBy,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return DB::table('receipts')->where('id', $id)->first();
    }

    /**
     * Retrieves rich receipt details for printing, PDF generation, or WhatsApp sharing.
     */
    public static function getDetails(string|int $idOrNo): ?object
    {
        $query = DB::table('receipts')
            ->join('members', 'members.id', '=', 'receipts.member_id')
            ->join('users', 'users.id', '=', 'members.user_id')
            ->leftJoin('accounts', 'accounts.id', '=', 'receipts.account_id')
            ->leftJoin('loan_applications', 'loan_applications.id', '=', 'receipts.loan_id')
            ->leftJoin('users as staff', 'staff.id', '=', 'receipts.issued_by')
            ->select(
                'receipts.*',
                'members.member_no',
                'users.name as member_name',
                'users.phone as member_phone',
                'accounts.account_no',
                'loan_applications.application_no',
                'staff.name as issued_by_name'
            );

        if (is_numeric($idOrNo)) {
            $query->where('receipts.id', (int) $idOrNo);
        } else {
            $query->where('receipts.receipt_no', (string) $idOrNo);
        }

        return $query->first();
    }
}
