<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'purpose', 'date', 'amount', 'status',
        'category', 'note', 'attachment', 'attachment_title',
    ];

    protected $casts = [
        'date'   => 'date',
        'amount' => 'decimal:2',
        'status' => 'integer',
    ];

    // ─── Constants ───────────────────────────────────────────────

    public const STATUS_APPROVED = 1;
    public const STATUS_PENDING  = 2;
    public const STATUS_REJECTED = 3;

    public const STATUS_LIST = [
        self::STATUS_APPROVED => 'Approved',
        self::STATUS_PENDING  => 'Pending',
        self::STATUS_REJECTED => 'Rejected',
    ];

    public const STATUS_COLORS = [
        self::STATUS_APPROVED => 'success',
        self::STATUS_PENDING  => 'warning',
        self::STATUS_REJECTED => 'danger',
    ];

    public const CATEGORY_LIST = [
        'office'       => 'Office Supplies',
        'transport'    => 'Transport',
        'salary'       => 'Salary',
        'marketing'    => 'Marketing',
        'utilities'    => 'Utilities',
        'product'      => 'Product Purchase',
        'maintenance'  => 'Maintenance',
        'other'        => 'Other',
    ];

    // ─── Relationships ───────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ─── Scopes ──────────────────────────────────────────────────

    public function scopeByStatus(Builder $q, int $status): Builder
    {
        return $q->where('status', $status);
    }

    // ─── Helpers ─────────────────────────────────────────────────

    public function getStatusLabel(): string
    {
        return self::STATUS_LIST[$this->status] ?? 'Unknown';
    }

    public function getStatusColor(): string
    {
        return self::STATUS_COLORS[$this->status] ?? 'secondary';
    }
}
