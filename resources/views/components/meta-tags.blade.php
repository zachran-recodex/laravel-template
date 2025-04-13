@props(['page' => null])

@php
    // Jika page diberikan, coba dapatkan meta tag untuk halaman tersebut
    if ($page) {
        $customMetaTag = \App\Models\MetaTag::getByPage($page);
        if ($customMetaTag) {
            $metaTag = $customMetaTag;
        }
    }
@endphp

<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="description" content="{{ $metaTag->description ?? '' }}">
<meta name="keywords" content="{{ $metaTag->keywords ?? '' }}">
<meta name="author" content="{{ $metaTag->author ?? '' }}">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="robots" content="index, follow">

<meta property="og:title" content="{{ $metaTag->og_title ?? $metaTag->title ?? '' }}">
<meta property="og:description" content="{{ $metaTag->og_description ?? $metaTag->description ?? '' }}">
<meta property="og:image" content="{{ $metaTag->og_image ?? '' }}">
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:type" content="{{ $metaTag->og_type ?? 'website' }}">

<meta name="twitter:card" content="{{ $metaTag->twitter_card ?? 'summary_large_image' }}">
<meta name="twitter:title" content="{{ $metaTag->twitter_title ?? $metaTag->title ?? '' }}">
<meta name="twitter:description" content="{{ $metaTag->twitter_description ?? $metaTag->description ?? '' }}">
<meta name="twitter:image" content="{{ $metaTag->twitter_image ?? '' }}">

<link rel="canonical" href="{{ url()->current() }}">

<title>{{ $metaTag->title ?? $title ?? 'Laravel' }}</title>
