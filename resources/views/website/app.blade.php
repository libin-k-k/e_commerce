<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        @php
            $seo = $page['props']['seo'] ?? [];
            $seoKeywords = $seo['keywords'] ?? '';
            $seoKeywords = is_array($seoKeywords) ? implode(', ', $seoKeywords) : $seoKeywords;
        @endphp

        <title data-inertia="">{{ filled($seo['title'] ?? null) ? $seo['title'].' - '.config('app.name') : config('app.name') }}</title>
        @if (filled($seo['description'] ?? null))
            <meta name="description" content="{{ $seo['description'] }}" data-inertia="description">
        @endif
        @if (filled($seoKeywords))
            <meta name="keywords" content="{{ $seoKeywords }}" data-inertia="keywords">
        @endif

        @viteReactRefresh
        @vite(['resources/css/website/app.css', 'resources/js/website/app.jsx'])
        @inertiaHead
    </head>
    <body>
        @inertia
    </body>
</html>
