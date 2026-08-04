<?php

namespace App\Services;

use App\Models\User;

final class StaffPermissions
{
    public const DEFINITIONS = [
        'member.approve' => 'Approve or reject members',
        'kyc.approve' => 'Approve or reject KYC',
        'account.create' => 'Create member accounts',
        'account.approve' => 'Approve account requests',
        'account.reject' => 'Reject account requests',
        'payment.accept' => 'Accept or reject payments',
        'transaction.create' => 'Post account transactions',
        'loan.offer' => 'Create individual loan offers',
        'loan.approve' => 'Approve loan applications',
        'loan.reject' => 'Reject loan applications',
        'loan.repayment' => 'Accept or reject loan repayments',
        'loan.fine' => 'Set or waive overdue EMI fines',
        'interest.manage' => 'Set member-specific interest rates',
        'account.close' => 'Manage maturity and account closures',
        'cash.manage' => 'Post bank and treasury cash entries',
        'profit_loss.manage' => 'Post income and expense adjustments',
        'reports.view' => 'View and export operational reports',
    ];

    public static function defaults(string $role): array
    {
        return match ($role) {
            'super_admin' => array_keys(self::DEFINITIONS),
            'manager' => ['member.approve','kyc.approve','account.create','account.approve','account.reject','payment.accept','transaction.create','loan.offer','loan.approve','loan.reject','loan.repayment','loan.fine','interest.manage','account.close','cash.manage','profit_loss.manage','reports.view'],
            'secretary' => ['member.approve','kyc.approve'],
            'president' => ['loan.offer','loan.approve','loan.reject'],
            'agent' => [],
            default => [],
        };
    }

    public static function for(User $user): array
    {
        if ($user->role === 'super_admin') {
            return array_keys(self::DEFINITIONS);
        }
        $stored = $user->getRawOriginal('permissions') === null
            ? self::defaults($user->role)
            : (is_array($user->permissions) ? $user->permissions : []);
        return array_values(array_intersect($stored, array_keys(self::DEFINITIONS)));
    }

    public static function allows(User $user, string $permission): bool
    {
        return in_array($permission, self::for($user), true);
    }
}
