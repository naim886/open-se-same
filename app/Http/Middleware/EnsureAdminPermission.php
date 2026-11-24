<?php

namespace App\Http\Middleware;

use App\Support\AdminPermission;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Authorises a backend request against the permission registry.
 *
 * The permission is derived from the route name, so every admin route is
 * covered automatically (resources included) without sprinkling dozens of
 * `permission:` middleware declarations through routes/web.php.
 */
class EnsureAdminPermission
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        $permission = AdminPermission::resolve($request->route());

        // Unmapped backend route: fail closed, only the super role may pass.
        if ($permission === null) {
            abort_unless($user->hasRole(AdminPermission::superRole()), 403);

            return $next($request);
        }

        // Prime the relations once. The sidebar renders ~30 @can checks and
        // without this each one would query the pivot tables again.
        $user->loadMissing(['roles.permissions', 'permissions']);

        abort_unless(
            $user->can($permission),
            403,
            'Your role is missing the "'.$permission.'" permission.'
        );

        return $next($request);
    }
}
