<script setup>
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import SettingsLayout from '@/Layouts/SettingsLayout.vue';

const props = defineProps({
    mustVerifyEmail: { type: Boolean, default: false },
    status:          { type: String,  default: null },
});

const page = usePage();
const user = computed(() => page.props.auth?.user);

const form = useForm({
    name:  user.value?.name  ?? '',
    email: user.value?.email ?? '',
});

const showDeleteModal = ref(false);
const deleteForm = useForm({ password: '' });

function save() {
    form.put(route('profile.update'));
}

function deleteAccount() {
    deleteForm.delete(route('profile.destroy'), {
        onFinish: () => deleteForm.reset('password'),
    });
}
</script>

<template>
    <Head title="Profil" />
    <SettingsLayout>
        <h2 class="mb-6 text-xl font-semibold text-zinc-900">Profil</h2>

        <!-- Infos perso -->
        <section class="mb-8 rounded-2xl border border-zinc-200 bg-white p-6">
            <h3 class="mb-4 font-medium text-zinc-700">Informations personnelles</h3>
            <form @submit.prevent="save" class="space-y-4">
                <div>
                    <label class="mb-1 block text-sm font-medium text-zinc-700">Nom</label>
                    <input v-model="form.name" type="text" class="w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none" />
                    <p v-if="form.errors.name" class="mt-1 text-xs text-red-600">{{ form.errors.name }}</p>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-zinc-700">E-mail</label>
                    <input v-model="form.email" type="email" class="w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none" />
                    <p v-if="form.errors.email" class="mt-1 text-xs text-red-600">{{ form.errors.email }}</p>
                </div>

                <div v-if="mustVerifyEmail && !user?.email_verified_at" class="rounded-lg bg-amber-50 border border-amber-200 px-4 py-3 text-sm text-amber-800">
                    Votre e-mail n'est pas vérifié.
                    <a :href="route('verification.resend')" class="ml-1 underline" @click.prevent="$inertia.post(route('verification.resend'))">Renvoyer le lien</a>
                </div>

                <div v-if="status === 'verification-link-sent'" class="rounded-lg bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm text-emerald-800">
                    Lien de vérification envoyé !
                </div>

                <div class="flex justify-end">
                    <button type="submit" :disabled="form.processing" class="rounded-xl bg-blue-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-blue-700 disabled:opacity-50">
                        {{ form.processing ? 'Enregistrement…' : 'Enregistrer' }}
                    </button>
                </div>
            </form>
        </section>

        <!-- Supprimer le compte -->
        <section class="rounded-2xl border border-red-200 bg-white p-6">
            <h3 class="mb-2 font-medium text-red-700">Supprimer le compte</h3>
            <p class="mb-4 text-sm text-zinc-500">Cette action est irréversible. Toutes vos données seront supprimées.</p>
            <button @click="showDeleteModal = true" class="rounded-xl border border-red-300 px-4 py-2 text-sm font-medium text-red-700 transition hover:bg-red-50">
                Supprimer mon compte
            </button>
        </section>

        <!-- Modal suppression -->
        <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4">
            <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
                <h3 class="mb-2 text-lg font-bold text-zinc-900">Confirmer la suppression</h3>
                <p class="mb-4 text-sm text-zinc-500">Entrez votre mot de passe pour confirmer.</p>
                <input
                    v-model="deleteForm.password"
                    type="password"
                    placeholder="Mot de passe"
                    class="mb-4 w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none"
                />
                <p v-if="deleteForm.errors.password" class="mb-2 text-xs text-red-600">{{ deleteForm.errors.password }}</p>
                <div class="flex gap-3">
                    <button @click="showDeleteModal = false" class="flex-1 rounded-xl border border-zinc-300 px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50">Annuler</button>
                    <button @click="deleteAccount" :disabled="deleteForm.processing" class="flex-1 rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 disabled:opacity-50">
                        {{ deleteForm.processing ? 'Suppression…' : 'Supprimer' }}
                    </button>
                </div>
            </div>
        </div>
    </SettingsLayout>
</template>
