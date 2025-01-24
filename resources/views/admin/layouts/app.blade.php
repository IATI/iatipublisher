<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

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
        href={{ env('IATI_DESIGN_SYSTEM_URL')}}
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

            <loggedin-header :user="{{ Auth::user() }}"
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
                :user="{{ Auth::user() }}" :organization="{{ Auth::user()->organization }}"
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
