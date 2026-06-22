<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import Input from '@/components/ui/Input.vue';
import Label from '@/components/ui/Label.vue';
import Button from '@/components/ui/Button.vue';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

function submit() {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
}
</script>

<template>
    <Head title="Créer un compte" />
    <AuthLayout title="Créer un compte" description="Rejoignez KleverKat gratuitement">
        <form @submit.prevent="submit" class="space-y-4">
            <div class="space-y-1.5">
                <Label for="name">Nom complet</Label>
                <Input id="name" v-model="form.name" type="text" autocomplete="name" placeholder="Jean Dupont" required :class="{ 'border-red-500': form.errors.name }" />
                <p v-if="form.errors.name" class="text-xs text-red-600">{{ form.errors.name }}</p>
            </div>

            <div class="space-y-1.5">
                <Label for="email">Adresse e-mail</Label>
                <Input id="email" v-model="form.email" type="email" autocomplete="email" placeholder="vous@exemple.fr" required :class="{ 'border-red-500': form.errors.email }" />
                <p v-if="form.errors.email" class="text-xs text-red-600">{{ form.errors.email }}</p>
            </div>

            <div class="space-y-1.5">
                <Label for="password">Mot de passe</Label>
                <Input id="password" v-model="form.password" type="password" autocomplete="new-password" placeholder="Minimum 8 caractères" required :class="{ 'border-red-500': form.errors.password }" />
                <p v-if="form.errors.password" class="text-xs text-red-600">{{ form.errors.password }}</p>
            </div>

            <div class="space-y-1.5">
                <Label for="password_confirmation">Confirmer le mot de passe</Label>
                <Input id="password_confirmation" v-model="form.password_confirmation" type="password" autocomplete="new-password" placeholder="••••••••" required />
            </div>

            <Button type="submit" class="w-full" :disabled="form.processing">
                {{ form.processing ? 'Création…' : 'Créer mon compte' }}
            </Button>
        </form>

        <p class="mt-6 text-center text-sm text-zinc-500">
            Déjà inscrit ?
            <Link :href="route('login')" class="font-medium text-blue-600 hover:underline">Se connecter</Link>
        </p>
    </AuthLayout>
</template>
