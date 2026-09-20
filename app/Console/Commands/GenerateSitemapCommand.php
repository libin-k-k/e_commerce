<?php

namespace App\Console\Commands;

use App\Core\Seo\SitemapService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('sitemap:generate')]
#[Description('Regenerate public/sitemap.xml with static pages and all launched products')]
class GenerateSitemapCommand extends Command
{
    public function handle(SitemapService $sitemap): int
    {
        $path = $sitemap->refresh();

        $this->info('Sitemap written to '.$path);

        return self::SUCCESS;
    }
}
