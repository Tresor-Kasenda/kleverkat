<script setup>
import { Head } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import SettingsLayout from '@/Layouts/SettingsLayout.vue';

const appearance = ref(localStorage.getItem('appearance') ?? 'system');

watch(appearance, (val) => {
    localStorage.setItem('appearance', val);
    const root = document.documentElement;
    if (val === 'dark') {
        root.classList.add('dark');
    } else if (val === 'light') {
        root.classList.remove('dark');
    } else {
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        root.classList.toggle('dark', prefersDark);
    }
});

const options = [
    { value: 'light',  label: 'Clair' },
    { value: 'dark',   label: 'Sombre' },
    { value: 'system', label: 'Système' },
];
</script>

<template>
    <Head title="Apparence" />
    <SettingsLayout>
        <h2 class="mb-6 text-xl font-semibold text-zinc-900">Apparence</h2>

        <section class="rounded-2xl border border-zinc-200 bg-white p-6">
            <h3 class="mb-4 font-medium text-zinc-700">Thème</h3>
            <div class="flex gap-3">
                <label
                    v-for="opt in options"
                    :key="opt.value"
                    class="flex cursor-pointer items-center gap-2 rounded-xl border px-5 py-2.5 text-sm font-medium transition"
                    :class="appearance === opt.value ? 'border-blue-600 bg-blue-50 text-blue-700' : 'border-zinc-200 text-zinc-600 hover:border-zinc-300'"
                >
                    <input type="radio" :value="opt.value" v-model="appearance" class="sr-only" />
                    {{ opt.label }}
                </label>
            </div>
        </section>
    </SettingsLayout>
</template>
