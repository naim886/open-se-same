<?php

namespace App\Models;

use App\Manager\Constant\GlobalConstants;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class ContactMessage extends Model
{
    use HasFactory;

    protected $guarded = [];

    public const STATUS_UNREAD = 1;
    public const STATUS_READ   = 2;

    public const STATUS_LIST = [
        self::STATUS_UNREAD => 'Unread',
        self::STATUS_READ   => 'Read',
    ];

    public const STATUS_COLORS = [
        self::STATUS_UNREAD => 'warning',
        self::STATUS_READ   => 'secondary',
    ];

    final public function getMessages(Request $request)
    {
        $query = self::query()->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%");
            });
        }

        return $query->paginate(GlobalConstants::DEFAULT_PAGINATION);
    }

    final public function markAsRead(): bool
    {
        if ($this->status === self::STATUS_UNREAD) {
            return $this->update(['status' => self::STATUS_READ]);
        }
        return true;
    }

    final public function isUnread(): bool
    {
        return $this->status === self::STATUS_UNREAD;
    }

    final public static function storeMessage(Request $request): self
    {
        return self::query()->create([
            'name'    => $request->input('name'),
            'email'   => $request->input('email'),
            'phone'   => $request->input('phone'),
            'subject' => $request->input('subject'),
            'message' => $request->input('message'),
            'status'  => self::STATUS_UNREAD,
        ]);
    }

    final public static function unreadCount(): int
    {
        return self::where('status', self::STATUS_UNREAD)->count();
    }
}
