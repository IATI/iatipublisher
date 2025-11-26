<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <style>
        html { display: none; }

        /* Styles for the Animated Downtime Banner */
        @keyframes barberpole {
            from { background-position: 0 0; }
            to { background-position: 60px 30px; }
        }

        .downtime-banner {
            /* 1. Base Colors: Two shades of yellow */
            background-color: #ffeeba;
            background-image: linear-gradient(
                45deg,
                #ffeeba 25%,
                #ffdf7e 25%,
                #ffdf7e 50%,
                #ffeeba 50%,
                #ffeeba 75%,
                #ffdf7e 75%,
                #ffdf7e
            );

            /* 2. Sizing and Animation */
            background-size: 30px 30px;
            animation: barberpole 2s linear infinite;

            /* 3. Layout & Borders */
            padding: 12px; /* Increased height/padding */
            text-align: center;
            border-bottom: 2px solid #d3b85c;

            /* 4. Text Styling */
            color: #000000; /* Dark brown for high contrast */
            font-size: 1em;
            /* White shadow helps text pop against moving background */
            text-shadow: 0px 1px 0px rgba(255,255,255, 0.6);
        }

        .downtime-banner a {
            color: #0056b3;
            text-decoration: underline;
            font-weight: bold;
        }

        .downtime-banner a:hover {
            color: #003d82;
        }
    </style>

    <!-- Google tag (gtag.js) -->
    @production
        <script defer data-domain="publisher.iatistandard.org" src=https://plausible.io/js/script.js></script>
    @endproduction

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'IATI Publisher') }}</title>

    <!-- Fonts -->
    {{-- Normal --}}
    <link rel="preload" href="{{ asset('fonts/Arial/arial-webfont.woff') }}" as="font" type="font/woff" crossorigin>
    <link rel="preload" href="{{ asset('fonts/Arial/arial-webfont.eot') }}" as="font" type="font/eot" crossorigin>
    <link rel="preload" href="{{ asset('fonts/Arial/arial-webfont.svg') }}" as="font" type="font/svg" crossorigin>
    <link rel="preload" href="{{ asset('fonts/Arial/arial-webfont.ttf') }}" as="font" type="font/ttf" crossorigin>

    {{-- Bold --}}
    <link rel="preload" href="{{ asset('fonts/Arial/arialbd-webfont.woff') }}" as="font" type="font/woff" crossorigin>
    <link rel="preload" href="{{ asset('fonts/Arial/arialbd-webfont.eot') }}" as="font" type="font/eot" crossorigin>
    <link rel="preload" href="{{ asset('fonts/Arial/arialbd-webfont.svg') }}" as="font" type="font/svg" crossorigin>
    <link rel="preload" href="{{ asset('fonts/Arial/arialbd-webfont.ttf') }}" as="font" type="font/ttf" crossorigin>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- styles -->
    <link rel="stylesheet" href="{{ mix('css/webportal-app.css') }}" media="print" onload="this.media='all'">
    <link href={{ env('IATI_DESIGN_SYSTEM_URL')}} rel="stylesheet" />

    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon" />

</head>

<body class="font-sans bg-n-10 antialiased overflow-x-hidden">
<div id="app">
    <web-header
        title='@yield('title', 'IATI PUBLISHER')'
        auth='{{ (bool) Auth::user() }}'
        :super-admin='{{ Auth::check() ? (int) isSuperAdmin() : 0 }}'
        :translated-data='{{json_encode($translatedData)}}'
        :current-language="{{json_encode($currentLanguage)}}"
    >
    </web-header>

    <!-- Animated Downtime Banner -->
    <div class="downtime-banner">
        ⚠️⚠️
        <strong> 📢 Planned downtime:</strong> IATI Publisher will be unavailable 1-5th December while we replace the IATI Registry.
        <a href="https://www.iaticonnect.org/technical-cop/stream/topic/iati-registry-relaunch-scheduled-dec-2025" target="_blank">
            Read more about this work on IATI Connect.
        </a>

        ⚠️⚠️
    </div>
    <!-- End Downtime Banner -->

    <main>@yield('content')</main>

    @if (Auth::user())
        <admin-footer
            :super-admin='{{ Auth::check() ? (int) isSuperAdmin() : 0 }}'
            :translated-data='{{json_encode($translatedData)}}'
            :current-language="{{json_encode($currentLanguage)}}"
        ></admin-footer>
    @else
        <web-footer
            :translated-data='{{json_encode($translatedData)}}'
            :current-language="{{json_encode($currentLanguage)}}"
        >
        </web-footer>
    @endif
</div>

<script defer src="{{ mix('/manifest.js') }}"></script>
<script defer src="{{ mix('/js/vendor.js') }}"></script>
<script defer src="{{ mix('/js/app.js') }}"></script>
<script defer src="{{ mix('js/webportal-script.js') }}"></script>
<script defer src="https://cdn.jsdelivr.net/npm/iati-design-system@3.5.0/dist/js/iati.js"></script>

</body>

</html>
