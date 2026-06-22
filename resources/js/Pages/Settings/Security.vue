<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import SettingsLayout from '@/Layouts/SettingsLayout.vue';

defineProps({
    canManageTwoFactor: { type: Boolean, default: false },
    twoFactorEnabled:   { type: Boolean, default: false },
    twoFactorConfirmed: { type: Boolean, default: false },
});

const form = useForm({
    current_password:      '',
    password:              '',
    password_confirmation: '',
});

function updatePassword() {
    form.put(route('security.password.update'), {
        onFinish: () => form.reset('current_password', 'password', 'password_confirmation'),
    });
}
</script>

<template>
    <Head title="Sécurité" />
    <SettingsLayout>
        <h2 class="mb-6 text-xl font-semibold text-zinc-900">Sécurité</h2>

        <!-- Mot de passe -->
        <section class="mb-6 rounded-2xl border border-zinc-200 bg-white p-6">
            <h3 class="mb-4 font-medium text-zinc-700">Changer le mot de passe</h3>
            <form @submit.prevent="updatePassword" class="space-y-4">
                <div>
                    <label class="mb-1 block text-sm font-medium text-zinc-700">Mot de passe actuel</label>
                    <input v-model="form.current_password" type="password" autocomplete="current-password" class="w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none" />
                    <p v-if="form.errors.current_password" class="mt-1 text-xs text-red-600">{{ form.errors.current_password }}</p>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-zinc-700">Nouveau mot de passe</label>
                    <input v-model="form.password" type="password" autocomplete="new-password" class="w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none" />
                    <p v-if="form.errors.password" class="mt-1 text-xs text-red-600">{{ form.errors.password }}</p>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-zinc-700">Confirmer le mot de passe</label>
                    <input v-model="form.password_confirmation" type="password" autocomplete="new-password" class="w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none" />
                </div>
                <div class="flex justify-end">
                    <button type="submit" :disabled="form.processing" class="rounded-xl bg-blue-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-blue-700 disabled:opacity-50">
                        {{ form.processing ? 'Enregistrement…' : 'Mettre à jour' }}
                    </button>
                </div>
            </form>
        </section>

        <!-- 2FA -->
        <section v-if="canManageTwoFactor" class="rounded-2xl border border-zinc-200 bg-white p-6">
            <h3 class="mb-2 font-medium text-zinc-700">Double authentification (2FA)</h3>
            <div v-if="twoFactorEnabled && twoFactorConfirmed" class="flex items-center gap-2 text-sm text-emerald-700">
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                La double authentification est activée.
            </div>
            <div v-else class="flex items-center gap-2 text-sm text-zinc-500">
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                La double authentification est désactivée.
            </div>
        </section>
    </SettingsLayout>
</template>
