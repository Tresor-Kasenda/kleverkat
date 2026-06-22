<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';

const page = usePage();
const auth = computed(() => page.props.auth);

const navItems = [
    { label: 'Profil',       route: 'profile.edit' },
    { label: 'Apparence',    route: 'appearance.edit' },
    { label: 'Sécurité',     route: 'security.edit' },
    { label: 'Équipes',      route: 'teams.index' },
];
</script>

<template>
    <AppLayout>
        <div class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-8 md:flex-row">
                <!-- Sidebar nav -->
                <aside class="shrink-0 md:w-48">
                    <nav class="space-y-1">
                        <Link
                            v-for="item in navItems"
                            :key="item.route"
                            :href="route(item.route)"
                            class="block rounded-lg px-3 py-2 text-sm font-medium transition-colors"
                            :class="$page.url.startsWith('/' + item.route.replace('.edit', '').replace('.index', ''))
                                ? 'bg-zinc-100 text-zinc-900'
                                : 'text-zinc-600 hover:bg-zinc-50 hover:text-zinc-900'"
                        >
                            {{ item.label }}
                        </Link>
                    </nav>
                </aside>

                <!-- Content -->
                <main class="flex-1 min-w-0">
                    <slot />
                </main>
            </div>
        </div>
    </AppLayout>
</template>
