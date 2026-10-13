<?php

namespace App\Manager;

use App\Models\Category;
use Illuminate\Support\Str;

class Utility
{
    /**
     * @param string|null $name
     * @return string
     */
    final public static function generateName(string|null $name): string
    {
        if ($name) {
            return Str::slug($name . '_' . time());
        }
        $name = Str::random(10);
        return Str::slug($name . '_' . time());
    }

    public static function isPhone(string $phone):bool
    {
        return is_numeric($phone) && strlen($phone) == 11;
    }

}
