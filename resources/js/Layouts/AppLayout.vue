<script setup>
import {Link, usePage} from '@inertiajs/vue3';
import {ArrowRight, ChevronDown, ChevronRight, CircleHelp, Moon, Sun} from 'lucide-vue-next';
import {useTheme} from '@/composables/useTheme';
import {computed, nextTick, onBeforeUnmount, ref} from 'vue';

const {isDark, toggle} = useTheme();

const page = usePage();
const auth = computed(() => page.props.auth);
const flash = computed(() => page.props.flash);
const navCategories = computed(() => page.props.navCategories ?? []);
const activeCategoryId = ref(null);
const activeSectorId = ref(null);
const menuPanel = ref(null);
const mobileMenuOpen = ref(false);
let closeTimerId = null;
let suppressNextCategoryFocus = false;

const activeCategory = computed(() => navCategories.value.find((category) => category.id === activeCategoryId.value) ?? null);
const activeSector = computed(() => activeCategory.value?.sectors?.find((sector) => sector.id === activeSectorId.value) ?? null);
const activeProducts = computed(() => activeSector.value?.products?.slice(0, 6) ?? []);

const CATEGORY_ICONS = {
    assurance: 'M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 1 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z',
    banque: 'M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0 0 12 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75Z',
    industrie: 'M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z',
    telecom: 'M8.288 15.038a5.25 5.25 0 0 1 7.424 0M5.106 11.856c3.807-3.808 9.98-3.808 13.788 0M1.924 8.674c5.565-5.565 14.587-5.565 20.152 0M12.53 18.22l-.53.53-.53-.53a.75.75 0 0 1 1.06 0Z',
    energie: 'M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75Z',
    sante: 'M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z',
    automobile: 'M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12',
    immobilier: 'M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25',
};

const FALLBACK_ICON = 'M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z';

function getCategoryIcon(slug) {
    const normalizedSlug = slug?.toLowerCase() ?? '';
    const matchingIcon = Object.keys(CATEGORY_ICONS).find((key) => normalizedSlug.includes(key));

    return CATEGORY_ICONS[matchingIcon] ?? FALLBACK_ICON;
}

function cancelMenuClose() {
    if (closeTimerId !== null) {
        window.clearTimeout(closeTimerId);
        closeTimerId = null;
    }
}

function openCategory(category) {
    cancelMenuClose();

    if (activeCategoryId.value !== category.id) {
        activeSectorId.value = category.sectors?.[0]?.id ?? null;
    }

    activeCategoryId.value = category.id;
}

function handleCategoryFocus(category) {
    if (suppressNextCategoryFocus) {
        suppressNextCategoryFocus = false;

        return;
    }

    openCategory(category);
}

function selectSector(sector) {
    activeSectorId.value = sector.id;
}

function closeMenu() {
    cancelMenuClose();
    activeCategoryId.value = null;
    activeSectorId.value = null;
}

function scheduleMenuClose() {
    cancelMenuClose();
    closeTimerId = window.setTimeout(closeMenu, 140);
}

function handleFocusOut(event) {
    if (!event.currentTarget.contains(event.relatedTarget)) {
        scheduleMenuClose();
    }
}

async function focusMenu(category) {
    openCategory(category);
    await nextTick();
    menuPanel.value?.querySelector('a')?.focus();
}

async function closeMenuAndRestoreFocus() {
    const categoryId = activeCategoryId.value;
    const shouldRestoreFocus = menuPanel.value?.contains(document.activeElement) ?? false;

    closeMenu();

    if (!shouldRestoreFocus) {
        return;
    }

    await nextTick();
    suppressNextCategoryFocus = true;
    document.getElementById(`category-trigger-${categoryId}`)?.focus();
}

function closeMobileMenu() {
    mobileMenuOpen.value = false;
}

onBeforeUnmount(cancelMenuClose);

const currentYear = new Date().getFullYear();

const footerLinks = [
    {
        title: 'Comparateurs',
        links: [
            {label: 'Assurances', href: '#'},
            {label: 'Banques', href: '#'},
            {label: 'Énergie', href: '#'},
            {label: 'Télécom', href: '#'},
        ],
    },
    {
        title: 'À propos',
        links: [
            {label: 'Qui sommes-nous', href: '#'},
            {label: 'Comment ça marche', href: '#'},
            {label: 'Nous contacter', href: '#'},
            {label: 'Recrutement', href: '#'},
        ],
    },
    {
        title: 'Ressources',
        links: [
            {label: 'Blog & Guides', href: '#'},
            {label: 'FAQ', href: '#'},
            {label: 'Partenaires', href: '#'},
            {label: 'Presse', href: '#'},
        ],
    },
    {
        title: 'Légal',
        links: [
            {label: 'Mentions légales', href: '#'},
            {label: 'Politique de confidentialité', href: '#'},
            {label: 'Cookies', href: '#'},
            {label: 'CGU', href: '#'},
        ],
    },
];
</script>

<template>
    <div class="min-h-screen bg-bg-light text-fg antialiased">

        <!-- Header + desktop mega-menu -->
        <div
            class="sticky top-0 z-30 flex w-full items-center px-2 sm:px-6 lg:px-4 pt-3 md:pt-5 pb-2"
            @focusout="handleFocusOut"
            @mouseenter="cancelMenuClose"
            @mouseleave="scheduleMenuClose"
            @keydown.esc.stop.prevent="closeMenuAndRestoreFocus"
        >
            <!-- Header card — glassmorphism -->
            <header
                class="xl:max-w-5xl mx-auto w-full bg-bg/80 backdrop-blur-lg shadow-lg shadow-fg-subtext/5 border border-border-light rounded-xl">
                <div class="flex items-center justify-between gap-6 lg:gap-10 p-4">

                    <!-- Logo — flex-1 pour équilibrer -->
                    <div class="lg:flex-1">
                        <Link :href="route('home')" aria-label="Accueil KleverKat"
                              class="flex items-center gap-2.5 w-max">
                            <!-- Logo complet desktop -->
                            <span class="hidden sm:flex items-center gap-2.5">
                                <svg class="size-10 shrink-0" fill="none" viewBox="0 0 40 40">
                                    <rect fill="hsl(26 80% 46%)" height="40" rx="10" width="40"/>
                                    <polygon fill="hsl(36 88% 92%)" points="6,18 10,7 16,16"/>
                                    <polygon fill="hsl(36 88% 92%)" points="24,16 30,7 34,18"/>
                                    <circle cx="20" cy="24" fill="hsl(36 88% 92%)" r="13"/>
                                    <polygon fill="hsl(30 85% 51%)" points="7.5,17 10.5,9 15,15.5"/>
                                    <polygon fill="hsl(30 85% 51%)" points="25,15.5 29.5,9 32.5,17"/>
                                    <ellipse cx="15.5" cy="22.5" fill="hsl(152 76% 80%)" rx="2.5" ry="3"/>
                                    <ellipse cx="24.5" cy="22.5" fill="hsl(152 76% 80%)" rx="2.5" ry="3"/>
                                    <ellipse cx="15.5" cy="22.5" fill="hsl(165 91% 9%)" rx="1.2" ry="2"/>
                                    <ellipse cx="24.5" cy="22.5" fill="hsl(165 91% 9%)" rx="1.2" ry="2"/>
                                    <circle cx="16" cy="21" fill="white" r="0.7"/>
                                    <circle cx="25" cy="21" fill="white" r="0.7"/>
                                    <ellipse cx="20" cy="27" fill="hsl(0 72% 51%)" rx="1.4" ry="1"/>
                                    <line opacity="0.5" stroke="hsl(217 19% 27%)" stroke-width="0.6" x1="6" x2="16"
                                          y1="26.5" y2="27"/>
                                    <line opacity="0.5" stroke="hsl(217 19% 27%)" stroke-width="0.6" x1="6" x2="16"
                                          y1="28.5" y2="28"/>
                                    <line opacity="0.5" stroke="hsl(217 19% 27%)" stroke-width="0.6" x1="24" x2="34"
                                          y1="27" y2="26.5"/>
                                    <line opacity="0.5" stroke="hsl(217 19% 27%)" stroke-width="0.6" x1="24" x2="34"
                                          y1="28" y2="28.5"/>
                                </svg>
                                <span class="text-[1.15rem] font-bold tracking-tight leading-none">
                                    <span class="text-fg-title">Klever</span><span class="text-primary-600">Kat</span>
                                </span>
                            </span>
                            <!-- Icône seule mobile -->
                            <svg class="size-10 shrink-0 sm:hidden" fill="none" viewBox="0 0 40 40">
                                <rect fill="hsl(26 80% 46%)" height="40" rx="10" width="40"/>
                                <polygon fill="hsl(36 88% 92%)" points="6,18 10,7 16,16"/>
                                <polygon fill="hsl(36 88% 92%)" points="24,16 30,7 34,18"/>
                                <circle cx="20" cy="24" fill="hsl(36 88% 92%)" r="13"/>
                                <ellipse cx="15.5" cy="22.5" fill="hsl(165 91% 9%)" rx="1.2" ry="2"/>
                                <ellipse cx="24.5" cy="22.5" fill="hsl(165 91% 9%)" rx="1.2" ry="2"/>
                                <ellipse cx="20" cy="27" fill="hsl(0 72% 51%)" rx="1.4" ry="1"/>
                            </svg>
                        </Link>
                    </div>

                    <!-- Desktop categories nav -->
                    <nav aria-label="Catégories de comparaison"
                         class="hidden items-center gap-2 lg:flex text-fg-subtext">
                        <div
                            v-for="cat in navCategories"
                            :key="cat.id"
                            class="flex w-full lg:w-max"
                            @mouseenter="openCategory(cat)"
                        >
                            <Link
                                :id="`category-trigger-${cat.id}`"
                                :aria-controls="`category-menu-${cat.id}`"
                                :aria-expanded="activeCategoryId === cat.id"
                                :class="[
                                    'flex items-center gap-2 whitespace-nowrap rounded-lg py-1 px-2 text-sm font-semibold border transition-colors ease-linear focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-500',
                                    activeCategoryId === cat.id
                                        ? 'text-primary-600 bg-primary-50 border-primary-200/40'
                                        : 'border-transparent hover:bg-bg-light hover:text-fg-subtitle',
                                ]"
                                :href="route('compare.sectors', cat.slug)"
                                @click="closeMenu"
                                @focus="handleCategoryFocus(cat)"
                                @keydown.down.prevent="focusMenu(cat)"
                            >
                                <span class="flex shrink-0 *:size-5">
                                    <svg fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                                        <path :d="getCategoryIcon(cat.slug)" stroke-linecap="round"
                                              stroke-linejoin="round"/>
                                    </svg>
                                </span>
                                {{ cat.name }}
                                <ChevronDown
                                    :class="['size-3 opacity-50 transition-transform duration-150 motion-reduce:transition-none', activeCategoryId === cat.id ? 'rotate-180 opacity-100' : '']"
                                    aria-hidden="true"
                                />
                            </Link>
                        </div>
                    </nav>

                    <!-- Right controls — flex-1 justify-end -->
                    <div class="flex flex-1 justify-end items-center gap-3">
                        <!-- Theme toggle -->
                        <button
                            :aria-label="isDark ? 'Activer le mode clair' : 'Activer le mode sombre'"
                            class="flex size-9 items-center justify-center rounded-lg text-fg-subtext transition-colors hover:bg-bg-high hover:text-fg-title"
                            type="button"
                            @click="toggle"
                        >
                            <Sun v-if="isDark" class="size-[1.1rem]" stroke-width="1.8"/>
                            <Moon v-else class="size-[1.1rem]" stroke-width="1.8"/>
                        </button>

                        <!-- Auth -->
                        <template v-if="auth?.user">
                            <Link
                                :href="route('dashboard', auth.currentTeam?.slug ?? '')"
                                class="hidden btn btn-outline btn-outline-neutral btn-sm rounded-lg sm:inline-flex"
                            >
                                Mon espace
                            </Link>
                        </template>
                        <template v-else>
                            <Link
                                :href="route('login')"
                                class="hidden btn btn-sm sm:btn-md btn-solid btn-solid-secondary rounded-lg sm:inline-flex"
                            >
                                Me connecter
                            </Link>
                        </template>

                        <!-- Hamburger — 2 barres animées comme REDPANDA -->
                        <button
                            :aria-expanded="mobileMenuOpen"
                            aria-label="Ouvrir le menu de navigation"
                            class="outline-none border-l border-border-light pl-3 relative py-3 group lg:hidden"
                            type="button"
                            @click="mobileMenuOpen = !mobileMenuOpen"
                        >
                            <span
                                :class="mobileMenuOpen ? 'rotate-45 translate-y-[0.33rem]' : ''"
                                class="flex h-0.5 w-6 rounded bg-fg-subtitle transition duration-300"
                            />
                            <span
                                :class="mobileMenuOpen ? '-rotate-45 -translate-y-[0.33rem]' : ''"
                                class="flex mt-2 h-0.5 w-6 rounded bg-fg-subtitle transition duration-300"
                            />
                        </button>
                    </div>
                </div>
            </header>

            <!-- Desktop mega-menu panel -->
            <Transition
                enter-active-class="transition duration-200 ease-out motion-reduce:transition-none"
                enter-from-class="-translate-y-1 scale-[0.99] opacity-0"
                enter-to-class="translate-y-0 scale-100 opacity-100"
                leave-active-class="transition duration-100 ease-in motion-reduce:transition-none"
                leave-from-class="translate-y-0 scale-100 opacity-100"
                leave-to-class="-translate-y-1 opacity-0"
            >
                <div
                    v-if="activeCategory"
                    :id="`category-menu-${activeCategory.id}`"
                    ref="menuPanel"
                    :aria-label="`Explorer ${activeCategory.name}`"
                    class="absolute inset-x-0 top-full hidden px-2 sm:px-6 lg:px-4 lg:block"
                    role="region"
                    @mouseenter="cancelMenuClose"
                >
                    <div
                        class="xl:max-w-[80rem] mx-auto overflow-hidden rounded-2xl border border-border-light bg-bg shadow-2xl shadow-neutral-900/20 ring-1 ring-border-lighter"
                        @mouseenter="cancelMenuClose"
                        @mouseleave="scheduleMenuClose"
                    >
                        <!-- Bandeau catégorie -->
                        <div class="flex items-center justify-between gap-4 border-b border-border bg-bg-lighter px-5 py-3">
                            <div class="flex items-center gap-3">
                                <span class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-primary-50 text-primary-600">
                                    <svg class="size-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true">
                                        <path :d="getCategoryIcon(activeCategory.slug)" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </span>
                                <div>
                                    <p class="text-sm font-bold text-fg-title leading-4">{{ activeCategory.name }}</p>
                                    <p class="text-xs text-fg-subtext mt-0.5">
                                        {{ activeCategory.sectors?.length ?? 0 }} secteur{{ (activeCategory.sectors?.length ?? 0) > 1 ? 's' : '' }} disponibles
                                    </p>
                                </div>
                            </div>
                            <Link
                                :href="route('compare.sectors', activeCategory.slug)"
                                class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold text-secondary-700 transition-colors hover:bg-secondary-50 hover:text-secondary-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-secondary-600"
                                @click="closeMenu"
                            >
                                Tout explorer
                                <ArrowRight aria-hidden="true" class="size-3.5"/>
                            </Link>
                        </div>

                        <!-- 3 colonnes -->
                        <div class="grid max-h-[calc(100vh-10rem)] grid-cols-[14rem_minmax(0,1fr)_16rem] overflow-y-auto">

                            <!-- Secteurs -->
                            <aside class="border-r border-border bg-bg-lighter/60 p-3">
                                <p class="mb-1.5 px-2 text-[10px] font-bold uppercase tracking-[0.14em] text-fg-subtext">Secteurs</p>
                                <nav :aria-label="`Secteurs ${activeCategory.name}`" class="flex flex-col gap-0.5">
                                    <Link
                                        v-for="sector in activeCategory.sectors"
                                        :key="sector.id"
                                        :class="[
                                            'flex items-center gap-2.5 rounded-xl px-2.5 py-2.5 text-sm transition-colors ease-linear focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-500',
                                            activeSectorId === sector.id
                                                ? 'bg-primary-50 font-semibold text-primary-700 ring-1 ring-primary-100'
                                                : 'font-medium text-fg-subtitle hover:bg-bg-high hover:text-fg-title',
                                        ]"
                                        :href="route('compare.products', [activeCategory?.slug, sector?.slug])"
                                        @click="closeMenu"
                                        @focus="selectSector(sector)"
                                        @mouseenter="selectSector(sector)"
                                    >
                                        <span
                                            :class="[
                                                'flex size-7 shrink-0 items-center justify-center rounded-lg',
                                                activeSectorId === sector.id ? 'bg-primary-100 text-primary-600' : 'bg-bg text-fg-subtext',
                                            ]"
                                        >
                                            <svg class="size-3.5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true">
                                                <path :d="getCategoryIcon(sector.slug ?? sector.name)" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </span>
                                        <span class="min-w-0 flex-1 truncate leading-5">{{ sector.name }}</span>
                                        <ChevronRight
                                            :class="['size-3.5 shrink-0 transition-opacity', activeSectorId === sector.id ? 'text-primary-600 opacity-60' : 'opacity-0']"
                                            aria-hidden="true"
                                        />
                                    </Link>

                                    <p v-if="!activeCategory.sectors?.length" class="px-2.5 py-3 text-sm text-fg-subtext">
                                        Aucun secteur disponible.
                                    </p>
                                </nav>
                            </aside>

                            <!-- Produits -->
                            <section class="min-w-0 p-5">
                                <div class="mb-4 flex items-center justify-between gap-4">
                                    <div class="min-w-0">
                                        <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-fg-subtext">Produits</p>
                                        <h3 class="mt-0.5 truncate text-sm font-bold text-fg-title">
                                            {{ activeSector?.name ?? 'Sélectionnez un secteur' }}
                                        </h3>
                                    </div>
                                    <Link
                                        v-if="activeSector"
                                        :href="route('compare.products', [activeCategory?.slug, activeSector?.slug])"
                                        class="shrink-0 btn btn-sm btn-solid btn-solid-secondary rounded-lg"
                                        @click="closeMenu"
                                    >
                                        Comparer
                                        <ArrowRight aria-hidden="true" class="ml-1.5 size-3.5"/>
                                    </Link>
                                </div>

                                <!-- Cartes produits -->
                                <div v-if="activeProducts.length" class="grid grid-cols-2 gap-2">
                                    <Link
                                        v-for="product in activeProducts"
                                        :key="product.id"
                                        :href="route('compare.wizard', [activeCategory?.slug, activeSector?.slug, product?.slug])"
                                        class="group flex flex-col justify-between rounded-xl border border-border bg-bg-lighter p-3.5 transition-all duration-150 ease-linear hover:border-primary-200 hover:bg-primary-50/40 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-500"
                                        @click="closeMenu"
                                    >
                                        <p class="line-clamp-2 text-sm font-semibold leading-5 text-fg-title transition-colors group-hover:text-primary-700">
                                            {{ product.name }}
                                        </p>
                                        <span class="mt-2 inline-flex items-center gap-1 text-xs font-semibold text-primary-600 opacity-0 transition-opacity group-hover:opacity-100">
                                            Comparer <ArrowRight class="size-3" stroke-width="2.5"/>
                                        </span>
                                    </Link>
                                </div>

                                <!-- Empty state -->
                                <div v-else class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-border bg-bg-lighter px-5 py-10 text-center">
                                    <span class="mb-3 flex size-10 items-center justify-center rounded-xl bg-bg-high text-fg-subtext">
                                        <svg class="size-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </span>
                                    <p class="text-sm font-medium text-fg-subtitle">Sélectionnez un secteur</p>
                                    <p class="mt-0.5 text-xs text-fg-subtext">pour voir les produits disponibles</p>
                                </div>

                                <Link
                                    v-if="activeSector?.products?.length > activeProducts.length"
                                    :href="route('compare.products', [activeCategory.slug, activeSector.slug])"
                                    class="mt-3 inline-flex items-center gap-1.5 text-xs font-semibold text-primary-700 hover:text-primary-800"
                                    @click="closeMenu"
                                >
                                    Voir tous les produits
                                    <ArrowRight aria-hidden="true" class="size-3.5"/>
                                </Link>
                            </section>

                            <!-- Aide + CTA -->
                            <aside class="flex flex-col gap-3 border-l border-border bg-bg-lighter/60 p-4">
                                <div class="rounded-2xl border border-border bg-bg p-4 shadow-sm">
                                    <div class="flex size-9 items-center justify-center rounded-xl bg-secondary-50 text-secondary-700">
                                        <CircleHelp :stroke-width="1.8" aria-hidden="true" class="size-4.5"/>
                                    </div>
                                    <h2 class="mt-3 text-sm font-bold text-fg-title">Comment comparer ?</h2>
                                    <p class="mt-1.5 text-xs leading-5 text-fg-subtext">
                                        Sélectionnez un secteur, choisissez un produit puis répondez au questionnaire pour obtenir votre comparatif personnalisé.
                                    </p>
                                    <Link
                                        :href="route('compare.sectors', activeCategory.slug)"
                                        class="mt-3 inline-flex items-center gap-1.5 text-xs font-semibold text-secondary-700 hover:text-secondary-800"
                                        @click="closeMenu"
                                    >
                                        Voir la catégorie
                                        <ArrowRight aria-hidden="true" class="size-3.5"/>
                                    </Link>
                                </div>

                                <div class="flex flex-col rounded-2xl bg-primary-600 p-4 text-white">
                                    <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-primary-200">KleverKat</p>
                                    <p class="mt-2 text-sm font-bold leading-5">Trouvez le produit adapté à votre besoin en 2 minutes.</p>
                                    <Link
                                        :href="activeSector
                                            ? route('compare.products', [activeCategory.slug, activeSector.slug])
                                            : route('compare.sectors', activeCategory.slug)"
                                        class="mt-4 inline-flex items-center justify-center gap-1.5 rounded-lg bg-white px-3 py-2 text-xs font-bold text-primary-700 transition-colors hover:bg-primary-50"
                                        @click="closeMenu"
                                    >
                                        Commencer
                                        <ArrowRight aria-hidden="true" class="size-3.5"/>
                                    </Link>
                                </div>
                            </aside>
                        </div>
                    </div>
                </div>
            </Transition>
        </div>

        <!-- Mobile menu panel -->
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="-translate-y-3 opacity-0"
            enter-to-class="translate-y-0 opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="translate-y-0 opacity-100"
            leave-to-class="-translate-y-2 opacity-0"
        >
            <div v-if="mobileMenuOpen" class="fixed inset-x-0 top-[4.75rem] z-20 px-2 sm:px-6 lg:hidden">
                <div
                    class="mx-auto max-w-7xl overflow-hidden rounded-2xl border border-border-light bg-bg/95 shadow-2xl shadow-fg-subtext/10 backdrop-blur-lg">
                    <!-- Auth row -->
                    <div class="border-b border-border px-4 py-3 flex items-center justify-between gap-3">
                        <template v-if="auth?.user">
                            <Link
                                :href="route('dashboard', auth.currentTeam?.slug ?? '')"
                                class="btn btn-solid btn-solid-secondary btn-sm rounded-lg w-full justify-center"
                                @click="closeMobileMenu"
                            >
                                Mon espace
                            </Link>
                        </template>
                        <template v-else>
                            <Link
                                :href="route('login')"
                                class="btn btn-solid btn-solid-secondary btn-sm rounded-lg w-full justify-center"
                                @click="closeMobileMenu"
                            >
                                Me connecter
                            </Link>
                        </template>
                    </div>
                    <!-- Categories list -->
                    <nav class="px-3 py-3">
                        <p class="mb-2 px-2 text-xs font-semibold uppercase tracking-[0.14em] text-fg-subtext">
                            Catégories</p>
                        <ul class="flex flex-col gap-0.5">
                            <li v-for="cat in navCategories" :key="cat.id">
                                <Link
                                    :href="route('compare.sectors', cat.slug)"
                                    class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-fg-subtitle transition-colors hover:bg-bg-high hover:text-fg-title"
                                    @click="closeMobileMenu"
                                >
                                    <span
                                        class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-bg-light text-primary-600">
                                        <svg class="size-4" fill="none" stroke="currentColor" stroke-width="1.75"
                                             viewBox="0 0 24 24">
                                            <path :d="getCategoryIcon(cat.slug)" stroke-linecap="round"
                                                  stroke-linejoin="round"/>
                                        </svg>
                                    </span>
                                    {{ cat.name }}
                                    <ChevronRight aria-hidden="true" class="ml-auto size-4 opacity-40"/>
                                </Link>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </Transition>

        <!-- Mobile overlay backdrop -->
        <Transition enter-active-class="transition duration-200" enter-from-class="opacity-0"
                    enter-to-class="opacity-100"
                    leave-active-class="transition duration-150" leave-from-class="opacity-100"
                    leave-to-class="opacity-0">
            <div v-if="mobileMenuOpen" class="fixed inset-0 z-10 bg-fg-title/20 backdrop-blur-sm lg:hidden"
                 @click="closeMobileMenu"/>
        </Transition>

        <!-- Flash messages -->
        <div v-if="flash?.success || flash?.error" class="mx-auto max-w-7xl px-4 pt-3 sm:px-6 lg:px-8">
            <div v-if="flash?.success"
                 class="rounded-lg border border-success-200 bg-success-50 px-4 py-3 text-sm text-success-800">
                {{ flash.success }}
            </div>
            <div v-if="flash?.error"
                 class="rounded-lg border border-danger-200 bg-danger-50 px-4 py-3 text-sm text-danger-800">
                {{ flash.error }}
            </div>
        </div>

        <!-- Content -->
        <main class="pb-16 lg:pb-0">
            <slot/>
        </main>

        <!-- Footer -->
        <footer class="border-t border-border-light bg-bg mt-16">
            <!-- RGPD notice -->
            <div class="mx-auto max-w-7xl px-5 sm:px-10 pt-10">
                <div class="rounded-xl bg-bg-light px-5 py-4 text-sm text-fg-subtext">
                    KleverKat collecte et traite vos données uniquement dans le cadre de vos demandes, en toute sécurité
                    et conformément à la réglementation sur la protection des données personnelles. Vous pouvez exercer
                    vos droits d'accès, de modification ou de suppression en nous contactant.
                    Pour en savoir plus, consultez notre
                    <a class="font-medium text-primary-600 underline-offset-2 hover:underline" href="#">Politique de
                        confidentialité</a>.
                </div>
            </div>

            <!-- Brand + nav links -->
            <div
                class="mx-auto max-w-7xl px-5 sm:px-10 py-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-b border-border-light">
                <Link :href="route('home')" class="flex items-center gap-2.5">
                    <svg class="size-8 shrink-0" fill="none" viewBox="0 0 40 40">
                        <rect fill="hsl(26 80% 46%)" height="40" rx="10" width="40"/>
                        <polygon fill="hsl(36 88% 92%)" points="6,18 10,7 16,16"/>
                        <polygon fill="hsl(36 88% 92%)" points="24,16 30,7 34,18"/>
                        <circle cx="20" cy="24" fill="hsl(36 88% 92%)" r="13"/>
                        <ellipse cx="15.5" cy="22.5" fill="hsl(152 76% 80%)" rx="2.5" ry="3"/>
                        <ellipse cx="24.5" cy="22.5" fill="hsl(152 76% 80%)" rx="2.5" ry="3"/>
                        <ellipse cx="15.5" cy="22.5" fill="hsl(165 91% 9%)" rx="1.2" ry="2"/>
                        <ellipse cx="24.5" cy="22.5" fill="hsl(165 91% 9%)" rx="1.2" ry="2"/>
                        <ellipse cx="20" cy="27" fill="hsl(0 72% 51%)" rx="1.4" ry="1"/>
                    </svg>
                    <span class="text-base font-bold tracking-tight">
                        <span class="text-fg-title">Klever</span><span class="text-primary-600">Kat</span>
                    </span>
                </Link>
                <ul class="flex flex-wrap gap-x-5 gap-y-2 text-sm text-fg-subtext">
                    <li><a class="transition-colors hover:text-fg-subtitle" href="#">À propos</a></li>
                    <li><a class="transition-colors hover:text-fg-subtitle" href="#">Nous contacter</a></li>
                    <li><a class="transition-colors hover:text-fg-subtitle" href="#">Politique de cookies</a></li>
                    <li><a class="transition-colors hover:text-fg-subtitle" href="#">Conditions générales</a></li>
                </ul>
            </div>

            <!-- 4-column links grid -->
            <div class="mx-auto max-w-7xl px-5 sm:px-10 py-8">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-x-6 gap-y-8">
                    <div v-for="group in footerLinks" :key="group.title" class="flex flex-col gap-2">
                        <h3 class="mb-1 text-sm font-semibold text-fg-title">{{ group.title }}</h3>
                        <ul class="space-y-1.5">
                            <li v-for="link in group.links" :key="link.label">
                                <a :href="link.href"
                                   class="block text-sm text-fg-subtext transition-colors hover:text-fg-subtitle">
                                    {{ link.label }}
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Copyright + social -->
            <div
                class="mx-auto max-w-7xl px-5 sm:px-10 py-5 border-t border-border-light flex flex-col md:flex-row items-center justify-between gap-3">
                <p class="text-sm text-fg-subtext">
                    Copyright © {{ currentYear }} — KleverKat, comparateur en ligne gratuit. Tous droits réservés.
                </p>
                <div class="flex items-center gap-4 text-fg-subtext">
                    <a aria-label="Facebook" class="transition-colors hover:text-fg-subtitle" href="#">
                        <svg class="size-5" fill="currentColor" viewBox="0 0 24 24">
                            <path clip-rule="evenodd"
                                  d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z"
                                  fill-rule="evenodd"/>
                        </svg>
                    </a>
                    <a aria-label="Instagram" class="transition-colors hover:text-fg-subtitle" href="#">
                        <svg class="size-5" fill="currentColor" viewBox="0 0 24 24">
                            <path clip-rule="evenodd"
                                  d="M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772A4.902 4.902 0 015.45 2.525c.636-.247 1.363-.416 2.427-.465C8.901 2.013 9.256 2 11.685 2h.63zm-.081 1.802h-.468c-2.456 0-2.784.011-3.807.058-.975.045-1.504.207-1.857.344-.467.182-.8.398-1.15.748-.35.35-.566.683-.748 1.15-.137.353-.3.882-.344 1.857-.047 1.023-.058 1.351-.058 3.807v.468c0 2.456.011 2.784.058 3.807.045.975.207 1.504.344 1.857.182.466.399.8.748 1.15.35.35.683.566 1.15.748.353.137.882.3 1.857.344 1.054.048 1.37.058 4.041.058h.08c2.597 0 2.917-.01 3.96-.058.976-.045 1.505-.207 1.858-.344.466-.182.8-.398 1.15-.748.35-.35.566-.683.748-1.15.137-.353.3-.882.344-1.857.048-1.055.058-1.37.058-4.041v-.08c0-2.597-.01-2.917-.058-3.96-.045-.976-.207-1.505-.344-1.858a3.097 3.097 0 00-.748-1.15 3.098 3.098 0 00-1.15-.748c-.353-.137-.882-.3-1.857-.344-1.023-.047-1.351-.058-3.807-.058zM12 6.865a5.135 5.135 0 110 10.27 5.135 5.135 0 010-10.27zm0 1.802a3.333 3.333 0 100 6.666 3.333 3.333 0 000-6.666zm5.338-3.205a1.2 1.2 0 110 2.4 1.2 1.2 0 010-2.4z"
                                  fill-rule="evenodd"/>
                        </svg>
                    </a>
                    <a aria-label="LinkedIn" class="transition-colors hover:text-fg-subtitle" href="#">
                        <svg class="size-5" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                        </svg>
                    </a>
                </div>
            </div>
        </footer>

        <!-- Mobile bottom nav -->
        <nav
            class="fixed bottom-0 inset-x-0 z-[60] lg:hidden flex items-center h-16 bg-bg/90 backdrop-blur-md border-t border-border-light">
            <ul :style="`grid-template-columns: repeat(${Math.min(navCategories.length, 5)}, minmax(0,1fr))`"
                class="h-full w-full grid">
                <li
                    v-for="cat in navCategories.slice(0, 5)"
                    :key="cat.id"
                    :class="page.url.startsWith('/compare/' + cat.slug) ? 'border-primary-600' : ''"
                    class="h-full border-t-2 border-transparent"
                >
                    <Link
                        :class="page.url.startsWith('/compare/' + cat.slug) ? 'text-primary-600' : ''"
                        :href="route('compare.sectors', cat.slug)"
                        class="flex flex-col items-center justify-center size-full px-1 py-1.5 text-fg-subtext transition-colors hover:bg-bg-light"
                    >
                        <svg class="size-5 mb-0.5" fill="none" stroke="currentColor" stroke-width="1.75"
                             viewBox="0 0 24 24">
                            <path :d="getCategoryIcon(cat.slug)" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span class="text-[10px] sm:text-xs line-clamp-1">{{ cat.name }}</span>
                    </Link>
                </li>
            </ul>
        </nav>
    </div>
</template>
