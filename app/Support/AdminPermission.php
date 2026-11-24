<?php

namespace App\Support;

use Illuminate\Routing\Route;

/**
 * Read-only helper around config/admin_permissions.php.
 *
 * The whole registry is static, so every lookup is memoised for the process:
 * rendering a 30-module permission matrix or resolving 150 routes costs no
 * extra config/DB work.
 */
final class AdminPermission
{
    /** @var array<string, mixed>|null */
    private static ?array $config = null;

    /** @var array<string, array{label:string, icon:string, permissions:array<string,string>}>|null */
    private static ?array $modules = null;

    /** @var array<string, string>|null permission => module */
    private static ?array $moduleIndex = null;

    /** @var array<string, string>|null permission => label */
    private static ?array $labels = null;

    public static function superRole(): string
    {
        return (string) self::config('super_role', 'Super Admin');
    }

    public static function guard(): string
    {
        return (string) self::config('guard', 'web');
    }

    /**
     * @return array<string, array{label:string, icon:string, permissions:array<string,string>}>
     */
    public static function modules(): array
    {
        if (self::$modules === null) {
            self::$modules = [];

            foreach ((array) self::config('modules', []) as $key => $module) {
                self::$modules[$key] = [
                    'label' => (string) ($module['label'] ?? ucfirst((string) $key)),
                    'icon' => (string) ($module['icon'] ?? 'circle'),
                    'permissions' => (array) ($module['permissions'] ?? []),
                ];
            }
        }

        return self::$modules;
    }

    /**
     * Flat list of every permission name defined by the registry.
     *
     * @return list<string>
     */
    public static function names(): array
    {
        if (self::$labels === null) {
            self::$labels = [];
            self::$moduleIndex = [];

            foreach (self::modules() as $module => $definition) {
                foreach ($definition['permissions'] as $action => $label) {
                    $name = $module.'.'.$action;
                    self::$labels[$name] = (string) $label;
                    self::$moduleIndex[$name] = (string) $module;
                }
            }
        }

        return array_keys(self::$labels);
    }

    public static function exists(string $permission): bool
    {
        self::names();

        return isset(self::$labels[$permission]);
    }

    public static function moduleOf(string $permission): ?string
    {
        self::names();

        return self::$moduleIndex[$permission] ?? null;
    }

    public static function label(string $permission): string
    {
        self::names();

        return self::$labels[$permission] ?? $permission;
    }

    public static function moduleLabel(string $module): string
    {
        return self::modules()[$module]['label'] ?? ucfirst(str_replace('_', ' ', $module));
    }

    public static function moduleIcon(string $module): string
    {
        return self::modules()[$module]['icon'] ?? 'circle';
    }

    /**
     * Display order for roles: staff roles follow the order in
     * default_roles (Super Admin, Admin, Manager, ...), unknown roles last.
     */
    public static function rolePriority(string $role): int
    {
        $order = array_keys((array) self::config('default_roles', []));
        $index = array_search($role, $order, true);

        return $index === false ? 1000 : (int) $index;
    }

    /**
     * Resolve the permission required to reach a backend route.
     * Explicit entries win; otherwise "{resource}.{action}" is mapped onto
     * the "{module}.{permission}" registry.
     */
    public static function resolve(?Route $route): ?string
    {
        if (! $route || ! ($name = $route->getName())) {
            return null;
        }

        $explicit = (array) self::config('route_permissions', []);
        if (isset($explicit[$name])) {
            return (string) $explicit[$name];
        }

        $segments = explode('.', $name);
        if (count($segments) < 2) {
            return null;
        }

        $action = array_pop($segments);
        $prefix = implode('.', $segments);

        $modules = (array) self::config('route_modules', []);
        $module = (string) ($modules[$prefix] ?? $prefix);

        if (! isset(self::modules()[$module])) {
            return null;
        }

        $actions = (array) self::config('route_actions', []);
        $permAction = (string) ($actions[$action] ?? $action);

        if (! isset(self::modules()[$module]['permissions'][$permAction])) {
            return null;
        }

        return $module.'.'.$permAction;
    }

    /**
     * Permission names granted to a role by the seed defaults.
     * Supports '*' (everything) and 'module.*'.
     *
     * @return list<string>
     */
    public static function defaultsFor(string $role): array
    {
        $defaults = (array) self::config('default_roles', []);
        $granted = $defaults[$role] ?? null;

        if ($granted === null) {
            return [];
        }

        if ($granted === '*') {
            return self::names();
        }

        $names = [];
        foreach ((array) $granted as $entry) {
            if ($entry === '*') {
                return self::names();
            }

            if (str_ends_with($entry, '.*')) {
                $module = substr($entry, 0, -2);
                foreach (self::modules()[$module]['permissions'] ?? [] as $action => $label) {
                    $names[] = $module.'.'.$action;
                }

                continue;
            }

            if (self::exists($entry)) {
                $names[] = $entry;
            }
        }

        return array_values(array_unique($names));
    }

    private static function config(string $key, mixed $default = null): mixed
    {
        if (self::$config === null) {
            self::$config = (array) config('admin_permissions', []);
        }

        return self::$config[$key] ?? $default;
    }
}
