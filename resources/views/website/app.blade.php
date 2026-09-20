<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title inertia>{{ config('app.name') }}</title>

        @viteReactRefresh
        @vite(['resources/css/website/app.css', 'resources/js/website/app.jsx'])
        @inertiaHead
    </head>
    <body>
        @inertia
    </body>
</html>
