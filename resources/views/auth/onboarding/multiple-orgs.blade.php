<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <style>html {
            display: none
        }</style>
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

<body class="overflow-x-hidden">
<div id="app">
    <header class="activity__header flex min-h-[60px] max-w-full justify-between gap-5 bg-bluecoral px-5 text-xs leading-normal text-white sm:gap-10 xl:px-10">
        <nav class="activity__header flex min-h-[60px] max-w-full justify-between gap-5 bg-bluecoral px-5 text-xs leading-normal text-white sm:gap-10 xl:px-10">
            <div class="flex items-center gap-5">
                <figure class="flex grow-0 items-center">
                    <a href="{{ route('logout.iati') }}">
                        <svg-vue icon="logo" class="text-4xl" />
                    </a>
                </figure>
            </div>
        </nav>
        <div class="user-nav pt-2">
            <a href="{{ route('logout.iati') }}" class="button secondary-btn flex w-full items-center">
                <svg-vue class="ml-1 mr-3" icon="logout"></svg-vue>
                <span class="text-sm">{{ trans('adminHeader/admin_header.logout') }}</span>
            </a>
        </div>
    </header>

    <div class="bg-slate-100 flex pt-8">
        <div class="mx-auto mt-8 w-[80%] space-y-2 rounded-xl bg-white p-8 shadow-lg">
            <div class="text-center">
                <i class="fas fa-users text-blue-600 mb-4 text-5xl"></i>

                <h2 class="text-slate-900 text-3xl font-bold tracking-tight">
                    {{ trans('auth.multiple_organizations_title') }}
                </h2>

                <p class="text-slate-600 mt-2 text-lg">
                    {{ trans('auth.multiple_organizations_subtitle') }}
                </p>
            </div>

            <div class="bg-slate-50 border-slate-200 rounded-lg border p-4 px-4 text-left">
                <p class="text-slate-800 font-medium text-center">
                    {{ trans('auth.multiple_organizations_message') }}
                </p>
            </div>

            <div class="py-4 text-center">
                <a
                    href="https://iatistandard.org/en/guidance/get-support/"
                    class="primary-btn font-bold"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    {{ trans('auth.contact_support_button') }}
                </a>
            </div>

            <div class="text-center">
                <p class="text-slate-400 text-sm">
                    {{ trans('auth.support_resolution_note') }}
                </p>
            </div>
        </div>
    </div>
</div>

<script defer src="{{ mix('/manifest.js') }}"></script>
<script defer src="{{ mix('/js/vendor.js') }}"></script>
<script defer src="{{ mix('/js/app.js') }}"></script>
<script defer src="https://cdn.jsdelivr.net/npm/iati-design-system@3.5.0/dist/js/iati.js"></script>
<!-- Start of iati Zendesk Widget script -->
<script id="ze-snippet"
        src="https://static.zdassets.com/ekr/snippet.js?key=f1df04e0-f01e-4ab5-9091-67b2fddd6e60"></script>
<script type="text/javascript">
    window.zESettings = {
        webWidget: {
            color: {
                theme: '#FFFFFF',
                launcherText: '#155366',
            },
            contactForm: {
                attachments: true,
            },
        },
    };
</script>
</body>
</html>
