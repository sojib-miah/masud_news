<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Font connection optimization -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&family=Roboto:wght@100..900&display=swap"
        rel="stylesheet">

    @php
        use App\Models\News;
        use App\Models\SiteSetting;
        use Illuminate\Support\Str;

        $site = SiteSetting::first();

        $slug = request()->route('slug');

        $newsForMeta = $slug ? News::where('slug', $slug)->first() : null;

        $defaultTitle = 'Bangladesh News Today - Latest Breaking News & Updates';

        $defaultDescription =
            'Get the latest breaking news, current events, politics, sports, and business updates from Bangladesh and around the world.';

        $metaTitle = $newsForMeta ? ($newsForMeta->title ?: $defaultTitle) : $defaultTitle;

        $rawDescription = $newsForMeta
            ? $newsForMeta->description ?? ($newsForMeta->content ?? $defaultDescription)
            : $defaultDescription;

        $plainDescription = trim(preg_replace('/\s+/u', ' ', strip_tags($rawDescription)));

        $metaDescription = Str::limit($plainDescription ?: $defaultDescription, 200, '');

        $metaUrl = $newsForMeta ? request()->url() : 'https://bangladeshnews.today/';

        $newsImage = $newsForMeta?->image;

        if ($newsImage && filter_var($newsImage, FILTER_VALIDATE_URL)) {
            $metaImage = $newsImage;
        } elseif ($newsImage) {
            $metaImage = asset('storage/' . ltrim($newsImage, '/'));
        } elseif ($site && $site->logo) {
            $logo = $site->logo;

            $metaImage = filter_var($logo, FILTER_VALIDATE_URL) ? $logo : asset('storage/' . ltrim($logo, '/'));
        } else {
            $metaImage = asset('storage/images/logo.jpg');
        }
    @endphp

    {{-- SEO --}}
    <title inertia>{{ $metaTitle }}</title>

    <meta name="description" content="{{ $metaDescription }}">
    <link rel="canonical" href="{{ $metaUrl }}">

    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">

    {{-- Open Graph: WhatsApp, Facebook, etc. --}}
    <meta property="og:type" content="{{ $newsForMeta ? 'article' : 'website' }}">
    <meta property="og:url" content="{{ $metaUrl }}">
    <meta property="og:title" content="{{ $metaTitle }}">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:image" content="{{ $metaImage }}">
    <meta property="og:image:secure_url" content="{{ $metaImage }}">
    <meta property="og:image:alt" content="{{ $metaTitle }}">
    <meta property="og:site_name" content="Bangladesh News Today">
    <meta property="og:locale" content="en_US">

    @if ($newsForMeta)
        <meta property="article:published_time" content="{{ $newsForMeta->created_at?->toIso8601String() }}">
    @endif

    {{-- Twitter / X --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="{{ $metaUrl }}">
    <meta name="twitter:title" content="{{ $metaTitle }}">
    <meta name="twitter:description" content="{{ $metaDescription }}">
    <meta name="twitter:image" content="{{ $metaImage }}">

    {{-- Favicon --}}
    @if ($site && $site->favaicon)
        @php
            $favicon = $site->favaicon;

            $faviconUrl = filter_var($favicon, FILTER_VALIDATE_URL)
                ? $favicon
                : asset('storage/' . ltrim($favicon, '/'));
        @endphp

        <link rel="icon" type="image/png" href="{{ $faviconUrl }}">
    @else
        <link rel="shortcut icon" href="{{ asset('storage/images/logo.jpg') }}" type="image/jpeg">
    @endif

    {{-- Application assets --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @routes
    @inertiaHead
</head>

<body>
    @inertia
</body>

</html>
