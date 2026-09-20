<?php

namespace App\Modules\Product\Http\Controllers;

use App\Core\Seo\SeoJsonLoader;
use App\Http\Controllers\Controller;
use App\Modules\Product\Services\ProductService;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductService $productService,
        private readonly SeoJsonLoader $seoJsonLoader,
    ) {}

    public function index(): Response
    {
        return Inertia::render('Product/Pages/Index', [
            'seo' => $this->seoJsonLoader->load('Modules/Product/index'),
            'products' => $this->productService->listing(),
        ]);
    }

    public function show(string $product): Response
    {
        $payload = $this->productService->details($product);

        return Inertia::render('Product/Pages/Show', $payload);
    }
}
