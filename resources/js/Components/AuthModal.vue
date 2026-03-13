<script setup>
import InputError from '@/Components/InputError.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    show: { type: Boolean, default: false },
});

const emit = defineEmits(['close']);

const showPassword = ref(false);

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Teleport to="body">
        <Transition name="auth-fade">
            <div v-if="show" class="fixed inset-0 z-[9999] flex items-center justify-center p-4">
                <!-- Backdrop -->
                <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" @click="emit('close')" />

                <!-- Modal panel -->
                <div class="relative z-10 w-full max-w-md bg-[#0d2040] border border-white/10 rounded-2xl shadow-2xl shadow-black/60 p-8">

                    <!-- Close -->
                    <button
                        type="button"
                        @click="emit('close')"
                        class="absolute top-4 right-4 p-1.5 rounded-lg text-gray-400 hover:text-white hover:bg-white/10 transition-colors"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>

                    <!-- Logo + heading -->
                    <div class="flex flex-col items-center mb-8">
                        <img src="/logo.png" alt="CORPIUS" class="h-12 w-auto mb-4">
                        <h2 class="text-2xl font-extrabold text-white">Welcome back</h2>
                        <p class="mt-1 text-sm text-gray-400 text-center">
                            Sign in to your <span class="text-[#d4a02f] font-medium">CORPIUS</span> account to continue
                        </p>
                    </div>

                    <!-- Login form -->
                    <form @submit.prevent="submit" class="space-y-5">

                        <!-- Email -->
                        <div>
                            <label class="block text-[11px] font-bold uppercase tracking-widest text-gray-400 mb-2">EMAIL</label>
                            <input
                                type="email" v-model="form.email"
                                required autofocus autocomplete="username"
                                placeholder="you@example.com"
                                class="w-full h-12 rounded-xl border border-white/10 bg-white/5 px-4 text-sm text-white placeholder-gray-500 outline-none transition-all focus:border-[#d4a02f]/50 focus:bg-white/8 focus:ring-2 focus:ring-[#d4a02f]/15"
                            />
                            <InputError class="mt-1.5" :message="form.errors.email" />
                        </div>

                        <!-- Password -->
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-[11px] font-bold uppercase tracking-widest text-gray-400">PASSWORD</label>
                                <Link
                                    :href="route('password.request')"
                                    class="text-xs text-[#d4a02f] hover:text-[#e6b84a] font-medium transition-colors"
                                >Forgot Password?</Link>
                            </div>
                            <div class="relative">
                                <input
                                    :type="showPassword ? 'text' : 'password'"
                                    v-model="form.password"
                                    required autocomplete="current-password"
                                    placeholder="••••••••"
                                    class="w-full h-12 rounded-xl border border-white/10 bg-white/5 px-4 pr-12 text-sm text-white placeholder-gray-500 outline-none transition-all focus:border-[#d4a02f]/50 focus:ring-2 focus:ring-[#d4a02f]/15"
                                />
                                <button
                                    type="button"
                                    @click="showPassword = !showPassword"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-white transition-colors"
                                >
                                    <svg v-if="showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/>
                                    </svg>
                                    <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </button>
                            </div>
                            <InputError class="mt-1.5" :message="form.errors.password" />
                        </div>

                        <!-- Sign In button -->
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="w-full h-12 rounded-xl bg-gradient-to-r from-yellow-400 to-yellow-500 text-gray-900 font-bold text-sm hover:from-yellow-500 hover:to-yellow-600 transition-all duration-300 disabled:opacity-60 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                        >
                            <svg v-if="form.processing" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 12 6.477 12 12h-4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                            </svg>
                            {{ form.processing ? 'Signing in…' : 'Sign In' }}
                        </button>
                    </form>

                    <!-- Register link at the bottom -->
                    <div class="mt-6 pt-6 border-t border-white/10 text-center">
                        <p class="text-sm text-gray-400">
                            Don't have an account?
                            <Link
                                :href="route('register')"
                                class="ml-1 text-[#d4a02f] hover:text-[#e6b84a] font-semibold transition-colors"
                            >Register new account</Link>
                        </p>
                    </div>

                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.auth-fade-enter-active,
.auth-fade-leave-active {
    transition: opacity 0.2s ease;
}
.auth-fade-enter-from,
.auth-fade-leave-to {
    opacity: 0;
}
</style>
