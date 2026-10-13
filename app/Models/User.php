<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Manager\Image\ImageUploadManager;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    public const STATUS_ACTIVE = 1;
    public const STATUS_INACTIVE = 0;
    public const STATUS_PENDING = 2;
    public const STATUS_BANNED = 3;

    public const STATUS_LIST = [
        self::STATUS_ACTIVE   => 'Active',
        self::STATUS_INACTIVE => 'Inactive',
        self::STATUS_PENDING  => 'Pending',
        self::STATUS_BANNED   => 'Banned',
    ];

    public const IMAGE_PATH = '/uploads/users/';
    public const PROFILE_PHOTO_WIDTH = 300;
    public const PROFILE_PHOTO_HEIGHT = 300;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    final public function storeUser(Request $request): Model|Authenticatable
    {
        return self::query()->create($this->prepareData($request));
    }

    /**
     * @param string $column
     * @param string|null $value
     * @return Model|null
     */
    final public function findUserByColumn(string $column, string|null $value):Model|null
    {
        if ($value) {
            return self::query()->where($column, $value)->first();
        }
        return null;
    }

    final public function orders(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Order::class);
    }

    public function addresses(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(UserAddress::class)->orderByDesc('is_default');
    }

    public function defaultAddress(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(UserAddress::class)->where('is_default', true)->latestOfMany();
    }

    private function prepareData(Request $request): array
    {
        $data = [
            'name'     => $request->input('name'),
            'email'    => $request->input('email'),
            'password' => Hash::make($request->input('password')),
            'phone'    => $request->input('phone'),
            'status'   => self::STATUS_PENDING,
        ];

        if ($request->hasFile('photo')) {
            $data['photo'] = (new ImageUploadManager())
                ->file($request->file('photo'))
                ->name(generateUniqueImageName($request->input('name')))
                ->path(self::IMAGE_PATH)
                ->width(self::PROFILE_PHOTO_WIDTH)
                ->height(self::PROFILE_PHOTO_HEIGHT)
                ->upload();
        }
        return $data;
    }
}
