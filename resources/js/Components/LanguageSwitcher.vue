<template>
    <div class="relative inline-block text-left" ref="dropdown">
        <!-- Compact (icon-only) trigger for collapsed sidebar -->
        <button
            v-if="compact"
            @click="toggleDropdown"
            title="Switch Language"
            class="flex items-center justify-center w-10 h-10 rounded-lg hover:bg-white/[0.06] transition-colors"
            :class="buttonClass"
        >
            <span class="text-lg leading-none">{{ currentFlag }}</span>
        </button>

        <!-- Full trigger -->
        <button
            v-else
            @click="toggleDropdown"
            class="flex items-center space-x-2 px-3 py-2 rounded-md hover:bg-white/[0.06] transition-colors"
            :class="buttonClass"
        >
            <span class="text-base">{{ currentFlag }}</span>
            <span class="text-sm font-medium">{{ currentLanguage }}</span>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>

        <transition
            enter-active-class="transition ease-out duration-100"
            enter-from-class="transform opacity-0 scale-95"
            enter-to-class="transform opacity-100 scale-100"
            leave-active-class="transition ease-in duration-75"
            leave-from-class="transform opacity-100 scale-100"
            leave-to-class="transform opacity-0 scale-95"
        >
            <div
                v-if="isOpen"
                class="absolute w-44 rounded-xl shadow-2xl bg-[#0d1b2e] border border-white/[0.08] z-50 overflow-hidden"
                :class="compact ? 'left-full ml-2 bottom-0' : 'left-0 top-full mt-2'"
            >
                <div class="py-1" role="menu">
                    <button
                        v-for="lang in languages"
                        :key="lang.code"
                        @click="changeLanguage(lang.code)"
                        class="flex items-center w-full px-4 py-2.5 text-sm transition-colors"
                        :class="{
                            'bg-[#d4a02f]/10 text-[#f4b840] font-semibold': currentLocale === lang.code,
                            'text-gray-400 hover:text-white hover:bg-white/[0.06]': currentLocale !== lang.code,
                        }"
                        role="menuitem"
                    >
                        <span class="text-lg mr-3 leading-none">{{ lang.flag }}</span>
                        <span>{{ lang.name }}</span>
                        <svg v-if="currentLocale === lang.code" class="w-3.5 h-3.5 ml-auto text-[#d4a02f]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                    </button>
                </div>
            </div>
        </transition>
    </div>
</template>

<script setup>
import { useForm, usePage } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';

const props = defineProps({
    locale: { type: String, default: 'en' },
    buttonClass: { type: String, default: 'text-gray-700 dark:text-gray-200' },
    dropdownPosition: { type: String, default: 'origin-top-right' },
    compact: { type: Boolean, default: false },
});

const page = usePage();
const isOpen = ref(false);
const dropdown = ref(null);

const currentLocale = computed(() => page.props.locale || props.locale || 'en');

const form = useForm({ locale: currentLocale.value });

watch(currentLocale, (v) => { form.locale = v; });

const languages = [
    { code: 'en', name: 'English', flag: '🇬🇧' },
    { code: 'es', name: 'Español', flag: '🇪🇸' },
    { code: 'ru', name: 'Русский', flag: '🇷🇺' },
    { code: 'ar', name: 'العربية', flag: '🇸🇦' },
];

const currentLanguage = computed(() => languages.find(l => l.code === currentLocale.value)?.name ?? 'English');
const currentFlag = computed(() => languages.find(l => l.code === currentLocale.value)?.flag ?? '🇬🇧');

const toggleDropdown = () => { isOpen.value = !isOpen.value; };

const changeLanguage = (locale) => {
    form.locale = locale;
    isOpen.value = false;
    form.post(route('language.switch'), {
        preserveScroll: true,
        onSuccess: () => window.location.reload(),
        onError: (e) => console.error('Language switch error:', e),
    });
};

const handleClickOutside = (e) => {
    if (dropdown.value && !dropdown.value.contains(e.target)) isOpen.value = false;
};

onMounted(() => document.addEventListener('click', handleClickOutside));
onUnmounted(() => document.removeEventListener('click', handleClickOutside));
</script>
