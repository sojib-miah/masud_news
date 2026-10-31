<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        $site = \App\Models\SiteSetting::first();
    @endphp

    <!-- Basic SEO -->
    <title inertia>Bangladesh News Today</title>
    <meta name="description"
        content="Get the latest breaking news, current events, politics, sports, and business updates from Bangladesh and around the world at Bangladesh News Today.">
    <link rel="canonical" href="https://bangladeshnews.today/">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">

    <!-- Open Graph / Facebook (Crucial for news sharing) -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://bangladeshnews.today/">
    <meta property="og:title" content="Bangladesh News Today - Latest Breaking News & Updates">
    <meta property="og:description"
        content="Get the latest breaking news, current events, politics, sports, and business updates from Bangladesh and around the world.">
    <meta property="og:image"
        content="{{ $site && $site->logo ? asset('storage/' . $site->logo) : asset('/storage/images/logo.jpg') }}">
    <meta property="og:site_name" content="Bangladesh News Today">

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="https://bangladeshnews.today/">
    <meta name="twitter:title" content="Bangladesh News Today - Latest Breaking News & Updates">
    <meta name="twitter:description"
        content="Get the latest breaking news, current events, politics, sports, and business updates from Bangladesh and around the world.">
    <meta name="twitter:image"
        content="{{ $site && $site->logo ? asset('storage/' . $site->logo) : asset('/storage/images/logo.jpg') }}">

    <!-- Favicon -->
    @if ($site && $site->favaicon)
        <link rel="icon" type="image/png" href="{{ asset('storage/' . $site->favaicon) }}">
    @else
        <link rel="shortcut icon" href="{{ asset('/storage/images/logo.jpg') }}" type="image/x-icon">
    @endif

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @routes
    @inertiaHead
</head>

<body>
    @inertia
</body>

</html>
