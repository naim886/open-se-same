<?php

namespace App\Models;

use App\Manager\Constant\GlobalConstants;
use Database\Factories\AttributeNameFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class AttributeName extends Model
{
    /** @use HasFactory<AttributeNameFactory> */
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public const STATUS_ACTIVE = 1;
    public const STATUS_INACTIVE = 2;

    public const STATUS_LIST = [
        self::STATUS_ACTIVE   => 'Active',
        self::STATUS_INACTIVE => 'Inactive',
    ];

    final public function getAttributeNames()
    {
        return self::query()->orderBy('name', 'asc')->paginate(GlobalConstants::DEFAULT_PAGINATION);
    }

    final public function storeAttributeName(Request $request): Model
    {
        return self::query()->create($this->prepareData($request));
    }

    final public function updateAttributeName(Request $request): bool
    {
        return $this->update($this->prepareData($request));
    }

    private function prepareData(Request $request): array
    {
        return [
            'name'   => $request->input('name'),
            'status' => $request->input('status'),
        ];
    }

    final public function getAttributeNameAssoc(): Collection
    {
        return self::query()
            ->where('status', self::STATUS_ACTIVE)
            ->orderBy('name', 'asc')
            ->pluck('name', 'id');
    }

    final public function getStatusText(): string
    {
        return self::STATUS_LIST[$this->status] ?? 'Unknown';
    }

    final public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function attributeValues()
    {
        return $this->hasMany(AttributeValue::class);
    }
}
