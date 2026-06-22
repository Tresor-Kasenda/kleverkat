<script setup>
import { cn } from '@/lib/utils';
import { cva } from 'class-variance-authority';

const buttonVariants = cva(
    'inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-lg text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50',
    {
        variants: {
            variant: {
                default: 'bg-blue-600 text-white shadow hover:bg-blue-700',
                outline: 'border border-blue-600 text-blue-600 bg-transparent hover:bg-blue-50',
                ghost: 'text-zinc-700 hover:bg-zinc-100 hover:text-zinc-900',
                link: 'text-blue-600 underline-offset-4 hover:underline',
                destructive: 'bg-red-600 text-white hover:bg-red-700',
                secondary: 'bg-zinc-100 text-zinc-900 hover:bg-zinc-200',
            },
            size: {
                default: 'h-9 px-4 py-2',
                sm: 'h-8 px-3 text-xs',
                lg: 'h-11 px-8',
                icon: 'h-9 w-9',
            },
        },
        defaultVariants: {
            variant: 'default',
            size: 'default',
        },
    },
);

const props = defineProps({
    variant: { type: String, default: 'default' },
    size: { type: String, default: 'default' },
    class: { type: String, default: '' },
    as: { type: String, default: 'button' },
});
</script>

<template>
    <component
        :is="as"
        :class="cn(buttonVariants({ variant, size }), props.class)"
        v-bind="$attrs"
    >
        <slot />
    </component>
</template>
