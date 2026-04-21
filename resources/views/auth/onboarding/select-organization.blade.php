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

    <div class="bg-slate-100 flex pt-8 min-h-screen">
        <div class="mx-auto mt-8 w-[80%] max-w-2xl space-y-6 rounded-xl bg-white p-8 shadow-lg h-fit">
            <div class="text-center">
                <h2 class="text-slate-900 text-3xl font-bold tracking-tight">
                    {{ trans('auth.select_organization_title', ['default' => 'Select Organization']) }}
                </h2>

                <p class="text-slate-600 mt-2 text-lg">
                    {{ trans('auth.select_organization_subtitle', ['default' => 'You are associated with multiple organizations. Please select one to continue.']) }}
                </p>
            </div>

            <div class="grid gap-4 mt-8">
                @foreach($organizations as $org)
                    <a href="{{ route('onboarding.process-selection', $org->uuid) }}"
                       class="flex items-center justify-between p-4 bg-slate-50 border border-slate-200 rounded-lg hover:bg-slate-100 hover:border-blue-400 transition-colors group">
                        <div class="flex items-center gap-4">
                            <div class="bg-white p-2 rounded-full shadow-sm">
                                <i class="fas fa-building text-blue-600"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-800 group-hover:text-blue-600">
                                    {{ $org->publisher_name }}
                                </h3>
                                <p class="text-sm text-slate-500">
                                    {{ $org->publisher_id }}
                                </p>
                            </div>
                        </div>
                        <i class="fas fa-chevron-right text-slate-300 group-hover:translate-x-1 transition-transform"></i>
                    </a>
                @endforeach
            </div>

            <div class="text-center pt-6 border-t border-slate-100">
                <p class="text-slate-500 text-sm">
                    {{ trans('auth.organization_selection_note', ['default' => 'You can switch between organizations later from your settings.']) }}
                </p>
            </div>
        </div>
    </div>
</div>

<script defer src="{{ mix('/manifest.js') }}"></script>
<script defer src="{{ mix('/js/vendor.js') }}"></script>
<script defer src="{{ mix('/js/app.js') }}"></script>
<script defer src="https://cdn.jsdelivr.net/npm/iati-design-system@3.5.0/dist/js/iati.js"></script>
</body>
</html>
