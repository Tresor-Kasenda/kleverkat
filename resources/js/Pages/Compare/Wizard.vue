<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import CompareLayout from '@/Layouts/CompareLayout.vue';

const props = defineProps({
    category: { type: Object, required: true },
    sector:   { type: Object, required: true },
    product:  { type: Object, required: true },
    steps:    { type: Object, required: true },
    stepKeys: { type: Array,  required: true },
});

const stepIndex = ref(0);
const answers = ref({});
const errors = ref({});
const submitting = ref(false);

const currentKey = computed(() => props.stepKeys[stepIndex.value] ?? null);
const currentQuestions = computed(() => currentKey.value ? (props.steps[currentKey.value] ?? []) : []);
const progress = computed(() => Math.round(((stepIndex.value + 1) / props.stepKeys.length) * 100));
const isLastStep = computed(() => stepIndex.value === props.stepKeys.length - 1);

// Init answers
props.stepKeys.forEach(key => {
    (props.steps[key] ?? []).forEach(q => {
        if (answers.value[q.field_key] === undefined) {
            answers.value[q.field_key] = q.input_type === 'checkbox' ? [] : '';
        }
    });
});

function validateCurrentStep() {
    errors.value = {};
    let valid = true;
    for (const q of currentQuestions.value) {
        if (!q.is_required) { continue; }
        const val = answers.value[q.field_key];
        if (Array.isArray(val) && val.length === 0) {
            errors.value[q.field_key] = 'Ce champ est requis.';
            valid = false;
        } else if (!Array.isArray(val) && (val === '' || val === null || val === undefined)) {
            errors.value[q.field_key] = 'Ce champ est requis.';
            valid = false;
        }
    }
    return valid;
}

function next() {
    if (!validateCurrentStep()) { return; }
    if (isLastStep.value) {
        submit();
    } else {
        stepIndex.value++;
    }
}

function prev() {
    if (stepIndex.value > 0) { stepIndex.value--; }
}

function submit() {
    submitting.value = true;
    router.post(
        route('compare.wizard.store', [props.category.slug, props.sector.slug, props.product.slug]),
        { answers: answers.value },
        { onError: () => { submitting.value = false; } }
    );
}
</script>

<template>
    <Head :title="`${product.name} — Questionnaire`" />
    <CompareLayout>
        <nav class="mb-6 flex flex-wrap items-center gap-2 text-sm text-zinc-500">
            <Link :href="route('compare.categories')" class="transition-colors hover:text-zinc-900">Catégories</Link>
            <span>›</span>
            <Link :href="route('compare.sectors', category.slug)" class="transition-colors hover:text-zinc-900">{{ category.name }}</Link>
            <span>›</span>
            <Link :href="route('compare.products', [category.slug, sector.slug])" class="transition-colors hover:text-zinc-900">{{ sector.name }}</Link>
            <span>›</span>
            <span class="font-medium text-zinc-900">{{ product.name }}</span>
        </nav>

        <div class="mx-auto max-w-2xl">
            <!-- Barre de progression -->
            <div class="mb-8">
                <div class="mb-2 flex items-center justify-between text-sm">
                    <span class="font-medium text-zinc-700">{{ product.name }}</span>
                    <span class="text-zinc-500">Étape {{ stepIndex + 1 }} / {{ stepKeys.length }}</span>
                </div>
                <div class="h-2 w-full overflow-hidden rounded-full bg-zinc-200">
                    <div
                        class="h-full rounded-full bg-blue-600 transition-all duration-300"
                        :style="{ width: `${progress}%` }"
                    />
                </div>
            </div>

            <!-- Questions de l'étape -->
            <div class="space-y-6 rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-zinc-900">{{ currentKey }}</h2>

                <div v-for="q in currentQuestions" :key="q.id" class="space-y-1.5">
                    <label :for="`q-${q.id}`" class="block text-sm font-medium text-zinc-700">
                        {{ q.label }}
                        <span v-if="q.is_required" class="text-red-500">*</span>
                    </label>

                    <!-- Text -->
                    <input
                        v-if="q.input_type === 'text'"
                        :id="`q-${q.id}`"
                        v-model="answers[q.field_key]"
                        type="text"
                        :placeholder="q.placeholder ?? ''"
                        class="w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none"
                        :class="{ 'border-red-500': errors[q.field_key] }"
                    />

                    <!-- Number -->
                    <input
                        v-else-if="q.input_type === 'number'"
                        :id="`q-${q.id}`"
                        v-model="answers[q.field_key]"
                        type="number"
                        :placeholder="q.placeholder ?? ''"
                        class="w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none"
                        :class="{ 'border-red-500': errors[q.field_key] }"
                    />

                    <!-- Date -->
                    <input
                        v-else-if="q.input_type === 'date'"
                        :id="`q-${q.id}`"
                        v-model="answers[q.field_key]"
                        type="date"
                        class="w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none"
                        :class="{ 'border-red-500': errors[q.field_key] }"
                    />

                    <!-- Textarea -->
                    <textarea
                        v-else-if="q.input_type === 'textarea'"
                        :id="`q-${q.id}`"
                        v-model="answers[q.field_key]"
                        :placeholder="q.placeholder ?? ''"
                        rows="3"
                        class="w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none"
                        :class="{ 'border-red-500': errors[q.field_key] }"
                    />

                    <!-- Select -->
                    <select
                        v-else-if="q.input_type === 'select'"
                        :id="`q-${q.id}`"
                        v-model="answers[q.field_key]"
                        class="w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none"
                        :class="{ 'border-red-500': errors[q.field_key] }"
                    >
                        <option value="" disabled>{{ q.placeholder ?? 'Sélectionner…' }}</option>
                        <option v-for="opt in (q.options ?? [])" :key="opt" :value="opt">{{ opt }}</option>
                    </select>

                    <!-- Radio -->
                    <div v-else-if="q.input_type === 'radio'" class="flex flex-wrap gap-3">
                        <label
                            v-for="opt in (q.options ?? [])"
                            :key="opt"
                            class="flex cursor-pointer items-center gap-2 rounded-lg border border-zinc-200 px-4 py-2 text-sm transition hover:border-blue-400"
                            :class="{ 'border-blue-600 bg-blue-50 text-blue-700': answers[q.field_key] === opt }"
                        >
                            <input type="radio" :name="`q-${q.id}`" :value="opt" v-model="answers[q.field_key]" class="sr-only" />
                            {{ opt }}
                        </label>
                    </div>

                    <!-- Checkbox -->
                    <div v-else-if="q.input_type === 'checkbox'" class="flex flex-wrap gap-3">
                        <label
                            v-for="opt in (q.options ?? [])"
                            :key="opt"
                            class="flex cursor-pointer items-center gap-2 rounded-lg border border-zinc-200 px-4 py-2 text-sm transition hover:border-blue-400"
                            :class="{ 'border-blue-600 bg-blue-50 text-blue-700': answers[q.field_key]?.includes(opt) }"
                        >
                            <input type="checkbox" :value="opt" v-model="answers[q.field_key]" class="sr-only" />
                            {{ opt }}
                        </label>
                    </div>

                    <!-- Boolean -->
                    <div v-else-if="q.input_type === 'boolean'" class="flex gap-3">
                        <label
                            class="flex cursor-pointer items-center gap-2 rounded-lg border px-6 py-2 text-sm transition"
                            :class="answers[q.field_key] === 'true' ? 'border-blue-600 bg-blue-50 text-blue-700' : 'border-zinc-200 hover:border-blue-400'"
                        >
                            <input type="radio" :name="`q-${q.id}`" value="true" v-model="answers[q.field_key]" class="sr-only" />
                            Oui
                        </label>
                        <label
                            class="flex cursor-pointer items-center gap-2 rounded-lg border px-6 py-2 text-sm transition"
                            :class="answers[q.field_key] === 'false' ? 'border-blue-600 bg-blue-50 text-blue-700' : 'border-zinc-200 hover:border-blue-400'"
                        >
                            <input type="radio" :name="`q-${q.id}`" value="false" v-model="answers[q.field_key]" class="sr-only" />
                            Non
                        </label>
                    </div>

                    <p v-if="q.helper_text" class="text-xs text-zinc-500">{{ q.helper_text }}</p>
                    <p v-if="errors[q.field_key]" class="text-xs text-red-600">{{ errors[q.field_key] }}</p>
                </div>
            </div>

            <!-- Navigation -->
            <div class="mt-6 flex items-center justify-between">
                <button
                    v-if="stepIndex > 0"
                    type="button"
                    @click="prev"
                    class="rounded-xl border border-zinc-300 px-5 py-2.5 text-sm font-medium text-zinc-700 transition hover:bg-zinc-50"
                >
                    ← Précédent
                </button>
                <div v-else />

                <button
                    type="button"
                    @click="next"
                    :disabled="submitting"
                    class="rounded-xl bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700 disabled:opacity-50"
                >
                    <span v-if="submitting">Comparaison en cours…</span>
                    <span v-else-if="isLastStep">Voir les résultats →</span>
                    <span v-else>Suivant →</span>
                </button>
            </div>
        </div>
    </CompareLayout>
</template>
