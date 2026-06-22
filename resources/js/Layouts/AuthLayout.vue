<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    title: { type: String, default: '' },
    description: { type: String, default: '' },
});
</script>

<template>
    <div class="min-h-screen bg-bg-light flex flex-col items-center justify-center px-4 py-12">
        <!-- Logo -->
        <Link :href="route('home')" class="mb-8 flex items-center gap-2.5 font-bold text-primary-600">
            <svg class="size-8" viewBox="0 0 40 40" fill="none">
                <rect width="40" height="40" rx="10" fill="currentColor"/>
                <path d="M10 20h20M20 10v20" stroke="white" stroke-width="3" stroke-linecap="round"/>
            </svg>
            <span class="text-2xl tracking-tight text-fg-title">KleverKat</span>
        </Link>

        <!-- Card -->
        <div class="w-full max-w-md rounded-2xl border border-border bg-bg p-8 shadow-lg">
            <div v-if="title" class="mb-6 text-center">
                <h1 class="text-2xl font-bold text-fg-title">{{ title }}</h1>
                <p v-if="description" class="mt-1 text-sm text-fg-subtext">{{ description }}</p>
            </div>
            <slot />
        </div>
    </div>
</template>
