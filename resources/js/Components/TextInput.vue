<script setup>
import { onMounted, ref } from 'vue';

const model = defineModel({
    type: String,
    required: true,
});

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <input
        class="rounded-xl border border-white/[0.08] bg-[#0a1628] text-white placeholder-gray-600 shadow-none focus:border-amber-400/50 focus:ring-1 focus:ring-amber-400/20 focus:outline-none transition-colors"
        v-model="model"
        ref="input"
    />
</template>
