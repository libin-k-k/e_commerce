<?php

namespace App\Core\Media;

/**
 * Build public media URLs relative to the current host (not hardcoded APP_URL).
 */
class PublicUrl
{
    public static function for(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        $path = trim($path);

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            $parts = parse_url($path);
            $relative = $parts['path'] ?? '/';

            if (! empty($parts['query'])) {
                $relative .= '?'.$parts['query'];
            }

            return $relative;
        }

        if (str_starts_with($path, '/')) {
            return $path;
        }

        if (str_starts_with($path, 'assets/')) {
            return '/'.$path;
        }

        if (str_starts_with($path, 'storage/')) {
            return '/'.$path;
        }

        return '/storage/'.ltrim($path, '/');
    }
}
