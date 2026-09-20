<?php

namespace App\Core\Seo;

use App\Modules\Product\Models\Product;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

class SitemapService
{
    public function refresh(): string
    {
        $path = public_path('sitemap.xml');
        $xml = $this->build();

        File::put($path, $xml);

        return $path;
    }

    public function build(): string
    {
        $urls = array_merge($this->staticUrls(), $this->productUrls());

        $lines = [
            '<?xml version="1.0" encoding="UTF-8"?>',
            '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">',
        ];

        foreach ($urls as $url) {
            $lines[] = '  <url>';
            $lines[] = '    <loc>'.$this->escape((string) $url['loc']).'</loc>';
            if (! empty($url['lastmod'])) {
                $lines[] = '    <lastmod>'.$this->escape((string) $url['lastmod']).'</lastmod>';
            }
            if (! empty($url['changefreq'])) {
                $lines[] = '    <changefreq>'.$this->escape((string) $url['changefreq']).'</changefreq>';
            }
            if (! empty($url['priority'])) {
                $lines[] = '    <priority>'.$this->escape((string) $url['priority']).'</priority>';
            }
            $lines[] = '  </url>';
        }

        $lines[] = '</urlset>';
        $lines[] = '';

        return implode("\n", $lines);
    }

    /**
     * @return list<array{loc: string, lastmod?: string, changefreq?: string, priority?: string}>
     */
    private function staticUrls(): array
    {
        $today = now()->toAtomString();

        $urls = [
            [
                'loc' => url('/'),
                'lastmod' => $today,
                'changefreq' => 'daily',
                'priority' => '1.0',
            ],
        ];

        if (Route::has('products.index')) {
            $urls[] = [
                'loc' => route('products.index'),
                'lastmod' => $today,
                'changefreq' => 'daily',
                'priority' => '0.9',
            ];
        }

        return $urls;
    }

    /**
     * @return list<array{loc: string, lastmod?: string, changefreq?: string, priority?: string}>
     */
    private function productUrls(): array
    {
        if (! Route::has('products.show')) {
            return [];
        }

        return Product::query()
            ->where('is_unlaunched', false)
            ->orderBy('id')
            ->get(['id', 'slug', 'updated_at'])
            ->map(fn (Product $product): array => [
                'loc' => route('products.show', ['product' => $product->slug]),
                'lastmod' => $product->updated_at?->toAtomString() ?? now()->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ])
            ->values()
            ->all();
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
