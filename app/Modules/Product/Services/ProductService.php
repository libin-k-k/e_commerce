<?php

namespace App\Modules\Product\Services;

use App\Core\Seo\SeoJsonLoader;
use App\Modules\Account\Services\AccountPageService;
use App\Modules\Banner\Services\BannerStorefrontService;
use App\Modules\Category\Models\Category;
use App\Modules\Category\Repositories\Contracts\CategoryRepositoryInterface;
use App\Modules\Product\Enums\PriceRange;
use App\Modules\Product\Enums\ProductSort;
use App\Modules\Product\Http\Requests\ProductIndexRequest;
use App\Modules\Product\Http\Requests\ProductSuggestionRequest;
use App\Modules\Product\Repositories\Contracts\ProductRepositoryInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProductService
{
    public const RelatedLimit = 6;

    public const SuggestionLimit = 5;

    public function __construct(
        private readonly ProductRepositoryInterface $products,
        private readonly SeoJsonLoader $seoJsonLoader,
        private readonly CategoryRepositoryInterface $categories,
        private readonly BannerStorefrontService $bannerStorefrontService,
        private readonly AccountPageService $accountPageService,
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
     * Everything the storefront product listing page renders.
     *
     * @param  array{category: ?string, q: ?string, size: ?string, color: ?string, sort: ProductSort, price: ?PriceRange, minPrice: ?int, maxPrice: ?int, rating: ?int, page: int, perPage: int}  $filters
     * @return array<string, mixed>
     */
    public function catalog(array $filters): array
    {
        $category = $filters['category'] !== null
            ? $this->categories->findActiveBySlug($filters['category'])
            : null;

        $matches = $this->products->search($filters, $category);
        $total = count($matches);
        $perPage = $filters['perPage'];
        $lastPage = max(1, (int) ceil($total / $perPage));
        $page = min($filters['page'], $lastPage);
        $offset = ($page - 1) * $perPage;
        $products = array_slice($matches, $offset, $perPage);
        $ratingCounts = $this->products->ratingCounts(ProductIndexRequest::RatingOptions, $category);

        return [
            'seo' => $this->catalogSeo($category, $filters['q']),
            'products' => $products,
            'total' => $total,
            'pagination' => [
                'page' => $page,
                'perPage' => $perPage,
                'lastPage' => $lastPage,
                'from' => $total === 0 ? 0 : $offset + 1,
                'to' => $offset + count($products),
                'perPageOptions' => ProductIndexRequest::PerPageOptions,
            ],
            'category' => $category === null ? null : [
                'name' => $category->name,
                'slug' => $category->slug,
                'description' => $category->description,
                'parentName' => $category->parent?->name,
                'parentSlug' => $category->parent?->slug,
            ],
            'chips' => $this->categoryChips($category),
            'categoryTree' => $this->categoryTree(),
            'filters' => [
                'category' => $category?->slug,
                'q' => $filters['q'],
                'size' => $filters['size'],
                'color' => $filters['color'],
                'sort' => $filters['sort']->value,
                'price' => $filters['price']?->value,
                'min_price' => $filters['minPrice'],
                'max_price' => $filters['maxPrice'],
                'rating' => $filters['rating'],
                'per_page' => $perPage,
            ],
            'filterOptions' => [
                'sorts' => ProductSort::options(),
                'prices' => PriceRange::options(),
                'sizes' => $this->products->sizeNames($category),
                'colors' => $this->products->colorOptions($category),
                'priceCeiling' => $this->products->priceCeiling($category),
                'ratings' => array_map(
                    fn (int $rating): array => [
                        'value' => $rating,
                        'label' => $rating.'★ & above',
                        'count' => $ratingCounts[$rating] ?? 0,
                    ],
                    ProductIndexRequest::RatingOptions,
                ),
            ],
            'banner' => $this->bannerStorefrontService->forProductListing(),
        ];
    }

    /**
     * Search-as-you-type results for the storefront search panel. Terms that
     * are too short fall back to the featured products (`matched` is false).
     *
     * @return array{query: string, matched: bool, total: int, products: list<array{id: int, slug: string, name: string, image: ?string, price: string, compareAtPrice: ?string, badge: ?string, isNew: bool, rating: float, categoryName: ?string, inStock: bool, hasVariants: bool}>}
     */
    public function suggestions(string $term): array
    {
        $matched = mb_strlen($term) >= ProductSuggestionRequest::MinLength;
        $matches = $this->products->suggest($matched ? $term : null, self::SuggestionLimit);

        return [
            'query' => $term,
            'matched' => $matched,
            'total' => $matches['total'],
            'products' => array_map(fn (array $product): array => [
                'id' => $product['id'],
                'slug' => $product['slug'],
                'name' => $product['name'],
                'image' => $product['image'],
                'price' => $product['price'],
                'compareAtPrice' => $product['compareAtPrice'],
                'badge' => $product['badge'],
                'isNew' => $product['isNew'],
                'rating' => $product['rating'],
                'categoryName' => $product['subcategoryName'] ?? $product['categoryName'],
                'inStock' => $product['inStock'],
                'hasVariants' => $product['variants'] !== [],
            ], $matches['items']),
        ];
    }

    /**
     * @return array{product: array<string, mixed>, related: list<array<string, mixed>>, deliveryAddress: ?array{label: string, name: string, line: string}, faqs: list<array{question: string, answer: string}>, seo: array{title: string, description: string, keywords: string}}
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
            'related' => $this->products->related($slug, self::RelatedLimit),
            'deliveryAddress' => $this->deliveryAddress(),
            'faqs' => $this->accountPageService->faq(),
            'seo' => $seo,
        ];
    }

    /**
     * The signed-in shopper's default address, shown in the delivery card.
     *
     * @return array{label: string, name: string, line: string}|null
     */
    private function deliveryAddress(): ?array
    {
        $address = $this->accountPageService->addresses()[0] ?? null;

        if ($address === null) {
            return null;
        }

        return [
            'label' => (string) $address['label'],
            'name' => (string) $address['name'],
            'line' => trim(implode(', ', array_filter([$address['district'], $address['state']])).' - '.$address['pincode'], ' -'),
        ];
    }

    /**
     * Chips shown above the listing: the sub categories of the current main
     * category (or its siblings for a sub category), or every main category.
     *
     * @return array{allSlug: ?string, activeSlug: ?string, items: list<array{name: string, slug: string, image: ?string}>}
     */
    private function categoryChips(?Category $category): array
    {
        $main = $category === null || $category->isMain() ? $category : $category->parent;

        $items = $main === null
            ? $this->categories->activeMainsWithChildren()
            : $main->children;

        return [
            'allSlug' => $main?->slug,
            'activeSlug' => $category !== null && ! $category->isMain() ? $category->slug : null,
            'items' => $items
                ->map(fn (Category $item): array => [
                    'name' => $item->name,
                    'slug' => $item->slug,
                    'image' => $item->imageUrl() ?? $main?->imageUrl(),
                ])
                ->values()
                ->all(),
        ];
    }

    /**
     * Every active main category with its sub categories and launched product counts.
     *
     * @return array{total: int, items: list<array{name: string, slug: string, count: int, children: list<array{name: string, slug: string, count: int}>}>}
     */
    private function categoryTree(): array
    {
        $counts = $this->products->launchedCounts();
        $countFor = fn (Category $category): int => $counts['byCategory'][$category->id] ?? 0;

        return [
            'total' => $counts['total'],
            'items' => $this->categories->activeMainsWithChildren()
                ->map(fn (Category $main): array => [
                    'name' => $main->name,
                    'slug' => $main->slug,
                    'count' => $countFor($main),
                    'children' => $main->children
                        ->map(fn (Category $child): array => [
                            'name' => $child->name,
                            'slug' => $child->slug,
                            'count' => $countFor($child),
                        ])
                        ->values()
                        ->all(),
                ])
                ->values()
                ->all(),
        ];
    }

    /**
     * @return array{title: string, description: string, keywords: string}
     */
    private function catalogSeo(?Category $category, ?string $search): array
    {
        $seo = $this->seoJsonLoader->load('Modules/Product/index');
        $keywords = $this->normalizeKeywords($seo['keywords'] ?? '');

        if ($category !== null) {
            return [
                'title' => 'Shop '.$category->name.' online',
                'description' => $category->description ?: 'Browse '.$category->name.' at the best prices.',
                'keywords' => trim($category->name.', '.$keywords, ', '),
            ];
        }

        if ($search !== null) {
            return [
                'title' => 'Search results for "'.$search.'"',
                'description' => (string) ($seo['description'] ?? ''),
                'keywords' => $keywords,
            ];
        }

        return [
            'title' => (string) ($seo['title'] ?? ''),
            'description' => (string) ($seo['description'] ?? ''),
            'keywords' => $keywords,
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
