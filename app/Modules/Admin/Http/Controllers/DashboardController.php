<?php

namespace App\Modules\Admin\Http\Controllers;

use App\Core\Seo\SeoJsonLoader;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        private readonly SeoJsonLoader $seoJsonLoader,
    ) {}

    public function index(): Response
    {
        return Inertia::render('Admin/Pages/Dashboard', [
            'seo' => $this->seoJsonLoader->load('Modules/Admin/dashboard'),
            'stats' => [
                ['label' => 'Products', 'value' => '4'],
                ['label' => 'Orders', 'value' => '3'],
                ['label' => 'Customers', 'value' => '1'],
                ['label' => 'Open tickets', 'value' => '0'],
            ],
        ]);
    }
}
