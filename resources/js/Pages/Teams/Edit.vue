<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import SettingsLayout from '@/Layouts/SettingsLayout.vue';

const props = defineProps({
    team:           { type: Object, required: true },
    members:        { type: Array,  default: () => [] },
    invitations:    { type: Array,  default: () => [] },
    availableRoles: { type: Array,  default: () => [] },
});

const nameForm = useForm({ name: props.team.name });
const inviteForm = useForm({ email: '', role: 'member' });
const showInviteModal = ref(false);

function updateName() {
    nameForm.put(route('teams.update', props.team.slug));
}

function invite() {
    inviteForm.post(route('teams.invite', props.team.slug), {
        onSuccess: () => {
            showInviteModal.value = false;
            inviteForm.reset();
        },
    });
}
</script>

<template>
    <Head :title="`Équipe — ${team.name}`" />
    <SettingsLayout>
        <h2 class="mb-6 text-xl font-semibold text-zinc-900">{{ team.name }}</h2>

        <!-- Renommer l'équipe -->
        <section v-if="!team.is_personal" class="mb-6 rounded-2xl border border-zinc-200 bg-white p-6">
            <h3 class="mb-4 font-medium text-zinc-700">Nom de l'équipe</h3>
            <form @submit.prevent="updateName" class="flex gap-3">
                <input
                    v-model="nameForm.name"
                    type="text"
                    class="flex-1 rounded-lg border border-zinc-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none"
                />
                <button type="submit" :disabled="nameForm.processing" class="rounded-xl bg-blue-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-blue-700 disabled:opacity-50">
                    Enregistrer
                </button>
            </form>
            <p v-if="nameForm.errors.name" class="mt-1 text-xs text-red-600">{{ nameForm.errors.name }}</p>
        </section>

        <!-- Membres -->
        <section class="mb-6 rounded-2xl border border-zinc-200 bg-white p-6">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="font-medium text-zinc-700">Membres ({{ members.length }})</h3>
                <button @click="showInviteModal = true" class="rounded-xl border border-zinc-300 px-3 py-1.5 text-sm font-medium text-zinc-600 transition hover:bg-zinc-50">
                    + Inviter
                </button>
            </div>
            <div class="space-y-2">
                <div v-for="m in members" :key="m.id" class="flex items-center justify-between rounded-lg bg-zinc-50 px-4 py-3">
                    <div>
                        <p class="text-sm font-medium text-zinc-900">{{ m.name }}</p>
                        <p class="text-xs text-zinc-500">{{ m.email }}</p>
                    </div>
                    <span class="rounded-full bg-zinc-200 px-2.5 py-0.5 text-xs capitalize text-zinc-700">{{ m.role }}</span>
                </div>
            </div>
        </section>

        <!-- Invitations en attente -->
        <section v-if="invitations.length" class="rounded-2xl border border-zinc-200 bg-white p-6">
            <h3 class="mb-4 font-medium text-zinc-700">Invitations en attente</h3>
            <div class="space-y-2">
                <div v-for="inv in invitations" :key="inv.id" class="flex items-center justify-between rounded-lg bg-amber-50 px-4 py-3">
                    <div>
                        <p class="text-sm font-medium text-zinc-900">{{ inv.email }}</p>
                        <p class="text-xs text-zinc-500 capitalize">{{ inv.role }}</p>
                    </div>
                    <span class="rounded-full bg-amber-200 px-2.5 py-0.5 text-xs text-amber-700">En attente</span>
                </div>
            </div>
        </section>

        <!-- Modal invitation -->
        <div v-if="showInviteModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4">
            <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
                <h3 class="mb-4 text-lg font-bold">Inviter un membre</h3>
                <div class="space-y-3">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-zinc-700">E-mail</label>
                        <input v-model="inviteForm.email" type="email" class="w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none" />
                        <p v-if="inviteForm.errors.email" class="mt-1 text-xs text-red-600">{{ inviteForm.errors.email }}</p>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-zinc-700">Rôle</label>
                        <select v-model="inviteForm.role" class="w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
                            <option v-for="r in availableRoles" :key="r.value" :value="r.value">{{ r.label }}</option>
                        </select>
                    </div>
                </div>
                <div class="mt-4 flex gap-3">
                    <button @click="showInviteModal = false" class="flex-1 rounded-xl border border-zinc-300 px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50">Annuler</button>
                    <button @click="invite" :disabled="inviteForm.processing" class="flex-1 rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 disabled:opacity-50">
                        {{ inviteForm.processing ? 'Envoi…' : 'Inviter' }}
                    </button>
                </div>
            </div>
        </div>
    </SettingsLayout>
</template>
