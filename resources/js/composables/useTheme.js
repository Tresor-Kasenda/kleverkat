import { computed, watch } from 'vue';
import { useStorage, usePreferredDark } from '@vueuse/core';

const appearance = useStorage('appearance', 'system');
const preferredDark = usePreferredDark();

const isDark = computed(() => {
    if (appearance.value === 'dark') return true;
    if (appearance.value === 'light') return false;

    return preferredDark.value;
});

watch(isDark, (val) => {
    document.documentElement.classList.toggle('dark', val);
}, { immediate: true });

export function useTheme() {
    function setAppearance(val) {
        appearance.value = val;
    }

    function toggle() {
        appearance.value = isDark.value ? 'light' : 'dark';
    }

    return {
        appearance,
        isDark,
        setAppearance,
        toggle,
    };
}
