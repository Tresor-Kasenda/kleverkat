<script setup>
import { computed } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import Button from '@/components/ui/Button.vue';
import Alert from '@/components/ui/Alert.vue';

const page = usePage();
const status = computed(() => page.props.flash?.status ?? null);

const form = useForm({});

function submit() {
    form.post(route('verification.send'));
}
</script>

<template>
    <Head title="Vérification e-mail" />
    <AuthLayout title="Vérifiez votre e-mail" description="Un lien de confirmation vous a été envoyé">
        <Alert v-if="status === 'verification-link-sent'" variant="success" class="mb-4">
            Un nouveau lien de vérification a été envoyé à votre adresse e-mail.
        </Alert>

        <p class="mb-6 text-sm text-zinc-600 text-center">
            Avant de continuer, merci de vérifier votre adresse e-mail en cliquant sur le lien que nous venons de vous envoyer.
        </p>

        <form @submit.prevent="submit">
            <Button type="submit" class="w-full" :disabled="form.processing">
                {{ form.processing ? 'Envoi…' : 'Renvoyer l\'e-mail' }}
            </Button>
        </form>

        <p class="mt-4 text-center text-sm">
            <Link :href="route('logout')" method="post" as="button" class="text-zinc-500 hover:text-zinc-700 underline">
                Se déconnecter
            </Link>
        </p>
    </AuthLayout>
</template>
