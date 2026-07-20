<?php

namespace App\Support;

use Carbon\Carbon;
use Carbon\CarbonInterface;

class ApiDateFormatter
{
    public static function format(mixed $value): mixed
    {
        if ($value instanceof CarbonInterface) {
            return $value->timezone(config('app.timezone', 'Asia/Jakarta'))->format('Y-m-d');
        }

        if (is_array($value)) {
            foreach ($value as $key => $item) {
                $value[$key] = self::format($item);
            }

            return $value;
        }

        if (is_string($value) && self::looksLikeIsoDateTime($value)) {
            return Carbon::parse($value)
                ->timezone(config('app.timezone', 'Asia/Jakarta'))
                ->format('Y-m-d');
        }

        return $value;
    }

    private static function looksLikeIsoDateTime(string $value): bool
    {
        return (bool) preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/', $value);
    }
}
