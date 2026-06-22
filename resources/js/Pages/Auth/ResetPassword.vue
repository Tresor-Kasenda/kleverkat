<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import Input from '@/components/ui/Input.vue';
import Label from '@/components/ui/Label.vue';
import Button from '@/components/ui/Button.vue';

const props = defineProps({
    token: { type: String, required: true },
    email: { type: String, default: '' },
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

function submit() {
    form.post(route('password.update'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
}
</script>

<template>
    <Head title="Nouveau mot de passe" />
    <AuthLayout title="Nouveau mot de passe" description="Choisissez votre nouveau mot de passe">
        <form @submit.prevent="submit" class="space-y-4">
            <div class="space-y-1.5">
                <Label for="email">Adresse e-mail</Label>
                <Input id="email" v-model="form.email" type="email" autocomplete="email" required :class="{ 'border-red-500': form.errors.email }" />
                <p v-if="form.errors.email" class="text-xs text-red-600">{{ form.errors.email }}</p>
            </div>

            <div class="space-y-1.5">
                <Label for="password">Nouveau mot de passe</Label>
                <Input id="password" v-model="form.password" type="password" autocomplete="new-password" placeholder="Minimum 8 caractères" required :class="{ 'border-red-500': form.errors.password }" />
                <p v-if="form.errors.password" class="text-xs text-red-600">{{ form.errors.password }}</p>
            </div>

            <div class="space-y-1.5">
                <Label for="password_confirmation">Confirmer</Label>
                <Input id="password_confirmation" v-model="form.password_confirmation" type="password" autocomplete="new-password" placeholder="••••••••" required />
            </div>

            <Button type="submit" class="w-full" :disabled="form.processing">
                {{ form.processing ? 'Réinitialisation…' : 'Réinitialiser le mot de passe' }}
            </Button>
        </form>
    </AuthLayout>
</template>
