<?php

namespace Tests\Feature\Modules\Help;

use Tests\TestCase;

class HelpPageTest extends TestCase
{
    public function test_help_page_renders_chat_payload(): void
    {
        $response = $this->get(route('help.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Help/Pages/Index')
            ->has('seo.title')
            ->has('seo.description')
            ->has('seo.keywords')
            ->has('chat.welcome')
            ->has('chat.quickReplies')
            ->has('chat.replies')
        );
    }
}
