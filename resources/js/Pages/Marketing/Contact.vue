<script setup>
import { useTranslations } from '@/Composables/useTranslations';
import MarketingLayout from '@/Layouts/MarketingLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';

const { __ } = useTranslations();

const form = reactive({
    name: '',
    email: '',
    phone: '',
    subject: '',
    message: '',
    business_type: 'LLC',
    processing: false
});

const submitForm = () => {
    form.processing = true;
    // Form submission logic would go here
    setTimeout(() => {
        form.processing = false;
        alert(__('marketing.message_sent_success'));
    }, 1000);
};

// FAQ accordion state
const openFaqIndex = ref(null);

const toggleFaq = (index) => {
    openFaqIndex.value = openFaqIndex.value === index ? null : index;
};
</script>

<template>
    <Head title="Contact CORPIUS | Business Formation Support">
        <meta head-key="description" name="description" content="Get in touch with CORPIUS for business formation questions. Expert advice on LLC, C-Corp, S-Corp, Nonprofit, income tax, and Green Card services." />
        <link head-key="canonical" rel="canonical" href="https://corpius.net/contact" />
        <meta head-key="og:title" property="og:title" content="Contact CORPIUS | Business Formation Support" />
        <meta head-key="og:description" property="og:description" content="Get in touch with CORPIUS for business formation questions. Expert advice on LLC, C-Corp, S-Corp, Nonprofit, income tax, and Green Card services." />
        <meta head-key="og:url" property="og:url" content="https://corpius.net/contact" />
        <meta head-key="twitter:title" name="twitter:title" content="Contact CORPIUS | Business Formation Support" />
        <meta head-key="twitter:description" name="twitter:description" content="Get in touch with CORPIUS for expert business formation advice on LLC, corporation, nonprofit, tax filing, and immigration services." />
    </Head>

    <MarketingLayout>
        <!-- Hero Section -->
        <section class="bg-gradient-to-br from-navy-900 via-blue-900 to-navy-900 py-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h1 class="text-4xl lg:text-5xl font-bold mb-6 gradient-text-shimmer">
                    Contact Us
                </h1>
                <p class="text-xl lg:text-2xl text-gray-300 mb-8 max-w-3xl mx-auto">
                    {{ __('marketing.contact_hero_subtitle') }}
                </p>
            </div>
        </section>

        <!-- Contact Options & Form -->
        <section class="py-20" style="background-color: #0b1e33;">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
                    <!-- Contact Information -->
                    <div class="lg:col-span-1">
                        <h2 class="text-2xl font-bold text-white mb-8">{{ __('marketing.get_in_touch') }}</h2>
                        
                        <div class="space-y-6">
                            <div class="flex items-start space-x-4">
                                <div class="w-10 h-10 bg-yellow-400 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-white mb-2">{{ __('marketing.email') }}</h3>
                                    <p class="text-yellow-400 font-medium">{{ __('marketing.email_primary') }}</p>
                                    <p class="text-yellow-400 font-medium">{{ __('marketing.email_secondary') }}</p>
                                    <p class="text-sm text-gray-400 mt-2">{{ __('marketing.email_response') }}</p>
                                </div>
                            </div>

                            <div class="flex items-start space-x-4">
                                <div class="w-10 h-10 bg-yellow-400 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-white mb-2">{{ __('marketing.office_hours') }}</h3>
                                    <p class="text-gray-300">{{ __('marketing.office_hours_weekday') }}</p>
                                    <p class="text-gray-300">{{ __('marketing.office_hours_saturday') }}</p>
                                    <p class="text-gray-300">{{ __('marketing.office_hours_sunday') }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Links -->
                        <div class="mt-12">
                            <h3 class="text-lg font-semibold text-white mb-4">{{ __('marketing.quick_links') }}</h3>
                            <div class="space-y-2">
                                <Link :href="route('marketing.pricing')" class="block text-yellow-400 hover:text-yellow-300 transition-colors">
                                    {{ __('marketing.view_pricing') }}
                                </Link>
                                <Link :href="route('services.llc')" class="block text-yellow-400 hover:text-yellow-300 transition-colors">
                                    {{ __('marketing.llc') }}
                                </Link>
                                <Link :href="route('register')" class="block text-yellow-400 hover:text-yellow-300 transition-colors">
                                    {{ __('marketing.get_started') }}
                                </Link>
                                <Link :href="route('marketing.contact')" class="block text-yellow-400 hover:text-yellow-300 transition-colors">
                                    {{ __('marketing.talk_to_an_expert') }}
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Form -->
                    <div class="lg:col-span-2">
                        <div class="border border-gray-600 rounded-xl shadow-lg p-8" style="background-color: rgba(255, 255, 255, 0.05); backdrop-filter: blur(10px);">
                            <h2 class="text-2xl font-bold text-white mb-6">{{ __('marketing.send_message') }}</h2>
                            
                            <form @submit.prevent="submitForm" class="space-y-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="name" class="block text-sm font-medium text-gray-200 mb-2">
                                            {{ __('marketing.full_name') }} *
                                        </label>
                                        <input 
                                            v-model="form.name"
                                            type="text" 
                                            id="name" 
                                            required
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                            :placeholder="__('marketing.your_full_name')"
                                        />
                                    </div>

                                    <div>
                                        <label for="email" class="block text-sm font-medium text-gray-200 mb-2">
                                            {{ __('marketing.email_address') }} *
                                        </label>
                                        <input 
                                            v-model="form.email"
                                            type="email" 
                                            id="email" 
                                            required
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                            :placeholder="__('marketing.your_email')"
                                        />
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="phone" class="block text-sm font-medium text-gray-200 mb-2">
                                            {{ __('marketing.phone_number') }}
                                        </label>
                                        <input 
                                            v-model="form.phone"
                                            type="tel" 
                                            id="phone"
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                            :placeholder="__('marketing.your_phone')"
                                        />
                                    </div>

                                    <div>
                                        <label for="business_type" class="block text-sm font-medium text-gray-200 mb-2">
                                            {{ __('marketing.business_type') }}
                                        </label>
                                        <select 
                                            v-model="form.business_type"
                                            id="business_type"
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        >
                                            <option value="LLC">{{ __('marketing.llc') }}</option>
                                            <option value="C-Corp">{{ __('marketing.c_corporation') }}</option>
                                            <option value="S-Corp">{{ __('marketing.s_corporation') }}</option>
                                            <option value="Nonprofit">{{ __('marketing.nonprofit') }}</option>
                                            <option value="Green Card Lottery">{{ __('marketing.green_card') }}</option>
                                            <option value="Other">{{ __('marketing.other') }}</option>
                                        </select>
                                    </div>
                                </div>

                                <div>
                                    <label for="subject" class="block text-sm font-medium text-gray-200 mb-2">
                                        {{ __('marketing.subject') }} *
                                    </label>
                                    <input 
                                        v-model="form.subject"
                                        type="text" 
                                        id="subject" 
                                        required
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        :placeholder="__('marketing.subject_placeholder')"
                                    />
                                </div>

                                <div>
                                    <label for="message" class="block text-sm font-medium text-gray-200 mb-2">
                                        {{ __('marketing.message') }} *
                                    </label>
                                    <textarea 
                                        v-model="form.message"
                                        id="message" 
                                        rows="6" 
                                        required
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        :placeholder="__('marketing.message_placeholder')"
                                    ></textarea>
                                </div>

                                <button 
                                    type="submit" 
                                    :disabled="form.processing"
                                    class="w-full bg-yellow-400 hover:bg-yellow-500 text-black py-3 px-6 rounded-lg font-semibold transition-colors duration-300 disabled:opacity-50"
                                >
                                    <span v-if="form.processing">{{ __('marketing.sending') }}</span>
                                    <span v-else>{{ __('marketing.send_message') }}</span>
                                </button>

                                <!-- Contact Form Disclaimer -->
                                <div class="mt-6 p-4 bg-blue-50 border-l-4 border-blue-400 rounded">
                                    <div class="flex items-start">
                                        <svg class="h-5 w-5 text-blue-600 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <p class="text-sm text-blue-800 leading-relaxed">
                                            <strong>Important Notice:</strong> CORPIUS provides business formation and document preparation services but cannot provide legal advice. Communications with CORPIUS do not create an attorney-client relationship. For legal advice specific to your situation, consult a licensed attorney.
                                        </p>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- FAQ Section -->
        <section class="py-20" style="background-color: #0b1e33;">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <h2 class="text-3xl font-bold text-white mb-4">{{ __('marketing.faq') }}</h2>
                    <p class="text-gray-300">{{ __('marketing.faq_subtitle') }}</p>
                </div>

                <div class="space-y-4">
                    <!-- FAQ Item 1 -->
                    <div class="bg-gray-800/40 backdrop-blur-sm border border-gray-700/50 rounded-lg overflow-hidden transition-all duration-300 hover:border-yellow-400/50">
                        <button 
                            @click="toggleFaq(0)"
                            class="w-full flex items-center justify-between p-6 text-left transition-colors duration-300 hover:bg-gray-800/60"
                        >
                            <h3 class="text-lg font-semibold text-white pr-8">{{ __('marketing.faq_q1') }}</h3>
                            <svg 
                                class="w-6 h-6 text-yellow-400 flex-shrink-0 transition-transform duration-300"
                                :class="{ 'rotate-180': openFaqIndex === 0 }"
                                fill="none" 
                                stroke="currentColor" 
                                viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <transition
                            enter-active-class="transition-all duration-300 ease-out"
                            enter-from-class="max-h-0 opacity-0"
                            enter-to-class="max-h-96 opacity-100"
                            leave-active-class="transition-all duration-300 ease-in"
                            leave-from-class="max-h-96 opacity-100"
                            leave-to-class="max-h-0 opacity-0"
                        >
                            <div v-show="openFaqIndex === 0" class="overflow-hidden">
                                <div class="px-6 pb-6">
                                    <p class="text-gray-300 leading-relaxed">
                                        {{ __('marketing.faq_a1') }}
                                    </p>
                                </div>
                            </div>
                        </transition>
                    </div>

                    <!-- FAQ Item 2 -->
                    <div class="bg-gray-800/40 backdrop-blur-sm border border-gray-700/50 rounded-lg overflow-hidden transition-all duration-300 hover:border-yellow-400/50">
                        <button 
                            @click="toggleFaq(1)"
                            class="w-full flex items-center justify-between p-6 text-left transition-colors duration-300 hover:bg-gray-800/60"
                        >
                            <h3 class="text-lg font-semibold text-white pr-8">{{ __('marketing.faq_q2') }}</h3>
                            <svg 
                                class="w-6 h-6 text-yellow-400 flex-shrink-0 transition-transform duration-300"
                                :class="{ 'rotate-180': openFaqIndex === 1 }"
                                fill="none" 
                                stroke="currentColor" 
                                viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <transition
                            enter-active-class="transition-all duration-300 ease-out"
                            enter-from-class="max-h-0 opacity-0"
                            enter-to-class="max-h-96 opacity-100"
                            leave-active-class="transition-all duration-300 ease-in"
                            leave-from-class="max-h-96 opacity-100"
                            leave-to-class="max-h-0 opacity-0"
                        >
                            <div v-show="openFaqIndex === 1" class="overflow-hidden">
                                <div class="px-6 pb-6">
                                    <p class="text-gray-300 leading-relaxed">
                                        {{ __('marketing.faq_a2') }}
                                    </p>
                                </div>
                            </div>
                        </transition>
                    </div>

                    <!-- FAQ Item 3 -->
                    <div class="bg-gray-800/40 backdrop-blur-sm border border-gray-700/50 rounded-lg overflow-hidden transition-all duration-300 hover:border-yellow-400/50">
                        <button 
                            @click="toggleFaq(2)"
                            class="w-full flex items-center justify-between p-6 text-left transition-colors duration-300 hover:bg-gray-800/60"
                        >
                            <h3 class="text-lg font-semibold text-white pr-8">{{ __('marketing.faq_q3') }}</h3>
                            <svg 
                                class="w-6 h-6 text-yellow-400 flex-shrink-0 transition-transform duration-300"
                                :class="{ 'rotate-180': openFaqIndex === 2 }"
                                fill="none" 
                                stroke="currentColor" 
                                viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <transition
                            enter-active-class="transition-all duration-300 ease-out"
                            enter-from-class="max-h-0 opacity-0"
                            enter-to-class="max-h-96 opacity-100"
                            leave-active-class="transition-all duration-300 ease-in"
                            leave-from-class="max-h-96 opacity-100"
                            leave-to-class="max-h-0 opacity-0"
                        >
                            <div v-show="openFaqIndex === 2" class="overflow-hidden">
                                <div class="px-6 pb-6">
                                    <p class="text-gray-300 leading-relaxed">
                                        {{ __('marketing.faq_a3') }}
                                    </p>
                                </div>
                            </div>
                        </transition>
                    </div>

                    <!-- FAQ Item 4 -->
                    <div class="bg-gray-800/40 backdrop-blur-sm border border-gray-700/50 rounded-lg overflow-hidden transition-all duration-300 hover:border-yellow-400/50">
                        <button 
                            @click="toggleFaq(3)"
                            class="w-full flex items-center justify-between p-6 text-left transition-colors duration-300 hover:bg-gray-800/60"
                        >
                            <h3 class="text-lg font-semibold text-white pr-8">{{ __('marketing.faq_q4') }}</h3>
                            <svg 
                                class="w-6 h-6 text-yellow-400 flex-shrink-0 transition-transform duration-300"
                                :class="{ 'rotate-180': openFaqIndex === 3 }"
                                fill="none" 
                                stroke="currentColor" 
                                viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <transition
                            enter-active-class="transition-all duration-300 ease-out"
                            enter-from-class="max-h-0 opacity-0"
                            enter-to-class="max-h-96 opacity-100"
                            leave-active-class="transition-all duration-300 ease-in"
                            leave-from-class="max-h-96 opacity-100"
                            leave-to-class="max-h-0 opacity-0"
                        >
                            <div v-show="openFaqIndex === 3" class="overflow-hidden">
                                <div class="px-6 pb-6">
                                    <p class="text-gray-300 leading-relaxed">
                                        {{ __('marketing.faq_a4') }}
                                    </p>
                                </div>
                            </div>
                        </transition>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="py-20 bg-gradient-to-r from-navy-900 to-blue-900">
            <div class="max-w-4xl mx-auto text-center px-4 sm:px-6 lg:px-8">
                <h2 class="text-3xl lg:text-4xl font-bold text-white mb-6">
                    {{ __('marketing.ready_to_start') }}
                </h2>
                <p class="text-xl text-gray-300 mb-8">
                    {{ __('marketing.contact_cta_subtitle') }}
                </p>
                <Link :href="route('register')" class="bg-yellow-400 hover:bg-yellow-500 text-white px-8 py-4 rounded-lg text-lg font-semibold transition-all duration-300 shadow-lg hover:shadow-xl inline-block">
                    {{ __('marketing.get_started_now') }}
                </Link>
            </div>
        </section>
    </MarketingLayout>
</template>

<style scoped>
@keyframes gradient-text-shimmer {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

.gradient-text-shimmer {
    background: linear-gradient(
        90deg,
        #facc15 0%,
        #ffffff 20%,
        #fde047 40%,
        #facc15 60%,
        #ffffff 80%,
        #facc15 100%
    );
    background-size: 300% auto;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    animation: gradient-text-shimmer 2s linear infinite;
}
</style>