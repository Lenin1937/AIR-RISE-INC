<script setup>
import AuthModal from '@/Components/AuthModal.vue';
import { useTranslations } from '@/Composables/useTranslations';
import MarketingLayout from '@/Layouts/MarketingLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';

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

// Scroll animation observer
onMounted(() => {
    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-in');
                }
            });
        },
        {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        }
    );

    const animatedElements = document.querySelectorAll('.fade-in-section, .slide-in-section');
    animatedElements.forEach((el) => observer.observe(el));
});
</script>

<template>
    <Head title="Professional Income Tax Filing & Planning | CORPIUS">
        <meta head-key="description" name="description" content="Professional income tax filing and tax planning with CORPIUS. Maximize deductions, minimize tax liability, and stay IRS-compliant with expert CPAs." />
        <link head-key="canonical" rel="canonical" href="https://corpius.net/services/income-tax-filing-planning" />
        <meta head-key="og:title" property="og:title" content="Professional Income Tax Filing & Planning | CORPIUS" />
        <meta head-key="og:description" property="og:description" content="Professional income tax filing and planning with CORPIUS. Maximize deductions, minimize tax liability, and stay IRS-compliant with expert CPAs." />
        <meta head-key="og:url" property="og:url" content="https://corpius.net/services/income-tax-filing-planning" />
        <meta head-key="twitter:title" name="twitter:title" content="Professional Income Tax Filing & Planning | CORPIUS" />
        <meta head-key="twitter:description" name="twitter:description" content="Expert income tax filing and planning from CORPIUS CPAs. Maximize deductions, minimize liability, stay fully IRS-compliant." />
        <meta head-key="og:type" property="og:type" content="website" />
        <meta head-key="og:image" property="og:image" content="https://corpius.net/images/og-preview.jpg" />
        <meta head-key="og:image:width" property="og:image:width" content="1200" />
        <meta head-key="og:image:height" property="og:image:height" content="630" />
        <meta head-key="og:site_name" property="og:site_name" content="CORPIUS" />
        <meta head-key="twitter:card" name="twitter:card" content="summary_large_image" />
        <meta head-key="twitter:image" name="twitter:image" content="https://corpius.net/images/og-preview.jpg" />
        <script head-key="schema-service" type="application/ld+json">{"@context":"https://schema.org","@graph":[{"@type":"Service","@id":"https://corpius.net/services/income-tax-filing-planning#service","name":"Business & Personal Income Tax Filing Service","description":"Professional income tax filing and tax planning with CORPIUS. Maximize deductions, minimize tax liability, and stay IRS-compliant with expert CPAs.","provider":{"@type":"Organization","name":"CORPIUS","url":"https://corpius.net"},"areaServed":"US","url":"https://corpius.net/services/income-tax-filing-planning","offers":{"@type":"Offer","priceCurrency":"USD","availability":"https://schema.org/InStock"}},{"@type":"FAQPage","mainEntity":[{"@type":"Question","name":"What documents do I need for tax filing?","acceptedAnswer":{"@type":"Answer","text":"For individuals: W-2s, 1099s, receipts for deductions, investment statements. For businesses: profit/loss statements, balance sheets, payroll records, business expense receipts, and previous year returns."}},{"@type":"Question","name":"When are the tax filing deadlines?","acceptedAnswer":{"@type":"Answer","text":"Individual tax returns (Form 1040) are due April 15th. C-Corporation returns (Form 1120) are due April 15th. S-Corporation and Partnership returns are due March 15th. Extensions are available."}},{"@type":"Question","name":"Can you help reduce my tax liability?","acceptedAnswer":{"@type":"Answer","text":"Absolutely. Our tax professionals identify all applicable deductions and credits, suggest tax-saving strategies, and provide year-round planning to minimize your tax burden legally."}}]}]}</script>
    </Head>

    <MarketingLayout>
        <style scoped>
        /* Scroll Animation Styles */
        .fade-in-section,
        .slide-in-section {
            opacity: 0;
            transition: opacity 0.6s ease-out, transform 0.6s ease-out;
        }

        .fade-in-section {
            transform: translateY(20px);
        }

        .slide-in-section {
            transform: translateX(-30px);
        }

        .fade-in-section.animate-in,
        .slide-in-section.animate-in {
            opacity: 1;
            transform: translate(0, 0);
        }

        /* Enhanced Card Hover Effects */
        .service-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .service-card:hover {
            transform: translateY(-8px) scale(1.02);
        }

        /* Button Hover Animation */
        .cta-button {
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .cta-button::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .cta-button:hover::before {
            width: 300px;
            height: 300px;
        }

        .cta-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 20px 40px rgba(212, 160, 47, 0.4);
        }

        /* Stagger animation delay for grid items */
        .stagger-item {
            animation-delay: calc(var(--stagger-index) * 0.1s);
        }
        </style>
        <!-- Hero Section -->
        <section class="bg-gradient-to-br from-navy-900 via-blue-900 to-navy-900 py-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                    <div class="fade-in-section">
                        <h1 class="text-4xl lg:text-5xl font-bold text-yellow-400 mb-6 gradient-text-shimmer">
                            {{ __('marketing.incometax_hero_title') }}
                        </h1>
                        <p class="text-xl text-gray-300 mb-4">
                            {{ __('marketing.incometax_hero_subtitle') }}
                        </p>
                        
                        <!-- Trust-Enhancing One-Liner -->
                        <div class="bg-yellow-400/10 border border-yellow-400/30 rounded-lg px-6 py-3 mb-8 inline-block">
                            <p class="text-yellow-400 font-semibold text-lg">
                                {{ __('marketing.incometax_hero_description') }}
                            </p>
                        </div>
                        
                        <div class="flex flex-col sm:flex-row gap-4">
                            <button type="button" @click="openAuth()" class="cta-button bg-yellow-400 hover:bg-yellow-500 text-white px-8 py-4 rounded-lg font-semibold transition-all duration-300 text-center relative z-10">
                                {{ __('marketing.incometax_hero_button') }}
                            </button>
                            <Link href="#packages" class="border-2 border-white text-white hover:bg-white/10 px-8 py-4 rounded-lg font-semibold transition-all duration-300 text-center hover:scale-105">
                                {{ __('marketing.incometax_view_packages') }}
                            </Link>
                        </div>
                    </div>
                    <div class="slide-in-section relative overflow-hidden rounded-2xl shadow-2xl border-2 border-yellow-400/30 hover:shadow-yellow-400/40 transition-shadow duration-500" style="background: #d4a02f;">
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
                                <h3 class="text-2xl font-bold text-white">{{ __('marketing.incometax_trust_title') }}</h3>
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
                                        {{ __('marketing.incometax_trust_item1') }}
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
                                        {{ __('marketing.incometax_trust_item2') }}
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
                                        {{ __('marketing.incometax_trust_item3') }}
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
                                        {{ __('marketing.incometax_trust_item4') }}
                                    </span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Tax Service Disclaimer -->
        <section class="py-6" style="background-color: #1a2f4d;">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-blue-50 border-l-4 border-blue-600 rounded-lg p-6">
                    <h3 class="text-lg font-bold text-blue-900 mb-3">{{ __('marketing.incometax_disclosure_title') }}</h3>
                    <div class="space-y-2 text-blue-800 text-sm leading-relaxed">
                        <p>{{ __('marketing.incometax_disclosure_item1') }}</p>
                        <p>{{ __('marketing.incometax_disclosure_item2') }}</p>
                        <p>{{ __('marketing.incometax_disclosure_item3') }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Who We Serve Section -->
        <section class="py-20 fade-in-section" style="background-color: #0b1e33;">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-3xl lg:text-4xl font-bold text-white mb-6">
                        {{ __('marketing.incometax_serve_title') }}
                    </h2>
                    <p class="text-lg text-gray-300 max-w-3xl mx-auto">
                        {{ __('marketing.incometax_serve_subtitle') }}
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-12">
                    <div class="service-card bg-gradient-to-br from-gray-800/60 to-gray-900/60 border-2 border-gray-700/50 rounded-2xl p-8 hover:border-yellow-400/70 transition-all duration-300">
                        <div class="flex items-center mb-6">
                            <div class="bg-yellow-400 rounded-full p-4 mr-4">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                            <h3 class="text-2xl font-bold text-white">{{ __('marketing.incometax_serve_individuals_title') }}</h3>
                        </div>
                        <p class="text-gray-300 mb-4 leading-relaxed">
                            {{ __('marketing.incometax_serve_individuals_desc') }}
                        </p>
                    </div>

                    <div class="service-card bg-gradient-to-br from-gray-800/60 to-gray-900/60 border-2 border-gray-700/50 rounded-2xl p-8 hover:border-yellow-400/70 transition-all duration-300">
                        <div class="flex items-center mb-6">
                            <div class="bg-yellow-400 rounded-full p-4 mr-4 transition-transform duration-300 hover:scale-110 hover:rotate-6">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                            </div>
                            <h3 class="text-2xl font-bold text-white">{{ __('marketing.incometax_serve_businesses_title') }}</h3>
                        </div>
                        <p class="text-gray-300 mb-4 leading-relaxed">
                            {{ __('marketing.incometax_serve_businesses_desc') }}
                        </p>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-blue-900/20 to-indigo-900/20 border border-blue-500/30 rounded-2xl p-8">
                    <h3 class="text-xl font-bold text-white mb-4">{{ __('marketing.incometax_forms_title') }}</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-yellow-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="text-gray-200">{{ __('marketing.incometax_forms_item1') }}</span>
                        </div>
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-yellow-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="text-gray-200">{{ __('marketing.incometax_forms_item2') }}</span>
                        </div>
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-yellow-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="text-gray-200">{{ __('marketing.incometax_forms_item3') }}</span>
                        </div>
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-yellow-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="text-gray-200">{{ __('marketing.incometax_forms_item4') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- How the Process Works -->
        <section class="py-20 relative overflow-hidden fade-in-section" style="background: linear-gradient(135deg, #0a1628 0%, #0b1e33 50%, #0d2540 100%);">
            <div class="absolute inset-0 opacity-5">
                <div class="absolute top-20 left-10 w-72 h-72 bg-yellow-400 rounded-full filter blur-3xl"></div>
                <div class="absolute bottom-20 right-10 w-96 h-96 bg-blue-500 rounded-full filter blur-3xl"></div>
            </div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="text-center mb-16">
                    <h2 class="text-3xl lg:text-4xl font-bold text-white mb-6">{{ __('marketing.incometax_process_title') }}</h2>
                    <p class="text-lg text-gray-300 max-w-3xl mx-auto">{{ __('marketing.incometax_process_subtitle') }}</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                    <!-- Step 1 -->
                    <div class="text-center">
                        <div class="relative mb-6">
                            <div class="w-20 h-20 mx-auto flex items-center justify-center">
                                <img src="/images/service-page-logo/icon%201.1.png" class="w-20 h-20 object-contain" alt="process step logo" />
                            </div>
                        </div>
                        <h3 class="text-lg font-bold text-white mb-3">{{ __('marketing.incometax_process_step1_title') }}</h3>
                        <p class="text-gray-300 text-sm">{{ __('marketing.incometax_process_step1_desc') }}</p>
                    </div>

                    <!-- Step 2 -->
                    <div class="text-center">
                        <div class="relative mb-6">
                            <div class="w-20 h-20 mx-auto flex items-center justify-center">
                                <img src="/images/service-page-logo/icon%202.1.png" class="w-20 h-20 object-contain" alt="process step logo" />
                            </div>
                        </div>
                        <h3 class="text-lg font-bold text-white mb-3">{{ __('marketing.incometax_process_step2_title') }}</h3>
                        <p class="text-gray-300 text-sm">{{ __('marketing.incometax_process_step2_desc') }}</p>
                    </div>

                    <!-- Step 3 -->
                    <div class="text-center">
                        <div class="relative mb-6">
                            <div class="w-20 h-20 mx-auto flex items-center justify-center">
                                <img src="/images/service-page-logo/icon%203.1.png" class="w-20 h-20 object-contain" alt="process step logo" />
                            </div>
                        </div>
                        <h3 class="text-lg font-bold text-white mb-3">{{ __('marketing.incometax_process_step3_title') }}</h3>
                        <p class="text-gray-300 text-sm">{{ __('marketing.incometax_process_step3_desc') }}</p>
                    </div>

                    <!-- Step 4 -->
                    <div class="text-center">
                        <div class="relative mb-6">
                            <div class="w-20 h-20 mx-auto flex items-center justify-center">
                                <img src="/images/service-page-logo/icon%204.1.png" class="w-20 h-20 object-contain" alt="process step logo" />
                            </div>
                        </div>
                        <h3 class="text-lg font-bold text-white mb-3">{{ __('marketing.incometax_process_step4_title') }}</h3>
                        <p class="text-gray-300 text-sm">{{ __('marketing.incometax_process_step4_desc') }}</p>
                    </div>
                </div>

                <div class="mt-12 text-center">
                    <button type="button" @click="openAuth()" class="cta-button bg-yellow-400 hover:bg-yellow-500 text-white px-10 py-4 rounded-lg text-lg font-semibold transition-all duration-300 shadow-lg hover:shadow-xl inline-block">
                        {{ __('marketing.incometax_cta1_button') }}
                    </button>
                </div>
            </div>
        </section>

        <!-- What's Included Section -->
        <section class="py-20 fade-in-section" style="background-color: #0b1e33;">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-3xl lg:text-4xl font-bold text-white mb-6">
                        {{ __('marketing.incometax_included_title') }}
                    </h2>
                    <p class="text-lg text-gray-300 max-w-3xl mx-auto">
                        {{ __('marketing.incometax_included_subtitle') }}
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div class="group relative bg-gradient-to-br from-gray-800/40 to-gray-900/40 backdrop-blur-sm border border-gray-700/50 rounded-2xl p-8 text-center transition-all duration-500 hover:scale-105 hover:shadow-2xl hover:shadow-yellow-400/20 hover:border-yellow-400/50 hover:-translate-y-2 cursor-pointer">
                        <div class="absolute inset-0 bg-gradient-to-br from-yellow-400/0 to-yellow-600/0 rounded-2xl opacity-0 group-hover:opacity-10 transition-opacity duration-500"></div>
                        
                        <div class="w-20 h-20 mx-auto mb-6 flex items-center justify-center group-hover:scale-110 transition-transform duration-500">
                            <img src="/images/service-page-logo/icon%201.1.png" class="relative w-20 h-20 group-hover:scale-110 transition-transform duration-500 object-contain" alt="benefit icon" />
                        </div>
                        <h3 class="relative text-xl font-bold text-white mb-4 group-hover:text-yellow-400 transition-colors duration-300">{{ __('marketing.incometax_included_feature1') }}</h3>
                        <div class="absolute bottom-0 left-1/2 transform -translate-x-1/2 w-0 h-1 bg-gradient-to-r from-yellow-400 via-yellow-500 to-yellow-600 group-hover:w-full transition-all duration-500 rounded-b-2xl"></div>
                    </div>

                    <div class="group relative bg-gradient-to-br from-gray-800/40 to-gray-900/40 backdrop-blur-sm border border-gray-700/50 rounded-2xl p-8 text-center transition-all duration-500 hover:scale-105 hover:shadow-2xl hover:shadow-yellow-400/20 hover:border-yellow-400/50 hover:-translate-y-2 cursor-pointer">
                        <div class="absolute inset-0 bg-gradient-to-br from-yellow-400/0 to-yellow-600/0 rounded-2xl opacity-0 group-hover:opacity-10 transition-opacity duration-500"></div>
                        
                        <div class="w-20 h-20 mx-auto mb-6 flex items-center justify-center group-hover:scale-110 transition-transform duration-500">
                            <img src="/images/service-page-logo/icon%202.1.png" class="relative w-20 h-20 group-hover:scale-110 transition-transform duration-500 object-contain" alt="benefit icon" />
                        </div>
                        <h3 class="relative text-xl font-bold text-white mb-4 group-hover:text-yellow-400 transition-colors duration-300">{{ __('marketing.incometax_included_feature2') }}</h3>
                        <div class="absolute bottom-0 left-1/2 transform -translate-x-1/2 w-0 h-1 bg-gradient-to-r from-yellow-400 via-yellow-500 to-yellow-600 group-hover:w-full transition-all duration-500 rounded-b-2xl"></div>
                    </div>

                    <div class="group relative bg-gradient-to-br from-gray-800/40 to-gray-900/40 backdrop-blur-sm border border-gray-700/50 rounded-2xl p-8 text-center transition-all duration-500 hover:scale-105 hover:shadow-2xl hover:shadow-yellow-400/20 hover:border-yellow-400/50 hover:-translate-y-2 cursor-pointer">
                        <div class="absolute inset-0 bg-gradient-to-br from-yellow-400/0 to-yellow-600/0 rounded-2xl opacity-0 group-hover:opacity-10 transition-opacity duration-500"></div>
                        
                        <div class="w-20 h-20 mx-auto mb-6 flex items-center justify-center group-hover:scale-110 transition-transform duration-500">
                            <img src="/images/service-page-logo/icon%203.1.png" class="relative w-20 h-20 group-hover:scale-110 transition-transform duration-500 object-contain" alt="benefit icon" />
                        </div>
                        <h3 class="relative text-xl font-bold text-white mb-4 group-hover:text-yellow-400 transition-colors duration-300">{{ __('marketing.incometax_included_feature3') }}</h3>
                        <div class="absolute bottom-0 left-1/2 transform -translate-x-1/2 w-0 h-1 bg-gradient-to-r from-yellow-400 via-yellow-500 to-yellow-600 group-hover:w-full transition-all duration-500 rounded-b-2xl"></div>
                    </div>

                    <div class="group relative bg-gradient-to-br from-gray-800/40 to-gray-900/40 backdrop-blur-sm border border-gray-700/50 rounded-2xl p-8 text-center transition-all duration-500 hover:scale-105 hover:shadow-2xl hover:shadow-yellow-400/20 hover:border-yellow-400/50 hover:-translate-y-2 cursor-pointer">
                        <div class="absolute inset-0 bg-gradient-to-br from-yellow-400/0 to-yellow-600/0 rounded-2xl opacity-0 group-hover:opacity-10 transition-opacity duration-500"></div>
                        
                        <div class="w-20 h-20 mx-auto mb-6 flex items-center justify-center group-hover:scale-110 transition-transform duration-500">
                            <img src="/images/service-page-logo/icon%204.1.png" class="relative w-20 h-20 group-hover:scale-110 transition-transform duration-500 object-contain" alt="benefit icon" />
                        </div>
                        <h3 class="relative text-xl font-bold text-white mb-4 group-hover:text-yellow-400 transition-colors duration-300">{{ __('marketing.incometax_included_feature4') }}</h3>
                        <div class="absolute bottom-0 left-1/2 transform -translate-x-1/2 w-0 h-1 bg-gradient-to-r from-yellow-400 via-yellow-500 to-yellow-600 group-hover:w-full transition-all duration-500 rounded-b-2xl"></div>
                    </div>

                    <div class="group relative bg-gradient-to-br from-gray-800/40 to-gray-900/40 backdrop-blur-sm border border-gray-700/50 rounded-2xl p-8 text-center transition-all duration-500 hover:scale-105 hover:shadow-2xl hover:shadow-yellow-400/20 hover:border-yellow-400/50 hover:-translate-y-2 cursor-pointer">
                        <div class="absolute inset-0 bg-gradient-to-br from-yellow-400/0 to-yellow-600/0 rounded-2xl opacity-0 group-hover:opacity-10 transition-opacity duration-500"></div>
                        
                        <div class="w-20 h-20 mx-auto mb-6 flex items-center justify-center group-hover:scale-110 transition-transform duration-500">
                            <img src="/images/service-page-logo/icon%205.1.1.png" class="relative w-20 h-20 group-hover:scale-110 transition-transform duration-500 object-contain" alt="benefit icon" />
                        </div>
                        <h3 class="relative text-xl font-bold text-white mb-4 group-hover:text-yellow-400 transition-colors duration-300">{{ __('marketing.incometax_included_feature5') }}</h3>
                        <div class="absolute bottom-0 left-1/2 transform -translate-x-1/2 w-0 h-1 bg-gradient-to-r from-yellow-400 via-yellow-500 to-yellow-600 group-hover:w-full transition-all duration-500 rounded-b-2xl"></div>
                    </div>

                    <div class="group relative bg-gradient-to-br from-gray-800/40 to-gray-900/40 backdrop-blur-sm border border-gray-700/50 rounded-2xl p-8 text-center transition-all duration-500 hover:scale-105 hover:shadow-2xl hover:shadow-yellow-400/20 hover:border-yellow-400/50 hover:-translate-y-2 cursor-pointer">
                        <div class="absolute inset-0 bg-gradient-to-br from-yellow-400/0 to-yellow-600/0 rounded-2xl opacity-0 group-hover:opacity-10 transition-opacity duration-500"></div>
                        
                        <div class="w-20 h-20 mx-auto mb-6 flex items-center justify-center group-hover:scale-110 transition-transform duration-500">
                            <img src="/images/service-page-logo/icon%206.1.png" class="relative w-20 h-20 group-hover:scale-110 transition-transform duration-500 object-contain" alt="benefit icon" />
                        </div>
                        <h3 class="relative text-xl font-bold text-white mb-4 group-hover:text-yellow-400 transition-colors duration-300">{{ __('marketing.incometax_included_feature6') }}</h3>
                        <div class="absolute bottom-0 left-1/2 transform -translate-x-1/2 w-0 h-1 bg-gradient-to-r from-yellow-400 via-yellow-500 to-yellow-600 group-hover:w-full transition-all duration-500 rounded-b-2xl"></div>
                    </div>
                </div>

                <!-- Additional CTA after What's Included -->
                <div class="mt-16 text-center fade-in-section">
                    <div class="bg-gradient-to-br from-gray-800/60 to-gray-900/60 border-2 border-yellow-400/50 rounded-2xl p-10 max-w-3xl mx-auto">
                        <h3 class="text-2xl font-bold text-white mb-4">
                            {{ __('marketing.incometax_cta1_title') }}
                        </h3>
                        <p class="text-gray-300 mb-6 text-lg">
                            {{ __('marketing.incometax_cta1_subtitle') }}
                        </p>
                        <button type="button" @click="openAuth()" class="cta-button bg-yellow-400 hover:bg-yellow-500 text-white px-10 py-4 rounded-lg text-lg font-semibold transition-all duration-300 shadow-lg hover:shadow-xl inline-block">
                            {{ __('marketing.incometax_cta1_button') }}
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <!-- Expense Categorization Overview -->
        <section class="py-20 relative overflow-hidden fade-in-section" style="background: linear-gradient(135deg, #0a1628 0%, #0b1e33 50%, #0d2540 100%);">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-3xl lg:text-4xl font-bold text-white mb-6">
                        {{ __('marketing.incometax_categories_title') }}
                    </h2>
                    <p class="text-lg text-gray-300 max-w-3xl mx-auto">
                        {{ __('marketing.incometax_categories_subtitle') }}
                    </p>
                </div>

                <div class="bg-gray-800/60 border border-gray-700/50 rounded-2xl overflow-hidden hover:border-yellow-400/30 transition-all duration-300">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-yellow-400/10">
                                <tr>
                                    <th class="px-6 py-4 text-left text-white font-bold">{{ __('marketing.incometax_table_category') }}</th>
                                    <th class="px-6 py-4 text-left text-white font-bold">{{ __('marketing.incometax_table_description') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-700/50">
                                <tr class="hover:bg-gray-700/30 transition-all duration-300 hover:scale-[1.02] hover:shadow-lg">
                                    <td class="px-6 py-4 text-yellow-400 font-semibold">{{ __('marketing.incometax_category_rent') }}</td>
                                    <td class="px-6 py-4 text-gray-300">{{ __('marketing.incometax_category_rent_desc') }}</td>
                                </tr>
                                <tr class="hover:bg-gray-700/30 transition-colors duration-200">
                                    <td class="px-6 py-4 text-yellow-400 font-semibold">{{ __('marketing.incometax_category_wages') }}</td>
                                    <td class="px-6 py-4 text-gray-300">{{ __('marketing.incometax_category_wages_desc') }}</td>
                                </tr>
                                <tr class="hover:bg-gray-700/30 transition-colors duration-200">
                                    <td class="px-6 py-4 text-yellow-400 font-semibold">{{ __('marketing.incometax_category_auto') }}</td>
                                    <td class="px-6 py-4 text-gray-300">{{ __('marketing.incometax_category_auto_desc') }}</td>
                                </tr>
                                <tr class="hover:bg-gray-700/30 transition-colors duration-200">
                                    <td class="px-6 py-4 text-yellow-400 font-semibold">{{ __('marketing.incometax_category_advertising') }}</td>
                                    <td class="px-6 py-4 text-gray-300">{{ __('marketing.incometax_category_advertising_desc') }}</td>
                                </tr>
                                <tr class="hover:bg-gray-700/30 transition-colors duration-200">
                                    <td class="px-6 py-4 text-yellow-400 font-semibold">{{ __('marketing.incometax_category_office') }}</td>
                                    <td class="px-6 py-4 text-gray-300">{{ __('marketing.incometax_category_office_desc') }}</td>
                                </tr>
                                <tr class="hover:bg-gray-700/30 transition-colors duration-200">
                                    <td class="px-6 py-4 text-yellow-400 font-semibold">{{ __('marketing.incometax_category_legal') }}</td>
                                    <td class="px-6 py-4 text-gray-300">{{ __('marketing.incometax_category_legal_desc') }}</td>
                                </tr>
                                <tr class="hover:bg-gray-700/30 transition-colors duration-200">
                                    <td class="px-6 py-4 text-yellow-400 font-semibold">{{ __('marketing.incometax_category_software') }}</td>
                                    <td class="px-6 py-4 text-gray-300">{{ __('marketing.incometax_category_software_desc') }}</td>
                                </tr>
                                <tr class="hover:bg-gray-700/30 transition-colors duration-200">
                                    <td class="px-6 py-4 text-yellow-400 font-semibold">{{ __('marketing.incometax_category_bank') }}</td>
                                    <td class="px-6 py-4 text-gray-300">{{ __('marketing.incometax_category_bank_desc') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="px-6 py-4 bg-gray-900/40 text-center">
                        <p class="text-gray-400 text-sm">
                            {{ __('marketing.incometax_categories_note') }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Data Security & Compliance -->
        <section class="py-20 fade-in-section" style="background-color: #0b1e33;">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-3xl lg:text-4xl font-bold text-white mb-6">
                        {{ __('marketing.incometax_security_title') }}
                    </h2>
                    <p class="text-lg text-gray-300 max-w-3xl mx-auto">{{ __('marketing.incometax_security_subtitle') }}</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                    <div class="group text-center cursor-pointer">
                        <div class="relative bg-yellow-400/10 rounded-full w-20 h-20 flex items-center justify-center mx-auto mb-4 transition-all duration-300 group-hover:scale-110 group-hover:bg-yellow-400/20">
                            <div class="absolute inset-0 bg-yellow-400 rounded-full blur opacity-0 group-hover:opacity-30 transition-opacity duration-300"></div>
                            <svg class="relative w-10 h-10 text-yellow-400 group-hover:scale-110 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                        </div>
                        <h3 class="text-white font-bold mb-2 group-hover:text-yellow-400 transition-colors duration-300">{{ __('marketing.incometax_security_feature1') }}</h3>
                    </div>

                    <div class="group text-center cursor-pointer">
                        <div class="relative bg-yellow-400/10 rounded-full w-20 h-20 flex items-center justify-center mx-auto mb-4 transition-all duration-300 group-hover:scale-110 group-hover:bg-yellow-400/20">
                            <div class="absolute inset-0 bg-yellow-400 rounded-full blur opacity-0 group-hover:opacity-30 transition-opacity duration-300"></div>
                            <svg class="relative w-10 h-10 text-yellow-400 group-hover:scale-110 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"></path>
                            </svg>
                        </div>
                        <h3 class="text-white font-bold mb-2 group-hover:text-yellow-400 transition-colors duration-300">{{ __('marketing.incometax_security_feature2') }}</h3>
                    </div>

                    <div class="group text-center cursor-pointer">
                        <div class="relative bg-yellow-400/10 rounded-full w-20 h-20 flex items-center justify-center mx-auto mb-4 transition-all duration-300 group-hover:scale-110 group-hover:bg-yellow-400/20">
                            <div class="absolute inset-0 bg-yellow-400 rounded-full blur opacity-0 group-hover:opacity-30 transition-opacity duration-300"></div>
                            <svg class="relative w-10 h-10 text-yellow-400 group-hover:scale-110 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                        </div>
                        <h3 class="text-white font-bold mb-2 group-hover:text-yellow-400 transition-colors duration-300">{{ __('marketing.incometax_security_feature3') }}</h3>
                    </div>

                    <div class="group text-center cursor-pointer">
                        <div class="relative bg-yellow-400/10 rounded-full w-20 h-20 flex items-center justify-center mx-auto mb-4 transition-all duration-300 group-hover:scale-110 group-hover:bg-yellow-400/20">
                            <div class="absolute inset-0 bg-yellow-400 rounded-full blur opacity-0 group-hover:opacity-30 transition-opacity duration-300"></div>
                            <svg class="relative w-10 h-10 text-yellow-400 group-hover:scale-110 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-white font-bold mb-2 group-hover:text-yellow-400 transition-colors duration-300">{{ __('marketing.incometax_security_feature4') }}</h3>
                    </div>
                </div>

                <!-- Additional CTA after Data Security -->
                <div class="mt-16 text-center fade-in-section">
                    <button type="button" @click="openAuth()" class="cta-button bg-yellow-400 hover:bg-yellow-500 text-white px-10 py-4 rounded-lg text-lg font-semibold transition-all duration-300 shadow-lg hover:shadow-xl inline-block">
                        {{ __('marketing.incometax_cta1_button') }}
                    </button>
                    <p class="text-gray-400 mt-4 text-sm">
                        {{ __('marketing.incometax_hero_description') }}
                    </p>
                </div>
            </div>
        </section>

        <!-- Final CTA Section -->
        <section class="py-20 relative overflow-hidden" style="background: linear-gradient(135deg, #0a1628 0%, #0b1e33 50%, #0d2540 100%);">
            <div class="absolute inset-0 opacity-10">
                <div class="absolute top-0 left-0 w-full h-full bg-gradient-to-br from-yellow-400/20 to-transparent"></div>
            </div>

            <div class="max-w-4xl mx-auto text-center px-4 sm:px-6 lg:px-8 relative z-10">
                <h2 class="text-3xl lg:text-5xl font-bold text-white mb-6">
                    {{ __('marketing.incometax_cta1_title') }}
                </h2>
                <p class="text-xl text-gray-300 mb-8 leading-relaxed">
                    {{ __('marketing.incometax_cta1_subtitle') }}
                </p>
                <button type="button" @click="openAuth()" class="cta-button bg-yellow-400 hover:bg-yellow-500 text-white px-12 py-5 rounded-lg text-xl font-bold transition-all duration-300 shadow-2xl hover:shadow-yellow-400/50 inline-block transform hover:scale-105">
                    {{ __('marketing.incometax_cta1_button') }}
                </button>
                <p class="text-gray-400 mt-4 text-sm">
                    {{ __('marketing.incometax_hero_description') }}
                </p>
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
