<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

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

    <head>
    <style>html{display:none}</style>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'IATI Publisher') }}</title>


    {{-- Normal --}}
    <link rel="preload" href="{{ asset('fonts/Arial/arial-webfont.woff') }}" as="font" type="font/woff"
        crossorigin>
    <link rel="preload" href="{{ asset('fonts/Arial/arial-webfont.eot') }}" as="font" type="font/eot" crossorigin>
    <link rel="preload" href="{{ asset('fonts/Arial/arial-webfont.svg') }}" as="font" type="font/svg" crossorigin>
    <link rel="preload" href="{{ asset('fonts/Arial/arial-webfont.ttf') }}" as="font" type="font/ttf" crossorigin>

    {{-- Bold --}}
    <link rel="preload" href="{{ asset('fonts/Arial/arialbd-webfont.woff') }}" as="font" type="font/woff"
        crossorigin>
    <link rel="preload" href="{{ asset('fonts/Arial/arialbd-webfont.eot') }}" as="font" type="font/eot"
        crossorigin>
    <link rel="preload" href="{{ asset('fonts/Arial/arialbd-webfont.svg') }}" as="font" type="font/svg"
        crossorigin>
    <link rel="preload" href="{{ asset('fonts/Arial/arialbd-webfont.ttf') }}" as="font" type="font/ttf"
        crossorigin>


  <!-- Styles -->
    <link rel="stylesheet" href="{{ mix('css/app.css') }}" media="print" onload="this.media='all'">
     <link rel="icon"
        href="{{ asset('favicon.ico') }}"
        type="image/x-icon" />
    <link
        href={{ config('services.oidc.iatiDesignSystemUrl')}}
        rel="stylesheet"

    />



</head>
<body  class="overflow-x-hidden" >
    <div id="app">
        @if (isSuperAdmin() && Auth::user()->organization)
            <admin-bar :name="{{ json_encode(Auth::user()->full_name, JSON_THROW_ON_ERROR) }}"
                :organization-name="{{ json_encode(Auth::user()->organization?->publisher_name, JSON_THROW_ON_ERROR) }}">
            </admin-bar>
        @endif

        @if (isSuperAdmin())

            <loggedin-header
                :user="{{ Auth::user() }}"
                has-admin-bar = "{{ isSuperAdmin() && Auth::user()->organization }}"
                :languages="{{ json_encode(getCodeList('Language', 'Activity'), JSON_THROW_ON_ERROR) }}"
                v-bind:super-admin="{{ isSuperAdminRoute() ? 1 : 0 }}"
                :default-language="{{ json_encode(getSettingDefaultLanguage()) }}"
                 :onboarding="{{ json_encode(Auth::user()->organization ? Auth::user()->organization->onboarding : null) }}"
                :translated-data="{{json_encode($translatedData)}}"
                :current-language="{{json_encode($currentLanguage)}}"
            > </loggedin-header>
        @else
            <loggedin-header
                :user="{{ Auth::user() }}"
                :organization="{{ Auth::user()->organization }}"
                :languages="{{ json_encode(getCodeList('Language', 'Activity'), JSON_THROW_ON_ERROR) }}"
                v-bind:super-admin="{{ isSuperAdminRoute() ? 1 : 0 }}"
                :default-language="{{ json_encode(getSettingDefaultLanguage()) }}"
                :onboarding="{{ json_encode(Auth::user()->organization ? Auth::user()->organization->onboarding : null) }}"
                :translated-data="{{json_encode($translatedData)}}"
                :current-language="{{json_encode($currentLanguage)}}"
                ></loggedin-header>
                @endif
                <main>
                    @yield('content')
                    @stack('scripts')
                </main>
                <admin-footer
                    v-bind:super-admin="{{ (int) isSuperAdmin() }}"
                    :translated-data="{{json_encode($translatedData)}}"
                    :current-language="{{json_encode($currentLanguage)}}"
                >
                </admin-footer>
    </div>
    <script defer src="{{ mix('/manifest.js') }}"></script>
    <script defer src="{{ mix('/js/vendor.js') }}"></script>
    <script defer src="{{ mix('/js/app.js') }}"></script>
    <script defer src="{{ mix('/js/script.js') }}"></script>
    <script defer src="{{ mix('js/formbuilder.js') }}"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/iati-design-system@3.5.0/dist/js/iati.js"></script>
    <!-- Start of iati Zendesk Widget script -->
    <script id="ze-snippet" src="https://static.zdassets.com/ekr/snippet.js?key=f1df04e0-f01e-4ab5-9091-67b2fddd6e60"> </script>
    <script type="text/javascript">
        window.zESettings = {
            webWidget: {
                color: { theme: '#FFFFFF',
                launcherText: '#155366'},

                contactForm: {
                    attachments: true,
                }
            }
        };
    </script>

    @yield('additional-scripts')
</body>

</html>
