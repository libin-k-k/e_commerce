<?php

namespace App\Modules\Help\Http\Controllers;

use App\Core\Seo\SeoJsonLoader;
use App\Http\Controllers\Controller;
use App\Modules\Help\Services\HelpChatService;
use Inertia\Inertia;
use Inertia\Response;

class HelpController extends Controller
{
    public function __construct(
        private readonly HelpChatService $helpChatService,
        private readonly SeoJsonLoader $seoJsonLoader,
    ) {}

    public function index(): Response
    {
        return Inertia::render('Help/Pages/Index', [
            'seo' => $this->seoJsonLoader->load('Modules/Help/index'),
            'chat' => $this->helpChatService->conversation(),
        ]);
    }
}
