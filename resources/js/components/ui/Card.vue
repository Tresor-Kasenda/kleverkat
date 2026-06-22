<script setup>
import { cn } from '@/lib/utils';

const props = defineProps({ class: { type: String, default: '' } });
</script>

<template>
    <div :class="cn('rounded-2xl bg-white shadow-md', props.class)" v-bind="$attrs">
        <slot />
    </div>
</template>
