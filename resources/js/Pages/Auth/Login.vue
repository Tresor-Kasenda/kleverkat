<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import Input from '@/components/ui/Input.vue';
import Label from '@/components/ui/Label.vue';
import Button from '@/components/ui/Button.vue';
import Alert from '@/components/ui/Alert.vue';

defineProps({
    status: { type: String, default: null },
    canResetPassword: { type: Boolean, default: true },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

function submit() {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
}
</script>

<template>
    <Head title="Connexion" />
    <AuthLayout title="Connexion" description="Accédez à votre espace personnel">
        <Alert v-if="status" variant="success" class="mb-4">{{ status }}</Alert>

        <form @submit.prevent="submit" class="space-y-4">
            <div class="space-y-1.5">
                <Label for="email">Adresse e-mail</Label>
                <Input
                    id="email"
                    v-model="form.email"
                    type="email"
                    autocomplete="email"
                    placeholder="vous@exemple.fr"
                    :class="{ 'border-red-500': form.errors.email }"
                    required
                />
                <p v-if="form.errors.email" class="text-xs text-red-600">{{ form.errors.email }}</p>
            </div>

            <div class="space-y-1.5">
                <div class="flex items-center justify-between">
                    <Label for="password">Mot de passe</Label>
                    <Link v-if="canResetPassword" :href="route('password.request')" class="text-xs text-blue-600 hover:underline">
                        Mot de passe oublié ?
                    </Link>
                </div>
                <Input
                    id="password"
                    v-model="form.password"
                    type="password"
                    autocomplete="current-password"
                    placeholder="••••••••"
                    :class="{ 'border-red-500': form.errors.password }"
                    required
                />
                <p v-if="form.errors.password" class="text-xs text-red-600">{{ form.errors.password }}</p>
            </div>

            <div class="flex items-center gap-2">
                <input id="remember" v-model="form.remember" type="checkbox" class="h-4 w-4 rounded border-zinc-300 text-blue-600 focus:ring-blue-500" />
                <Label for="remember" class="font-normal">Se souvenir de moi</Label>
            </div>

            <Button type="submit" class="w-full" :disabled="form.processing">
                {{ form.processing ? 'Connexion…' : 'Se connecter' }}
            </Button>
        </form>

        <p class="mt-6 text-center text-sm text-zinc-500">
            Pas encore de compte ?
            <Link :href="route('register')" class="font-medium text-blue-600 hover:underline">Créer un compte</Link>
        </p>
    </AuthLayout>
</template>
