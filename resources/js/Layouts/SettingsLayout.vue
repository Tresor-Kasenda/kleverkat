<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const page = usePage();

const navItems = [
    { label: 'Profil',     route: 'profile.edit',    path: '/profile' },
    { label: 'Apparence',  route: 'appearance.edit',  path: '/appearance' },
    { label: 'Sécurité',   route: 'security.edit',    path: '/security' },
    { label: 'Équipes',    route: 'teams.index',       path: '/teams' },
];

function isActive(path) {
    return page.url.startsWith(path);
}
</script>

<template>
    <AppLayout>
        <div class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-8 md:flex-row">
                <!-- Sidebar -->
                <aside class="shrink-0 md:w-52">
                    <nav class="space-y-0.5">
                        <Link
                            v-for="item in navItems"
                            :key="item.route"
                            :href="route(item.route)"
                            class="sidebar-nav-item text-sm"
                            :class="isActive(item.path) ? 'active' : ''"
                        >
                            {{ item.label }}
                        </Link>
                    </nav>
                </aside>

                <!-- Content -->
                <main class="min-w-0 flex-1">
                    <slot />
                </main>
            </div>
        </div>
    </AppLayout>
</template>
