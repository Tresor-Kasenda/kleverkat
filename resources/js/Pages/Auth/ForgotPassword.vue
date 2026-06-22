<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import Input from '@/components/ui/Input.vue';
import Label from '@/components/ui/Label.vue';
import Button from '@/components/ui/Button.vue';
import Alert from '@/components/ui/Alert.vue';

defineProps({ status: { type: String, default: null } });

const form = useForm({ email: '' });

function submit() {
    form.post(route('password.email'));
}
</script>

<template>
    <Head title="Mot de passe oublié" />
    <AuthLayout title="Mot de passe oublié" description="Saisissez votre e-mail pour recevoir un lien de réinitialisation">
        <Alert v-if="status" variant="success" class="mb-4">{{ status }}</Alert>

        <form @submit.prevent="submit" class="space-y-4">
            <div class="space-y-1.5">
                <Label for="email">Adresse e-mail</Label>
                <Input id="email" v-model="form.email" type="email" autocomplete="email" placeholder="vous@exemple.fr" required :class="{ 'border-red-500': form.errors.email }" />
                <p v-if="form.errors.email" class="text-xs text-red-600">{{ form.errors.email }}</p>
            </div>

            <Button type="submit" class="w-full" :disabled="form.processing">
                {{ form.processing ? 'Envoi…' : 'Envoyer le lien' }}
            </Button>
        </form>

        <p class="mt-4 text-center text-sm">
            <Link :href="route('login')" class="text-blue-600 hover:underline">← Retour à la connexion</Link>
        </p>
    </AuthLayout>
</template>
