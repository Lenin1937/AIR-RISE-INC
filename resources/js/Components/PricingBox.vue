<template>
    <div class="relative group">
        <!-- Popular badge -->
        <div v-if="package.popular" class="absolute -top-5 left-1/2 transform -translate-x-1/2 z-10">
            <span class="bg-gradient-to-r from-yellow-400 to-yellow-500 text-white px-6 py-2 rounded-full text-sm font-bold shadow-lg">
                MOST POPULAR
            </span>
        </div>

        <!-- Main card -->
        <div 
            class="relative h-full rounded-2xl transition-all duration-500 flex flex-col"
            :class="package.popular 
                ? 'bg-gradient-to-br from-yellow-400/10 to-yellow-600/10 border-2 border-yellow-400 scale-105' 
                : 'bg-gradient-to-br from-gray-800/40 to-gray-900/40 border border-gray-700/50 hover:border-yellow-400/50 hover:scale-105'"
        >
            <!-- Glow effect on hover -->
            <div class="absolute inset-0 bg-gradient-to-br from-yellow-400/0 to-yellow-600/0 rounded-2xl opacity-0 group-hover:opacity-10 transition-opacity duration-500"></div>
            
            <div class="relative p-8 flex flex-col flex-1">
                <!-- Package name -->
                <div class="text-center mb-6">
                    <h3 class="text-2xl font-bold text-white mb-2">
                        {{ package.name }}
                    </h3>
                    <p class="text-gray-400 text-sm">
                        {{ package.description }}
                    </p>
                </div>

                <!-- Price -->
                <div class="text-center mb-8">
                    <div class="flex items-center justify-center">
                        <span class="text-5xl font-bold text-white">{{ package.price }}</span>
                    </div>
                    <p class="text-gray-400 mt-2 text-sm">One-time payment</p>
                </div>

                <!-- Features list -->
                <div class="space-y-4 mb-8 flex-grow">
                    <div 
                        v-for="(feature, index) in package.features" 
                        :key="index"
                        class="flex items-start gap-3 group/item"
                    >
                        <!-- Checkmark icon -->
                        <div class="flex-shrink-0 w-6 h-6 rounded-full bg-yellow-400/20 flex items-center justify-center mt-0.5 group-hover/item:bg-yellow-400/30 transition-colors duration-300">
                            <svg class="w-4 h-4 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <!-- Feature text -->
                        <span class="text-gray-300 text-sm leading-relaxed group-hover/item:text-white transition-colors duration-300">
                            {{ feature }}
                        </span>
                    </div>
                </div>

                <!-- CTA Button -->
                <button
                    @click="$emit('select', package)"
                    class="w-full py-4 rounded-lg font-semibold text-lg transition-all duration-300 transform hover:scale-105 mt-auto"
                    :class="package.popular
                        ? 'bg-gradient-to-r from-yellow-400 to-yellow-500 text-white shadow-lg'
                        : 'bg-white/10 text-white border-2 border-yellow-400/50 hover:bg-yellow-400 hover:border-yellow-400'"
                >
                    Get Started
                </button>

                <!-- Money-back guarantee badge -->
                <div class="mt-6 text-center">
                    <div class="inline-flex items-center gap-2 text-gray-400 text-xs">
                        <svg class="w-4 h-4 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        <span>100% Satisfaction Guaranteed</span>
                    </div>
                </div>
            </div>

            <!-- Bottom accent line -->
            <div 
                class="absolute bottom-0 left-0 right-0 h-1 rounded-b-2xl transition-all duration-500"
                :class="package.popular 
                    ? 'bg-gradient-to-r from-yellow-400 via-yellow-500 to-yellow-600' 
                    : 'bg-gradient-to-r from-yellow-400 via-yellow-500 to-yellow-600 opacity-0 group-hover:opacity-100'"
            ></div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    package: {
        type: Object,
        required: true
    }
});

defineEmits(['select']);
</script>
