<script setup>
import { cn } from '@/lib/utils';

const props = defineProps({
    class: { type: String, default: '' },
    modelValue: { type: [String, Number], default: '' },
});

const emit = defineEmits(['update:modelValue']);
</script>

<template>
    <input
        :value="modelValue"
        :class="cn(
            'flex h-9 w-full rounded-lg border border-zinc-300 bg-white px-3 py-1 text-sm shadow-sm transition-colors placeholder:text-zinc-400 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 disabled:cursor-not-allowed disabled:opacity-50',
            props.class
        )"
        v-bind="$attrs"
        @input="emit('update:modelValue', $event.target.value)"
    />
</template>
