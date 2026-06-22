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
        <div class="mb-10 text-center">
            <h1 class="mb-2 text-3xl font-bold text-fg-title">Que souhaitez-vous comparer ?</h1>
            <p class="text-fg-subtext">Sélectionnez une catégorie pour démarrer votre comparaison gratuite.</p>
        </div>

        <div v-if="!categories.length" class="rounded-xl border border-border bg-bg p-8 text-center text-fg-subtext">
            Aucune catégorie disponible. Revenez bientôt.
        </div>

        <div v-else class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <Link
                v-for="cat in categories"
                :key="cat.id"
                :href="route('compare.sectors', cat.slug)"
                class="group rounded-2xl border border-border bg-bg p-6 shadow-sm transition-all hover:border-primary-300 hover:shadow-md"
            >
                <div class="mb-3 flex items-start justify-between">
                    <h2 class="text-lg font-semibold text-fg-subtitle group-hover:text-primary-600">{{ cat.name }}</h2>
                    <span class="rounded-full bg-bg-high px-2.5 py-0.5 text-xs text-fg-subtext">
                        {{ cat.sectors_count }} secteur{{ cat.sectors_count > 1 ? 's' : '' }}
                    </span>
                </div>
                <p v-if="cat.description" class="line-clamp-2 text-sm text-fg-subtext">{{ cat.description }}</p>
                <div class="mt-4 flex items-center gap-1 text-sm font-medium text-primary-600">
                    Comparer
                    <svg class="size-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd"/>
                    </svg>
                </div>
            </Link>
        </div>
    </CompareLayout>
</template>
