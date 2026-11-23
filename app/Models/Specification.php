<?php

namespace App\Models;

use App\Manager\Constant\GlobalConstants;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class Specification extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public const STATUS_ACTIVE = 1;
    public const STATUS_INACTIVE = 2;

    public const STATUS_LIST = [
        self::STATUS_ACTIVE   => 'Active',
        self::STATUS_INACTIVE => 'Inactive',
    ];

    final public function getSpecifications(?Request $request = null)
    {
        $search = trim((string) ($request?->input('search', '') ?? ''));

        return self::query()
            ->when($search !== '', fn ($query) => $query->where('specifications_name', 'like', "%{$search}%"))
            ->when($request && $request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
            ->orderBy('specifications_name', 'asc')
            ->paginate(GlobalConstants::DEFAULT_PAGINATION)
            ->withQueryString();
    }

    final public function storeSpecification(Request $request): Model
    {
        return self::query()->create($this->prepareData($request));
    }

    final public function updateSpecification(Request $request): bool
    {
        return $this->update($this->prepareData($request));
    }

    private function prepareData(Request $request): array
    {
        return [
            'specifications_name' => $request->input('specifications_name'),
            'status'             => $request->input('status'),
        ];
    }

    final public function getSpecificationAssoc(): Collection
    {
        return self::query()
            ->where('status', self::STATUS_ACTIVE)
            ->orderBy('specifications_name', 'asc')
            ->pluck('specifications_name', 'id');
    }

    final public function getStatusText(): string
    {
        return self::STATUS_LIST[$this->status] ?? 'Unknown';
    }

    final public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }
}
