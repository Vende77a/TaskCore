<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ config('app.name') }}</title>

        <!-- Scripts -->
        @routes
        @vite('resources/js/app.js')
        @inertiaHead
        <link rel="icon" type="image/png" href="{{ asset('images/taskcore-logo.png') }}">
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
