<script setup>
import { useTranslations } from '@/Composables/useTranslations';
import { computed, onMounted, watch } from 'vue';

const { isRTL } = useTranslations();

const direction = computed(() => isRTL() ? 'rtl' : 'ltr');

const setDirection = () => {
    document.documentElement.setAttribute('dir', direction.value);
    document.documentElement.setAttribute('lang', useTranslations().currentLocale());
};

onMounted(() => {
    setDirection();
});

watch(direction, () => {
    setDirection();
});
</script>

<template>
    <div :dir="direction">
        <slot />
    </div>
</template>
