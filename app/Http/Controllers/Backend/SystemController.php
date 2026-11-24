<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\PermissionRegistrar;
use Throwable;

class SystemController extends Controller
{
    /**
     * Flushes every application cache from the admin topbar: compiled views,
     * config, routes, events and the data cache, plus Spatie's permission map.
     */
    public function clearCache(Request $request): JsonResponse|RedirectResponse
    {
        try {
            Artisan::call('optimize:clear');
            app(PermissionRegistrar::class)->forgetCachedPermissions();
        } catch (Throwable $e) {
            report($e);

            $message = 'Could not clear the cache: '.$e->getMessage();

            return $request->wantsJson()
                ? response()->json(['ok' => false, 'message' => $message], 500)
                : back()->with('error', $message);
        }

        $message = 'Cache cleared — config, routes, views, events, data and permissions.';

        return $request->wantsJson()
            ? response()->json(['ok' => true, 'message' => $message])
            : back()->with('success', $message);
    }
}
