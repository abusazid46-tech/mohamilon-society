<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class FinanceLedger
{
    public static function cash(
        string $location,
        string $direction,
        string $category,
        float $amount,
        string $note,
        ?int $createdBy = null,
        ?string $sourceType = null,
        ?int $sourceId = null,
        ?string $occurredOn = null
    ): int {
        return DB::table('cash_ledger_entries')->insertGetId([
            'reference' => (string) Str::uuid(),
            'location' => $location,
            'direction' => $direction,
            'category' => $category,
            'amount' => round($amount, 2),
            'source_type' => $sourceType,
            'source_id' => $sourceId,
            'note' => $note,
            'occurred_on' => $occurredOn ?: today()->toDateString(),
            'created_by' => $createdBy,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public static function profitLoss(
        string $type,
        string $category,
        float $amount,
        string $note,
        ?int $createdBy = null,
        ?string $sourceType = null,
        ?int $sourceId = null,
        ?string $occurredOn = null
    ): int {
        return DB::table('profit_loss_entries')->insertGetId([
            'reference' => (string) Str::uuid(),
            'entry_type' => $type,
            'category' => $category,
            'amount' => round($amount, 2),
            'source_type' => $sourceType,
            'source_id' => $sourceId,
            'note' => $note,
            'occurred_on' => $occurredOn ?: today()->toDateString(),
            'created_by' => $createdBy,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public static function cashBalance(string $location): float
    {
        return (float) DB::table('cash_ledger_entries')->where('location', $location)
            ->selectRaw("COALESCE(SUM(CASE WHEN direction='credit' THEN amount ELSE -amount END),0) AS balance")
            ->value('balance');
    }

    public static function profitLossSummary(string $from, string $to): array
    {
        $row = DB::table('profit_loss_entries')->whereBetween('occurred_on', [$from, $to])
            ->selectRaw("COALESCE(SUM(CASE WHEN entry_type='income' THEN amount ELSE 0 END),0) AS income")
            ->selectRaw("COALESCE(SUM(CASE WHEN entry_type='expense' THEN amount ELSE 0 END),0) AS expense")
            ->first();
        $income = (float) ($row->income ?? 0);
        $expense = (float) ($row->expense ?? 0);
        return ['income' => $income, 'expense' => $expense, 'net' => $income - $expense];
    }
}
