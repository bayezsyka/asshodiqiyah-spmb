<!DOCTYPE html>
<html lang="id">
<head>
    @php($seo = $page['props']['seo'] ?? [])
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title inertia>{{ $seo['title'] ?? config('app.name', 'SPMB Asshodiqiyah') }}</title>
    <meta name="description" content="{{ $seo['description'] ?? 'Portal resmi penerimaan santri dan siswa baru Pondok Pesantren Asshodiqiyah Kaligawe.' }}">
    <meta name="robots" content="{{ $seo['robots'] ?? 'noindex, nofollow' }}">
    <link rel="canonical" href="{{ $seo['canonical'] ?? url()->current() }}">
    @if (! empty($seo['ogImage']))
        <meta property="og:image" content="{{ $seo['ogImage'] }}">
        <meta property="og:image:alt" content="Pondok Pesantren Asshodiqiyah Kaligawe">
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:image" content="{{ $seo['ogImage'] }}">
    @endif
    <meta property="og:locale" content="id_ID">
    <meta property="og:site_name" content="SPMB Asshodiqiyah">
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $seo['title'] ?? config('app.name', 'SPMB Asshodiqiyah') }}">
    <meta property="og:description" content="{{ $seo['description'] ?? 'Portal resmi penerimaan santri dan siswa baru Pondok Pesantren Asshodiqiyah Kaligawe.' }}">
    <meta property="og:url" content="{{ $seo['canonical'] ?? url()->current() }}">
    <meta name="twitter:title" content="{{ $seo['title'] ?? config('app.name', 'SPMB Asshodiqiyah') }}">
    <meta name="twitter:description" content="{{ $seo['description'] ?? 'Portal resmi penerimaan santri dan siswa baru Pondok Pesantren Asshodiqiyah Kaligawe.' }}">
    @if (! empty($seo['jsonLd']))
        <script type="application/ld+json">@json($seo['jsonLd'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)</script>
    @endif
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="icon" type="image/png" sizes="64x64" href="/favicon-64.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600;9..40,700&display=swap" rel="stylesheet">
    @viteReactRefresh
    @vite(['resources/css/app.css', 'resources/js/app.tsx'])
    @inertiaHead
</head>
<body>
    @inertia
</body>
</html>
