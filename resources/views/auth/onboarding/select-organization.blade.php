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
    <style>
        .org-list { display: grid; gap: 12px; margin-top: 32px; }

        .org-card {
            display: flex; align-items: center; gap: 16px;
            padding: 16px 20px;
            background: rgb(var(--n-0));
            border: 1px solid rgb(var(--n-20));
            border-left: 4px solid rgb(var(--n-20));
            border-radius: 8px;
            color: rgb(var(--n-50));
            text-decoration: none;
            transition: border-color .15s ease, background-color .15s ease;
        }
        a.org-card:hover { border-color: rgb(var(--bluecoral)); background: rgb(var(--n-10)); }
        a.org-card:focus-visible { outline: 2px solid rgb(var(--bluecoral)); outline-offset: 2px; }

        .org-card__icon {
            display: grid; place-items: center; flex-shrink: 0;
            width: 40px; height: 40px; border-radius: 50%;
            background: rgb(var(--n-10)); color: rgb(var(--bluecoral)); font-size: 18px;
        }
        .org-card__body { flex: 1; min-width: 0; }
        .org-card__name { margin: 0; font-size: 17px; font-weight: 700; line-height: 1.4; }
        .org-card__arrow { color: rgb(var(--n-30)); transition: transform .15s ease, color .15s ease; }
        a.org-card:hover .org-card__arrow { color: rgb(var(--bluecoral)); transform: translateX(4px); }

        /* Locked: the user's access to this organisation is still awaiting approval */
        .org-card--locked {
            cursor: not-allowed;
            color: rgb(var(--n-40));
            border-style: dashed;
            border-left-style: solid;
            border-left-color: rgb(var(--camel-50));
            background:
                repeating-linear-gradient(-45deg, transparent 0 10px, rgba(var(--n-20), .35) 10px 11px),
                rgb(var(--n-10));
        }
        .org-card--locked .org-card__icon { background: rgb(var(--n-0)); color: rgb(var(--n-40)); }
        .org-card__note { margin: 2px 0 0; font-size: 13px; line-height: 1.45; color: rgb(var(--n-40)); }
        .org-card__tag {
            flex-shrink: 0; align-self: flex-start;
            padding: 3px 10px; border-radius: 999px;
            background: rgb(var(--camel-10)); color: rgb(var(--n-50));
            font-size: 11px; font-weight: 700; white-space: nowrap;
        }

        @media (max-width: 480px) {
            .org-card { flex-wrap: wrap; }
            .org-card__tag { order: -1; margin-left: 56px; }
        }
    </style>
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
            </div>

            <div class="org-list">
                @foreach($organizations->sortBy(fn ($org) => $org->pivot?->role === 'contributor_pending') as $org)
                    @if($org->pivot?->role === 'contributor_pending')
                        <div class="org-card org-card--locked" aria-disabled="true">
                            <span class="org-card__icon" aria-hidden="true">
                                <svg-vue icon="pw-lock"></svg-vue>
                            </span>
                            <div class="org-card__body">
                                <h3 class="org-card__name">{{ $org->publisher_name }}</h3>
                                <p class="org-card__note">
                                    {{ trans('auth.organization_access_pending_note') }}
                                </p>
                            </div>
                            <span class="org-card__tag">
                                {{ trans('auth.organization_access_pending') }}
                            </span>
                        </div>
                    @else
                        <a href="{{ route('onboarding.process-selection', $org->uuid) }}" class="org-card">
                            <span class="org-card__icon" aria-hidden="true">
                                <svg-vue icon="building"></svg-vue>
                            </span>
                            <div class="org-card__body">
                                <h3 class="org-card__name">{{ $org->publisher_name }}</h3>
                            </div>
                            <span class="org-card__arrow" aria-hidden="true">
                                <svg-vue icon="arrow-right"></svg-vue>
                            </span>
                        </a>
                    @endif
                @endforeach
            </div>

            <div class="text-center pt-6 border-t border-slate-100">
                <p class="text-slate-500 text-sm">
                    {{ trans('auth.organization_selection_note', ['default' => 'You can switch between organisations later from your settings.']) }}
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
