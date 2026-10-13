<?php

namespace App\Models;

use Database\Factories\NewsLetterFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class NewsLetter extends Model
{
    /** @use HasFactory<NewsLetterFactory> */
    use HasFactory;

    protected $guarded = [];

    /**
     * @param Request $request
     * @return Model
     */

    public const STATUS_ACTIVE = 1;
    public const STATUS_INACTIVE = 0;

    final public function storeNewsLetter(Request $request): Model
    {
        return self::query()->create($this->prepareData($request));
    }

    /**
     * @param Request $request
     * @return array
     */
    private function prepareData(Request $request): array
    {
        return [
            'email'  => $request->input('email'),
            'status' => $request->input('status', self::STATUS_ACTIVE),
        ];
    }
}
