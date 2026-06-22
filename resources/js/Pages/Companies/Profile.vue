<script setup>
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    company:   { type: Object,  required: true },
    canUpdate: { type: Boolean, default: false },
});

const page = usePage();
const teamSlug = computed(() => page.props.auth?.currentTeam?.slug ?? '');

const form = useForm({
    description:    props.company.description    ?? '',
    website_url:    props.company.website_url    ?? '',
    support_email:  props.company.support_email  ?? '',
    support_phone:  props.company.support_phone  ?? '',
    contact_name:   props.company.contact_name   ?? '',
    address_line_1: props.company.address_line_1 ?? '',
    address_line_2: props.company.address_line_2 ?? '',
    city:           props.company.city           ?? '',
    postal_code:    props.company.postal_code    ?? '',
    country:        props.company.country        ?? '',
});

function save() {
    form.put(route('company.profile.update', teamSlug.value));
}
</script>

<template>
    <Head title="Profil entreprise" />
    <AppLayout>
        <div class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-zinc-900">{{ company.name }}</h1>
                <p v-if="company.category" class="text-sm text-zinc-500">{{ company.category.name }}</p>
            </div>

            <form @submit.prevent="save" class="space-y-6">
                <!-- Description -->
                <section class="rounded-2xl border border-zinc-200 bg-white p-6">
                    <h2 class="mb-4 font-medium text-zinc-700">Présentation</h2>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-zinc-700">Description</label>
                        <textarea v-model="form.description" rows="4" :disabled="!canUpdate" class="w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none disabled:bg-zinc-50" />
                    </div>
                    <div class="mt-3">
                        <label class="mb-1 block text-sm font-medium text-zinc-700">Site web</label>
                        <input v-model="form.website_url" type="url" :disabled="!canUpdate" class="w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none disabled:bg-zinc-50" />
                        <p v-if="form.errors.website_url" class="mt-1 text-xs text-red-600">{{ form.errors.website_url }}</p>
                    </div>
                </section>

                <!-- Contact -->
                <section class="rounded-2xl border border-zinc-200 bg-white p-6">
                    <h2 class="mb-4 font-medium text-zinc-700">Contact</h2>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-zinc-700">Nom du contact</label>
                            <input v-model="form.contact_name" type="text" :disabled="!canUpdate" class="w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none disabled:bg-zinc-50" />
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-zinc-700">E-mail support</label>
                            <input v-model="form.support_email" type="email" :disabled="!canUpdate" class="w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none disabled:bg-zinc-50" />
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-zinc-700">Téléphone support</label>
                            <input v-model="form.support_phone" type="tel" :disabled="!canUpdate" class="w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none disabled:bg-zinc-50" />
                        </div>
                    </div>
                </section>

                <!-- Adresse -->
                <section class="rounded-2xl border border-zinc-200 bg-white p-6">
                    <h2 class="mb-4 font-medium text-zinc-700">Adresse</h2>
                    <div class="space-y-3">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-zinc-700">Adresse ligne 1</label>
                            <input v-model="form.address_line_1" type="text" :disabled="!canUpdate" class="w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none disabled:bg-zinc-50" />
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-zinc-700">Adresse ligne 2</label>
                            <input v-model="form.address_line_2" type="text" :disabled="!canUpdate" class="w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none disabled:bg-zinc-50" />
                        </div>
                        <div class="grid gap-3 sm:grid-cols-3">
                            <div>
                                <label class="mb-1 block text-sm font-medium text-zinc-700">Ville</label>
                                <input v-model="form.city" type="text" :disabled="!canUpdate" class="w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none disabled:bg-zinc-50" />
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-zinc-700">Code postal</label>
                                <input v-model="form.postal_code" type="text" :disabled="!canUpdate" class="w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none disabled:bg-zinc-50" />
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-zinc-700">Pays</label>
                                <input v-model="form.country" type="text" :disabled="!canUpdate" class="w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none disabled:bg-zinc-50" />
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Gestionnaire -->
                <section v-if="company.manager" class="rounded-2xl border border-zinc-200 bg-white p-6">
                    <h2 class="mb-2 font-medium text-zinc-700">Gestionnaire</h2>
                    <p class="text-sm text-zinc-900">{{ company.manager.name }}</p>
                    <p class="text-sm text-zinc-500">{{ company.manager.email }}</p>
                </section>

                <div v-if="canUpdate" class="flex justify-end">
                    <button type="submit" :disabled="form.processing" class="rounded-xl bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700 disabled:opacity-50">
                        {{ form.processing ? 'Enregistrement…' : 'Enregistrer' }}
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
