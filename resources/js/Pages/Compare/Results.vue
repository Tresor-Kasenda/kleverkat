<script setup>
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import CompareLayout from '@/Layouts/CompareLayout.vue';

const props = defineProps({
    session: { type: Object, required: true },
    results: { type: Array,  default: () => [] },
});

const showModal = ref(false);
const selectedResult = ref(null);
const actionType = ref('');
const form = ref({ first_name: '', last_name: '', email: '', phone: '' });
const sending = ref(false);
const sent = ref(false);
const formError = ref('');

function openLead(result, type) {
    selectedResult.value = result;
    actionType.value = type;
    showModal.value = true;
    sent.value = false;
    formError.value = '';
    form.value = { first_name: '', last_name: '', email: '', phone: '' };
}

async function submitLead() {
    sending.value = true;
    formError.value = '';
    try {
        const res = await fetch(route('compare.leads.create', selectedResult.value.id), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ ...form.value, action_type: actionType.value }),
        });
        if (!res.ok) { throw new Error(); }
        sent.value = true;
    } catch {
        formError.value = 'Une erreur est survenue. Réessayez.';
    } finally {
        sending.value = false;
    }
}
</script>

<template>
    <Head title="Résultats de comparaison" />
    <CompareLayout>
        <nav class="mb-6 flex flex-wrap items-center gap-2 text-sm text-zinc-500">
            <Link :href="route('compare.categories')" class="transition-colors hover:text-zinc-900">Catégories</Link>
            <span>›</span>
            <Link
                :href="route('compare.sectors', session.product.sector.category.slug)"
                class="transition-colors hover:text-zinc-900"
            >{{ session.product.sector.category.name }}</Link>
            <span>›</span>
            <span class="font-medium text-zinc-900">Résultats — {{ session.product.name }}</span>
        </nav>

        <div class="mb-8">
            <h1 class="text-3xl font-bold text-zinc-900">Résultats pour {{ session.product.name }}</h1>
            <p class="mt-1 text-zinc-500">{{ results.length }} offre{{ results.length > 1 ? 's' : '' }} comparée{{ results.length > 1 ? 's' : '' }}, classées par prix</p>
        </div>

        <div v-if="!results.length" class="rounded-xl border border-zinc-200 bg-white p-8 text-center text-zinc-500">
            Aucune offre disponible pour ce produit.
        </div>

        <div v-else class="space-y-4">
            <div
                v-for="(r, idx) in results"
                :key="r.id"
                class="relative rounded-2xl border bg-white p-6 shadow-sm transition hover:shadow-md"
                :class="r.offer.is_featured ? 'border-blue-300 ring-1 ring-blue-200' : 'border-zinc-200'"
            >
                <div v-if="r.offer.is_featured" class="absolute -top-2.5 left-6">
                    <span class="rounded-full bg-blue-600 px-3 py-0.5 text-xs font-semibold text-white">Recommandé</span>
                </div>

                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <!-- Infos offre -->
                    <div class="flex-1">
                        <div class="mb-1 flex items-center gap-3">
                            <span class="text-sm font-semibold text-zinc-400">#{{ idx + 1 }}</span>
                            <h2 class="text-lg font-bold text-zinc-900">{{ r.offer.company.name }}</h2>
                            <span class="text-sm text-zinc-500">— {{ r.offer.name }}</span>
                        </div>
                        <p v-if="r.offer.short_description" class="mb-3 text-sm text-zinc-600">{{ r.offer.short_description }}</p>

                        <!-- Features -->
                        <div class="flex flex-wrap gap-2">
                            <span
                                v-for="f in r.offer.features"
                                :key="f.label"
                                class="rounded-lg px-2.5 py-1 text-xs"
                                :class="f.is_highlight ? 'bg-blue-50 text-blue-700 font-medium' : 'bg-zinc-100 text-zinc-600'"
                            >
                                {{ f.label }} : {{ f.value }}
                            </span>
                        </div>
                    </div>

                    <!-- Prix & CTA -->
                    <div class="flex shrink-0 flex-col items-end gap-3">
                        <div class="text-right">
                            <span class="text-2xl font-bold text-zinc-900">{{ Number(r.price).toLocaleString('fr-FR', { style: 'currency', currency: 'EUR' }) }}</span>
                            <span v-if="r.offer.price_note" class="block text-xs text-zinc-500">{{ r.offer.price_note }}</span>
                        </div>
                        <div class="flex gap-2">
                            <button
                                @click="openLead(r, 'quote_request')"
                                class="rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700"
                            >
                                Devis gratuit
                            </button>
                            <a
                                v-if="r.offer.company.website_url"
                                :href="r.offer.company.website_url"
                                target="_blank"
                                rel="noopener"
                                class="rounded-xl border border-zinc-300 px-4 py-2 text-sm font-medium text-zinc-700 transition hover:bg-zinc-50"
                            >
                                Voir le site →
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recommencer -->
        <div class="mt-8 text-center">
            <Link
                :href="route('compare.categories')"
                class="text-sm font-medium text-blue-600 hover:underline"
            >
                ← Nouvelle comparaison
            </Link>
        </div>

        <!-- Modal lead -->
        <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4">
            <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
                <template v-if="sent">
                    <div class="text-center">
                        <div class="mx-auto mb-4 flex size-12 items-center justify-center rounded-full bg-emerald-100">
                            <svg class="size-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <h3 class="mb-2 text-lg font-bold">Demande envoyée !</h3>
                        <p class="mb-6 text-sm text-zinc-500">Nous vous contacterons très prochainement.</p>
                        <button @click="showModal = false" class="rounded-xl bg-zinc-100 px-6 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-200">Fermer</button>
                    </div>
                </template>
                <template v-else>
                    <h3 class="mb-4 text-lg font-bold">Vos coordonnées</h3>
                    <div class="space-y-3">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="mb-1 block text-xs font-medium text-zinc-700">Prénom *</label>
                                <input v-model="form.first_name" type="text" class="w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none" />
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-medium text-zinc-700">Nom *</label>
                                <input v-model="form.last_name" type="text" class="w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none" />
                            </div>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-zinc-700">E-mail *</label>
                            <input v-model="form.email" type="email" class="w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none" />
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-zinc-700">Téléphone</label>
                            <input v-model="form.phone" type="tel" class="w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none" />
                        </div>
                        <p v-if="formError" class="text-xs text-red-600">{{ formError }}</p>
                    </div>
                    <div class="mt-4 flex gap-3">
                        <button @click="showModal = false" class="flex-1 rounded-xl border border-zinc-300 px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50">Annuler</button>
                        <button @click="submitLead" :disabled="sending" class="flex-1 rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 disabled:opacity-50">
                            {{ sending ? 'Envoi…' : 'Envoyer' }}
                        </button>
                    </div>
                </template>
            </div>
        </div>
    </CompareLayout>
</template>
