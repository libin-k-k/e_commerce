<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['commerce.sitemap.path' => storage_path('framework/testing/sitemap.xml')]);
    }
}
