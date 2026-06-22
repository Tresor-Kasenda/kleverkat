<script setup>
import { Head, Link } from '@inertiajs/vue3';
import CompareLayout from '@/Layouts/CompareLayout.vue';

defineProps({
    categories: { type: Array, default: () => [] },
});
</script>

<template>
    <Head title="Que souhaitez-vous comparer ?" />
    <CompareLayout>
        <div class="mb-8 text-center">
            <h1 class="mb-2 text-3xl font-bold text-zinc-900">Que souhaitez-vous comparer ?</h1>
            <p class="text-zinc-500">Sélectionnez une catégorie pour démarrer votre comparaison gratuite.</p>
        </div>

        <div v-if="!categories.length" class="rounded-xl border border-zinc-200 bg-white p-8 text-center text-zinc-500">
            Aucune catégorie disponible. Revenez bientôt.
        </div>

        <div v-else class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <Link
                v-for="cat in categories"
                :key="cat.id"
                :href="route('compare.sectors', cat.slug)"
                class="group rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm transition-all hover:border-blue-300 hover:shadow-md"
            >
                <div class="mb-3 flex items-start justify-between">
                    <h2 class="text-lg font-semibold group-hover:text-blue-600">{{ cat.name }}</h2>
                    <span class="rounded-full bg-zinc-100 px-2.5 py-0.5 text-xs text-zinc-600">
                        {{ cat.sectors_count }} secteur{{ cat.sectors_count > 1 ? 's' : '' }}
                    </span>
                </div>
                <p v-if="cat.description" class="line-clamp-2 text-sm text-zinc-500">{{ cat.description }}</p>
                <div class="mt-4 flex items-center gap-1 text-sm font-medium text-blue-600">
                    Comparer
                    <svg class="size-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd"/></svg>
                </div>
            </Link>
        </div>
    </CompareLayout>
</template>
