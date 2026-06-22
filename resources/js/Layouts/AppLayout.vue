<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const auth = computed(() => page.props.auth);
const flash = computed(() => page.props.flash);
</script>

<template>
    <div class="min-h-screen bg-white text-zinc-900 antialiased">
        <!-- Header -->
        <header class="sticky top-0 z-20 border-b border-zinc-100 bg-white shadow-sm">
            <div class="mx-auto flex h-16 max-w-7xl items-center justify-between gap-6 px-4 sm:px-6 lg:px-8">
                <!-- Logo -->
                <Link :href="route('home')" class="flex shrink-0 items-center gap-2.5 font-bold text-blue-600">
                    <svg class="size-7" viewBox="0 0 40 40" fill="none">
                        <rect width="40" height="40" rx="10" fill="#2563eb"/>
                        <path d="M10 20h20M20 10v20" stroke="white" stroke-width="3" stroke-linecap="round"/>
                    </svg>
                    <span class="text-xl tracking-tight">{{ $page.props.appName ?? 'KleverKat' }}</span>
                </Link>

                <!-- Navigation catégories -->
                <nav class="hidden items-center gap-1 md:flex">
                    <slot name="nav" />
                </nav>

                <!-- Auth -->
                <div class="flex shrink-0 items-center gap-3">
                    <template v-if="auth?.user">
                        <Link
                            :href="route('dashboard', auth.currentTeam?.slug ?? '')"
                            class="rounded-lg px-4 py-2 text-sm font-medium text-zinc-700 transition hover:text-blue-600"
                        >
                            Mon espace
                        </Link>
                    </template>
                    <template v-else>
                        <Link
                            :href="route('login')"
                            class="rounded-lg border border-blue-600 px-5 py-2 text-sm font-semibold text-blue-600 transition hover:bg-blue-50"
                        >
                            Me connecter
                        </Link>
                    </template>
                </div>
            </div>
        </header>

        <!-- Flash messages -->
        <div v-if="flash?.success || flash?.error" class="mx-auto max-w-7xl px-4 pt-4 sm:px-6 lg:px-8">
            <div v-if="flash?.success" class="rounded-lg bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm text-emerald-800">
                {{ flash.success }}
            </div>
            <div v-if="flash?.error" class="rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-800">
                {{ flash.error }}
            </div>
        </div>

        <!-- Content -->
        <main>
            <slot />
        </main>
    </div>
</template>
