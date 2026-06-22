<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import SettingsLayout from '@/Layouts/SettingsLayout.vue';

defineProps({
    teams: { type: Array, default: () => [] },
});

const showCreate = ref(false);
const form = useForm({ name: '' });

function createTeam() {
    form.post(route('teams.store'), {
        onSuccess: () => {
            showCreate.value = false;
            form.reset('name');
        },
    });
}
</script>

<template>
    <Head title="Équipes" />
    <SettingsLayout>
        <div class="mb-6 flex items-center justify-between">
            <h2 class="text-xl font-semibold text-zinc-900">Équipes</h2>
            <button @click="showCreate = true" class="rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700">
                + Nouvelle équipe
            </button>
        </div>

        <div class="space-y-3">
            <div
                v-for="team in teams"
                :key="team.id"
                class="flex items-center justify-between rounded-xl border border-zinc-200 bg-white p-4"
            >
                <div>
                    <div class="flex items-center gap-2">
                        <span class="font-medium text-zinc-900">{{ team.name }}</span>
                        <span v-if="team.isPersonal" class="rounded-full bg-zinc-100 px-2 py-0.5 text-xs text-zinc-500">Personnel</span>
                    </div>
                    <p class="text-sm text-zinc-500">{{ team.roleLabel }}</p>
                </div>
                <Link
                    :href="route('teams.edit', team.slug)"
                    class="rounded-lg border border-zinc-200 px-3 py-1.5 text-sm text-zinc-600 transition hover:bg-zinc-50"
                >
                    {{ team.role === 'member' ? 'Voir' : 'Gérer' }}
                </Link>
            </div>
        </div>

        <!-- Modal création équipe -->
        <div v-if="showCreate" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4">
            <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
                <h3 class="mb-4 text-lg font-bold">Nouvelle équipe</h3>
                <input
                    v-model="form.name"
                    type="text"
                    placeholder="Nom de l'équipe"
                    class="mb-1 w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none"
                />
                <p v-if="form.errors.name" class="mb-3 text-xs text-red-600">{{ form.errors.name }}</p>
                <div class="mt-4 flex gap-3">
                    <button @click="showCreate = false" class="flex-1 rounded-xl border border-zinc-300 px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50">Annuler</button>
                    <button @click="createTeam" :disabled="form.processing" class="flex-1 rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 disabled:opacity-50">
                        {{ form.processing ? 'Création…' : 'Créer' }}
                    </button>
                </div>
            </div>
        </div>
    </SettingsLayout>
</template>
