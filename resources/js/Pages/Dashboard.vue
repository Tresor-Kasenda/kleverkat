<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { computed } from 'vue';

defineProps({
    team: { type: Object, default: null },
});

const page = usePage();
const user = computed(() => page.props.auth?.user);
</script>

<template>
    <Head title="Tableau de bord" />
    <AppLayout>
        <div class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8">
            <h1 class="mb-2 text-2xl font-bold text-fg-title">Bonjour, {{ user?.name }}</h1>
            <p class="mb-8 text-fg-subtext">Bienvenue dans votre espace {{ team?.name }}.</p>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <Link
                    :href="route('company.profile', team?.slug ?? '')"
                    class="group rounded-2xl border border-border bg-bg p-6 shadow-sm transition-all hover:border-primary-300 hover:shadow-md"
                >
                    <div class="mb-3 flex size-10 items-center justify-center rounded-xl bg-primary-100 text-primary-600">
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </div>
                    <h2 class="font-semibold text-fg-subtitle group-hover:text-primary-600">Profil entreprise</h2>
                    <p class="mt-1 text-sm text-fg-subtext">Gérer les informations de votre entreprise</p>
                </Link>

                <Link
                    :href="route('teams.index')"
                    class="group rounded-2xl border border-border bg-bg p-6 shadow-sm transition-all hover:border-secondary-300 hover:shadow-md"
                >
                    <div class="mb-3 flex size-10 items-center justify-center rounded-xl bg-secondary-100 text-secondary-700">
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <h2 class="font-semibold text-fg-subtitle group-hover:text-secondary-700">Équipes</h2>
                    <p class="mt-1 text-sm text-fg-subtext">Gérer vos équipes et membres</p>
                </Link>

                <Link
                    :href="route('profile.edit')"
                    class="group rounded-2xl border border-border bg-bg p-6 shadow-sm transition-all hover:border-accent-300 hover:shadow-md"
                >
                    <div class="mb-3 flex size-10 items-center justify-center rounded-xl bg-accent-100 text-accent-700">
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </div>
                    <h2 class="font-semibold text-fg-subtitle group-hover:text-accent-700">Mon profil</h2>
                    <p class="mt-1 text-sm text-fg-subtext">Paramètres de votre compte</p>
                </Link>
            </div>
        </div>
    </AppLayout>
</template>
