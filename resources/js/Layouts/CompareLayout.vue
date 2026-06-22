<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const auth = computed(() => page.props.auth);
</script>

<template>
    <div class="min-h-screen bg-bg-light antialiased">
        <!-- Header -->
        <header class="sticky top-0 z-10 border-b border-border bg-bg">
            <div class="mx-auto flex h-14 max-w-6xl items-center justify-between px-4 sm:px-6 lg:px-8">
                <Link :href="route('compare.categories')" class="flex items-center gap-2.5 font-semibold text-primary-600">
                    <svg class="size-6" viewBox="0 0 40 40" fill="none">
                        <rect width="40" height="40" rx="10" fill="currentColor"/>
                        <path d="M10 20h20M20 10v20" stroke="white" stroke-width="3" stroke-linecap="round"/>
                    </svg>
                    <span class="text-fg-title">KleverKat</span>
                </Link>

                <nav class="flex items-center gap-4 text-sm">
                    <Link :href="route('compare.categories')" class="text-fg-subtext transition-colors hover:text-fg-title">
                        Comparer
                    </Link>
                    <template v-if="auth?.user">
                        <Link :href="route('dashboard', auth.currentTeam?.slug ?? '')" class="text-fg-subtext transition-colors hover:text-fg-title">
                            Mon espace
                        </Link>
                    </template>
                    <template v-else>
                        <Link :href="route('login')" class="btn btn-outline btn-outline-primary btn-sm rounded-lg">
                            Connexion
                        </Link>
                    </template>
                </nav>
            </div>
        </header>

        <main class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
            <slot />
        </main>

        <footer class="mt-16 border-t border-border bg-bg py-6 text-center text-sm text-fg-subtext">
            © {{ new Date().getFullYear() }} KleverKat — Comparateur en ligne gratuit
        </footer>
    </div>
</template>
