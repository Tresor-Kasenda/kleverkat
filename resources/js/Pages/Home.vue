<script setup>
import {computed, ref} from 'vue';
import {Head, Link} from '@inertiajs/vue3';
import {
    ArrowLeft,
    ArrowRight,
    BadgeCheck,
    Banknote,
    Building2,
    Car,
    CheckCircle2,
    CreditCard,
    HeartPulse,
    Leaf,
    Plane,
    ShieldCheck,
    Sparkles,
    Zap,
} from 'lucide-vue-next';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    categories: {type: Array, default: () => []},
});

const view = ref('insuranceProducts');
const selectedCategory = ref(null);
const selectedSector = ref(null);
const sectors = ref([]);
const products = ref([]);
const showAll = ref(false);

const insuranceCategory = computed(() => props.categories.find((category) => {
    const haystack = normalizeText(`${category?.name ?? ''} ${category?.slug ?? ''}`);

    return haystack.includes('assurance') || haystack.includes('insurance');
}) ?? null);

const insuranceProducts = computed(() => {
    if (!insuranceCategory.value) {
        return [];
    }

    return (insuranceCategory.value.sectors ?? []).flatMap((sector) => (sector.products ?? []).map((product) => ({
        ...product,
        category: insuranceCategory.value,
        sector,
    })));
});

const items = computed(() => {
    if (view.value === 'insuranceProducts') return insuranceProducts.value;
    if (view.value === 'sectors') return sectors.value;
    if (view.value === 'products') return products.value;

    return props.categories;
});

const visibleItems = computed(() => showAll.value ? items.value : items.value.slice(0, 6));
const hasMore = computed(() => !showAll.value && items.value.length > 6);

const viewCopy = computed(() => {
    if (view.value === 'insuranceProducts') {
        return {
            eyebrow: "Produits d'assurance",
            title: "Trouvez l'assurance qui vous correspond",
            subtitle: 'Auto, habitation, santé ou voyage : choisissez votre besoin et lancez le bon comparatif.',
        };
    }

    if (view.value === 'sectors') {
        return {
            eyebrow: 'Choisissez un secteur',
            title: selectedCategory.value?.name ?? 'Choisissez votre besoin',
            subtitle: 'Affinez votre recherche pour accéder au comparatif le plus pertinent.',
        };
    }

    if (view.value === 'products') {
        return {
            eyebrow: 'Choisissez un produit',
            title: selectedSector.value?.name ?? 'Sélectionnez une offre',
            subtitle: 'Lancez le parcours adapté à votre situation et comparez les options disponibles.',
        };
    }

    return {
        eyebrow: 'Choisissez une catégorie',
        title: "Trouvez l'offre qui vous correspond",
        subtitle: 'KleverKat vous guide vers les bons comparatifs, sans jargon et sans perte de temps.',
    };
});

const primaryActionLabel = computed(() => {
    if (view.value === 'insuranceProducts') return 'Comparer le premier produit';
    if (view.value === 'sectors') return 'Voir les produits';
    if (view.value === 'products') return 'Comparer le premier produit';

    return 'Démarrer ma comparaison';
});

const primaryProductHref = computed(() => {
    const firstProduct = visibleItems.value[0] ?? items.value[0];

    if (!firstProduct || !firstProduct.category || !firstProduct.sector) {
        return null;
    }

    return route('compare.wizard', [firstProduct.category.slug, firstProduct.sector.slug, firstProduct.slug]);
});

const reassuranceItems = [
    {label: 'Gratuit', icon: CheckCircle2},
    {label: 'Sans engagement', icon: Leaf},
];

const visualRules = [
    {keywords: ['auto', 'automobile', 'voiture'], icon: Car, bg: 'bg-primary-50', text: 'text-primary-600'},
    {keywords: ['habitation', 'maison', 'immobilier', 'logement'], icon: Building2, bg: 'bg-secondary-50', text: 'text-secondary-700'},
    {keywords: ['sante', 'mutuelle'], icon: HeartPulse, bg: 'bg-primary-50', text: 'text-primary-600'},
    {keywords: ['voyage'], icon: Plane, bg: 'bg-primary-50', text: 'text-primary-700'},
    {keywords: ['banque', 'compte'], icon: Banknote, bg: 'bg-secondary-50', text: 'text-secondary-700'},
    {keywords: ['carte', 'credit'], icon: CreditCard, bg: 'bg-secondary-50', text: 'text-secondary-700'},
    {keywords: ['energie', 'énergie', 'electricite', 'gaz'], icon: Zap, bg: 'bg-primary-50', text: 'text-primary-600'},
    {keywords: ['assurance'], icon: ShieldCheck, bg: 'bg-primary-50', text: 'text-primary-600'},
];

const fallbackVisuals = [
    {icon: Sparkles, bg: 'bg-primary-50', text: 'text-primary-600'},
    {icon: BadgeCheck, bg: 'bg-secondary-50', text: 'text-secondary-700'},
    {icon: ShieldCheck, bg: 'bg-bg-high', text: 'text-fg-subtitle'},
];

function normalizeText(value) {
    return (value ?? '')
        .toString()
        .normalize('NFD')
        .replace(/[̀-ͯ]/g, '')
        .toLowerCase();
}

function getItemVisual(item, index) {
    const haystack = normalizeText(`${item?.name ?? ''} ${item?.slug ?? ''} ${item?.sector?.name ?? ''} ${item?.sector?.slug ?? ''}`);
    const matchingRule = visualRules.find((rule) => rule.keywords.some((keyword) => haystack.includes(normalizeText(keyword))));

    return matchingRule ?? fallbackVisuals[index % fallbackVisuals.length];
}

function getItemMeta(item) {
    if (view.value === 'insuranceProducts') {
        if ((item.offers_count ?? 0) > 0) {
            return `${item.offers_count} offre${item.offers_count > 1 ? 's' : ''}`;
        }

        return item.sector?.name ?? "Produit d'assurance";
    }

    if (view.value === 'categories') {
        const count = item.sectors_count ?? item.sectors?.length ?? 0;

        return `${count} secteur${count > 1 ? 's' : ''}`;
    }

    if (view.value === 'sectors') {
        const count = item.products_count ?? item.products?.length ?? 0;

        return `${count} produit${count > 1 ? 's' : ''}`;
    }

    if ((item.offers_count ?? 0) > 0) {
        return `${item.offers_count} offre${item.offers_count > 1 ? 's' : ''}`;
    }

    return 'Bientôt disponible';
}

function getCardActionLabel() {
    if (view.value === 'insuranceProducts') return 'Comparer';
    if (view.value === 'categories') return 'Explorer';
    if (view.value === 'sectors') return 'Voir les produits';

    return 'Comparer';
}

function getProductHref(item) {
    const category = item.category ?? selectedCategory.value;
    const sector = item.sector ?? selectedSector.value;

    if (!category || !sector) {
        return route('compare.categories');
    }

    return route('compare.wizard', [category.slug, sector.slug, item.slug]);
}

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

function resetToInsuranceProducts() {
    selectedCategory.value = null;
    selectedSector.value = null;
    sectors.value = [];
    products.value = [];
    showAll.value = false;
    view.value = 'insuranceProducts';
}

function back() {
    if (view.value === 'products') {
        view.value = 'sectors';
        selectedSector.value = null;
        products.value = [];
    } else if (view.value === 'sectors') {
        resetToInsuranceProducts();
    }

    showAll.value = false;
}

function startComparison() {
    const firstItem = visibleItems.value[0] ?? items.value[0];

    if (!firstItem) {
        return;
    }

    if (view.value === 'categories') {
        browseCategory(firstItem);
    } else if (view.value === 'sectors') {
        browseSector(firstItem);
    }
}
</script>

<template>
    <Head title="Comparez et économisez"/>

    <AppLayout>
        <!-- Hero section -->
        <section class="relative isolate overflow-hidden">
            <!-- Background decorations -->
            <div aria-hidden="true" class="pointer-events-none absolute inset-0 -z-10 overflow-hidden">
                <div class="absolute left-[48%] top-[-18rem] h-[58rem] w-[58rem] rounded-full border border-secondary-200/60"/>
                <div class="absolute left-[50%] top-[-11rem] h-[44rem] w-[44rem] rounded-full border border-primary-200/50"/>
                <div class="absolute right-[-8rem] top-12 hidden h-[36rem] w-[36rem] rounded-full bg-secondary-50/80 lg:block"/>
                <div class="absolute inset-0 bg-gradient-to-b from-bg-lighter via-transparent to-transparent"/>
            </div>

            <div class="mx-auto grid min-h-[calc(100vh-6rem)] max-w-7xl items-center gap-10 px-4 py-10 sm:px-6 lg:grid-cols-[minmax(0,1.05fr)_minmax(360px,0.95fr)] lg:px-8 lg:py-14">
                <!-- Left: copy + cards -->
                <div class="max-w-3xl">
                    <!-- Breadcrumb navigation (when drilling) -->
                    <div v-if="view !== 'insuranceProducts' && view !== 'categories'" class="mb-7 flex flex-wrap items-center gap-3 text-sm">
                        <button
                            class="inline-flex items-center gap-2 rounded-full border border-border bg-bg px-4 py-2 font-semibold text-fg-subtitle shadow-sm transition hover:border-secondary-200 hover:text-secondary-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-secondary-600"
                            type="button"
                            @click="back"
                        >
                            <ArrowLeft class="size-4" stroke-width="2"/>
                            Retour
                        </button>

                        <nav aria-label="Fil d'Ariane" class="flex items-center gap-2 text-fg-subtext">
                            <button class="transition hover:text-secondary-700" type="button" @click="resetToInsuranceProducts">Accueil</button>
                            <template v-if="selectedCategory">
                                <span>/</span>
                                <button
                                    :class="view === 'sectors' ? 'font-semibold text-fg-title' : 'transition hover:text-secondary-700'"
                                    type="button"
                                    @click="view === 'products' ? (view = 'sectors', selectedSector = null, products = []) : null"
                                >
                                    {{ selectedCategory.name }}
                                </button>
                            </template>
                            <template v-if="selectedSector">
                                <span>/</span>
                                <span class="font-semibold text-fg-title">{{ selectedSector.name }}</span>
                            </template>
                        </nav>
                    </div>

                    <!-- Eyebrow badge -->
                    <div class="mb-5 inline-flex items-center gap-2 rounded-full bg-secondary-50 px-4 py-2 text-sm font-bold text-secondary-800 ring-1 ring-secondary-100">
                        <Sparkles class="size-4" stroke-width="2.25"/>
                        {{ viewCopy.eyebrow }}
                    </div>

                    <!-- Headline -->
                    <h1 class="max-w-3xl text-balance text-5xl font-black leading-[0.98] tracking-[-0.055em] text-fg-title sm:text-6xl lg:text-[5.25rem]">
                        {{ viewCopy.title }}
                    </h1>
                    <p class="mt-5 max-w-xl text-lg leading-8 text-fg-subtext">
                        {{ viewCopy.subtitle }}
                    </p>

                    <!-- Empty state -->
                    <div v-if="items.length === 0" class="mt-5 rounded-3xl border border-dashed border-border bg-bg/80 p-10 text-center shadow-sm">
                        <p class="text-base font-semibold text-fg-subtitle">Aucun élément disponible pour le moment.</p>
                    </div>

                    <template v-else>
                        <!-- Product/category cards -->
                        <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3">
                            <template v-for="(item, idx) in visibleItems" :key="item.id">
                                <Link
                                    v-if="view === 'products' || view === 'insuranceProducts'"
                                    :href="getProductHref(item)"
                                    class="group flex min-h-[8.5rem] flex-col justify-between rounded-[1.35rem] border border-border bg-bg p-5 text-left shadow-[0_18px_50px_rgba(15,23,42,0.06)] transition duration-200 hover:-translate-y-1 hover:border-secondary-200 hover:shadow-[0_24px_70px_rgba(15,23,42,0.1)] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-secondary-600"
                                >
                                    <div :class="['mb-5 flex size-12 items-center justify-center rounded-2xl', getItemVisual(item, idx).bg]">
                                        <component :is="getItemVisual(item, idx).icon" :class="['size-6', getItemVisual(item, idx).text]" stroke-width="2"/>
                                    </div>
                                    <div class="space-y-3">
                                        <div>
                                            <h3 class="text-base font-black tracking-[-0.02em] text-fg-title">{{ item.name }}</h3>
                                            <p class="mt-1 text-sm font-medium text-fg-subtext">{{ getItemMeta(item) }}</p>
                                        </div>
                                        <span class="inline-flex items-center gap-2 text-sm font-black text-secondary-700 transition group-hover:gap-3">
                                            {{ getCardActionLabel() }}
                                            <ArrowRight class="size-4" stroke-width="2.25"/>
                                        </span>
                                    </div>
                                </Link>

                                <button
                                    v-else
                                    class="group flex min-h-[8.5rem] flex-col justify-between rounded-[1.35rem] border border-border bg-bg p-5 text-left shadow-[0_18px_50px_rgba(15,23,42,0.06)] transition duration-200 hover:-translate-y-1 hover:border-secondary-200 hover:shadow-[0_24px_70px_rgba(15,23,42,0.1)] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-secondary-600"
                                    type="button"
                                    @click="view === 'categories' ? browseCategory(item) : browseSector(item)"
                                >
                                    <div :class="['mb-5 flex size-12 items-center justify-center rounded-2xl', getItemVisual(item, idx).bg]">
                                        <component :is="getItemVisual(item, idx).icon" :class="['size-6', getItemVisual(item, idx).text]" stroke-width="2"/>
                                    </div>
                                    <div class="space-y-3">
                                        <div>
                                            <h3 class="text-base font-black tracking-[-0.02em] text-fg-title">{{ item.name }}</h3>
                                            <p class="mt-1 text-sm font-medium text-fg-subtext">{{ getItemMeta(item) }}</p>
                                        </div>
                                        <span class="inline-flex items-center gap-2 text-sm font-black text-secondary-700 transition group-hover:gap-3">
                                            {{ getCardActionLabel() }}
                                            <ArrowRight class="size-4" stroke-width="2.25"/>
                                        </span>
                                    </div>
                                </button>
                            </template>
                        </div>

                        <!-- Show more -->
                        <div v-if="hasMore" class="mt-5">
                            <button
                                class="inline-flex items-center gap-2 rounded-full px-1 text-sm font-black text-secondary-700 transition hover:text-secondary-900 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-secondary-600"
                                type="button"
                                @click="showAll = true"
                            >
                                Voir plus de choix
                                <ArrowRight class="size-4" stroke-width="2.25"/>
                            </button>
                        </div>

                        <!-- CTA buttons -->
                        <div class="mt-7 flex flex-col gap-4 sm:flex-row sm:items-center">
                            <Link
                                v-if="view === 'insuranceProducts' && primaryProductHref"
                                :href="primaryProductHref"
                                class="inline-flex h-14 items-center justify-center gap-3 rounded-full bg-primary-600 px-8 text-base font-black text-white shadow-[0_18px_45px_rgba(217,119,6,0.25)] transition hover:-translate-y-0.5 hover:bg-primary-700 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-primary-600"
                            >
                                {{ primaryActionLabel }}
                                <ArrowRight class="size-5" stroke-width="2.5"/>
                            </Link>

                            <button
                                v-else-if="view !== 'products'"
                                class="inline-flex h-14 items-center justify-center gap-3 rounded-full bg-primary-600 px-8 text-base font-black text-white shadow-[0_18px_45px_rgba(217,119,6,0.25)] transition hover:-translate-y-0.5 hover:bg-primary-700 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-primary-600"
                                type="button"
                                @click="startComparison"
                            >
                                {{ primaryActionLabel }}
                                <ArrowRight class="size-5" stroke-width="2.5"/>
                            </button>

                            <Link
                                v-else-if="visibleItems.length > 0"
                                :href="route('compare.wizard', [selectedCategory.slug, selectedSector.slug, visibleItems[0].slug])"
                                class="inline-flex h-14 items-center justify-center gap-3 rounded-full bg-primary-600 px-8 text-base font-black text-white shadow-[0_18px_45px_rgba(217,119,6,0.25)] transition hover:-translate-y-0.5 hover:bg-primary-700 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-primary-600"
                            >
                                {{ primaryActionLabel }}
                                <ArrowRight class="size-5" stroke-width="2.5"/>
                            </Link>

                            <Link
                                :href="route('compare.categories')"
                                class="inline-flex h-14 items-center justify-center gap-2 rounded-full px-5 text-base font-black text-secondary-700 transition hover:text-secondary-900 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-secondary-600"
                            >
                                Explorer les guides
                                <ArrowRight class="size-4" stroke-width="2.5"/>
                            </Link>
                        </div>
                    </template>

                    <!-- Reassurance pills -->
                    <div class="mt-7 flex flex-wrap gap-5">
                        <div
                            v-for="item in reassuranceItems"
                            :key="item.label"
                            class="inline-flex items-center gap-3 text-sm font-black text-fg-subtitle"
                        >
                            <span class="flex size-10 items-center justify-center rounded-full bg-secondary-50 text-secondary-700 ring-1 ring-secondary-100">
                                <component :is="item.icon" class="size-5" stroke-width="2.25"/>
                            </span>
                            {{ item.label }}
                        </div>
                    </div>
                </div>

                <!-- Right: mascot image (desktop) -->
                <aside aria-hidden="true" class="relative mx-auto hidden min-h-[34rem] w-full max-w-xl lg:block">
                    <div class="absolute inset-x-6 top-12 h-[30rem] rounded-full bg-secondary-50 shadow-[0_40px_120px_rgba(4,120,87,0.10)]"/>
                    <img
                        alt=""
                        class="absolute bottom-0 left-1/2 max-h-[39rem] w-auto max-w-none -translate-x-1/2 object-contain drop-shadow-[0_24px_38px_rgba(15,23,42,0.14)]"
                        src="/images/kleverkat-advisor.png"
                    >
                </aside>

                <!-- Mascot (mobile) -->
                <div aria-hidden="true" class="mx-auto mt-2 max-w-sm lg:hidden">
                    <img
                        alt=""
                        class="mx-auto max-h-64 w-auto object-contain drop-shadow-[0_20px_35px_rgba(15,23,42,0.12)]"
                        src="/images/kleverkat-advisor.png"
                    >
                </div>
            </div>
        </section>
    </AppLayout>
</template>
