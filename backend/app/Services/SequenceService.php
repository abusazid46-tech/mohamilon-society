<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

final class SequenceService
{
    /**
     * Generates a sequential Member ID formatted as MMS-000001 (Section 2.1).
     */
    public static function nextMemberNo(): string
    {
        $latest = DB::table('members')
            ->where('member_no', 'like', 'MMS-%')
            ->orderByDesc('id')
            ->value('member_no');

        if ($latest && preg_match('/^MMS-(\d+)$/', $latest, $matches)) {
            $next = ((int) $matches[1]) + 1;
        } else {
            $count = (int) DB::table('members')->count();
            $next = max(1, $count + 1);
        }

        do {
            $candidate = 'MMS-' . str_pad((string) $next, 6, '0', STR_PAD_LEFT);
            $next++;
        } while (DB::table('members')->where('member_no', $candidate)->exists());

        return $candidate;
    }

    /**
     * Generates a sequential Receipt Number formatted as MMS/RCP/YYYY/00001 (Section 18).
     */
    public static function nextReceiptNo(): string
    {
        $year = now()->format('Y');
        $prefix = "MMS/RCP/{$year}/";
        $count = (int) DB::table('receipts')
            ->where('receipt_no', 'like', "{$prefix}%")
            ->count();
        $next = $count + 1;

        do {
            $candidate = $prefix . str_pad((string) $next, 5, '0', STR_PAD_LEFT);
            $next++;
        } while (DB::table('receipts')->where('receipt_no', $candidate)->exists());

        return $candidate;
    }

    /**
     * Generates a unique Transaction Reference formatted as TXN-YYYY-000001 (Section 13).
     */
    public static function nextTransactionRef(): string
    {
        $year = now()->format('Y');
        $prefix = "TXN-{$year}-";
        $count = (int) DB::table('ledger_entries')
            ->where('reference', 'like', "{$prefix}%")
            ->count();
        $next = $count + 1;

        do {
            $candidate = $prefix . str_pad((string) $next, 6, '0', STR_PAD_LEFT);
            $next++;
        } while (DB::table('ledger_entries')->where('reference', $candidate)->exists());

        return $candidate;
    }

    /**
     * Generates an FD Certificate Number formatted as FD-YYYY-000001 (Section 4).
     */
    public static function nextCertificateNo(): string
    {
        $year = now()->format('Y');
        $prefix = "FD-{$year}-";
        $count = (int) DB::table('accounts')
            ->where('certificate_no', 'like', "{$prefix}%")
            ->count();
        $next = $count + 1;

        do {
            $candidate = $prefix . str_pad((string) $next, 6, '0', STR_PAD_LEFT);
            $next++;
        } while (DB::table('accounts')->where('certificate_no', $candidate)->exists());

        return $candidate;
    }

    /**
     * Generates a sequential Loan Application Number formatted as MMS/LN/YYYY/00001 (Section 6).
     */
    public static function nextLoanApplicationNo(): string
    {
        $year = now()->format('Y');
        $prefix = "MMS/LN/{$year}/";
        $count = (int) DB::table('loan_applications')
            ->where('application_no', 'like', "{$prefix}%")
            ->count();
        $next = $count + 1;

        do {
            $candidate = $prefix . str_pad((string) $next, 5, '0', STR_PAD_LEFT);
            $next++;
        } while (DB::table('loan_applications')->where('application_no', $candidate)->exists());

        return $candidate;
    }
}
