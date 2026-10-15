<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

/**
 * Creates — or repairs — the bootstrap administrator account.
 *
 * The login form accepts either an email or an 11-digit phone number
 * (see AuthenticatedSessionController), so this account is keyed on the phone
 * number and can be signed into with it.
 *
 * Safe to re-run: the phone number is the lookup key, so an existing account is
 * updated rather than duplicated.
 */
class AdminUserSeeder extends Seeder
{
    public const PHONE    = '01719123886';
    public const PASSWORD = '12345678';
    public const EMAIL    = 'admin@opensesame.com';
    public const NAME     = 'Administrator';
    public const ROLE     = 'Super Admin';

    public function run(): void
    {
        $user = User::firstOrNew(['phone' => self::PHONE]);

        // users.email is NOT NULL and unique, and phone logins are authenticated
        // against it, so a brand new account needs one. An existing account keeps
        // whatever email it already has.
        if (! $user->exists) {
            $user->email = self::EMAIL;
            $user->name  = self::NAME;
        }

        $user->password          = self::PASSWORD;                  // hashed by the model's cast
        $user->status            = User::STATUS_ACTIVE;             // login rejects non-active users
        $user->email_verified_at = $user->email_verified_at ?? now();
        $user->save();

        // Super Admin is needed for backend access, and for the post-login
        // redirect to send this account to the dashboard rather than the
        // customer panel.
        if (! Role::where('name', self::ROLE)->exists()) {
            Role::create(['name' => self::ROLE]);
        }

        $user->syncRoles([self::ROLE]);
    }
}
