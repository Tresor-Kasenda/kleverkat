<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.head')
</head>
<body class="min-h-screen bg-zinc-50 dark:bg-zinc-950 text-zinc-900 dark:text-zinc-100">

<header class="border-b border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 sticky top-0 z-10">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-14 items-center justify-between">
            <a href="{{ route('compare.categories') }}" class="flex items-center gap-2 font-semibold text-zinc-900 dark:text-zinc-100">
                <x-app-logo-icon class="size-6 text-zinc-800 dark:text-zinc-200" />
                <span>{{ config('app.name') }}</span>
            </a>

            <nav class="flex items-center gap-4 text-sm">
                <a href="{{ route('compare.categories') }}" class="text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100 transition-colors">
                    Comparer
                </a>
                @auth
                    <a href="{{ route('dashboard') }}" class="text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100 transition-colors">
                        Mon espace
                    </a>
                @else
                    <a href="{{ route('login') }}" class="text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100 transition-colors">
                        Connexion
                    </a>
                @endauth
            </nav>
        </div>
    </div>
</header>

<main class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
    {{ $slot }}
</main>

<footer class="mt-16 border-t border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 py-6 text-center text-sm text-zinc-500">
    © {{ date('Y') }} {{ config('app.name') }} — Comparez les meilleures offres
</footer>

@fluxScripts
</body>
</html>
