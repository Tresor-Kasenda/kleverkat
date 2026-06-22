<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import Input from '@/components/ui/Input.vue';
import Label from '@/components/ui/Label.vue';
import Button from '@/components/ui/Button.vue';

const recovery = ref(false);

const form = useForm({
    code: '',
    recovery_code: '',
});

function submit() {
    form.post(route('two-factor.login'), {
        onFinish: () => form.reset('code', 'recovery_code'),
    });
}
</script>

<template>
    <Head title="Double authentification" />
    <AuthLayout title="Double authentification" :description="recovery ? 'Utilisez un code de récupération' : 'Saisissez votre code OTP'">
        <form @submit.prevent="submit" class="space-y-4">
            <template v-if="!recovery">
                <div class="space-y-1.5">
                    <Label for="code">Code d'authentification</Label>
                    <Input id="code" v-model="form.code" type="text" inputmode="numeric" autocomplete="one-time-code" placeholder="123456" autofocus :class="{ 'border-red-500': form.errors.code }" />
                    <p v-if="form.errors.code" class="text-xs text-red-600">{{ form.errors.code }}</p>
                </div>
            </template>
            <template v-else>
                <div class="space-y-1.5">
                    <Label for="recovery_code">Code de récupération</Label>
                    <Input id="recovery_code" v-model="form.recovery_code" type="text" autocomplete="one-time-code" :class="{ 'border-red-500': form.errors.recovery_code }" />
                    <p v-if="form.errors.recovery_code" class="text-xs text-red-600">{{ form.errors.recovery_code }}</p>
                </div>
            </template>

            <Button type="submit" class="w-full" :disabled="form.processing">
                {{ form.processing ? 'Vérification…' : 'Vérifier' }}
            </Button>
        </form>

        <p class="mt-4 text-center text-sm">
            <button @click="recovery = !recovery" class="text-blue-600 hover:underline">
                {{ recovery ? 'Utiliser un code OTP' : 'Utiliser un code de récupération' }}
            </button>
        </p>
    </AuthLayout>
</template>
