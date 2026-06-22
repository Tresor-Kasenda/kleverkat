<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.head')
</head>
<body class="min-h-screen bg-white text-zinc-900 dark:text-zinc-100 antialiased">

{{-- Header white ─────────────────────────────────────────────────────────── --}}
<header class="sticky top-0 z-20 border-b border-zinc-100 bg-white shadow-sm">
    <div class="mx-auto flex h-16 max-w-7xl items-center justify-between gap-6 px-4 sm:px-6 lg:px-8">

        {{-- Logo --}}
        <a href="{{ route('home') }}" class="flex shrink-0 items-center gap-2.5 font-bold text-blue-600">
            <x-app-logo-icon class="size-7 text-blue-600" />
            <span class="text-xl tracking-tight">{{ config('app.name') }}</span>
        </a>

        {{-- Nav catégories --}}
        @php
            $navCategories = \App\Models\Category::where('is_active', true)->orderBy('sort_order')->get();
        @endphp
        <nav class="hidden items-center gap-1 md:flex">
            @foreach ($navCategories as $navCat)
                <a
                    href="{{ route('compare.sectors', $navCat->slug) }}"
                    wire:navigate
                    class="flex items-center gap-1 rounded-lg px-3 py-2 text-sm font-medium text-zinc-700 transition hover:bg-zinc-50 hover:text-blue-600"
                >
                    {{ $navCat->name }}
                    <svg class="size-3.5 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                    </svg>
                </a>
            @endforeach
        </nav>

        {{-- CTA auth --}}
        <div class="flex shrink-0 items-center gap-3">
            @auth
                <a
                    href="{{ route('dashboard') }}"
                    wire:navigate
                    class="rounded-lg px-4 py-2 text-sm font-medium text-zinc-700 transition hover:text-blue-600"
                >
                    Mon espace
                </a>
            @else
                <a
                    href="{{ route('login') }}"
                    class="rounded-lg border border-blue-600 px-5 py-2 text-sm font-semibold text-blue-600 transition hover:bg-blue-50"
                >
                    Me connecter
                </a>
            @endauth
        </div>
    </div>
</header>

{{-- Contenu --}}
<main>{{ $slot }}</main>

@fluxScripts
</body>
</html>
