<script setup>
defineProps({
    type: {
        type: String,
        default: 'button',
    },
});
</script>

<template>
    <button
        :type="type"
        class="inline-flex items-center rounded-xl border border-white/[0.10] bg-white/[0.04] px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-300 transition duration-150 ease-in-out hover:bg-white/[0.08] focus:outline-none focus:ring-2 focus:ring-amber-400/30 disabled:opacity-25"
    >
        <slot />
    </button>
</template>
