<?php

namespace App\Modules\Product\Services;

use App\Core\Seo\SeoJsonLoader;
use App\Modules\Product\Repositories\Contracts\ProductRepositoryInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProductService
{
    public function __construct(
        private readonly ProductRepositoryInterface $products,
        private readonly SeoJsonLoader $seoJsonLoader,
    ) {}

    /**
     * @return list<array<string, mixed>>
     */
    public function featured(int $limit = 4): array
    {
        return $this->products->featured($limit);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function onSale(): array
    {
        return $this->products->onSale();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listing(): array
    {
        return $this->products->all();
    }

    /**
     * @return array{product: array<string, mixed>, related: list<array<string, mixed>>, seo: array{title: string, description: string, keywords: string}}
     */
    public function details(string $slug): array
    {
        $product = $this->products->findBySlug($slug);

        if ($product === null) {
            throw new NotFoundHttpException('Product not found.');
        }

        $template = $this->seoJsonLoader->load('Modules/Product/show.template');

        $seo = [
            'title' => (string) ($product['meta_title'] ?: $template['title'] ?: $product['name']),
            'description' => (string) ($product['meta_description'] ?: $template['description'] ?: $product['shortDescription']),
            'keywords' => $this->normalizeKeywords(
                $product['meta_keywords'] ?? $template['keywords'],
            ),
        ];

        return [
            'product' => $product,
            'related' => $this->products->related($slug),
            'seo' => $seo,
        ];
    }

    private function normalizeKeywords(mixed $keywords): string
    {
        if (is_array($keywords)) {
            return implode(', ', array_map('strval', $keywords));
        }

        return (string) $keywords;
    }
}
