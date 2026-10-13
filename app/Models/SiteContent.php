<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class SiteContent extends Model
{
    use HasFactory;

    protected $guarded = [];

    public const TYPE_TEXT     = 'text';
    public const TYPE_TEXTAREA = 'textarea';
    public const TYPE_HTML     = 'html';

    public const TYPE_LIST = [
        self::TYPE_TEXT     => 'Short Text',
        self::TYPE_TEXTAREA => 'Plain Textarea',
        self::TYPE_HTML     => 'Rich Text (HTML)',
    ];

    final public function getContents()
    {
        return self::query()
            ->orderBy('label')
            ->paginate(50);
    }

    final public function storeContent(Request $request): Model
    {
        return self::query()->create($this->prepareData($request));
    }

    final public function updateContent(Request $request): bool
    {
        return $this->update($this->prepareData($request, isUpdate: true));
    }

    private function prepareData(Request $request, bool $isUpdate = false): array
    {
        $data = [
            'label' => $request->input('label'),
            'value' => $request->input('value'),
            'type'  => $request->input('type', self::TYPE_TEXT),
        ];

        if (!$isUpdate) {
            $data['key'] = $request->input('key');
        }

        return $data;
    }

    /**
     * Get content value by key — use anywhere in the app.
     */
    final public static function get(string $key, string $default = ''): string
    {
        $item = self::query()->where('key', $key)->first();
        return $item ? ($item->value ?? $default) : $default;
    }
}
