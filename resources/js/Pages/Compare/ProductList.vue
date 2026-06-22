<script setup>
import {Head, Link} from '@inertiajs/vue3';
import AppLayout from "@/Layouts/AppLayout.vue";

defineProps({
    category: {type: Object, required: true},
    sector: {type: Object, required: true},
    products: {type: Array, default: () => []},
});
</script>

<template>
    <Head :title="`${sector.name} — Produits`"/>
    <AppLayout>
        <nav class="mb-6 flex flex-wrap items-center gap-2 text-sm text-zinc-500">
            <Link :href="route('compare.categories')" class="transition-colors hover:text-zinc-900">Catégories</Link>
            <span>›</span>
            <Link :href="route('compare.sectors', category.slug)" class="transition-colors hover:text-zinc-900">
                {{ category.name }}
            </Link>
            <span>›</span>
            <span class="font-medium text-zinc-900">{{ sector.name }}</span>
        </nav>

        <div class="mb-8">
            <h1 class="text-3xl font-bold text-zinc-900">{{ sector.name }}</h1>
            <p v-if="sector.description" class="mt-1 text-zinc-500">{{ sector.description }}</p>
        </div>

        <div v-if="!products.length" class="rounded-xl border border-zinc-200 bg-white p-8 text-center text-zinc-500">
            Aucun produit comparable disponible dans ce secteur.
        </div>

        <div v-else class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <div
                v-for="product in products"
                :key="product.id"
                class="flex flex-col rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm"
            >
                <div class="mb-3 flex items-start justify-between">
                    <h2 class="text-lg font-semibold">{{ product.name }}</h2>
                    <span v-if="product.offers_count > 0"
                          class="rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-700">
                        {{ product.offers_count }} offre{{ product.offers_count > 1 ? 's' : '' }}
                    </span>
                </div>
                <p v-if="product.description" class="mb-4 line-clamp-3 flex-1 text-sm text-zinc-500">
                    {{ product.description }}</p>
                <Link
                    :href="route('compare.wizard', [category.slug, sector.slug, product.slug])"
                    class="mt-auto block w-full rounded-xl bg-blue-600 px-4 py-2.5 text-center text-sm font-semibold text-white transition hover:bg-blue-700"
                >
                    Comparer les offres
                </Link>
            </div>
        </div>
    </AppLayout>
</template>
