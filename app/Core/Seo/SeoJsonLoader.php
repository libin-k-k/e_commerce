<?php

namespace App\Core\Seo;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use RuntimeException;

class SeoJsonLoader
{
    /**
     * @return array{title: string, description: string, keywords: string}
     */
    public function load(string $key): array
    {
        $path = $this->resolvePath($key);

        if ($path === null) {
            $checked = implode(', ', $this->candidatePaths($key));

            Log::warning('SEO JSON missing; using fallback meta.', [
                'key' => $key,
                'checked' => $this->candidatePaths($key),
            ]);

            if (! app()->isProduction()) {
                throw new InvalidArgumentException(
                    "SEO JSON not found for [{$key}]. Checked: {$checked}"
                );
            }

            return $this->fallback($key);
        }

        $decoded = json_decode(File::get($path), true);

        if (! is_array($decoded)) {
            throw new RuntimeException("Invalid SEO JSON for [{$key}] at [{$path}].");
        }

        $title = (string) ($decoded['title'] ?? '');
        $description = (string) ($decoded['description'] ?? '');
        $keywords = $decoded['keywords'] ?? '';

        if (is_array($keywords)) {
            $keywords = implode(', ', array_map('strval', $keywords));
        }

        return [
            'title' => $title !== '' ? $title : $this->fallback($key)['title'],
            'description' => $description,
            'keywords' => (string) $keywords,
        ];
    }

    /**
     * @return list<string>
     */
    private function candidatePaths(string $key): array
    {
        $relative = str_replace(['\\', '..'], ['/', ''], $key).'.json';

        return [
            resource_path('seo/'.$relative),
            resource_path('seo/website/'.$relative),
        ];
    }

    private function resolvePath(string $key): ?string
    {
        foreach ($this->candidatePaths($key) as $path) {
            if (File::isFile($path)) {
                return $path;
            }
        }

        return null;
    }

    /**
     * @return array{title: string, description: string, keywords: string}
     */
    private function fallback(string $key): array
    {
        $label = str_replace(['Modules/', '/', '.', '_', '-'], ['', ' ', ' ', ' ', ' '], $key);
        $label = trim(ucwords($label)) ?: 'Page';
        $appName = (string) config('app.name', 'Store');

        return [
            'title' => $label.' - '.$appName,
            'description' => $appName.' - '.$label,
            'keywords' => strtolower($appName.', '.$label),
        ];
    }
}
