<script setup>
import AuthModal from '@/Components/AuthModal.vue';
import { useTranslations } from '@/Composables/useTranslations';
import MarketingLayout from '@/Layouts/MarketingLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

const { __ } = useTranslations();

// FAQ accordion state
const openFaqIndex = ref(null);

const showAuthModal = ref(false);
const page = usePage();
const openAuth = () => {
    if (page.props.auth?.user) {
        router.visit(route('orders.create'));
    } else {
        showAuthModal.value = true;
    }
};

const toggleFaq = (index) => {
    openFaqIndex.value = openFaqIndex.value === index ? null : index;
};

// Icons for benefits (kept for rendering logic)
const benefitIcons = {
    protection: 'shield-check',
    tax: 'calculator',
    management: 'document-text',
    credibility: 'star',
    transfer: 'users',
    banking: 'credit-card'
};
</script>

<template>
    <Head title="LLC Formation Services | Register Your LLC | CORPIUS">
        <meta head-key="description" name="description" content="Form your LLC quickly with CORPIUS. Get limited liability protection, pass-through taxation, and flexible management. Free registered agent included." />
        <link head-key="canonical" rel="canonical" href="https://corpius.net/services/llc" />
        <meta head-key="og:title" property="og:title" content="LLC Formation Services | Register Your LLC | CORPIUS" />
        <meta head-key="og:description" property="og:description" content="Form your LLC quickly with CORPIUS. Get limited liability protection, pass-through taxation, and flexible management. Free registered agent included." />
        <meta head-key="og:url" property="og:url" content="https://corpius.net/services/llc" />
        <meta head-key="twitter:title" name="twitter:title" content="LLC Formation Services | Register Your LLC | CORPIUS" />
        <meta head-key="twitter:description" name="twitter:description" content="Form your LLC with CORPIUS. Limited liability protection, pass-through taxation, flexible management. Free registered agent included." />
        <meta head-key="og:type" property="og:type" content="website" />
        <meta head-key="og:image" property="og:image" content="https://corpius.net/images/og-preview.jpg" />
        <meta head-key="og:image:width" property="og:image:width" content="1200" />
        <meta head-key="og:image:height" property="og:image:height" content="630" />
        <meta head-key="og:site_name" property="og:site_name" content="CORPIUS" />
        <meta head-key="twitter:card" name="twitter:card" content="summary_large_image" />
        <meta head-key="twitter:image" name="twitter:image" content="https://corpius.net/images/og-preview.jpg" />
        <script head-key="schema-service" type="application/ld+json">{"@context":"https://schema.org","@graph":[{"@type":"Service","@id":"https://corpius.net/services/llc#service","name":"LLC Formation Service","description":"Form your Limited Liability Company (LLC) online with full liability protection, pass-through taxation, and flexible management. Trusted by thousands of U.S. entrepreneurs.","provider":{"@type":"Organization","name":"CORPIUS","url":"https://corpius.net"},"areaServed":"US","url":"https://corpius.net/services/llc","offers":{"@type":"Offer","priceCurrency":"USD","price":"1480","availability":"https://schema.org/InStock"}},{"@type":"FAQPage","mainEntity":[{"@type":"Question","name":"What is an LLC?","acceptedAnswer":{"@type":"Answer","text":"A Limited Liability Company (LLC) is a flexible business structure that provides the liability protection of a corporation and the tax efficiency of a sole proprietorship or partnership. LLCs are ideal for entrepreneurs, freelancers, and small business owners."}},{"@type":"Question","name":"How long does it take to form an LLC?","acceptedAnswer":{"@type":"Answer","text":"Most LLCs are approved within 1-3 business days using expedited filing. Standard processing may take 5-10 business days, depending on the state. CORPIUS handles filing, EIN registration, and all state correspondence."}},{"@type":"Question","name":"Can a non-US citizen form an LLC?","acceptedAnswer":{"@type":"Answer","text":"Yes. Non-U.S. citizens and non-residents can form an LLC in the United States. CORPIUS specializes in helping international entrepreneurs establish their U.S. business presence with full compliance."}}]}]}</script>
    </Head>

    <MarketingLayout>
        <!-- Hero Section -->
        <section class="bg-gradient-to-br from-navy-900 via-blue-900 to-navy-900 py-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center text-white">
                    <h1 class="text-4xl lg:text-5xl font-bold text-yellow-400 mb-6 gradient-text-shimmer">
                        Start Your LLC Online | Fast & Affordable LLC Formation Service
                    </h1>
                    <p class="text-xl lg:text-2xl text-gray-300 mb-4 max-w-4xl mx-auto">
                        {{ __('marketing.llc_hero_subtitle') }}
                    </p>
                    <p class="text-lg text-gray-400 mb-8 max-w-3xl mx-auto">
                        {{ __('marketing.llc_hero_description') }}
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <button type="button" @click="openAuth()" class="bg-yellow-400 hover:bg-yellow-500 hover:shadow-yellow-400/50 text-white px-8 py-4 rounded-lg text-lg font-semibold transition-all duration-300 shadow-lg hover:scale-105">
                            {{ __('marketing.llc_start_cta') }}
                        </button>
                        <a href="#packages" class="border-2 border-white text-white hover:bg-white hover:text-navy-900 px-8 py-4 rounded-lg text-lg font-semibold transition-all duration-300">
                            {{ __('marketing.llc_view_packages') }}
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Benefits Section -->
        <section class="py-20" style="background-color: #0b1e33;">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-3xl lg:text-4xl font-bold text-white mb-4">
                        {{ __('marketing.llc_why_choose') }}
                    </h2>
                    <p class="text-xl text-gray-300 max-w-3xl mx-auto">
                        {{ __('marketing.llc_why_subtitle') }}
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <!-- Protection -->
                    <div class="group relative bg-gradient-to-br from-gray-800/40 to-gray-900/40 backdrop-blur-sm border border-gray-700/50 rounded-2xl p-8 text-center transition-all duration-500 hover:scale-105 hover:shadow-2xl hover:shadow-yellow-400/20 hover:border-yellow-400/50 hover:-translate-y-2 cursor-pointer">
                        <div class="absolute inset-0 bg-gradient-to-br from-yellow-400/0 to-yellow-600/0 rounded-2xl opacity-0 group-hover:opacity-10 transition-opacity duration-500"></div>
                        <div class="w-20 h-20 mx-auto mb-6 flex items-center justify-center group-hover:scale-110 transition-transform duration-500">
                            <img src="/images/service-page-logo/icon%201.1.png" class="relative w-20 h-20 group-hover:scale-110 transition-transform duration-500 object-contain" alt="benefit icon" />
                        </div>
                        <h3 class="relative text-xl font-bold text-white mb-4 group-hover:text-yellow-400 transition-colors duration-300">
                            {{ __('marketing.llc_benefit_protection') }}
                        </h3>
                        <p class="relative text-gray-300 leading-relaxed group-hover:text-gray-200 transition-colors duration-300">
                            {{ __('marketing.llc_benefit_protection_desc') }}
                        </p>
                        <div class="absolute bottom-0 left-1/2 transform -translate-x-1/2 w-0 h-1 bg-gradient-to-r from-yellow-400 via-yellow-500 to-yellow-600 group-hover:w-full transition-all duration-500 rounded-b-2xl"></div>
                    </div>

                    <!-- Tax -->
                    <div class="group relative bg-gradient-to-br from-gray-800/40 to-gray-900/40 backdrop-blur-sm border border-gray-700/50 rounded-2xl p-8 text-center transition-all duration-500 hover:scale-105 hover:shadow-2xl hover:shadow-yellow-400/20 hover:border-yellow-400/50 hover:-translate-y-2 cursor-pointer">
                        <div class="absolute inset-0 bg-gradient-to-br from-yellow-400/0 to-yellow-600/0 rounded-2xl opacity-0 group-hover:opacity-10 transition-opacity duration-500"></div>
                        <div class="w-20 h-20 mx-auto mb-6 flex items-center justify-center group-hover:scale-110 transition-transform duration-500">
                            <img src="/images/service-page-logo/icon%202.1.png" class="relative w-20 h-20 group-hover:scale-110 transition-transform duration-500 object-contain" alt="benefit icon" />
                        </div>
                        <h3 class="relative text-xl font-bold text-white mb-4 group-hover:text-yellow-400 transition-colors duration-300">
                            {{ __('marketing.llc_benefit_tax') }}
                        </h3>
                        <p class="relative text-gray-300 leading-relaxed group-hover:text-gray-200 transition-colors duration-300">
                            {{ __('marketing.llc_benefit_tax_desc') }}
                        </p>
                        <div class="absolute bottom-0 left-1/2 transform -translate-x-1/2 w-0 h-1 bg-gradient-to-r from-yellow-400 via-yellow-500 to-yellow-600 group-hover:w-full transition-all duration-500 rounded-b-2xl"></div>
                    </div>

                    <!-- Management -->
                    <div class="group relative bg-gradient-to-br from-gray-800/40 to-gray-900/40 backdrop-blur-sm border border-gray-700/50 rounded-2xl p-8 text-center transition-all duration-500 hover:scale-105 hover:shadow-2xl hover:shadow-yellow-400/20 hover:border-yellow-400/50 hover:-translate-y-2 cursor-pointer">
                        <div class="absolute inset-0 bg-gradient-to-br from-yellow-400/0 to-yellow-600/0 rounded-2xl opacity-0 group-hover:opacity-10 transition-opacity duration-500"></div>
                        <div class="w-20 h-20 mx-auto mb-6 flex items-center justify-center group-hover:scale-110 transition-transform duration-500">
                            <img src="/images/service-page-logo/icon%203.1.png" class="relative w-20 h-20 group-hover:scale-110 transition-transform duration-500 object-contain" alt="benefit icon" />
                        </div>
                        <h3 class="relative text-xl font-bold text-white mb-4 group-hover:text-yellow-400 transition-colors duration-300">
                            {{ __('marketing.llc_benefit_management') }}
                        </h3>
                        <p class="relative text-gray-300 leading-relaxed group-hover:text-gray-200 transition-colors duration-300">
                            {{ __('marketing.llc_benefit_management_desc') }}
                        </p>
                        <div class="absolute bottom-0 left-1/2 transform -translate-x-1/2 w-0 h-1 bg-gradient-to-r from-yellow-400 via-yellow-500 to-yellow-600 group-hover:w-full transition-all duration-500 rounded-b-2xl"></div>
                    </div>

                    <!-- Credibility -->
                    <div class="group relative bg-gradient-to-br from-gray-800/40 to-gray-900/40 backdrop-blur-sm border border-gray-700/50 rounded-2xl p-8 text-center transition-all duration-500 hover:scale-105 hover:shadow-2xl hover:shadow-yellow-400/20 hover:border-yellow-400/50 hover:-translate-y-2 cursor-pointer">
                        <div class="absolute inset-0 bg-gradient-to-br from-yellow-400/0 to-yellow-600/0 rounded-2xl opacity-0 group-hover:opacity-10 transition-opacity duration-500"></div>
                        <div class="w-20 h-20 mx-auto mb-6 flex items-center justify-center group-hover:scale-110 transition-transform duration-500">
                            <img src="/images/service-page-logo/icon%204.1.png" class="relative w-20 h-20 group-hover:scale-110 transition-transform duration-500 object-contain" alt="benefit icon" />
                        </div>
                        <h3 class="relative text-xl font-bold text-white mb-4 group-hover:text-yellow-400 transition-colors duration-300">
                            {{ __('marketing.llc_benefit_credibility') }}
                        </h3>
                        <p class="relative text-gray-300 leading-relaxed group-hover:text-gray-200 transition-colors duration-300">
                            {{ __('marketing.llc_benefit_credibility_desc') }}
                        </p>
                        <div class="absolute bottom-0 left-1/2 transform -translate-x-1/2 w-0 h-1 bg-gradient-to-r from-yellow-400 via-yellow-500 to-yellow-600 group-hover:w-full transition-all duration-500 rounded-b-2xl"></div>
                    </div>

                    <!-- Transfer -->
                    <div class="group relative bg-gradient-to-br from-gray-800/40 to-gray-900/40 backdrop-blur-sm border border-gray-700/50 rounded-2xl p-8 text-center transition-all duration-500 hover:scale-105 hover:shadow-2xl hover:shadow-yellow-400/20 hover:border-yellow-400/50 hover:-translate-y-2 cursor-pointer">
                        <div class="absolute inset-0 bg-gradient-to-br from-yellow-400/0 to-yellow-600/0 rounded-2xl opacity-0 group-hover:opacity-10 transition-opacity duration-500"></div>
                        <div class="w-20 h-20 mx-auto mb-6 flex items-center justify-center group-hover:scale-110 transition-transform duration-500">
                            <img src="/images/service-page-logo/icon%205.1.1.png" class="relative w-20 h-20 group-hover:scale-110 transition-transform duration-500 object-contain" alt="benefit icon" />
                        </div>
                        <h3 class="relative text-xl font-bold text-white mb-4 group-hover:text-yellow-400 transition-colors duration-300">
                            {{ __('marketing.llc_benefit_transfer') }}
                        </h3>
                        <p class="relative text-gray-300 leading-relaxed group-hover:text-gray-200 transition-colors duration-300">
                            {{ __('marketing.llc_benefit_transfer_desc') }}
                        </p>
                        <div class="absolute bottom-0 left-1/2 transform -translate-x-1/2 w-0 h-1 bg-gradient-to-r from-yellow-400 via-yellow-500 to-yellow-600 group-hover:w-full transition-all duration-500 rounded-b-2xl"></div>
                    </div>

                    <!-- Banking -->
                    <div class="group relative bg-gradient-to-br from-gray-800/40 to-gray-900/40 backdrop-blur-sm border border-gray-700/50 rounded-2xl p-8 text-center transition-all duration-500 hover:scale-105 hover:shadow-2xl hover:shadow-yellow-400/20 hover:border-yellow-400/50 hover:-translate-y-2 cursor-pointer">
                        <div class="absolute inset-0 bg-gradient-to-br from-yellow-400/0 to-yellow-600/0 rounded-2xl opacity-0 group-hover:opacity-10 transition-opacity duration-500"></div>
                        <div class="w-20 h-20 mx-auto mb-6 flex items-center justify-center group-hover:scale-110 transition-transform duration-500">
                            <img src="/images/service-page-logo/icon%206.1.png" class="relative w-20 h-20 group-hover:scale-110 transition-transform duration-500 object-contain" alt="benefit icon" />
                        </div>
                        <h3 class="relative text-xl font-bold text-white mb-4 group-hover:text-yellow-400 transition-colors duration-300">
                            {{ __('marketing.llc_benefit_banking') }}
                        </h3>
                        <p class="relative text-gray-300 leading-relaxed group-hover:text-gray-200 transition-colors duration-300">
                            {{ __('marketing.llc_benefit_banking_desc') }}
                        </p>
                        <div class="absolute bottom-0 left-1/2 transform -translate-x-1/2 w-0 h-1 bg-gradient-to-r from-yellow-400 via-yellow-500 to-yellow-600 group-hover:w-full transition-all duration-500 rounded-b-2xl"></div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Packages Section -->
        <section id="packages" class="py-20 relative overflow-hidden" style="background: linear-gradient(135deg, #0a1628 0%, #0b1e33 50%, #0d2540 100%);">
            <!-- Background decoration -->
            <div class="absolute inset-0 opacity-5">
                <div class="absolute top-20 left-10 w-72 h-72 bg-yellow-400 rounded-full filter blur-3xl"></div>
                <div class="absolute bottom-20 right-10 w-96 h-96 bg-blue-500 rounded-full filter blur-3xl"></div>
            </div>
            
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="text-center mb-16">
                    <div class="inline-block mb-4">
                        <span class="bg-gradient-to-r from-yellow-400 to-yellow-500 text-gray-900 px-4 py-2 rounded-full text-sm font-bold uppercase tracking-wide">{{ __('marketing.llc_packages_title') }}</span>
                    </div>
                    <h2 class="text-3xl lg:text-5xl font-bold text-white mb-6">
                        {{ __('marketing.llc_packages_title') }}
                    </h2>
                    <p class="text-xl text-gray-300 max-w-3xl mx-auto leading-relaxed">
                        {{ __('marketing.llc_packages_subtitle') }}
                    </p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 max-w-7xl mx-auto mb-16">
                    <!-- Basic Package -->
                    <div class="group relative h-full transition-all duration-500 hover:-translate-y-2">
                        <div class="relative h-full bg-gradient-to-br from-gray-800/60 to-gray-900/60 border-2 border-gray-700/50 group-hover:border-yellow-400/70 rounded-3xl p-7 transition-all duration-500 shadow-xl min-h-[750px] flex flex-col" style="backdrop-filter: blur(20px);">
                            <div class="text-center mb-10">
                                <h3 class="text-3xl font-bold text-white mb-6 group-hover:text-yellow-400 transition-colors duration-300">{{ __('marketing.llc_package_basic') }}</h3>
                                <div class="flex items-baseline justify-center mb-4">
                                    <span class="text-4xl font-bold text-yellow-400">$</span>
                                    <span class="text-6xl font-bold bg-gradient-to-r from-yellow-400 to-yellow-500 bg-clip-text text-transparent">{{ __('marketing.llc_package_basic_price') }}</span>
                                </div>
                                <p class="text-base text-gray-300 group-hover:text-gray-200 transition-colors duration-300 min-h-[3rem]">{{ __('marketing.llc_package_basic_desc') }}</p>
                            </div>
                            <div class="w-full h-px bg-gradient-to-r from-transparent via-gray-700 to-transparent mb-8"></div>
                            <ul class="space-y-5 mb-8 flex-grow">
                                <li class="flex items-start group/item">
                                    <div class="relative flex-shrink-0 mt-1">
                                        <div class="absolute inset-0 bg-green-400 rounded-full blur opacity-0 group-hover/item:opacity-60 transition-opacity duration-300"></div>
                                        <svg class="relative w-6 h-6 text-green-400 mr-4 group-hover/item:scale-110 transition-transform duration-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                    </div>
                                    <span class="text-gray-200 text-base group-hover/item:text-white transition-colors duration-300 leading-relaxed">{{ __('marketing.llc_package_basic_f1') }}</span>
                                </li>
                                <li class="flex items-start group/item">
                                    <div class="relative flex-shrink-0 mt-1">
                                        <div class="absolute inset-0 bg-green-400 rounded-full blur opacity-0 group-hover/item:opacity-60 transition-opacity duration-300"></div>
                                        <svg class="relative w-6 h-6 text-green-400 mr-4 group-hover/item:scale-110 transition-transform duration-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                    </div>
                                    <span class="text-gray-200 text-base group-hover/item:text-white transition-colors duration-300 leading-relaxed">{{ __('marketing.llc_package_basic_f2') }}</span>
                                </li>
                                <li class="flex items-start group/item">
                                    <div class="relative flex-shrink-0 mt-1">
                                        <div class="absolute inset-0 bg-green-400 rounded-full blur opacity-0 group-hover/item:opacity-60 transition-opacity duration-300"></div>
                                        <svg class="relative w-6 h-6 text-green-400 mr-4 group-hover/item:scale-110 transition-transform duration-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                    </div>
                                    <span class="text-gray-200 text-base group-hover/item:text-white transition-colors duration-300 leading-relaxed">{{ __('marketing.llc_package_basic_f3') }}</span>
                                </li>
                                <li class="flex items-start group/item">
                                    <div class="relative flex-shrink-0 mt-1">
                                        <div class="absolute inset-0 bg-green-400 rounded-full blur opacity-0 group-hover/item:opacity-60 transition-opacity duration-300"></div>
                                        <svg class="relative w-6 h-6 text-green-400 mr-4 group-hover/item:scale-110 transition-transform duration-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                    </div>
                                    <span class="text-gray-200 text-base group-hover/item:text-white transition-colors duration-300 leading-relaxed">{{ __('marketing.llc_package_basic_f4') }}</span>
                                </li>
                                <li class="flex items-start group/item">
                                    <div class="relative flex-shrink-0 mt-1">
                                        <div class="absolute inset-0 bg-green-400 rounded-full blur opacity-0 group-hover/item:opacity-60 transition-opacity duration-300"></div>
                                        <svg class="relative w-6 h-6 text-green-400 mr-4 group-hover/item:scale-110 transition-transform duration-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                    </div>
                                    <span class="text-gray-200 text-base group-hover/item:text-white transition-colors duration-300 leading-relaxed">{{ __('marketing.llc_package_basic_f5') }}</span>
                                </li>
                                <li class="flex items-start group/item">
                                    <div class="relative flex-shrink-0 mt-1">
                                        <div class="absolute inset-0 bg-green-400 rounded-full blur opacity-0 group-hover/item:opacity-60 transition-opacity duration-300"></div>
                                        <svg class="relative w-6 h-6 text-green-400 mr-4 group-hover/item:scale-110 transition-transform duration-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                    </div>
                                    <span class="text-gray-200 text-base group-hover/item:text-white transition-colors duration-300 leading-relaxed">{{ __('marketing.llc_package_basic_f6') }}</span>
                                </li>
                                <li class="flex items-start opacity-0 pointer-events-none">
                                    <div class="relative flex-shrink-0 mt-1">
                                        <svg class="relative w-6 h-6 mr-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                    </div>
                                    <span class="text-base leading-relaxed">&nbsp;</span>
                                </li>
                            </ul>
                            <button type="button" @click="openAuth()" class="relative w-full mt-auto py-4 px-6 rounded-xl font-bold text-base text-center block overflow-hidden group/btn bg-gradient-to-r from-yellow-400 to-yellow-500 text-gray-900 hover:shadow-lg transition-all duration-300 transform hover:scale-[1.02]"><span class="relative z-10 flex items-center justify-center gap-2">{{ __('marketing.llc_package_basic_button') }}<svg class="w-5 h-5 group-hover/btn:translate-x-1 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg></span><div class="absolute inset-0 bg-gradient-to-r from-yellow-500 to-yellow-600 opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300"></div></button>
                        </div>
                    </div>

                    <!-- Professional Package -->
                    <div class="group relative h-full transition-all duration-500 hover:-translate-y-2">
                        <div class="relative h-full bg-gradient-to-br from-yellow-400/15 to-yellow-600/15 border-2 border-yellow-400/60 rounded-3xl p-7 shadow-xl transition-all duration-500 min-h-[750px] flex flex-col" style="backdrop-filter: blur(20px);">
                            <div class="absolute -top-5 left-1/2 transform -translate-x-1/2 z-10">
                                <div class="relative">
                                    <div class="absolute inset-0 bg-yellow-400 blur-xl opacity-70 animate-pulse"></div>
                                    <span class="relative bg-gradient-to-r from-yellow-400 to-yellow-500 text-gray-900 px-6 py-2.5 rounded-full text-sm font-bold shadow-2xl flex items-center gap-2 whitespace-nowrap">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                        {{ __('marketing.llc_most_popular') }}
                                    </span>
                                </div>
                            </div>
                            <div class="text-center mb-10 pt-4">
                                <h3 class="text-3xl font-bold text-white mb-6 group-hover:text-yellow-400 transition-colors duration-300">{{ __('marketing.llc_package_professional') }}</h3>
                                <div class="flex items-baseline justify-center mb-4">
                                    <span class="text-4xl font-bold text-yellow-400">$</span>
                                    <span class="text-6xl font-bold bg-gradient-to-r from-yellow-400 to-yellow-500 bg-clip-text text-transparent">{{ __('marketing.llc_package_professional_price') }}</span>
                                </div>
                                <p class="text-base text-gray-300 group-hover:text-gray-200 transition-colors duration-300 min-h-[3rem]">{{ __('marketing.llc_package_professional_desc') }}</p>
                            </div>
                            <div class="w-full h-px bg-gradient-to-r from-transparent via-yellow-400/50 to-transparent mb-8"></div>
                            <ul class="space-y-5 mb-8 flex-grow">
                                <li class="flex items-start group/item">
                                    <div class="relative flex-shrink-0 mt-1">
                                        <div class="absolute inset-0 bg-yellow-400 rounded-full blur opacity-0 group-hover/item:opacity-70 transition-opacity duration-300"></div>
                                        <svg class="relative w-6 h-6 text-yellow-400 mr-4 group-hover/item:scale-110 transition-transform duration-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                    </div>
                                    <span class="text-gray-100 text-base font-medium group-hover/item:text-white transition-colors duration-300 leading-relaxed">{{ __('marketing.llc_package_professional_f1') }}</span>
                                </li>
                                <li class="flex items-start group/item">
                                    <div class="relative flex-shrink-0 mt-1">
                                        <div class="absolute inset-0 bg-yellow-400 rounded-full blur opacity-0 group-hover/item:opacity-70 transition-opacity duration-300"></div>
                                        <svg class="relative w-6 h-6 text-yellow-400 mr-4 group-hover/item:scale-110 transition-transform duration-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                    </div>
                                    <span class="text-gray-100 text-base font-medium group-hover/item:text-white transition-colors duration-300 leading-relaxed">{{ __('marketing.llc_package_professional_f2') }}</span>
                                </li>
                                <li class="flex items-start group/item">
                                    <div class="relative flex-shrink-0 mt-1">
                                        <div class="absolute inset-0 bg-yellow-400 rounded-full blur opacity-0 group-hover/item:opacity-70 transition-opacity duration-300"></div>
                                        <svg class="relative w-6 h-6 text-yellow-400 mr-4 group-hover/item:scale-110 transition-transform duration-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                    </div>
                                    <span class="text-gray-100 text-base font-medium group-hover/item:text-white transition-colors duration-300 leading-relaxed">{{ __('marketing.llc_package_professional_f3') }}</span>
                                </li>
                                <li class="flex items-start group/item">
                                    <div class="relative flex-shrink-0 mt-1">
                                        <div class="absolute inset-0 bg-yellow-400 rounded-full blur opacity-0 group-hover/item:opacity-70 transition-opacity duration-300"></div>
                                        <svg class="relative w-6 h-6 text-yellow-400 mr-4 group-hover/item:scale-110 transition-transform duration-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                    </div>
                                    <span class="text-gray-100 text-base font-medium group-hover/item:text-white transition-colors duration-300 leading-relaxed">{{ __('marketing.llc_package_professional_f4') }}</span>
                                </li>
                                <li class="flex items-start group/item">
                                    <div class="relative flex-shrink-0 mt-1">
                                        <div class="absolute inset-0 bg-yellow-400 rounded-full blur opacity-0 group-hover/item:opacity-70 transition-opacity duration-300"></div>
                                        <svg class="relative w-6 h-6 text-yellow-400 mr-4 group-hover/item:scale-110 transition-transform duration-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                    </div>
                                    <span class="text-gray-100 text-base font-medium group-hover/item:text-white transition-colors duration-300 leading-relaxed">{{ __('marketing.llc_package_professional_f5') }}</span>
                                </li>
                                <li class="flex items-start group/item">
                                    <div class="relative flex-shrink-0 mt-1">
                                        <div class="absolute inset-0 bg-yellow-400 rounded-full blur opacity-0 group-hover/item:opacity-70 transition-opacity duration-300"></div>
                                        <svg class="relative w-6 h-6 text-yellow-400 mr-4 group-hover/item:scale-110 transition-transform duration-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                    </div>
                                    <span class="text-gray-100 text-base font-medium group-hover/item:text-white transition-colors duration-300 leading-relaxed">{{ __('marketing.llc_package_professional_f6') }}</span>
                                </li>
                                <li class="flex items-start group/item">
                                    <div class="relative flex-shrink-0 mt-1">
                                        <div class="absolute inset-0 bg-yellow-400 rounded-full blur opacity-0 group-hover/item:opacity-70 transition-opacity duration-300"></div>
                                        <svg class="relative w-6 h-6 text-yellow-400 mr-4 group-hover/item:scale-110 transition-transform duration-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                    </div>
                                    <span class="text-gray-100 text-base font-medium group-hover/item:text-white transition-colors duration-300 leading-relaxed">{{ __('marketing.llc_package_professional_f7') }}</span>
                                </li>
                            </ul>
                            <button type="button" @click="openAuth()" class="relative w-full mt-auto py-4 px-6 rounded-xl font-bold text-base text-center block overflow-hidden group/btn bg-gradient-to-r from-yellow-400 to-yellow-500 text-gray-900 shadow-lg shadow-yellow-400/30 hover:shadow-yellow-400/50 transition-all duration-300 transform hover:scale-[1.02]"><span class="relative z-10 flex items-center justify-center gap-2">{{ __('marketing.llc_package_professional_button') }}<svg class="w-5 h-5 group-hover/btn:translate-x-1 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg></span><div class="absolute inset-0 bg-gradient-to-r from-yellow-500 to-yellow-600 opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300"></div></button>
                        </div>
                    </div>

                    <!-- Premium Package -->
                    <div class="group relative h-full transition-all duration-500 hover:-translate-y-2">
                        <div class="relative h-full bg-gradient-to-br from-gray-800/60 to-gray-900/60 border-2 border-gray-700/50 group-hover:border-yellow-400/70 rounded-3xl p-7 transition-all duration-500 shadow-xl min-h-[750px] flex flex-col" style="backdrop-filter: blur(20px);">
                            <div class="text-center mb-10">
                                <h3 class="text-3xl font-bold text-white mb-6 group-hover:text-yellow-400 transition-colors duration-300">{{ __('marketing.llc_package_premium') }}</h3>
                                <div class="flex items-baseline justify-center mb-4">
                                    <span class="text-4xl font-bold text-yellow-400">$</span>
                                    <span class="text-6xl font-bold bg-gradient-to-r from-yellow-400 to-yellow-500 bg-clip-text text-transparent">{{ __('marketing.llc_package_premium_price') }}</span>
                                </div>
                                <p class="text-base text-gray-300 group-hover:text-gray-200 transition-colors duration-300 min-h-[3rem]">{{ __('marketing.llc_package_premium_desc') }}</p>
                            </div>
                            <div class="w-full h-px bg-gradient-to-r from-transparent via-gray-700 to-transparent mb-8"></div>
                            <ul class="space-y-5 mb-8 flex-grow">
                                <li class="flex items-start group/item">
                                    <div class="relative flex-shrink-0 mt-1">
                                        <div class="absolute inset-0 bg-green-400 rounded-full blur opacity-0 group-hover/item:opacity-60 transition-opacity duration-300"></div>
                                        <svg class="relative w-6 h-6 text-green-400 mr-4 group-hover/item:scale-110 transition-transform duration-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                    </div>
                                    <span class="text-gray-200 text-base group-hover/item:text-white transition-colors duration-300 leading-relaxed">{{ __('marketing.llc_package_premium_f1') }}</span>
                                </li>
                                <li class="flex items-start group/item">
                                    <div class="relative flex-shrink-0 mt-1">
                                        <div class="absolute inset-0 bg-green-400 rounded-full blur opacity-0 group-hover/item:opacity-60 transition-opacity duration-300"></div>
                                        <svg class="relative w-6 h-6 text-green-400 mr-4 group-hover/item:scale-110 transition-transform duration-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                    </div>
                                    <span class="text-gray-200 text-base group-hover/item:text-white transition-colors duration-300 leading-relaxed">{{ __('marketing.llc_package_premium_f2') }}</span>
                                </li>
                                <li class="flex items-start group/item">
                                    <div class="relative flex-shrink-0 mt-1">
                                        <div class="absolute inset-0 bg-green-400 rounded-full blur opacity-0 group-hover/item:opacity-60 transition-opacity duration-300"></div>
                                        <svg class="relative w-6 h-6 text-green-400 mr-4 group-hover/item:scale-110 transition-transform duration-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                    </div>
                                    <span class="text-gray-200 text-base group-hover/item:text-white transition-colors duration-300 leading-relaxed">{{ __('marketing.llc_package_premium_f3') }}</span>
                                </li>
                                <li class="flex items-start group/item">
                                    <div class="relative flex-shrink-0 mt-1">
                                        <div class="absolute inset-0 bg-green-400 rounded-full blur opacity-0 group-hover/item:opacity-60 transition-opacity duration-300"></div>
                                        <svg class="relative w-6 h-6 text-green-400 mr-4 group-hover/item:scale-110 transition-transform duration-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                    </div>
                                    <span class="text-gray-200 text-base group-hover/item:text-white transition-colors duration-300 leading-relaxed">{{ __('marketing.llc_package_premium_f4') }}</span>
                                </li>
                                <li class="flex items-start group/item">
                                    <div class="relative flex-shrink-0 mt-1">
                                        <div class="absolute inset-0 bg-green-400 rounded-full blur opacity-0 group-hover/item:opacity-60 transition-opacity duration-300"></div>
                                        <svg class="relative w-6 h-6 text-green-400 mr-4 group-hover/item:scale-110 transition-transform duration-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                    </div>
                                    <span class="text-gray-200 text-base group-hover/item:text-white transition-colors duration-300 leading-relaxed">{{ __('marketing.llc_package_premium_f5') }}</span>
                                </li>
                                <li class="flex items-start group/item">
                                    <div class="relative flex-shrink-0 mt-1">
                                        <div class="absolute inset-0 bg-green-400 rounded-full blur opacity-0 group-hover/item:opacity-60 transition-opacity duration-300"></div>
                                        <svg class="relative w-6 h-6 text-green-400 mr-4 group-hover/item:scale-110 transition-transform duration-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                    </div>
                                    <span class="text-gray-200 text-base group-hover/item:text-white transition-colors duration-300 leading-relaxed">{{ __('marketing.llc_package_premium_f6') }}</span>
                                </li>
                                <li class="flex items-start group/item">
                                    <div class="relative flex-shrink-0 mt-1">
                                        <div class="absolute inset-0 bg-green-400 rounded-full blur opacity-0 group-hover/item:opacity-60 transition-opacity duration-300"></div>
                                        <svg class="relative w-6 h-6 text-green-400 mr-4 group-hover/item:scale-110 transition-transform duration-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                    </div>
                                    <span class="text-gray-200 text-base group-hover/item:text-white transition-colors duration-300 leading-relaxed">{{ __('marketing.llc_package_premium_f7') }}</span>
                                </li>
                            </ul>
                            <button type="button" @click="openAuth()" class="relative w-full mt-auto py-4 px-6 rounded-xl font-bold text-base text-center block overflow-hidden group/btn bg-gradient-to-r from-yellow-400 to-yellow-500 text-gray-900 hover:shadow-lg transition-all duration-300 transform hover:scale-[1.02]"><span class="relative z-10 flex items-center justify-center gap-2">{{ __('marketing.llc_package_premium_button') }}<svg class="w-5 h-5 group-hover/btn:translate-x-1 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg></span><div class="absolute inset-0 bg-gradient-to-r from-yellow-500 to-yellow-600 opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300"></div></button>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-12">
                    <p class="text-gray-300 mb-4">{{ __('marketing.llc_all_packages_note') }}</p>
                    <div class="flex justify-center space-x-8">
                        <div class="flex items-center">
                            <svg class="h-6 w-6 text-green-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-gray-200">{{ __('marketing.llc_money_back') }}</span>
                        </div>
                        <div class="flex items-center">
                            <svg class="h-6 w-6 text-green-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-gray-200">{{ __('marketing.llc_secure_processing') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- FAQ Section -->
        <section class="py-20" style="background-color: #0b1e33;">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <h2 class="text-3xl font-bold text-white mb-4">
                        {{ __('marketing.llc_faq_title') }}
                    </h2>
                    <p class="text-gray-300">
                        {{ __('marketing.llc_faq_subtitle') }}
                    </p>
                </div>

                <div class="space-y-4">
                    <!-- FAQ 1 -->
                    <div 
                        class="bg-gray-800/40 backdrop-blur-sm border border-gray-700/50 rounded-lg overflow-hidden transition-all duration-300 hover:border-yellow-400/50"
                    >
                        <button 
                            @click="toggleFaq(0)"
                            class="w-full flex items-center justify-between p-6 text-left transition-colors duration-300 hover:bg-gray-800/60"
                        >
                            <h3 class="text-lg font-semibold text-white pr-8">{{ __('marketing.llc_faq_q1') }}</h3>
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
                                    <p class="text-gray-300 leading-relaxed">{{ __('marketing.llc_faq_a1') }}</p>
                                </div>
                            </div>
                        </transition>
                    </div>

                    <!-- FAQ 2 -->
                    <div 
                        class="bg-gray-800/40 backdrop-blur-sm border border-gray-700/50 rounded-lg overflow-hidden transition-all duration-300 hover:border-yellow-400/50"
                    >
                        <button 
                            @click="toggleFaq(1)"
                            class="w-full flex items-center justify-between p-6 text-left transition-colors duration-300 hover:bg-gray-800/60"
                        >
                            <h3 class="text-lg font-semibold text-white pr-8">{{ __('marketing.llc_faq_q2') }}</h3>
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
                                    <p class="text-gray-300 leading-relaxed">{{ __('marketing.llc_faq_a2') }}</p>
                                </div>
                            </div>
                        </transition>
                    </div>

                    <!-- FAQ 3 -->
                    <div 
                        class="bg-gray-800/40 backdrop-blur-sm border border-gray-700/50 rounded-lg overflow-hidden transition-all duration-300 hover:border-yellow-400/50"
                    >
                        <button 
                            @click="toggleFaq(2)"
                            class="w-full flex items-center justify-between p-6 text-left transition-colors duration-300 hover:bg-gray-800/60"
                        >
                            <h3 class="text-lg font-semibold text-white pr-8">{{ __('marketing.llc_faq_q3') }}</h3>
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
                                    <p class="text-gray-300 leading-relaxed">{{ __('marketing.llc_faq_a3') }}</p>
                                </div>
                            </div>
                        </transition>
                    </div>

                    <!-- FAQ 4 -->
                    <div 
                        class="bg-gray-800/40 backdrop-blur-sm border border-gray-700/50 rounded-lg overflow-hidden transition-all duration-300 hover:border-yellow-400/50"
                    >
                        <button 
                            @click="toggleFaq(3)"
                            class="w-full flex items-center justify-between p-6 text-left transition-colors duration-300 hover:bg-gray-800/60"
                        >
                            <h3 class="text-lg font-semibold text-white pr-8">{{ __('marketing.llc_faq_q4') }}</h3>
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
                                    <p class="text-gray-300 leading-relaxed">{{ __('marketing.llc_faq_a4') }}</p>
                                </div>
                            </div>
                        </transition>
                    </div>

                    <!-- FAQ 5 -->
                    <div 
                        class="bg-gray-800/40 backdrop-blur-sm border border-gray-700/50 rounded-lg overflow-hidden transition-all duration-300 hover:border-yellow-400/50"
                    >
                        <button 
                            @click="toggleFaq(4)"
                            class="w-full flex items-center justify-between p-6 text-left transition-colors duration-300 hover:bg-gray-800/60"
                        >
                            <h3 class="text-lg font-semibold text-white pr-8">{{ __('marketing.llc_faq_q5') }}</h3>
                            <svg 
                                class="w-6 h-6 text-yellow-400 flex-shrink-0 transition-transform duration-300"
                                :class="{ 'rotate-180': openFaqIndex === 4 }"
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
                            <div v-show="openFaqIndex === 4" class="overflow-hidden">
                                <div class="px-6 pb-6">
                                    <p class="text-gray-300 leading-relaxed">{{ __('marketing.llc_faq_a5') }}</p>
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
                    {{ __('marketing.llc_cta_title') }}
                </h2>
                <p class="text-xl text-gray-300 mb-8">
                    {{ __('marketing.llc_cta_subtitle') }}
                </p>
                <button type="button" @click="openAuth()" class="bg-yellow-400 hover:bg-yellow-500 text-white px-8 py-4 rounded-lg text-lg font-semibold transition-all duration-300 shadow-lg hover:shadow-xl inline-block">
                    {{ __('marketing.llc_cta_button') }}
                </button>
            </div>
        </section>

        <AuthModal :show="showAuthModal" @close="showAuthModal = false" />
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