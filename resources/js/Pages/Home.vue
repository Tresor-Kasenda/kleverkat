<script setup>
import {computed, ref} from 'vue';
import {Head, Link} from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    categories: {type: Array, default: () => []},
});

const view = ref('categories');
const selectedCategory = ref(null);
const selectedSector = ref(null);
const sectors = ref([]);
const products = ref([]);
const showAll = ref(false);

const items = computed(() => {
    if (view.value === 'sectors') return sectors.value;
    if (view.value === 'products') return products.value;
    return props.categories;
});

const visibleItems = computed(() => showAll.value ? items.value : items.value.slice(0, 6));
const hasMore = computed(() => !showAll.value && items.value.length > 6);

function browseCategory(category) {
    selectedCategory.value = category;
    sectors.value = category.sectors ?? [];
    selectedSector.value = null;
    products.value = [];
    showAll.value = false;
    view.value = 'sectors';
}

function browseSector(sector) {
    selectedSector.value = sector;
    products.value = sector.products ?? [];
    showAll.value = false;
    view.value = 'products';
}

function back() {
    if (view.value === 'products') {
        view.value = 'sectors';
        selectedSector.value = null;
        products.value = [];
    } else if (view.value === 'sectors') {
        view.value = 'categories';
        selectedCategory.value = null;
        sectors.value = [];
    }
    showAll.value = false;
}

const palettes = [
    {bg: 'bg-orange-100', icon: 'text-orange-500'},
    {bg: 'bg-blue-100', icon: 'text-blue-500'},
    {bg: 'bg-emerald-100', icon: 'text-emerald-500'},
    {bg: 'bg-violet-100', icon: 'text-violet-500'},
    {bg: 'bg-rose-100', icon: 'text-rose-500'},
    {bg: 'bg-amber-100', icon: 'text-amber-500'},
];

const iconPaths = [
    'M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z',
    'M8.25 21v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21m0 0h4.5V3.545M12.75 21h7.5V10.75M2.25 21h1.5m18 0h-18M2.25 9l4.5-1.636M18.75 3l-1.5.545m0 6.205l3 1m1.5.5l-1.5-.5M6.75 7.364V3h-3v18m3-13.636l10.5-3.819',
    'M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z',
    'M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z',
    'M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 8.25h3m-3 3h3m-3 3h3',
    'M12 18v-5.25m0 0a6.01 6.01 0 001.5-.189m-1.5.189a6.01 6.01 0 01-1.5-.189m3.75 7.478a12.06 12.06 0 01-4.5 0m3.75 2.383a14.406 14.406 0 01-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.316a7.5 7.5 0 10-7.517 0c.85.493 1.509 1.333 1.509 2.316V18',
];
</script>

<template>
    <Head title="Comparez et économisez"/>
    <AppLayout>
        <template #nav>
            <Link
                v-for="cat in categories"
                :key="cat.id"
                :href="route('compare.sectors', cat.slug)"
                class="flex items-center gap-1 rounded-lg px-3 py-2 text-sm font-medium text-zinc-700 transition hover:bg-zinc-50 hover:text-blue-600"
                view-transition
            >
                {{ cat.name }}
                <svg class="size-3.5 text-zinc-400" fill="none" stroke="currentColor" stroke-width="2"
                     viewBox="0 0 24 24">
                    <path d="M19.5 8.25l-7.5 7.5-7.5-7.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </Link>
        </template>

        <!-- Gradient section -->
        <div class="min-h-[92vh] bg-linear-to-b from-sky-300 via-blue-400 to-blue-700 pb-16 pt-12">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

                <!-- Breadcrumb / Title -->
                <div class="mb-8">
                    <template v-if="view === 'categories'">
                        <h1 class="text-3xl font-bold text-white sm:text-4xl">
                            <span class="mr-2 text-orange-400">✦</span>
                            Avec KleverKat, comparer c'est gagner
                        </h1>
                    </template>
                    <template v-else>
                        <div class="mb-5 flex items-center gap-3">
                            <button
                                class="flex items-center gap-1.5 rounded-full bg-white/20 px-4 py-2 text-sm font-medium text-white backdrop-blur-sm transition hover:bg-white/30"
                                @click="back"
                            >
                                <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2"
                                     viewBox="0 0 24 24">
                                    <path d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" stroke-linecap="round"
                                          stroke-linejoin="round"/>
                                </svg>
                                Retour
                            </button>
                            <nav class="flex items-center gap-2 text-sm text-white/80">
                                <button class="hover:text-white transition-colors"
                                        @click="view = 'categories'; selectedCategory = null; sectors = [];">Accueil
                                </button>
                                <template v-if="selectedCategory">
                                    <span class="text-white/50">/</span>
                                    <button
                                        :class="view === 'sectors' ? 'text-white font-medium pointer-events-none' : 'hover:text-white transition-colors'"
                                        @click="view === 'products' ? (view = 'sectors', selectedSector = null, products = []) : null"
                                    >{{ selectedCategory.name }}
                                    </button>
                                </template>
                                <template v-if="selectedSector">
                                    <span class="text-white/50">/</span>
                                    <span class="text-white font-medium">{{ selectedSector.name }}</span>
                                </template>
                            </nav>
                        </div>
                        <h1 class="text-2xl font-bold text-white sm:text-3xl">
                            <span class="mr-2 text-orange-400">✦</span>
                            {{ view === 'sectors' ? selectedCategory?.name : selectedSector?.name }}
                        </h1>
                    </template>
                </div>

                <!-- Cards grid -->
                <div v-if="items.length === 0"
                     class="rounded-2xl bg-white/20 p-12 text-center text-white backdrop-blur-sm">
                    <p class="text-lg font-medium opacity-80">Aucun élément disponible pour le moment.</p>
                </div>

                <template v-else>
                    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3">
                        <template v-for="(item, idx) in visibleItems" :key="item.id">
                            <!-- Product card → navigate to wizard -->
                            <Link
                                v-if="view === 'products'"
                                :href="route('compare.wizard', [selectedCategory.slug, selectedSector.slug, item.slug])"
                                class="group flex flex-col items-center rounded-2xl bg-white p-6 text-center shadow-md transition-all duration-200 hover:-translate-y-1 hover:shadow-xl"
                            >
                                <div
                                    :class="['mb-4 flex size-16 items-center justify-center rounded-2xl', palettes[idx % palettes.length].bg]">
                                    <svg :class="['size-8', palettes[idx % palettes.length].icon]" fill="none"
                                         stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path :d="iconPaths[idx % iconPaths.length]" stroke-linecap="round"
                                              stroke-linejoin="round"/>
                                    </svg>
                                </div>
                                <h3 class="mb-1 text-sm font-semibold text-zinc-900 sm:text-base">{{ item.name }}</h3>
                                <p v-if="item.offers_count > 0" class="text-xs font-medium text-blue-600 sm:text-sm">
                                    {{ item.offers_count }} offre{{ item.offers_count > 1 ? 's' : '' }}
                                </p>
                                <p v-else class="text-xs text-zinc-400">Bientôt disponible</p>
                            </Link>

                            <!-- Category / Sector card → drill down -->
                            <button
                                v-else
                                class="group flex flex-col items-center rounded-2xl bg-white p-6 text-center shadow-md transition-all duration-200 hover:-translate-y-1 hover:shadow-xl"
                                @click="view === 'categories' ? browseCategory(item) : browseSector(item)"
                            >
                                <div
                                    :class="['mb-4 flex size-16 items-center justify-center rounded-2xl', palettes[idx % palettes.length].bg]">
                                    <svg :class="['size-8', palettes[idx % palettes.length].icon]" fill="none"
                                         stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path :d="iconPaths[idx % iconPaths.length]" stroke-linecap="round"
                                              stroke-linejoin="round"/>
                                    </svg>
                                </div>
                                <h3 class="mb-1 text-sm font-semibold text-zinc-900 sm:text-base">{{ item.name }}</h3>
                                <p class="text-xs font-medium text-blue-600 sm:text-sm">
                                    <template v-if="view === 'categories'">{{ item.sectors_count }}
                                        secteur{{ item.sectors_count > 1 ? 's' : '' }}
                                    </template>
                                    <template v-else>{{ item.products_count }}
                                        produit{{ item.products_count > 1 ? 's' : '' }}
                                    </template>
                                </p>
                            </button>
                        </template>
                    </div>

                    <!-- Voir plus -->
                    <div v-if="hasMore" class="mt-8 text-center">
                        <button
                            class="inline-flex items-center gap-2 text-sm font-semibold text-white transition hover:text-white/80"
                            @click="showAll = true"
                        >
                            Voir plus
                            <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M19.5 8.25l-7.5 7.5-7.5-7.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
                    </div>
                </template>
            </div>
        </div>

        <!-- Footer -->
        <footer class="border-t border-zinc-100 bg-white py-6 text-center text-sm text-zinc-400">
            © {{ new Date().getFullYear() }} KleverKat — Comparateur en ligne gratuit
        </footer>
    </AppLayout>
</template>
