<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import Input from '@/components/ui/Input.vue';
import Label from '@/components/ui/Label.vue';
import Button from '@/components/ui/Button.vue';

const form = useForm({ password: '' });

function submit() {
    form.post(route('password.confirm'), {
        onFinish: () => form.reset('password'),
    });
}
</script>

<template>
    <Head title="Confirmer le mot de passe" />
    <AuthLayout title="Confirmation requise" description="Cette zone est sécurisée. Confirmez votre mot de passe pour continuer.">
        <form @submit.prevent="submit" class="space-y-4">
            <div class="space-y-1.5">
                <Label for="password">Mot de passe</Label>
                <Input id="password" v-model="form.password" type="password" autocomplete="current-password" placeholder="••••••••" required :class="{ 'border-red-500': form.errors.password }" />
                <p v-if="form.errors.password" class="text-xs text-red-600">{{ form.errors.password }}</p>
            </div>

            <Button type="submit" class="w-full" :disabled="form.processing">
                {{ form.processing ? 'Vérification…' : 'Confirmer' }}
            </Button>
        </form>
    </AuthLayout>
</template>
