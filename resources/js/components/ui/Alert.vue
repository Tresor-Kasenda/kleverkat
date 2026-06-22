<script setup>
import { cn } from '@/lib/utils';

const props = defineProps({
    variant: { type: String, default: 'default' },
    class: { type: String, default: '' },
});

const classes = {
    default: 'bg-blue-50 border-blue-200 text-blue-800',
    error: 'bg-red-50 border-red-200 text-red-800',
    success: 'bg-emerald-50 border-emerald-200 text-emerald-800',
    warning: 'bg-amber-50 border-amber-200 text-amber-800',
};
</script>

<template>
    <div
        :class="cn('rounded-lg border px-4 py-3 text-sm', classes[variant], props.class)"
        v-bind="$attrs"
    >
        <slot />
    </div>
</template>
