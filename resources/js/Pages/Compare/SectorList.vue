<script setup>
import { Head, Link } from '@inertiajs/vue3';
import CompareLayout from '@/Layouts/CompareLayout.vue';

defineProps({
    category: { type: Object, required: true },
    sectors:  { type: Array, default: () => [] },
});
</script>

<template>
    <Head :title="`${category.name} — Secteurs`" />
    <CompareLayout>
        <nav class="mb-6 flex flex-wrap items-center gap-2 text-sm text-zinc-500">
            <Link :href="route('compare.categories')" class="transition-colors hover:text-zinc-900">Catégories</Link>
            <span>›</span>
            <span class="font-medium text-zinc-900">{{ category.name }}</span>
        </nav>

        <div class="mb-8">
            <h1 class="text-3xl font-bold text-zinc-900">{{ category.name }}</h1>
            <p v-if="category.description" class="mt-1 text-zinc-500">{{ category.description }}</p>
        </div>

        <div v-if="!sectors.length" class="rounded-xl border border-zinc-200 bg-white p-8 text-center text-zinc-500">
            Aucun secteur disponible.
        </div>

        <div v-else class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <Link
                v-for="sector in sectors"
                :key="sector.id"
                :href="route('compare.products', [category.slug, sector.slug])"
                class="group rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm transition-all hover:border-blue-300 hover:shadow-md"
            >
                <div class="mb-3 flex items-start justify-between">
                    <h2 class="text-lg font-semibold group-hover:text-blue-600">{{ sector.name }}</h2>
                    <span class="rounded-full bg-zinc-100 px-2.5 py-0.5 text-xs text-zinc-600">
                        {{ sector.products_count }} produit{{ sector.products_count > 1 ? 's' : '' }}
                    </span>
                </div>
                <p v-if="sector.description" class="line-clamp-2 text-sm text-zinc-500">{{ sector.description }}</p>
                <div class="mt-4 flex items-center gap-1 text-sm font-medium text-blue-600">
                    Voir les produits
                    <svg class="size-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd"/></svg>
                </div>
            </Link>
        </div>
    </CompareLayout>
</template>
