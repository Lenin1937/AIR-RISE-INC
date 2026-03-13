<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center bg-[#0b1e33] pt-6 sm:justify-center sm:pt-0"
    >
        <div class="mb-8">
            <Link href="/" class="flex items-center justify-center">
                <img src="/logo.png" alt="CORPIUS Logo" class="h-16 w-auto">
                <span class="ml-3 text-2xl font-bold text-white">CORPIUS</span>
            </Link>
        </div>

        <div
            class="mt-6 w-full overflow-hidden bg-[#0b1e33] border-2 border-[#d4a02f] px-8 py-8 shadow-xl sm:max-w-md sm:rounded-lg"
        >
            <slot />
        </div>
    </div>
</template>
