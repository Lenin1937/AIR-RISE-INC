<script setup>
import AuthModal from '@/Components/AuthModal.vue';
import { useTranslations } from '@/Composables/useTranslations';
import MarketingLayout from '@/Layouts/MarketingLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
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
</script>

<template>
    <Head title="C-Corporation Formation | Incorporate Your C-Corp | CORPIUS">
        <meta head-key="description" name="description" content="Incorporate your C-Corporation with CORPIUS. Issue stock, raise venture capital, and scale your business with the most flexible corporate structure available." />
        <link head-key="canonical" rel="canonical" href="https://corpius.net/services/c-corporation" />
        <meta head-key="og:title" property="og:title" content="C-Corporation Formation | Incorporate Your C-Corp | CORPIUS" />
        <meta head-key="og:description" property="og:description" content="Incorporate your C-Corporation with CORPIUS. Issue stock, raise capital, and scale with the most flexible corporate structure available." />
        <meta head-key="og:url" property="og:url" content="https://corpius.net/services/c-corporation" />
        <meta head-key="twitter:title" name="twitter:title" content="C-Corporation Formation | Incorporate Your C-Corp | CORPIUS" />
        <meta head-key="twitter:description" name="twitter:description" content="Form your C-Corporation with CORPIUS. Issue stock, raise venture capital, unlimited shareholders, and the most flexible corporate structure." />
        <meta head-key="og:type" property="og:type" content="website" />
        <meta head-key="og:image" property="og:image" content="https://corpius.net/images/og-preview.jpg" />
        <meta head-key="og:image:width" property="og:image:width" content="1200" />
        <meta head-key="og:image:height" property="og:image:height" content="630" />
        <meta head-key="og:site_name" property="og:site_name" content="CORPIUS" />
        <meta head-key="twitter:card" name="twitter:card" content="summary_large_image" />
        <meta head-key="twitter:image" name="twitter:image" content="https://corpius.net/images/og-preview.jpg" />
        <script head-key="schema-service" type="application/ld+json">{"@context":"https://schema.org","@graph":[{"@type":"Service","@id":"https://corpius.net/services/c-corporation#service","name":"C-Corporation Formation Service","description":"C-Corporation formation online — the preferred structure for venture-backed startups, investor-ready businesses, and companies seeking unlimited growth. Issue stock, raise capital, and enjoy full limited liability protection.","provider":{"@type":"Organization","name":"CORPIUS","url":"https://corpius.net"},"areaServed":"US","url":"https://corpius.net/services/c-corporation","offers":{"@type":"Offer","priceCurrency":"USD","availability":"https://schema.org/InStock"}},{"@type":"FAQPage","mainEntity":[{"@type":"Question","name":"What is the difference between a C-Corp and an S-Corp?","acceptedAnswer":{"@type":"Answer","text":"A C-Corporation can have unlimited shareholders and multiple stock classes, ideal for raising capital and venture funding. An S-Corporation is limited to 100 shareholders and offers pass-through taxation. C-Corps face double taxation on distributed dividends."}},{"@type":"Question","name":"Can I convert my LLC to a C-Corporation later?","acceptedAnswer":{"@type":"Answer","text":"Yes, you can convert your LLC to a C-Corporation. This process typically involves either forming a new C-Corporation and transferring assets or electing corporate taxation for the LLC. Consult with a tax advisor to choose the best option."}},{"@type":"Question","name":"How long does C-Corporation formation take?","acceptedAnswer":{"@type":"Answer","text":"CORPIUS prepares and submits your filing within 5-10 business days. State processing and approval times vary by state — typically 1-3 weeks for standard filing, or 3-5 business days with expedited options where available. Once approved, you receive your Certificate of Incorporation and full digital corporate kit."}}]}]}</script>
    </Head>

    <MarketingLayout>
        <!-- Hero Section -->
        <section class="bg-gradient-to-br from-navy-900 via-blue-900 to-navy-900 py-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                    <div>
                        <h1 class="text-4xl lg:text-5xl font-bold text-yellow-400 mb-6 gradient-text-shimmer">
                            C-Corporation Formation — Incorporate Your C-Corp Online
                        </h1>
                        <p class="text-xl text-gray-300 mb-8">
                            {{ __('marketing.ccorp_hero_subtitle') }}
                        </p>
                        <div class="flex flex-col sm:flex-row gap-4">
                            <button type="button" @click="openAuth()" class="bg-yellow-400 hover:bg-yellow-500 text-white px-8 py-4 rounded-lg font-semibold transition-all duration-300 text-center">
                                {{ __('marketing.ccorp_form_cta') }}
                            </button>
                            <Link :href="route('marketing.pricing')" class="border-2 border-white text-white hover:bg-transparent hover:text-white px-8 py-4 rounded-lg font-semibold transition-all duration-300 text-center">
                                {{ __('marketing.ccorp_view_pricing') }}
                            </Link>
                        </div>
                    </div>
                    <div class="relative overflow-hidden rounded-2xl shadow-2xl border-2 border-yellow-400/30" style="background: #d4a02f;">
                        <!-- Decorative elements -->
                        <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -mr-16 -mt-16"></div>
                        <div class="absolute bottom-0 left-0 w-24 h-24 bg-white/10 rounded-full -ml-12 -mb-12"></div>
                        
                        <div class="relative p-8">
                            <div class="flex items-center mb-6">
                                <div class="bg-white/20 backdrop-blur-sm p-3 rounded-xl mr-4">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <h3 class="text-2xl font-bold text-white">{{ __('marketing.ccorp_why_choose') }}</h3>
                            </div>
                            
                            <ul class="space-y-4">
                                <li class="flex items-start group">
                                    <div class="flex-shrink-0 mt-1">
                                        <div class="bg-white/90 rounded-lg p-1.5 shadow-lg group-hover:scale-110 transition-transform duration-300">
                                            <svg class="w-4 h-4 text-amber-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <span class="ml-3 text-white font-medium leading-relaxed group-hover:translate-x-1 transition-transform duration-300">
                                        {{ __('marketing.ccorp_why_choose_1') }}
                                    </span>
                                </li>
                                <li class="flex items-start group">
                                    <div class="flex-shrink-0 mt-1">
                                        <div class="bg-white/90 rounded-lg p-1.5 shadow-lg group-hover:scale-110 transition-transform duration-300">
                                            <svg class="w-4 h-4 text-amber-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <span class="ml-3 text-white font-medium leading-relaxed group-hover:translate-x-1 transition-transform duration-300">
                                        {{ __('marketing.ccorp_why_choose_2') }}
                                    </span>
                                </li>
                                <li class="flex items-start group">
                                    <div class="flex-shrink-0 mt-1">
                                        <div class="bg-white/90 rounded-lg p-1.5 shadow-lg group-hover:scale-110 transition-transform duration-300">
                                            <svg class="w-4 h-4 text-amber-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <span class="ml-3 text-white font-medium leading-relaxed group-hover:translate-x-1 transition-transform duration-300">
                                        {{ __('marketing.ccorp_why_choose_3') }}
                                    </span>
                                </li>
                                <li class="flex items-start group">
                                    <div class="flex-shrink-0 mt-1">
                                        <div class="bg-white/90 rounded-lg p-1.5 shadow-lg group-hover:scale-110 transition-transform duration-300">
                                            <svg class="w-4 h-4 text-amber-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <span class="ml-3 text-white font-medium leading-relaxed group-hover:translate-x-1 transition-transform duration-300">
                                        {{ __('marketing.ccorp_why_choose_4') }}
                                    </span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Benefits Section -->
        <section class="py-20" style="background-color: #0b1e33;">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-3xl lg:text-4xl font-bold text-white mb-6">
                        {{ __('marketing.ccorp_benefits_title') }}
                    </h2>
                    <p class="text-lg text-gray-300 max-w-3xl mx-auto">
                        {{ __('marketing.ccorp_benefits_subtitle') }}
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div class="group relative bg-gradient-to-br from-gray-800/40 to-gray-900/40 backdrop-blur-sm border border-gray-700/50 rounded-2xl p-8 text-center transition-all duration-500 hover:scale-105 hover:shadow-2xl hover:shadow-yellow-400/20 hover:border-yellow-400/50 hover:-translate-y-2 cursor-pointer">
                        <div class="absolute inset-0 bg-gradient-to-br from-yellow-400/0 to-yellow-600/0 rounded-2xl opacity-0 group-hover:opacity-10 transition-opacity duration-500"></div>
                        
                        <div class="w-20 h-20 mx-auto mb-6 flex items-center justify-center group-hover:scale-110 transition-transform duration-500">
                            <img src="/images/service-page-logo/icon%201.1.png" class="relative w-20 h-20 group-hover:scale-110 transition-transform duration-500 object-contain" alt="benefit icon" />
                        </div>
                        <h3 class="relative text-xl font-bold text-white mb-4 group-hover:text-yellow-400 transition-colors duration-300">{{ __('marketing.ccorp_benefit_growth_title') }}</h3>
                        <p class="relative text-gray-300 leading-relaxed group-hover:text-gray-200 transition-colors duration-300">
                            {{ __('marketing.ccorp_benefit_growth_desc') }}
                        </p>
                        <div class="absolute bottom-0 left-1/2 transform -translate-x-1/2 w-0 h-1 bg-gradient-to-r from-yellow-400 via-yellow-500 to-yellow-600 group-hover:w-full transition-all duration-500 rounded-b-2xl"></div>
                    </div>

                    <div class="group relative bg-gradient-to-br from-gray-800/40 to-gray-900/40 backdrop-blur-sm border border-gray-700/50 rounded-2xl p-8 text-center transition-all duration-500 hover:scale-105 hover:shadow-2xl hover:shadow-yellow-400/20 hover:border-yellow-400/50 hover:-translate-y-2 cursor-pointer">
                        <div class="absolute inset-0 bg-gradient-to-br from-yellow-400/0 to-yellow-600/0 rounded-2xl opacity-0 group-hover:opacity-10 transition-opacity duration-500"></div>
                        
                        <div class="w-20 h-20 mx-auto mb-6 flex items-center justify-center group-hover:scale-110 transition-transform duration-500">
                            <img src="/images/service-page-logo/icon%202.1.png" class="relative w-20 h-20 group-hover:scale-110 transition-transform duration-500 object-contain" alt="benefit icon" />
                        </div>
                        <h3 class="relative text-xl font-bold text-white mb-4 group-hover:text-yellow-400 transition-colors duration-300">{{ __('marketing.ccorp_benefit_protection_title') }}</h3>
                        <p class="relative text-gray-300 leading-relaxed group-hover:text-gray-200 transition-colors duration-300">
                            {{ __('marketing.ccorp_benefit_protection_desc') }}
                        </p>
                        <div class="absolute bottom-0 left-1/2 transform -translate-x-1/2 w-0 h-1 bg-gradient-to-r from-yellow-400 via-yellow-500 to-yellow-600 group-hover:w-full transition-all duration-500 rounded-b-2xl"></div>
                    </div>

                    <div class="group relative bg-gradient-to-br from-gray-800/40 to-gray-900/40 backdrop-blur-sm border border-gray-700/50 rounded-2xl p-8 text-center transition-all duration-500 hover:scale-105 hover:shadow-2xl hover:shadow-yellow-400/20 hover:border-yellow-400/50 hover:-translate-y-2 cursor-pointer">
                        <div class="absolute inset-0 bg-gradient-to-br from-yellow-400/0 to-yellow-600/0 rounded-2xl opacity-0 group-hover:opacity-10 transition-opacity duration-500"></div>
                        
                        <div class="w-20 h-20 mx-auto mb-6 flex items-center justify-center group-hover:scale-110 transition-transform duration-500">
                            <img src="/images/service-page-logo/icon%203.1.png" class="relative w-20 h-20 group-hover:scale-110 transition-transform duration-500 object-contain" alt="benefit icon" />
                        </div>
                        <h3 class="relative text-xl font-bold text-white mb-4 group-hover:text-yellow-400 transition-colors duration-300">{{ __('marketing.ccorp_benefit_capital_title') }}</h3>
                        <p class="relative text-gray-300 leading-relaxed group-hover:text-gray-200 transition-colors duration-300">
                            {{ __('marketing.ccorp_benefit_capital_desc') }}
                        </p>
                        <div class="absolute bottom-0 left-1/2 transform -translate-x-1/2 w-0 h-1 bg-gradient-to-r from-yellow-400 via-yellow-500 to-yellow-600 group-hover:w-full transition-all duration-500 rounded-b-2xl"></div>
                    </div>

                    <div class="group relative bg-gradient-to-br from-gray-800/40 to-gray-900/40 backdrop-blur-sm border border-gray-700/50 rounded-2xl p-8 text-center transition-all duration-500 hover:scale-105 hover:shadow-2xl hover:shadow-yellow-400/20 hover:border-yellow-400/50 hover:-translate-y-2 cursor-pointer">
                        <div class="absolute inset-0 bg-gradient-to-br from-yellow-400/0 to-yellow-600/0 rounded-2xl opacity-0 group-hover:opacity-10 transition-opacity duration-500"></div>
                        
                        <div class="w-20 h-20 mx-auto mb-6 flex items-center justify-center group-hover:scale-110 transition-transform duration-500">
                            <img src="/images/service-page-logo/icon%204.1.png" class="relative w-20 h-20 group-hover:scale-110 transition-transform duration-500 object-contain" alt="benefit icon" />
                        </div>
                        <h3 class="relative text-xl font-bold text-white mb-4 group-hover:text-yellow-400 transition-colors duration-300">{{ __('marketing.ccorp_benefit_credibility_title') }}</h3>
                        <p class="relative text-gray-300 leading-relaxed group-hover:text-gray-200 transition-colors duration-300">
                            {{ __('marketing.ccorp_benefit_credibility_desc') }}
                        </p>
                        <div class="absolute bottom-0 left-1/2 transform -translate-x-1/2 w-0 h-1 bg-gradient-to-r from-yellow-400 via-yellow-500 to-yellow-600 group-hover:w-full transition-all duration-500 rounded-b-2xl"></div>
                    </div>

                    <div class="group relative bg-gradient-to-br from-gray-800/40 to-gray-900/40 backdrop-blur-sm border border-gray-700/50 rounded-2xl p-8 text-center transition-all duration-500 hover:scale-105 hover:shadow-2xl hover:shadow-yellow-400/20 hover:border-yellow-400/50 hover:-translate-y-2 cursor-pointer">
                        <div class="absolute inset-0 bg-gradient-to-br from-yellow-400/0 to-yellow-600/0 rounded-2xl opacity-0 group-hover:opacity-10 transition-opacity duration-500"></div>
                        
                        <div class="w-20 h-20 mx-auto mb-6 flex items-center justify-center group-hover:scale-110 transition-transform duration-500">
                            <img src="/images/service-page-logo/icon%205.1.1.png" class="relative w-20 h-20 group-hover:scale-110 transition-transform duration-500 object-contain" alt="benefit icon" />
                        </div>
                        <h3 class="relative text-xl font-bold text-white mb-4 group-hover:text-yellow-400 transition-colors duration-300">{{ __('marketing.ccorp_benefit_employees_title') }}</h3>
                        <p class="relative text-gray-300 leading-relaxed group-hover:text-gray-200 transition-colors duration-300">
                            {{ __('marketing.ccorp_benefit_employees_desc') }}
                        </p>
                        <div class="absolute bottom-0 left-1/2 transform -translate-x-1/2 w-0 h-1 bg-gradient-to-r from-yellow-400 via-yellow-500 to-yellow-600 group-hover:w-full transition-all duration-500 rounded-b-2xl"></div>
                    </div>

                    <div class="group relative bg-gradient-to-br from-gray-800/40 to-gray-900/40 backdrop-blur-sm border border-gray-700/50 rounded-2xl p-8 text-center transition-all duration-500 hover:scale-105 hover:shadow-2xl hover:shadow-yellow-400/20 hover:border-yellow-400/50 hover:-translate-y-2 cursor-pointer">
                        <div class="absolute inset-0 bg-gradient-to-br from-yellow-400/0 to-yellow-600/0 rounded-2xl opacity-0 group-hover:opacity-10 transition-opacity duration-500"></div>
                        
                        <div class="w-20 h-20 mx-auto mb-6 flex items-center justify-center group-hover:scale-110 transition-transform duration-500">
                            <img src="/images/service-page-logo/icon%206.1.png" class="relative w-20 h-20 group-hover:scale-110 transition-transform duration-500 object-contain" alt="benefit icon" />
                        </div>
                        <h3 class="relative text-xl font-bold text-white mb-4 group-hover:text-yellow-400 transition-colors duration-300">{{ __('marketing.ccorp_benefit_perpetual_title') }}</h3>
                        <p class="relative text-gray-300 leading-relaxed group-hover:text-gray-200 transition-colors duration-300">
                            {{ __('marketing.ccorp_benefit_perpetual_desc') }}
                        </p>
                        <div class="absolute bottom-0 left-1/2 transform -translate-x-1/2 w-0 h-1 bg-gradient-to-r from-yellow-400 via-yellow-500 to-yellow-600 group-hover:w-full transition-all duration-500 rounded-b-2xl"></div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Pricing Packages -->
        <section class="py-20 relative overflow-hidden" style="background: linear-gradient(135deg, #0a1628 0%, #0b1e33 50%, #0d2540 100%);">
            <!-- Background decoration -->
            <div class="absolute inset-0 opacity-5">
                <div class="absolute top-20 left-10 w-72 h-72 bg-yellow-400 rounded-full filter blur-3xl"></div>
                <div class="absolute bottom-20 right-10 w-96 h-96 bg-blue-500 rounded-full filter blur-3xl"></div>
            </div>
            
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="text-center mb-16">
                    <div class="inline-block mb-4">
                        <span class="bg-gradient-to-r from-yellow-400 to-yellow-500 text-gray-900 px-4 py-2 rounded-full text-sm font-bold uppercase tracking-wide">Pricing</span>
                    </div>
                    <h2 class="text-3xl lg:text-5xl font-bold text-white mb-6">
                        {{ __('marketing.ccorp_packages_title') }}
                    </h2>
                    <p class="text-xl text-gray-300 max-w-3xl mx-auto leading-relaxed">
                        {{ __('marketing.ccorp_packages_subtitle') }}
                    </p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 max-w-6xl mx-auto mb-16">
                    <!-- Starter Package -->
                    <div class="group relative h-full transition-all duration-500 hover:-translate-y-2">
                        <div class="relative h-full bg-gradient-to-br from-gray-800/60 to-gray-900/60 border-2 border-gray-700/50 group-hover:border-yellow-400/70 rounded-3xl p-7 transition-all duration-500 shadow-xl min-h-[680px] flex flex-col" style="backdrop-filter: blur(20px);">
                            <div class="text-center mb-10">
                                <h3 class="text-3xl font-bold text-white mb-6 group-hover:text-yellow-400 transition-colors duration-300">{{ __('marketing.ccorp_package_starter') }}</h3>
                                <div class="flex items-baseline justify-center mb-4">
                                    <span class="text-4xl font-bold text-yellow-400">$</span>
                                    <span class="text-6xl font-bold bg-gradient-to-r from-yellow-400 to-yellow-500 bg-clip-text text-transparent">{{ __('marketing.ccorp_package_starter_price') }}</span>
                                </div>
                                <p class="text-base text-gray-300 group-hover:text-gray-200 transition-colors duration-300">{{ __('marketing.ccorp_package_starter_subtitle') }}</p>
                            </div>
                            <div class="w-full h-px bg-gradient-to-r from-transparent via-gray-700 to-transparent mb-8"></div>
                            <ul class="space-y-5 mb-8 flex-grow">
                                <li class="flex items-start group/item">
                                    <div class="relative flex-shrink-0 mt-1">
                                        <div class="absolute inset-0 bg-green-400 rounded-full blur opacity-0 group-hover/item:opacity-60 transition-opacity duration-300"></div>
                                        <svg class="relative w-6 h-6 text-green-400 mr-4 group-hover/item:scale-110 transition-transform duration-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                    </div>
                                    <span class="text-gray-200 text-base group-hover/item:text-white transition-colors duration-300 leading-relaxed">{{ __('marketing.ccorp_package_starter_feature_1') }}</span>
                                </li>
                                <li class="flex items-start group/item">
                                    <div class="relative flex-shrink-0 mt-1">
                                        <div class="absolute inset-0 bg-green-400 rounded-full blur opacity-0 group-hover/item:opacity-60 transition-opacity duration-300"></div>
                                        <svg class="relative w-6 h-6 text-green-400 mr-4 group-hover/item:scale-110 transition-transform duration-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                    </div>
                                    <span class="text-gray-200 text-base group-hover/item:text-white transition-colors duration-300 leading-relaxed">{{ __('marketing.ccorp_package_starter_feature_2') }}</span>
                                </li>
                                <li class="flex items-start group/item">
                                    <div class="relative flex-shrink-0 mt-1">
                                        <div class="absolute inset-0 bg-green-400 rounded-full blur opacity-0 group-hover/item:opacity-60 transition-opacity duration-300"></div>
                                        <svg class="relative w-6 h-6 text-green-400 mr-4 group-hover/item:scale-110 transition-transform duration-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                    </div>
                                    <span class="text-gray-200 text-base group-hover/item:text-white transition-colors duration-300 leading-relaxed">{{ __('marketing.ccorp_package_starter_feature_3') }}</span>
                                </li>
                                <li class="flex items-start group/item">
                                    <div class="relative flex-shrink-0 mt-1">
                                        <div class="absolute inset-0 bg-green-400 rounded-full blur opacity-0 group-hover/item:opacity-60 transition-opacity duration-300"></div>
                                        <svg class="relative w-6 h-6 text-green-400 mr-4 group-hover/item:scale-110 transition-transform duration-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                    </div>
                                    <span class="text-gray-200 text-base group-hover/item:text-white transition-colors duration-300 leading-relaxed">{{ __('marketing.ccorp_package_starter_feature_4') }}</span>
                                </li>
                            </ul>
                            <button type="button" @click="openAuth()" class="relative w-full py-4 px-6 rounded-xl font-bold text-base text-center block overflow-hidden group/btn bg-gradient-to-r from-yellow-400 to-yellow-500 text-gray-900 hover:shadow-lg transition-all duration-300 transform hover:scale-[1.02]"><span class="relative z-10 flex items-center justify-center gap-2">{{ __('marketing.ccorp_starter_cta') }}<svg class="w-5 h-5 group-hover/btn:translate-x-1 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg></span><div class="absolute inset-0 bg-gradient-to-r from-yellow-500 to-yellow-600 opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300"></div></button>
                        </div>
                    </div>

                    <!-- Standard Package -->
                    <div class="group relative h-full transition-all duration-500 hover:-translate-y-2">
                        <div class="relative h-full bg-gradient-to-br from-gray-800/60 to-gray-900/60 border-2 border-gray-700/50 group-hover:border-yellow-400/70 rounded-3xl p-7 transition-all duration-500 shadow-xl min-h-[680px] flex flex-col" style="backdrop-filter: blur(20px);">
                            <div class="text-center mb-10">
                                <h3 class="text-3xl font-bold text-white mb-6 group-hover:text-yellow-400 transition-colors duration-300">{{ __('marketing.ccorp_package_standard') }}</h3>
                                <div class="flex items-baseline justify-center mb-4">
                                    <span class="text-4xl font-bold text-yellow-400">$</span>
                                    <span class="text-6xl font-bold bg-gradient-to-r from-yellow-400 to-yellow-500 bg-clip-text text-transparent">{{ __('marketing.ccorp_package_standard_price') }}</span>
                                </div>
                                <p class="text-base text-gray-300 group-hover:text-gray-200 transition-colors duration-300">{{ __('marketing.ccorp_package_standard_subtitle') }}</p>
                            </div>
                            <div class="w-full h-px bg-gradient-to-r from-transparent via-gray-700 to-transparent mb-8"></div>
                            <ul class="space-y-5 mb-8 flex-grow">
                                <li class="flex items-start group/item">
                                    <div class="relative flex-shrink-0 mt-1">
                                        <div class="absolute inset-0 bg-green-400 rounded-full blur opacity-0 group-hover/item:opacity-60 transition-opacity duration-300"></div>
                                        <svg class="relative w-6 h-6 text-green-400 mr-4 group-hover/item:scale-110 transition-transform duration-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                    </div>
                                    <span class="text-gray-200 text-base group-hover/item:text-white transition-colors duration-300 leading-relaxed">{{ __('marketing.ccorp_package_standard_feature_1') }}</span>
                                </li>
                                <li class="flex items-start group/item">
                                    <div class="relative flex-shrink-0 mt-1">
                                        <div class="absolute inset-0 bg-green-400 rounded-full blur opacity-0 group-hover/item:opacity-60 transition-opacity duration-300"></div>
                                        <svg class="relative w-6 h-6 text-green-400 mr-4 group-hover/item:scale-110 transition-transform duration-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                    </div>
                                    <span class="text-gray-200 text-base group-hover/item:text-white transition-colors duration-300 leading-relaxed">{{ __('marketing.ccorp_package_standard_feature_2') }}</span>
                                </li>
                                <li class="flex items-start group/item">
                                    <div class="relative flex-shrink-0 mt-1">
                                        <div class="absolute inset-0 bg-green-400 rounded-full blur opacity-0 group-hover/item:opacity-60 transition-opacity duration-300"></div>
                                        <svg class="relative w-6 h-6 text-green-400 mr-4 group-hover/item:scale-110 transition-transform duration-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                    </div>
                                    <span class="text-gray-200 text-base group-hover/item:text-white transition-colors duration-300 leading-relaxed">{{ __('marketing.ccorp_package_standard_feature_3') }}</span>
                                </li>
                                <li class="flex items-start group/item">
                                    <div class="relative flex-shrink-0 mt-1">
                                        <div class="absolute inset-0 bg-green-400 rounded-full blur opacity-0 group-hover/item:opacity-60 transition-opacity duration-300"></div>
                                        <svg class="relative w-6 h-6 text-green-400 mr-4 group-hover/item:scale-110 transition-transform duration-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                    </div>
                                    <span class="text-gray-200 text-base group-hover/item:text-white transition-colors duration-300 leading-relaxed">{{ __('marketing.ccorp_package_standard_feature_4') }}</span>
                                </li>
                                <li class="flex items-start group/item">
                                    <div class="relative flex-shrink-0 mt-1">
                                        <div class="absolute inset-0 bg-green-400 rounded-full blur opacity-0 group-hover/item:opacity-60 transition-opacity duration-300"></div>
                                        <svg class="relative w-6 h-6 text-green-400 mr-4 group-hover/item:scale-110 transition-transform duration-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                    </div>
                                    <span class="text-gray-200 text-base group-hover/item:text-white transition-colors duration-300 leading-relaxed">{{ __('marketing.ccorp_package_standard_feature_5') }}</span>
                                </li>
                            </ul>
                            <button type="button" @click="openAuth()" class="relative w-full py-4 px-6 rounded-xl font-bold text-base text-center block overflow-hidden group/btn bg-gradient-to-r from-yellow-400 to-yellow-500 text-gray-900 hover:shadow-lg transition-all duration-300 transform hover:scale-[1.02]"><span class="relative z-10 flex items-center justify-center gap-2">{{ __('marketing.ccorp_standard_cta') }}<svg class="w-5 h-5 group-hover/btn:translate-x-1 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg></span><div class="absolute inset-0 bg-gradient-to-r from-yellow-500 to-yellow-600 opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300"></div></button>
                        </div>
                    </div>

                    <!-- Premium Package -->
                    <div class="group relative h-full transition-all duration-500 hover:-translate-y-2">
                        <div class="relative h-full bg-gradient-to-br from-yellow-400/15 to-yellow-600/15 border-2 border-yellow-400/60 rounded-3xl p-7 shadow-xl transition-all duration-500 min-h-[680px] flex flex-col" style="backdrop-filter: blur(20px);">
                            <div class="absolute -top-5 left-1/2 transform -translate-x-1/2 z-10">
                                <div class="relative">
                                    <div class="absolute inset-0 bg-yellow-400 blur-xl opacity-70 animate-pulse"></div>
                                    <span class="relative bg-gradient-to-r from-yellow-400 to-yellow-500 text-gray-900 px-8 py-3 rounded-full text-base font-bold shadow-2xl flex items-center gap-2">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                        {{ __('marketing.tier_popular') }}
                                    </span>
                                </div>
                            </div>
                            <div class="text-center mb-10 pt-4">
                                <h3 class="text-3xl font-bold text-white mb-6 group-hover:text-yellow-400 transition-colors duration-300">{{ __('marketing.ccorp_package_premium') }}</h3>
                                <div class="flex items-baseline justify-center mb-4">
                                    <span class="text-4xl font-bold text-yellow-400">$</span>
                                    <span class="text-6xl font-bold bg-gradient-to-r from-yellow-400 to-yellow-500 bg-clip-text text-transparent">{{ __('marketing.ccorp_package_premium_price') }}</span>
                                </div>
                                <p class="text-base text-gray-300 group-hover:text-gray-200 transition-colors duration-300">{{ __('marketing.ccorp_package_premium_subtitle') }}</p>
                            </div>
                            <div class="w-full h-px bg-gradient-to-r from-transparent via-yellow-400/50 to-transparent mb-8"></div>
                            <ul class="space-y-5 mb-8 flex-grow">
                                <li class="flex items-start group/item">
                                    <div class="relative flex-shrink-0 mt-1">
                                        <div class="absolute inset-0 bg-yellow-400 rounded-full blur opacity-0 group-hover/item:opacity-70 transition-opacity duration-300"></div>
                                        <svg class="relative w-6 h-6 text-yellow-400 mr-4 group-hover/item:scale-110 transition-transform duration-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                    </div>
                                    <span class="text-gray-100 text-base font-medium group-hover/item:text-white transition-colors duration-300 leading-relaxed">{{ __('marketing.ccorp_package_premium_feature_1') }}</span>
                                </li>
                                <li class="flex items-start group/item">
                                    <div class="relative flex-shrink-0 mt-1">
                                        <div class="absolute inset-0 bg-yellow-400 rounded-full blur opacity-0 group-hover/item:opacity-70 transition-opacity duration-300"></div>
                                        <svg class="relative w-6 h-6 text-yellow-400 mr-4 group-hover/item:scale-110 transition-transform duration-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                    </div>
                                    <span class="text-gray-100 text-base font-medium group-hover/item:text-white transition-colors duration-300 leading-relaxed">{{ __('marketing.ccorp_package_premium_feature_2') }}</span>
                                </li>
                                <li class="flex items-start group/item">
                                    <div class="relative flex-shrink-0 mt-1">
                                        <div class="absolute inset-0 bg-yellow-400 rounded-full blur opacity-0 group-hover/item:opacity-70 transition-opacity duration-300"></div>
                                        <svg class="relative w-6 h-6 text-yellow-400 mr-4 group-hover/item:scale-110 transition-transform duration-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                    </div>
                                    <span class="text-gray-100 text-base font-medium group-hover/item:text-white transition-colors duration-300 leading-relaxed">{{ __('marketing.ccorp_package_premium_feature_3') }}</span>
                                </li>
                                <li class="flex items-start group/item">
                                    <div class="relative flex-shrink-0 mt-1">
                                        <div class="absolute inset-0 bg-yellow-400 rounded-full blur opacity-0 group-hover/item:opacity-70 transition-opacity duration-300"></div>
                                        <svg class="relative w-6 h-6 text-yellow-400 mr-4 group-hover/item:scale-110 transition-transform duration-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                    </div>
                                    <span class="text-gray-100 text-base font-medium group-hover/item:text-white transition-colors duration-300 leading-relaxed">{{ __('marketing.ccorp_package_premium_feature_4') }}</span>
                                </li>
                                <li class="flex items-start group/item">
                                    <div class="relative flex-shrink-0 mt-1">
                                        <div class="absolute inset-0 bg-yellow-400 rounded-full blur opacity-0 group-hover/item:opacity-70 transition-opacity duration-300"></div>
                                        <svg class="relative w-6 h-6 text-yellow-400 mr-4 group-hover/item:scale-110 transition-transform duration-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                    </div>
                                    <span class="text-gray-100 text-base font-medium group-hover/item:text-white transition-colors duration-300 leading-relaxed">{{ __('marketing.ccorp_package_premium_feature_5') }}</span>
                                </li>
                            </ul>
                            <button type="button" @click="openAuth()" class="relative w-full py-4 px-6 rounded-xl font-bold text-base text-center block overflow-hidden group/btn bg-gradient-to-r from-yellow-400 to-yellow-500 text-gray-900 hover:shadow-lg transition-all duration-300 transform hover:scale-[1.02]"><span class="relative z-10 flex items-center justify-center gap-2">{{ __('marketing.ccorp_premium_cta') }}<svg class="w-5 h-5 group-hover/btn:translate-x-1 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg></span><div class="absolute inset-0 bg-gradient-to-r from-yellow-500 to-yellow-600 opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300"></div></button>
                        </div>
                    </div>
                </div>
                
                <!-- Add-Ons Section -->
                <div class="mt-16 max-w-3xl mx-auto">
                    <h3 class="text-2xl font-bold text-white text-center mb-8">{{ __('marketing.ccorp_addons_title') }}</h3>
                    <div class="rounded-2xl p-8" style="background-color: #d4a02f;">
                        <ul class="space-y-3 text-white">
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-white mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <span>{{ __('marketing.ccorp_addon_ein') }}</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-white mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <span>{{ __('marketing.ccorp_addon_corporate_kit') }}</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-white mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <span>{{ __('marketing.ccorp_addon_ekit') }}</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-white mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <span>{{ __('marketing.ccorp_addon_banking_docs') }}</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-white mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <span>{{ __('marketing.ccorp_addon_dba') }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <!-- FAQ Section -->
        <section class="py-20" style="background-color: #0b1e33;">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <h2 class="text-3xl font-bold text-white mb-4">Frequently Asked Questions</h2>
                    <p class="text-gray-300">Common questions about C-Corporation formation</p>
                </div>

                <div class="space-y-4">
                    <!-- FAQ Item 1 -->
                    <div class="bg-gray-800/40 backdrop-blur-sm border border-gray-700/50 rounded-lg overflow-hidden transition-all duration-300 hover:border-yellow-400/50">
                        <button 
                            @click="toggleFaq(0)"
                            class="w-full flex items-center justify-between p-6 text-left transition-colors duration-300 hover:bg-gray-800/60"
                        >
                            <h3 class="text-lg font-semibold text-white pr-8">{{ __('marketing.ccorp_faq_q1') }}</h3>
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
                                        {{ __('marketing.ccorp_faq_a1') }}
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
                            <h3 class="text-lg font-semibold text-white pr-8">{{ __('marketing.ccorp_faq_q2') }}</h3>
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
                                        {{ __('marketing.ccorp_faq_a2') }}
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
                            <h3 class="text-lg font-semibold text-white pr-8">{{ __('marketing.ccorp_faq_q3') }}</h3>
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
                                        {{ __('marketing.ccorp_faq_a3') }}
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
                            <h3 class="text-lg font-semibold text-white pr-8">{{ __('marketing.ccorp_faq_q4') }}</h3>
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
                                        {{ __('marketing.ccorp_faq_a4') }}
                                    </p>
                                </div>
                            </div>
                        </transition>
                    </div>

                    <!-- FAQ Item 5 -->
                    <div class="bg-gray-800/40 backdrop-blur-sm border border-gray-700/50 rounded-lg overflow-hidden transition-all duration-300 hover:border-yellow-400/50">
                        <button 
                            @click="toggleFaq(4)"
                            class="w-full flex items-center justify-between p-6 text-left transition-colors duration-300 hover:bg-gray-800/60"
                        >
                            <h3 class="text-lg font-semibold text-white pr-8">{{ __('marketing.ccorp_faq_q5') }}</h3>
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
                                    <p class="text-gray-300 leading-relaxed">
                                        {{ __('marketing.ccorp_faq_a5') }}
                                    </p>
                                </div>
                            </div>
                        </transition>
                    </div>

                    <!-- FAQ Item 6 -->
                    <div class="bg-gray-800/40 backdrop-blur-sm border border-gray-700/50 rounded-lg overflow-hidden transition-all duration-300 hover:border-yellow-400/50">
                        <button 
                            @click="toggleFaq(5)"
                            class="w-full flex items-center justify-between p-6 text-left transition-colors duration-300 hover:bg-gray-800/60"
                        >
                            <h3 class="text-lg font-semibold text-white pr-8">{{ __('marketing.ccorp_faq_q6') }}</h3>
                            <svg 
                                class="w-6 h-6 text-yellow-400 flex-shrink-0 transition-transform duration-300"
                                :class="{ 'rotate-180': openFaqIndex === 5 }"
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
                            <div v-show="openFaqIndex === 5" class="overflow-hidden">
                                <div class="px-6 pb-6">
                                    <p class="text-gray-300 leading-relaxed">
                                        {{ __('marketing.ccorp_faq_a6') }}
                                    </p>
                                </div>
                            </div>
                        </transition>
                    </div>

                    <!-- FAQ Item 7 -->
                    <div class="bg-gray-800/40 backdrop-blur-sm border border-gray-700/50 rounded-lg overflow-hidden transition-all duration-300 hover:border-yellow-400/50">
                        <button 
                            @click="toggleFaq(6)"
                            class="w-full flex items-center justify-between p-6 text-left transition-colors duration-300 hover:bg-gray-800/60"
                        >
                            <h3 class="text-lg font-semibold text-white pr-8">{{ __('marketing.ccorp_faq_q7') }}</h3>
                            <svg 
                                class="w-6 h-6 text-yellow-400 flex-shrink-0 transition-transform duration-300"
                                :class="{ 'rotate-180': openFaqIndex === 6 }"
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
                            <div v-show="openFaqIndex === 6" class="overflow-hidden">
                                <div class="px-6 pb-6">
                                    <p class="text-gray-300 leading-relaxed">
                                        {{ __('marketing.ccorp_faq_a7') }}
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
                    {{ __('marketing.ccorp_cta_title') }}
                </h2>
                <p class="text-xl text-gray-300 mb-8">
                    {{ __('marketing.ccorp_cta_subtitle') }}
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <button type="button" @click="openAuth()" class="bg-yellow-400 hover:bg-yellow-500 text-white px-8 py-4 rounded-lg text-lg font-semibold transition-all duration-300 shadow-lg hover:shadow-xl inline-block">
                        {{ __('marketing.ccorp_cta_button') }}
                    </button>
                    <Link :href="route('marketing.pricing')" class="border-2 border-white text-white hover:bg-white hover:text-navy-900 px-8 py-4 rounded-lg text-lg font-semibold transition-all duration-300 inline-block">
                        {{ __('marketing.ccorp_explore_pricing') }}
                    </Link>
                </div>
            </div>
        </section>

        <!-- Share This Page -->
        <section class="py-8 border-t border-white/[0.05]" style="background-color: #0b1e33;">
            <div class="max-w-4xl mx-auto text-center px-4 sm:px-6 lg:px-8">
                <p class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-3">Share This Page</p>
                <div class="flex flex-wrap justify-center gap-3 social-share-buttons">
                    <!-- Twitter/X Share -->
                    <a href="https://twitter.com/intent/tweet?url=https%3A%2F%2Fcorpius.net%2Fservices%2Fc-corporation&text=C-Corporation+Formation+%7C+CORPIUS"
                       target="_blank" rel="noopener noreferrer"
                       class="twitter-share-button inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-black text-white text-sm font-medium hover:opacity-80 transition-opacity"
                       data-url="https://corpius.net/services/c-corporation"
                       data-text="C-Corporation Formation | CORPIUS"
                       data-size="large">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.748l7.73-8.835L1.254 2.25H8.08l4.253 5.622 5.911-5.622Zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                        Tweet
                    </a>
                    <!-- Facebook Share -->
                    <div class="fb-share-button"
                         data-href="https://corpius.net/services/c-corporation"
                         data-layout="button_count"
                         data-size="large">
                        <a href="https://www.facebook.com/sharer/sharer.php?u=https%3A%2F%2Fcorpius.net%2Fservices%2Fc-corporation"
                           target="_blank" rel="noopener noreferrer"
                           class="fb-xfbml-parse-ignore inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-[#1877F2] text-white text-sm font-medium hover:opacity-80 transition-opacity">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                            Share
                        </a>
                    </div>
                    <!-- LinkedIn Share -->
                    <a href="https://www.linkedin.com/shareArticle?mini=true&url=https%3A%2F%2Fcorpius.net%2Fservices%2Fc-corporation&title=C-Corporation+Formation+Services"
                       target="_blank" rel="noopener noreferrer"
                       class="linkedin-share-button inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-[#0A66C2] text-white text-sm font-medium hover:opacity-80 transition-opacity"
                       data-url="https://corpius.net/services/c-corporation">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                        Share
                    </a>
                </div>
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