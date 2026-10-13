<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class AccountTransaction extends Model
{
    protected $fillable = [
        'account', 'type', 'amount', 'purpose', 'reference', 'note', 'date', 'user_id',
    ];

    protected $casts = [
        'date'   => 'date',
        'amount' => 'decimal:2',
    ];

    // ── Constants ────────────────────────────────────────────────

    public const ACCOUNT_BKASH = 'bkash';
    public const ACCOUNT_BANK  = 'bank';
    public const ACCOUNT_CASH  = 'cash';

    public const ACCOUNT_LIST = [
        self::ACCOUNT_BKASH => 'bKash',
        self::ACCOUNT_BANK  => 'Bank',
        self::ACCOUNT_CASH  => 'Cash',
    ];

    public const ACCOUNT_ICONS = [
        self::ACCOUNT_BKASH => 'fas fa-mobile-alt',
        self::ACCOUNT_BANK  => 'fas fa-university',
        self::ACCOUNT_CASH  => 'fas fa-money-bill-wave',
    ];

    public const ACCOUNT_COLORS = [
        self::ACCOUNT_BKASH => 'danger',
        self::ACCOUNT_BANK  => 'primary',
        self::ACCOUNT_CASH  => 'success',
    ];

    public const TYPE_CREDIT = 'credit';
    public const TYPE_DEBIT  = 'debit';

    // ── Relationships ────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ── Scopes ──────────────────────────────────────────────────

    public function scopeByAccount(Builder $q, string $account): Builder
    {
        return $q->where('account', $account);
    }

    // ── Helpers ──────────────────────────────────────────────────

    /**
     * Get current balance for a given account (credits - debits).
     */
    public static function balanceFor(string $account): float
    {
        $credits = static::where('account', $account)->where('type', self::TYPE_CREDIT)->sum('amount');
        $debits  = static::where('account', $account)->where('type', self::TYPE_DEBIT)->sum('amount');
        return (float) $credits - (float) $debits;
    }

    /**
     * Get total balance across all accounts.
     */
    public static function totalBalance(): float
    {
        $credits = static::where('type', self::TYPE_CREDIT)->sum('amount');
        $debits  = static::where('type', self::TYPE_DEBIT)->sum('amount');
        return (float) $credits - (float) $debits;
    }
}
