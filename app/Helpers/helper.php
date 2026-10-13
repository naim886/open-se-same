<?php

use App\Manager\Image\ImageUploadManager;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

function app_error_log(string $name, Throwable $throwable, string $type = 'info'): void
{
    Log::{$type}($name, ['message' => $throwable->getMessage(), 'error' => $throwable]);
}

function app_success_log(string $name, string $message, string $type = 'info'): void
{
    Log::{$type}($name, ['message' => $message]);
}

function success_alert($message): void
{
    session()->flash('message', $message);
    session()->flash('class', 'success');
}

function failed_alert($message): void
{
    session()->flash('message', $message);
    session()->flash('class', 'warning');
}

function capitalizeFirstWord($string)
{
    $words    = explode(' ', strtolower($string));
    $words[0] = ucfirst($words[0]);
    return implode(' ', $words);
}

function generateUniqueImageName(string|null $name = null): string
{
    if (empty($name)) {
        return uniqid('image_', true);
    }
    return Str::slug($name) . '_' . uniqid('image_', true);
}

function get_image(string|null $path): string
{
    return ImageUploadManager::get_image($path);
}

/**
 * Return a versioned URL to the minified asset if it exists,
 * otherwise fall back to the original file.
 *
 * Usage in Blade:  {{ minified_asset('frontend/css/shop.css') }}
 */
function minified_asset(string $publicRelativePath): string
{
    $info    = pathinfo($publicRelativePath);
    $minRel  = $info['dirname'] . '/' . $info['filename'] . '.min.' . $info['extension'];
    $minFull = public_path($minRel);

    if (file_exists($minFull)) {
        return asset($minRel) . '?v=' . filemtime($minFull);
    }

    $srcFull = public_path($publicRelativePath);
    $version = file_exists($srcFull) ? filemtime($srcFull) : time();
    return asset($publicRelativePath) . '?v=' . $version;
}
