<script setup>
import {Link, usePage} from '@inertiajs/vue3';
import {ArrowRight, ChevronDown, ChevronRight, CircleHelp, Layers3} from 'lucide-vue-next';
import {computed, nextTick, onBeforeUnmount, ref} from 'vue';

const page = usePage();
const auth = computed(() => page.props.auth);
const flash = computed(() => page.props.flash);
const navCategories = computed(() => page.props.navCategories ?? []);
const activeCategoryId = ref(null);
const activeSectorId = ref(null);
const menuPanel = ref(null);
let closeTimerId = null;
let suppressNextCategoryFocus = false;

const activeCategory = computed(() => navCategories.value.find((category) => category.id === activeCategoryId.value) ?? null);
const activeSector = computed(() => activeCategory.value?.sectors?.find((sector) => sector.id === activeSectorId.value) ?? null);
const activeProducts = computed(() => activeSector.value?.products?.slice(0, 6) ?? []);

/**
 * Heroicons 2 outline paths mapped by category slug.
 * Falls back to a generic grid icon for unmapped slugs.
 */
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

onBeforeUnmount(cancelMenuClose);
</script>

<template>
    <div class="min-h-screen bg-bg-light text-fg antialiased">

        <!-- Floating card header -->
        <div
            class="sticky top-0 z-30 bg-bg-light px-3 pb-2 pt-3 sm:px-5"
            @focusout="handleFocusOut"
            @mouseenter="cancelMenuClose"
            @mouseleave="scheduleMenuClose"
            @keydown.esc.stop.prevent="closeMenuAndRestoreFocus"
        >
            <header class="mx-auto max-w-7xl rounded-xl border border-border bg-bg shadow-sm">
                <div class="flex h-17 items-center justify-between gap-4 px-4 sm:px-5">

                    <!-- Logo -->
                    <Link :href="route('home')" class="flex shrink-0 items-center gap-2.5">
                        <!-- Cat mark -->
                        <svg class="size-10 shrink-0" fill="none" viewBox="0 0 40 40">
                            <!-- Orange rounded background -->
                            <rect fill="hsl(26 80% 46%)" height="40" rx="10" width="40"/>
                            <!-- Ears -->
                            <polygon fill="hsl(36 88% 92%)" points="6,18 10,7 16,16"/>
                            <polygon fill="hsl(36 88% 92%)" points="24,16 30,7 34,18"/>
                            <!-- Head -->
                            <circle cx="20" cy="24" fill="hsl(36 88% 92%)" r="13"/>
                            <!-- Inner ear colour -->
                            <polygon fill="hsl(30 85% 51%)" points="7.5,17 10.5,9 15,15.5"/>
                            <polygon fill="hsl(30 85% 51%)" points="25,15.5 29.5,9 32.5,17"/>
                            <!-- Eyes -->
                            <ellipse cx="15.5" cy="22.5" fill="hsl(152 76% 80%)" rx="2.5" ry="3"/>
                            <ellipse cx="24.5" cy="22.5" fill="hsl(152 76% 80%)" rx="2.5" ry="3"/>
                            <ellipse cx="15.5" cy="22.5" fill="hsl(165 91% 9%)" rx="1.2" ry="2"/>
                            <ellipse cx="24.5" cy="22.5" fill="hsl(165 91% 9%)" rx="1.2" ry="2"/>
                            <circle cx="16" cy="21" fill="white" r="0.7"/>
                            <circle cx="25" cy="21" fill="white" r="0.7"/>
                            <!-- Nose -->
                            <ellipse cx="20" cy="27" fill="hsl(0 72% 51%)" rx="1.4" ry="1"/>
                            <!-- Whiskers -->
                            <line opacity="0.5" stroke="hsl(217 19% 27%)" stroke-width="0.6" x1="6" x2="16" y1="26.5"
                                  y2="27"/>
                            <line opacity="0.5" stroke="hsl(217 19% 27%)" stroke-width="0.6" x1="6" x2="16" y1="28.5"
                                  y2="28"/>
                            <line opacity="0.5" stroke="hsl(217 19% 27%)" stroke-width="0.6" x1="24" x2="34" y1="27"
                                  y2="26.5"/>
                            <line opacity="0.5" stroke="hsl(217 19% 27%)" stroke-width="0.6" x1="24" x2="34" y1="28"
                                  y2="28.5"/>
                        </svg>
                        <span class="text-[1.2rem] font-bold tracking-tight leading-none">
                            <span class="text-fg-title">Klever</span><span class="text-primary-600">Kat</span>
                        </span>
                    </Link>

                    <!-- Categories nav -->
                    <nav aria-label="Catégories de comparaison"
                         class="hidden flex-1 items-center justify-center gap-0.5 lg:flex">
                        <div
                            v-for="cat in navCategories"
                            :key="cat.id"
                            class="shrink-0"
                            @mouseenter="openCategory(cat)"
                        >
                            <Link
                                :id="`category-trigger-${cat.id}`"
                                :aria-controls="`category-menu-${cat.id}`"
                                :aria-expanded="activeCategoryId === cat.id"
                                :class="[
                                    'flex items-center gap-2 whitespace-nowrap rounded-lg px-2.5 py-2 text-sm font-semibold transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-500 xl:px-3.5',
                                    activeCategoryId === cat.id
                                        ? 'bg-primary-50 text-primary-700'
                                        : 'text-fg-subtitle hover:bg-bg-high hover:text-fg-title',
                                ]"
                                :href="route('compare.sectors', cat.slug)"
                                @click="closeMenu"
                                @focus="handleCategoryFocus(cat)"
                                @keydown.down.prevent="focusMenu(cat)"
                            >
                                <svg class="size-[1.1rem] shrink-0" fill="none" stroke="currentColor"
                                     stroke-width="1.75"
                                     viewBox="0 0 24 24">
                                    <path :d="getCategoryIcon(cat.slug)" stroke-linecap="round"
                                          stroke-linejoin="round"/>
                                </svg>
                                {{ cat.name }}
                                <ChevronDown
                                    :class="['size-3.5 transition-transform duration-150 motion-reduce:transition-none', activeCategoryId === cat.id ? 'rotate-180' : '']"
                                    aria-hidden="true"
                                />
                            </Link>
                        </div>
                    </nav>

                    <!-- Right: language + auth CTA -->
                    <div class="flex shrink-0 items-center gap-2.5">
                        <!-- Language selector -->
                        <button
                            class="hidden items-center gap-1.5 rounded-lg border border-border px-3 py-2 text-sm font-medium text-fg-subtext transition-colors hover:bg-bg-high hover:text-fg-title sm:flex"
                            type="button"
                        >
                            <span class="text-base leading-none">🇬🇧</span>
                            <span>English</span>
                            <svg class="size-3 opacity-60" fill="none" stroke="currentColor" stroke-width="2.5"
                                 viewBox="0 0 24 24">
                                <path d="M19.5 8.25l-7.5 7.5-7.5-7.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>

                        <!-- Auth -->
                        <template v-if="auth?.user">
                            <Link
                                :href="route('dashboard', auth.currentTeam?.slug ?? '')"
                                class="btn btn-outline btn-outline-neutral btn-sm rounded-lg"
                            >
                                Mon espace
                            </Link>
                        </template>
                        <template v-else>
                            <Link
                                :href="route('login')"
                                class="btn btn-solid btn-solid-secondary btn-md rounded-lg"
                            >
                                Me connecter
                            </Link>
                        </template>
                    </div>
                </div>
            </header>

            <Transition
                enter-active-class="transition duration-150 ease-out motion-reduce:transition-none"
                enter-from-class="-translate-y-2 opacity-0"
                enter-to-class="translate-y-0 opacity-100"
                leave-active-class="transition duration-100 ease-in motion-reduce:transition-none"
                leave-from-class="translate-y-0 opacity-100"
                leave-to-class="-translate-y-1 opacity-0"
            >
                <div
                    v-if="activeCategory"
                    :id="`category-menu-${activeCategory.id}`"
                    ref="menuPanel"
                    :aria-label="`Explorer ${activeCategory.name}`"
                    class="absolute inset-x-0 top-full hidden px-3 sm:px-5 lg:block"
                    role="region"
                    @mouseenter="cancelMenuClose"
                >
                    <div
                        class="mx-auto max-h-[calc(100vh-6rem)] max-w-7xl overflow-x-hidden overflow-y-auto rounded-2xl border border-border bg-bg shadow-2xl shadow-neutral-900/15"
                        @mouseenter="cancelMenuClose"
                        @mouseleave="scheduleMenuClose"
                    >
                        <div class="grid min-h-96 grid-cols-[17rem_minmax(0,1fr)_19rem]">
                            <aside class="border-r border-border bg-bg-light p-4">
                                <nav :aria-label="`Secteurs ${activeCategory.name}`" class="flex flex-col gap-1">
                                    <Link
                                        v-for="sector in activeCategory.sectors"
                                        :key="sector.id"
                                        :class="[
                                            'group flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-semibold transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-500',
                                            activeSectorId === sector.id
                                                ? 'bg-primary-600 text-white shadow-sm'
                                                : 'text-fg-subtitle hover:bg-bg-high hover:text-fg-title',
                                        ]"
                                        :href="route('compare.products', [activeCategory?.slug, sector?.slug])"
                                        @click="closeMenu"
                                        @focus="selectSector(sector)"
                                        @mouseenter="selectSector(sector)"
                                    >
                                        <span
                                            :class="[
                                                'flex size-9 shrink-0 items-center justify-center rounded-lg',
                                                activeSectorId === sector.id ? 'bg-white/15' : 'bg-bg text-primary-600',
                                            ]"
                                        >
                                            <Layers3 :stroke-width="1.8" aria-hidden="true" class="size-4.5"/>
                                        </span>
                                        <span class="min-w-0 flex-1 leading-5">{{ sector.name }}</span>
                                        <ChevronRight aria-hidden="true" class="size-4 shrink-0 opacity-70"/>
                                    </Link>

                                    <p v-if="!activeCategory.sectors?.length"
                                       class="rounded-xl bg-bg px-3 py-4 text-sm text-fg-subtext">
                                        Aucun secteur disponible pour le moment.
                                    </p>
                                </nav>
                            </aside>

                            <section class="min-w-0 p-6 lg:p-7">
                                <div class="flex items-start justify-between gap-6 border-b border-border pb-5">
                                    <div class="min-w-0">
                                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-secondary-700">
                                            Nos produits</p>
                                        <h2 class="mt-1 text-xl font-bold text-fg-title">
                                            {{ activeSector?.name ?? activeCategory.name }}
                                        </h2>
                                    </div>

                                    <Link
                                        v-if="activeSector"
                                        :href="route('compare.products', [activeCategory?.slug, activeSector?.slug])"
                                        class="inline-flex shrink-0 items-center gap-2 rounded-lg bg-secondary-600 px-4 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-secondary-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-secondary-600"
                                        @click="closeMenu"
                                    >
                                        Comparer
                                        <ArrowRight aria-hidden="true" class="size-4"/>
                                    </Link>
                                </div>

                                <div v-if="activeProducts.length" class="mt-5 grid grid-cols-2 gap-2.5">
                                    <Link
                                        v-for="product in activeProducts"
                                        :key="product.id"
                                        :href="route('compare.wizard', [activeCategory?.slug, activeSector?.slug, product?.slug])"
                                        class="group rounded-xl border border-transparent px-3.5 py-3 transition-colors hover:border-border hover:bg-bg-light focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-500"
                                        @click="closeMenu"
                                    >
                                        <span class="flex items-start justify-between gap-3">
                                            <span class="min-w-0">
                                                <span
                                                    class="line-clamp-2 block text-sm font-semibold leading-5 text-fg-title group-hover:text-primary-700">
                                                    {{ product.name }}
                                                </span>
                                            </span>
                                            <ArrowRight aria-hidden="true"
                                                        class="mt-0.5 size-4 shrink-0 text-primary-600 opacity-0 transition-opacity group-hover:opacity-100 group-focus-visible:opacity-100"/>
                                        </span>
                                    </Link>
                                </div>

                                <div v-else
                                     class="mt-5 rounded-xl border border-dashed border-border bg-bg-light px-5 py-8 text-center">
                                    <p class="text-sm font-medium text-fg-subtitle">Sélectionnez un secteur pour
                                        découvrir ses produits.</p>
                                </div>

                                <Link
                                    v-if="activeSector?.products?.length > activeProducts.length"
                                    :href="route('compare.products', [activeCategory.slug, activeSector.slug])"
                                    class="mt-4 inline-flex items-center gap-1.5 text-sm font-semibold text-primary-700 hover:text-primary-800"
                                    @click="closeMenu"
                                >
                                    Voir tous les produits
                                    <ArrowRight aria-hidden="true" class="size-4"/>
                                </Link>
                            </section>

                            <aside class="border-l border-border bg-secondary-50 p-5">
                                <div class="rounded-2xl bg-bg p-5 shadow-sm ring-1 ring-secondary-100">
                                    <div
                                        class="flex size-11 items-center justify-center rounded-xl bg-secondary-100 text-secondary-700">
                                        <CircleHelp :stroke-width="1.8" aria-hidden="true" class="size-5"/>
                                    </div>
                                    <h2 class="mt-4 text-lg font-bold text-fg-title">Besoin d’aide ?</h2>
                                    <p class="mt-2 text-sm leading-6 text-fg-subtext">
                                        Choisissez un secteur, sélectionnez un produit puis répondez au questionnaire
                                        pour lancer votre comparaison.
                                    </p>
                                    <Link
                                        :href="route('compare.sectors', activeCategory.slug)"
                                        class="mt-5 inline-flex items-center gap-2 text-sm font-semibold text-secondary-700 hover:text-secondary-800"
                                        @click="closeMenu"
                                    >
                                        Voir toute la catégorie
                                        <ArrowRight aria-hidden="true" class="size-4"/>
                                    </Link>
                                </div>

                                <div class="mt-4 rounded-2xl bg-primary-600 p-5 text-white">
                                    <p class="text-xs font-semibold uppercase tracking-[0.14em] text-primary-100">
                                        KleverKat</p>
                                    <p class="mt-2 text-base font-bold">Trouvez le produit adapté à votre besoin.</p>
                                    <Link
                                        :href="activeSector
                                            ? route('compare.products', [activeCategory.slug, activeSector.slug])
                                            : route('compare.sectors', activeCategory.slug)"
                                        class="mt-4 inline-flex items-center gap-2 rounded-lg bg-white px-3.5 py-2 text-sm font-semibold text-primary-700 transition-colors hover:bg-primary-50"
                                        @click="closeMenu"
                                    >
                                        Commencer
                                        <ArrowRight aria-hidden="true" class="size-4"/>
                                    </Link>
                                </div>
                            </aside>
                        </div>
                    </div>
                </div>
            </Transition>
        </div>

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
        <main>
            <slot/>
        </main>
    </div>
</template>
