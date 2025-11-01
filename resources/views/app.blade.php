<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title inertia>{{ config('app.name', 'Bilikbecakap') }}</title>

        <meta name="description" content="Bilikbecakap - Bersatu Memajukan Kebudayaan">
        <meta name="keywords" content="bilikbecakap, pemajuan kebudayaan, pelestarian kebudayaan, Bilikbecakap adalah platform digital yang didedikasikan untuk pelestarian bahasa dan budaya Belitung Timur, khususnya Bahasa Belitung.">
        <meta name="author" content="Bilikbecakap">
        <meta name="robots" content="index, follow">

        <!-- Favicon - Multiple formats for better browser support -->
        <link rel="icon" type="image/png" href="/icon.png">
        <link rel="apple-touch-icon" href="/icon.png">
        
        <!-- Meta theme color -->
        <meta name="theme-color" content="#54b0af">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>