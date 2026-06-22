<script setup>
import { Link } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const auth = computed(() => page.props.auth);
</script>

<template>
    <div class="min-h-screen bg-zinc-50 antialiased">
        <!-- Header -->
        <header class="sticky top-0 z-10 border-b border-zinc-200 bg-white">
            <div class="mx-auto flex h-14 max-w-6xl items-center justify-between px-4 sm:px-6 lg:px-8">
                <Link :href="route('compare.categories')" class="flex items-center gap-2 font-semibold text-zinc-900">
                    <svg class="size-6" viewBox="0 0 40 40" fill="none">
                        <rect width="40" height="40" rx="10" fill="#2563eb"/>
                        <path d="M10 20h20M20 10v20" stroke="white" stroke-width="3" stroke-linecap="round"/>
                    </svg>
                    <span>KleverKat</span>
                </Link>

                <nav class="flex items-center gap-4 text-sm">
                    <Link :href="route('compare.categories')" class="text-zinc-600 transition-colors hover:text-zinc-900">
                        Comparer
                    </Link>
                    <template v-if="auth?.user">
                        <Link :href="route('dashboard', auth.currentTeam?.slug ?? '')" class="text-zinc-600 transition-colors hover:text-zinc-900">
                            Mon espace
                        </Link>
                    </template>
                    <template v-else>
                        <Link :href="route('login')" class="text-zinc-600 transition-colors hover:text-zinc-900">
                            Connexion
                        </Link>
                    </template>
                </nav>
            </div>
        </header>

        <main class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
            <slot />
        </main>

        <footer class="mt-16 border-t border-zinc-200 bg-white py-6 text-center text-sm text-zinc-500">
            © {{ new Date().getFullYear() }} KleverKat — Comparateur en ligne gratuit
        </footer>
    </div>
</template>
