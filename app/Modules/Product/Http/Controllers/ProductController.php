<?php

namespace App\Modules\Product\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Product\Http\Requests\ProductIndexRequest;
use App\Modules\Product\Http\Requests\ProductSuggestionRequest;
use App\Modules\Product\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductService $productService,
    ) {}

    public function index(ProductIndexRequest $request): Response
    {
        return Inertia::render('Product/Pages/Index', $this->productService->catalog($request->filters()));
    }

    public function suggestions(ProductSuggestionRequest $request): JsonResponse
    {
        return response()->json($this->productService->suggestions($request->term()));
    }

    public function show(string $product): Response
    {
        $payload = $this->productService->details($product);

        return Inertia::render('Product/Pages/Show', $payload);
    }
}
