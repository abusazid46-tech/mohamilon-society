<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

final class MemberNotifier
{
    public static function send(int $userId, string $category, string $title, string $message, ?string $dedupeKey = null, ?string $actionUrl = null): void
    {
        DB::table('member_notifications')->insertOrIgnore([
            'user_id' => $userId, 'dedupe_key' => $dedupeKey, 'category' => $category,
            'title' => $title, 'message' => $message, 'action_url' => $actionUrl,
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    public static function memberUserId(int $memberId): int
    {
        return (int) DB::table('members')->where('id', $memberId)->value('user_id');
    }
}
