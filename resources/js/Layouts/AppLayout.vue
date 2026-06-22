<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const auth = computed(() => page.props.auth);
const flash = computed(() => page.props.flash);
</script>

<template>
    <div class="min-h-screen bg-bg text-fg antialiased">
        <!-- Header -->
        <header class="sticky top-0 z-20 border-b border-border bg-bg shadow-sm">
            <div class="mx-auto flex h-16 max-w-7xl items-center justify-between gap-6 px-4 sm:px-6 lg:px-8">
                <!-- Logo -->
                <Link :href="route('home')" class="flex shrink-0 items-center gap-2.5 font-bold text-primary-600">
                    <svg class="size-7" viewBox="0 0 40 40" fill="none">
                        <rect width="40" height="40" rx="10" fill="currentColor"/>
                        <path d="M10 20h20M20 10v20" stroke="white" stroke-width="3" stroke-linecap="round"/>
                    </svg>
                    <span class="text-xl tracking-tight text-fg-title">{{ $page.props.appName ?? 'KleverKat' }}</span>
                </Link>

                <!-- Nav slot -->
                <nav class="hidden items-center gap-1 md:flex">
                    <slot name="nav" />
                </nav>

                <!-- Auth -->
                <div class="flex shrink-0 items-center gap-3">
                    <template v-if="auth?.user">
                        <Link
                            :href="route('dashboard', auth.currentTeam?.slug ?? '')"
                            class="rounded-lg px-4 py-2 text-sm font-medium text-fg-subtext transition-colors hover:text-fg-title hover:bg-bg-high"
                        >
                            Mon espace
                        </Link>
                    </template>
                    <template v-else>
                        <Link
                            :href="route('login')"
                            class="btn btn-outline btn-outline-primary btn-sm rounded-lg"
                        >
                            Me connecter
                        </Link>
                    </template>
                </div>
            </div>
        </header>

        <!-- Flash messages -->
        <div v-if="flash?.success || flash?.error" class="mx-auto max-w-7xl px-4 pt-4 sm:px-6 lg:px-8">
            <div v-if="flash?.success" class="rounded-lg border border-success-200 bg-success-50 px-4 py-3 text-sm text-success-800">
                {{ flash.success }}
            </div>
            <div v-if="flash?.error" class="rounded-lg border border-danger-200 bg-danger-50 px-4 py-3 text-sm text-danger-800">
                {{ flash.error }}
            </div>
        </div>

        <!-- Content -->
        <main>
            <slot />
        </main>
    </div>
</template>
