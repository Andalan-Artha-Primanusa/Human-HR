<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Str;

class UploadPath
{
    public static function forUser(?User $user, string $category): string
    {
        $date = now()->format('Y-m-d');
        $name = $user?->name ?: $user?->email ?: $user?->id ?: 'unknown-user';
        $safeName = Str::of($name)
            ->ascii()
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', '-')
            ->trim('-')
            ->limit(80, '');

        return trim($date . '/' . ($safeName ?: 'unknown-user') . '/' . trim($category, '/'), '/');
    }

    public static function safeOriginalName(string $name): string
    {
        $name = Str::of($name)->replace(['\\', '/'], '')->toString();
        $ext = pathinfo($name, PATHINFO_EXTENSION);
        $base = pathinfo($name, PATHINFO_FILENAME);

        $base = preg_replace('/[^A-Za-z0-9\-\_\.\s]+/u', '', $base) ?: 'file';
        $base = trim(preg_replace('/\s+/', ' ', $base));
        $ext = preg_replace('/[^A-Za-z0-9]+/u', '', $ext);

        return $ext ? ($base . '.' . strtolower($ext)) : $base;
    }
}
