<script setup>
import AuthModal from '@/Components/AuthModal.vue';
import { useTranslations } from '@/Composables/useTranslations';
import MarketingLayout from '@/Layouts/MarketingLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

const { __ } = useTranslations();

const showAuthModal = ref(false);
const page = usePage();
const openAuth = () => {
    if (page.props.auth?.user) {
        router.visit(route('orders.create'));
    } else {
        showAuthModal.value = true;
    }
};

defineProps({
    services: Array
});
</script>

<template>
    <Head title="Transparent Pricing | Business Formation Services | CORPIUS">
        <meta head-key="description" name="description" content="Affordable business formation packages from CORPIUS. Compare LLC, C-Corp, S-Corp, and Nonprofit formation plans. Simple pricing, no hidden fees." />
        <link head-key="canonical" rel="canonical" href="https://corpius.net/pricing" />
        <meta head-key="og:type" property="og:type" content="website" />
        <meta head-key="og:title" property="og:title" content="Transparent Pricing | Business Formation Services | CORPIUS" />
        <meta head-key="og:description" property="og:description" content="Affordable business formation packages from CORPIUS. Compare LLC, C-Corp, S-Corp, and Nonprofit plans. Simple pricing, no hidden fees." />
        <meta head-key="og:url" property="og:url" content="https://corpius.net/pricing" />
        <meta head-key="og:image" property="og:image" content="https://corpius.net/images/og-preview.jpg" />
        <meta head-key="og:image:width" property="og:image:width" content="1200" />
        <meta head-key="og:image:height" property="og:image:height" content="630" />
        <meta head-key="og:site_name" property="og:site_name" content="CORPIUS" />
        <meta head-key="twitter:card" name="twitter:card" content="summary_large_image" />
        <meta head-key="twitter:title" name="twitter:title" content="Transparent Pricing | Business Formation Services | CORPIUS" />
        <meta head-key="twitter:description" name="twitter:description" content="Affordable business formation packages from CORPIUS. Compare LLC, C-Corp, S-Corp, and Nonprofit plans. Simple pricing, no hidden fees." />
        <meta head-key="twitter:image" name="twitter:image" content="https://corpius.net/images/og-preview.jpg" />
        <script head-key="schema-pricing" type="application/ld+json">{"@context":"https://schema.org","@graph":[{"@type":"WebPage","@id":"https://corpius.net/pricing#webpage","url":"https://corpius.net/pricing","name":"Transparent Pricing | Business Formation Services | CORPIUS","description":"Affordable business formation packages from CORPIUS. Compare LLC, C-Corp, S-Corp, and Nonprofit formation plans. Simple pricing, no hidden fees.","inLanguage":"en-US","isPartOf":{"@type":"WebSite","@id":"https://corpius.net/#website","name":"CORPIUS","url":"https://corpius.net"}},{"@type":"OfferCatalog","@id":"https://corpius.net/pricing#catalog","name":"Business Formation Services Pricing","description":"Comprehensive pricing for LLC, C-Corp, S-Corp, Nonprofit, Green Card, and Income Tax services","provider":{"@type":"Organization","name":"CORPIUS","url":"https://corpius.net","logo":{"@type":"ImageObject","url":"https://corpius.net/images/og-preview.jpg"}},"itemListElement":[{"@type":"Offer","name":"LLC Formation","description":"The most flexible option for entrepreneurs and freelancers","price":"1480","priceCurrency":"USD","availability":"https://schema.org/InStock","url":"https://corpius.net/services/llc"},{"@type":"Offer","name":"C-Corporation Formation","description":"Perfect for businesses seeking strong investor credibility","price":"580","priceCurrency":"USD","availability":"https://schema.org/InStock","url":"https://corpius.net/services/c-corporation"},{"@type":"Offer","name":"S-Corporation Formation","description":"Best for small businesses with pass-through taxation","price":"880","priceCurrency":"USD","availability":"https://schema.org/InStock","url":"https://corpius.net/services/s-corporation"},{"@type":"Offer","name":"Nonprofit Organization Formation","description":"Build a mission-driven entity with tax-exempt benefits","price":"499","priceCurrency":"USD","availability":"https://schema.org/InStock","url":"https://corpius.net/services/nonprofit"},{"@type":"Offer","name":"Green Card Services","description":"Professional immigration assistance for U.S. residency","price":"49","priceCurrency":"USD","availability":"https://schema.org/InStock","url":"https://corpius.net/services/green-card-lottery"},{"@type":"Offer","name":"Income Tax Filing","description":"Professional tax filing for corporations and individuals","price":"175","priceCurrency":"USD","availability":"https://schema.org/InStock","url":"https://corpius.net/services/income-tax-filing-planning"}]},{"@type":"FAQPage","mainEntity":[{"@type":"Question","name":"How much does it cost to form an LLC with CORPIUS?","acceptedAnswer":{"@type":"Answer","text":"LLC formation with CORPIUS starts at $1,480 for the Starter package, which includes state filing fees, EIN registration, registered agent for the first year, and an operating agreement template. The Premium package is $1,650 and adds priority processing and compliance alerts."}},{"@type":"Question","name":"What is included in CORPIUS business formation packages?","acceptedAnswer":{"@type":"Answer","text":"Every CORPIUS formation package includes preparation and filing of your Articles of Formation or Incorporation, EIN application with the IRS, registered agent service for the first year, and a compliance dashboard. Higher-tier packages include operating agreement or corporate bylaws, banking resolution, and priority processing."}},{"@type":"Question","name":"How long does business formation take?","acceptedAnswer":{"@type":"Answer","text":"Most states process standard LLC formations within 3-10 business days. States with expedited filing services can approve formations in as little as 24 hours. CORPIUS Premium packages include priority processing."}},{"@type":"Question","name":"What is the difference between an LLC and a C-Corporation?","acceptedAnswer":{"@type":"Answer","text":"An LLC offers flexible pass-through taxation and minimal formalities, ideal for freelancers and small businesses. A C-Corporation pays corporate income tax at a flat 21% federal rate and is preferred for venture-capital-funded startups because it allows unlimited shareholders, multiple stock classes, and Qualified Small Business Stock (QSBS) exclusions."}},{"@type":"Question","name":"Do I need a physical address in the state where I form my business?","acceptedAnswer":{"@type":"Answer","text":"No. You do not need to live in or have an office in the formation state. You only need a registered agent with a physical street address in that state. CORPIUS provides registered agent service in all 50 states."}},{"@type":"Question","name":"Is CORPIUS pricing inclusive of government filing fees?","acceptedAnswer":{"@type":"Answer","text":"Yes. CORPIUS packages include all standard state government filing fees. Our displayed prices are all-inclusive with no hidden fees. Optional add-ons such as foreign qualification in additional states may have separate charges."}},{"@type":"Question","name":"Can CORPIUS help with post-formation compliance?","acceptedAnswer":{"@type":"Answer","text":"Yes. CORPIUS provides a compliance dashboard that tracks annual report deadlines, franchise tax due dates, and registered agent renewal across every state where you are registered. We also offer annual report filing services and EIN amendment services."}}]}]}</script>
    </Head>

    <MarketingLayout>
        <!-- Hero Section -->
        <section class="py-20" style="background-color: #0b1e33;">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h1 class="text-4xl lg:text-5xl font-bold text-yellow-400 mb-6 gradient-text-shimmer">
                    Business Formation Pricing | Transparent, Simple Plans for LLC, C-Corp & More
                </h1>
                <p class="text-xl lg:text-2xl text-gray-300 mb-8 max-w-3xl mx-auto">
                    {{ __('marketing.pricing_hero_subtitle') }}
                </p>
                <p class="text-base text-gray-400 max-w-4xl mx-auto leading-relaxed">
                    Transparent pricing for business formation services with simple plans for LLC, C-Corp, S-Corp, Nonprofit, Green Card, and Income Tax support.
                </p>
            </div>
        </section>

        <!-- Pricing Grid -->
        <section class="py-20" style="background-color: #0b1e33;">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Section Header -->
                <div class="text-center mb-16">
                    <h2 class="text-3xl lg:text-4xl font-bold text-white mb-4">
                        {{ __('marketing.choose_your') }} <span class="text-yellow-400">{{ __('marketing.business_formation_service') }}</span>
                    </h2>
                    <p class="text-xl text-gray-300 max-w-3xl mx-auto">
                        {{ __('marketing.pricing_packages_subtitle') }}
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 max-w-6xl mx-auto">
                    <div 
                        v-for="service in services" 
                        :key="service.name" 
                        class="group relative bg-gradient-to-br from-gray-800/40 to-gray-900/40 backdrop-blur-sm border border-gray-700/50 rounded-2xl p-6 transition-all duration-500 hover:scale-105 hover:shadow-2xl hover:shadow-yellow-400/20 hover:border-yellow-400/50 hover:-translate-y-2"
                    >
                        <!-- Animated background glow on hover -->
                        <div class="absolute inset-0 bg-gradient-to-br from-yellow-400/0 to-yellow-600/0 rounded-2xl opacity-0 group-hover:opacity-10 transition-opacity duration-500"></div>
                        
                        <!-- Service Header -->
                        <div class="relative text-center mb-6">
                            <div class="inline-block mb-3">
                                <div class="w-16 h-16 bg-gradient-to-br from-yellow-400 to-yellow-500 rounded-xl flex items-center justify-center mx-auto shadow-lg group-hover:shadow-yellow-400/50 group-hover:scale-110 group-hover:rotate-6 transition-all duration-500">
                                    <div class="absolute inset-0 bg-yellow-400 rounded-xl blur-xl opacity-0 group-hover:opacity-60 transition-opacity duration-500"></div>
                                    <svg class="relative w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                    </svg>
                                </div>
                            </div>
                            
                            <div class="text-xl font-bold text-white mb-2 group-hover:text-yellow-400 transition-colors duration-300">
                                {{ service.name }}
                            </div>
                            <p class="text-gray-400 text-sm mb-4 leading-relaxed">
                                {{ service.description }}
                            </p>
                            
                            <!-- Price Badge -->
                            <div class="mb-1">
                                <div class="text-xs text-gray-400 font-medium mb-1">{{ __('marketing.starting_at') }}</div>
                                <div class="text-3xl font-bold text-yellow-400">
                                    ${{ service.starting_price }}
                                </div>
                            </div>
                        </div>

                        <!-- Package Tiers -->
                        <div class="relative space-y-3 mb-6">
                            <div 
                                v-for="pkg in service.packages" 
                                :key="pkg.name" 
                                class="flex justify-between items-center p-3 bg-gray-800/30 border border-gray-700/30 rounded-lg hover:bg-gray-800/50 hover:border-yellow-400/30 transition-all duration-300"
                            >
                                <span class="font-medium text-gray-300 text-base">{{ pkg.name }}</span>
                                <span class="font-bold text-yellow-400 text-lg">${{ pkg.price }}</span>
                            </div>
                        </div>

                        <!-- CTA Button -->
                        <button type="button" @click="openAuth()"
                            class="relative w-full bg-gradient-to-r from-yellow-400 to-yellow-500 hover:from-yellow-500 hover:to-yellow-600 text-white py-3 px-6 rounded-lg font-semibold transition-all duration-300 text-center block shadow-lg hover:shadow-yellow-400/50 group-hover:scale-105"
                        >
                            <span class="relative z-10">{{ service.name === 'Income Tax Filing' ? __('marketing.file_my_tax') : __('marketing.get_started') }}</span>
                            <div class="absolute inset-0 bg-white rounded-lg opacity-0 group-hover:opacity-20 transition-opacity duration-300"></div>
                        </button>
                        
                        <!-- Bottom accent line -->
                        <div class="absolute bottom-0 left-1/2 transform -translate-x-1/2 w-0 h-1 bg-gradient-to-r from-yellow-400 via-yellow-500 to-yellow-600 group-hover:w-full transition-all duration-500 rounded-b-2xl"></div>
                    </div>
                </div>

                <!-- Service Links for Better Internal Linking -->
                <div class="mt-12">
                    <h2 class="text-2xl lg:text-3xl font-bold text-white text-center mb-4">Compare Services in Detail</h2>
                    <p class="text-gray-300 text-center max-w-3xl mx-auto mb-8">
                        Explore each service page for full scope, timelines, and filing details.
                    </p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 max-w-5xl mx-auto">
                        <Link :href="route('services.llc')" class="bg-gray-800/40 border border-gray-700/50 rounded-lg px-5 py-3 text-gray-200 hover:border-yellow-400/50 hover:text-yellow-400 transition-all duration-300 text-center font-medium">
                            LLC Formation Pricing Details
                        </Link>
                        <Link :href="route('services.c-corp')" class="bg-gray-800/40 border border-gray-700/50 rounded-lg px-5 py-3 text-gray-200 hover:border-yellow-400/50 hover:text-yellow-400 transition-all duration-300 text-center font-medium">
                            C-Corporation Package Breakdown
                        </Link>
                        <Link :href="route('services.s-corp')" class="bg-gray-800/40 border border-gray-700/50 rounded-lg px-5 py-3 text-gray-200 hover:border-yellow-400/50 hover:text-yellow-400 transition-all duration-300 text-center font-medium">
                            S-Corporation Service Costs
                        </Link>
                        <Link :href="route('services.nonprofit')" class="bg-gray-800/40 border border-gray-700/50 rounded-lg px-5 py-3 text-gray-200 hover:border-yellow-400/50 hover:text-yellow-400 transition-all duration-300 text-center font-medium">
                            Nonprofit Formation Fees
                        </Link>
                        <Link :href="route('services.green-card')" class="bg-gray-800/40 border border-gray-700/50 rounded-lg px-5 py-3 text-gray-200 hover:border-yellow-400/50 hover:text-yellow-400 transition-all duration-300 text-center font-medium">
                            Green Card Lottery Plans
                        </Link>
                        <Link :href="route('services.income-tax')" class="bg-gray-800/40 border border-gray-700/50 rounded-lg px-5 py-3 text-gray-200 hover:border-yellow-400/50 hover:text-yellow-400 transition-all duration-300 text-center font-medium">
                            Income Tax Filing Packages
                        </Link>
                    </div>
                </div>

                <!-- Features Comparison -->
                <div class="mt-20">
                    <h2 class="text-3xl lg:text-4xl font-bold text-white text-center mb-4">{{ __('marketing.whats_included') }}</h2>
                    <p class="text-xl text-gray-300 text-center mb-12 max-w-3xl mx-auto">
                        {{ __('marketing.compare_features') }}
                    </p>
                    
                    <div class="overflow-x-auto pb-4">
                        <div class="rounded-2xl shadow-2xl" style="min-width: 900px; background-color: rgba(15, 30, 50, 0.95);">
                            <div class="grid grid-cols-4 gap-0 items-start">
                                <!-- Feature Column -->
                                <div class="p-8 flex flex-col h-full" style="background-color: rgba(20, 35, 55, 0.8);">
                                    <div class="font-bold text-lg text-white mb-8 h-20 flex items-center">{{ __('marketing.features') }}</div>
                                    <div class="flex-1 flex flex-col space-y-6">
                                        <div class="flex items-center py-3 text-gray-300 text-base font-medium">
                                            <div class="w-5 h-5 mr-3 flex-shrink-0 flex items-center justify-center">
                                                <div class="w-2 h-2 rounded-full bg-yellow-400"></div>
                                            </div>
                                            <span>{{ __('marketing.feature_business_formation') }}</span>
                                        </div>
                                        <div class="flex items-center py-3 text-gray-300 text-base font-medium">
                                            <div class="w-5 h-5 mr-3 flex-shrink-0 flex items-center justify-center">
                                                <div class="w-2 h-2 rounded-full bg-yellow-400"></div>
                                            </div>
                                            <span>{{ __('marketing.feature_registered_agent') }}</span>
                                        </div>
                                        <div class="flex items-center py-3 text-gray-300 text-base font-medium">
                                            <div class="w-5 h-5 mr-3 flex-shrink-0 flex items-center justify-center">
                                                <div class="w-2 h-2 rounded-full bg-yellow-400"></div>
                                            </div>
                                            <span>{{ __('marketing.feature_ein_number') }}</span>
                                        </div>
                                        <div class="flex items-center py-3 text-gray-300 text-base font-medium">
                                            <div class="w-5 h-5 mr-3 flex-shrink-0 flex items-center justify-center">
                                                <div class="w-2 h-2 rounded-full bg-yellow-400"></div>
                                            </div>
                                            <span>{{ __('marketing.feature_operating_agreement') }}</span>
                                        </div>
                                        <div class="flex items-center py-3 text-gray-300 text-base font-medium">
                                            <div class="w-5 h-5 mr-3 flex-shrink-0 flex items-center justify-center">
                                                <div class="w-2 h-2 rounded-full bg-yellow-400"></div>
                                            </div>
                                            <span>{{ __('marketing.feature_expedited_processing') }}</span>
                                        </div>
                                        <div class="flex items-center py-3 text-gray-300 text-base font-medium">
                                            <div class="w-5 h-5 mr-3 flex-shrink-0 flex items-center justify-center">
                                                <div class="w-2 h-2 rounded-full bg-yellow-400"></div>
                                            </div>
                                            <span>{{ __('marketing.feature_priority_support') }}</span>
                                        </div>
                                        <div class="flex items-center py-3 text-gray-300 text-base font-medium">
                                            <div class="w-5 h-5 mr-3 flex-shrink-0 flex items-center justify-center">
                                                <div class="w-2 h-2 rounded-full bg-yellow-400"></div>
                                            </div>
                                            <span>{{ __('marketing.feature_compliance_calendar') }}</span>
                                        </div>
                                        <div class="flex items-center py-3 text-gray-300 text-base font-medium">
                                            <div class="w-5 h-5 mr-3 flex-shrink-0 flex items-center justify-center">
                                                <div class="w-2 h-2 rounded-full bg-yellow-400"></div>
                                            </div>
                                            <span>{{ __('marketing.feature_logo_marketing_kit') }}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Basic Column -->
                                <div class="p-8 flex flex-col h-full">
                                    <div class="font-bold text-lg text-white text-center mb-8 h-20 flex items-center justify-center">{{ __('marketing.tier_basic') }}</div>
                                    <div class="flex-1 flex flex-col space-y-6 text-center">
                                        <div class="py-3 flex items-center justify-center">
                                            <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </div>
                                        <div class="py-3 flex items-center justify-center">
                                            <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </div>
                                        <div class="py-3 flex items-center justify-center">
                                            <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </div>
                                        <div class="py-3 flex items-center justify-center">
                                            <span class="text-gray-400 text-base">{{ __('marketing.tier_template') }}</span>
                                        </div>
                                        <div class="py-3 flex items-center justify-center">
                                            <span class="text-gray-500 text-lg">-</span>
                                        </div>
                                        <div class="py-3 flex items-center justify-center">
                                            <span class="text-gray-400 text-base">{{ __('marketing.tier_email') }}</span>
                                        </div>
                                        <div class="py-3 flex items-center justify-center">
                                            <span class="text-gray-500 text-lg">-</span>
                                        </div>
                                        <div class="py-3 flex items-center justify-center">
                                            <span class="text-gray-500 text-lg">-</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Professional Column (Popular) -->
                                <div class="p-8 flex flex-col border border-yellow-600/60 relative h-full" style="background-color: rgba(30, 45, 60, 0.5);">
                                    <div class="text-center mb-8 h-20 flex flex-col items-center justify-center">
                                        <div class="inline-block px-8 py-3 bg-yellow-400 rounded-lg mb-2">
                                            <div class="font-bold text-xl text-gray-900">{{ __('marketing.tier_professional') }}</div>
                                        </div>
                                        <div>
                                            <span class="inline-block px-5 py-1.5 bg-yellow-600/70 text-yellow-400 text-xs font-bold rounded-full uppercase tracking-wider">{{ __('marketing.tier_popular') }}</span>
                                        </div>
                                    </div>
                                    <div class="flex-1 flex flex-col space-y-6 text-center">
                                        <div class="py-3 flex items-center justify-center">
                                            <svg class="w-6 h-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </div>
                                        <div class="py-3 flex items-center justify-center">
                                            <span class="text-base text-yellow-400 bg-yellow-500/20 px-4 py-1.5 rounded-full font-semibold">{{ __('marketing.tier_1_year') }}</span>
                                        </div>
                                        <div class="py-3 flex items-center justify-center">
                                            <svg class="w-6 h-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </div>
                                        <div class="py-3 flex items-center justify-center">
                                            <span class="text-base text-yellow-400 bg-yellow-500/20 px-4 py-1.5 rounded-full font-semibold">{{ __('marketing.tier_custom') }}</span>
                                        </div>
                                        <div class="py-3 flex items-center justify-center">
                                            <svg class="w-6 h-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </div>
                                        <div class="py-3 flex items-center justify-center">
                                            <span class="text-base text-yellow-400 bg-yellow-500/20 px-4 py-1.5 rounded-full font-semibold">{{ __('marketing.tier_priority') }}</span>
                                        </div>
                                        <div class="py-3 flex items-center justify-center">
                                            <svg class="w-6 h-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </div>
                                        <div class="py-3 flex items-center justify-center">
                                            <span class="text-gray-500 text-lg">-</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Premium Column -->
                                <div class="p-8 flex flex-col h-full">
                                    <div class="font-bold text-lg text-white text-center mb-8 h-20 flex items-center justify-center">{{ __('marketing.tier_premium') }}</div>
                                    <div class="flex-1 flex flex-col space-y-6 text-center">
                                        <div class="py-3 flex items-center justify-center">
                                            <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </div>
                                        <div class="py-3 flex items-center justify-center">
                                            <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </div>
                                        <div class="py-3 flex items-center justify-center">
                                            <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </div>
                                        <div class="py-3 flex items-center justify-center">
                                            <span class="text-gray-400 text-base">{{ __('marketing.tier_custom') }}</span>
                                        </div>
                                        <div class="py-3 flex items-center justify-center">
                                            <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </div>
                                        <div class="py-3 flex items-center justify-center">
                                            <span class="text-gray-400 text-base">{{ __('marketing.tier_dedicated') }}</span>
                                        </div>
                                        <div class="py-3 flex items-center justify-center">
                                            <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </div>
                                        <div class="py-3 flex items-center justify-center">
                                            <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Guarantee Section -->
                <div class="mt-20 relative">
                    <div class="bg-gradient-to-br from-gray-800/40 to-gray-900/40 backdrop-blur-sm border border-gray-700/50 rounded-2xl p-12 text-center shadow-2xl">
                        <!-- Badge Icon -->
                        <div class="inline-block mb-6">
                            <div class="w-20 h-20 bg-gradient-to-br from-yellow-400 to-yellow-500 rounded-full flex items-center justify-center mx-auto shadow-lg">
                                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                            </div>
                        </div>
                        
                        <h2 class="text-3xl font-bold text-white mb-4">{{ __('marketing.satisfaction_guarantee_title') }}</h2>
                        <p class="text-xl text-gray-300 max-w-2xl mx-auto leading-relaxed">
                            {{ __('marketing.satisfaction_guarantee_desc') }}
                        </p>
                        
                        <!-- Trust Indicators -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mt-12">
                            <div class="flex flex-col items-center">
                                <div class="w-12 h-12 bg-yellow-400/20 rounded-lg flex items-center justify-center mb-3">
                                    <svg class="w-6 h-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                    </svg>
                                </div>
                                <p class="text-white font-semibold mb-1">{{ __('marketing.fast_processing_title') }}</p>
                                <p class="text-gray-400 text-sm">{{ __('marketing.fast_processing_desc') }}</p>
                            </div>
                            <div class="flex flex-col items-center">
                                <div class="w-12 h-12 bg-yellow-400/20 rounded-lg flex items-center justify-center mb-3">
                                    <svg class="w-6 h-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                    </svg>
                                </div>
                                <p class="text-white font-semibold mb-1">{{ __('marketing.expert_support_title') }}</p>
                                <p class="text-gray-400 text-sm">{{ __('marketing.expert_support_desc_short') }}</p>
                            </div>
                            <div class="flex flex-col items-center">
                                <div class="w-12 h-12 bg-yellow-400/20 rounded-lg flex items-center justify-center mb-3">
                                    <svg class="w-6 h-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                </div>
                                <p class="text-white font-semibold mb-1">{{ __('marketing.secure_process_title') }}</p>
                                <p class="text-gray-400 text-sm">{{ __('marketing.secure_process_desc') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Social Sharing -->
                <div class="mt-12 text-center">
                    <p class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-3">Share This Pricing Page</p>
                    <div class="flex flex-wrap justify-center gap-3">
                        <a href="https://twitter.com/intent/tweet?url=https%3A%2F%2Fcorpius.net%2Fpricing&text=Transparent+Business+Formation+Pricing+at+CORPIUS"
                           target="_blank" rel="noopener noreferrer"
                           class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-black text-white text-sm font-medium hover:opacity-80 transition-opacity">
                            Share on X
                        </a>
                        <a href="https://www.facebook.com/sharer/sharer.php?u=https%3A%2F%2Fcorpius.net%2Fpricing"
                           target="_blank" rel="noopener noreferrer"
                           class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-[#1877F2] text-white text-sm font-medium hover:opacity-80 transition-opacity">
                            Share on Facebook
                        </a>
                        <a href="https://www.linkedin.com/shareArticle?mini=true&url=https%3A%2F%2Fcorpius.net%2Fpricing&title=Business+Formation+Pricing"
                           target="_blank" rel="noopener noreferrer"
                           class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-[#0A66C2] text-white text-sm font-medium hover:opacity-80 transition-opacity">
                            Share on LinkedIn
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- FAQ Section -->
        <section class="py-20" style="background-color: #0b1e33;" id="faq">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-14">
                    <h2 class="text-3xl lg:text-4xl font-bold text-white mb-4">Frequently Asked Questions</h2>
                    <p class="text-lg text-gray-300">Everything you need to know about our business formation services and pricing.</p>
                </div>
                <div class="space-y-4">
                    <details class="border border-white/10 rounded-xl p-6 group" style="background-color: #0f2540;" open>
                        <summary class="flex justify-between items-center cursor-pointer text-white font-semibold text-lg list-none">
                            How much does it cost to form an LLC with CORPIUS?
                            <span class="ml-4 text-yellow-400 text-2xl group-open:rotate-45 transition-transform">+</span>
                        </summary>
                        <p class="mt-4 text-gray-300 leading-relaxed">
                            LLC formation with CORPIUS starts at <strong class="text-white">$1,480</strong> for the Starter package,
                            which includes state filing fees, EIN (Employer Identification Number) registration, a registered agent
                            for the first year, and an operating agreement template. The Premium package is $1,650 and adds priority
                            processing and compliance alerts. All prices are fully transparent — there are no hidden fees or
                            unexpected charges.
                        </p>
                    </details>

                    <details class="border border-white/10 rounded-xl p-6 group" style="background-color: #0f2540;">
                        <summary class="flex justify-between items-center cursor-pointer text-white font-semibold text-lg list-none">
                            What is included in CORPIUS business formation packages?
                            <span class="ml-4 text-yellow-400 text-2xl group-open:rotate-45 transition-transform">+</span>
                        </summary>
                        <p class="mt-4 text-gray-300 leading-relaxed">
                            Every CORPIUS formation package includes: preparation and filing of your Articles of Formation or
                            Incorporation with the state, Employer Identification Number (EIN) application with the IRS, registered
                            agent service for the first year (required in every state), and a dedicated compliance dashboard to
                            track upcoming deadlines. Higher-tier packages include an operating agreement or corporate bylaws,
                            banking resolution, and priority processing. We handle all government paperwork — you focus on building
                            your business.
                        </p>
                    </details>

                    <details class="border border-white/10 rounded-xl p-6 group" style="background-color: #0f2540;">
                        <summary class="flex justify-between items-center cursor-pointer text-white font-semibold text-lg list-none">
                            How long does business formation take?
                            <span class="ml-4 text-yellow-400 text-2xl group-open:rotate-45 transition-transform">+</span>
                        </summary>
                        <p class="mt-4 text-gray-300 leading-relaxed">
                            Processing times vary by state and entity type. Most states process standard LLC formations within
                            <strong class="text-white">3–10 business days</strong>. States with expedited filing services (such as
                            Delaware, Wyoming, and Nevada) can approve formations in as little as 24 hours for an additional
                            expedite fee. CORPIUS Premium packages include priority processing, and we submit your documents
                            promptly after you complete your order. You will receive email confirmation with your official
                            formation documents as soon as the state approves your filing.
                        </p>
                    </details>

                    <details class="border border-white/10 rounded-xl p-6 group" style="background-color: #0f2540;">
                        <summary class="flex justify-between items-center cursor-pointer text-white font-semibold text-lg list-none">
                            What is the difference between an LLC and a C-Corporation?
                            <span class="ml-4 text-yellow-400 text-2xl group-open:rotate-45 transition-transform">+</span>
                        </summary>
                        <p class="mt-4 text-gray-300 leading-relaxed">
                            An <strong class="text-white">LLC (Limited Liability Company)</strong> is the most flexible business
                            structure. Profits and losses pass through to the owners' personal tax returns (avoiding double
                            taxation), and there are minimal formalities required. LLCs are ideal for freelancers, sole
                            proprietors, small businesses, and real estate investors.<br><br>
                            A <strong class="text-white">C-Corporation</strong> is a separate legal entity that pays its own
                            corporate income tax (currently a flat 21% federal rate). It is the preferred structure for businesses
                            seeking venture capital investment, because it allows for unlimited shareholders, multiple classes of
                            stock, and provides significant legal protections. C-Corps can also offer stock options and benefit
                            from Qualified Small Business Stock (QSBS) exclusions — a major advantage for tech startups.
                        </p>
                    </details>

                    <details class="border border-white/10 rounded-xl p-6 group" style="background-color: #0f2540;">
                        <summary class="flex justify-between items-center cursor-pointer text-white font-semibold text-lg list-none">
                            Do I need a physical address in the state where I form my business?
                            <span class="ml-4 text-yellow-400 text-2xl group-open:rotate-45 transition-transform">+</span>
                        </summary>
                        <p class="mt-4 text-gray-300 leading-relaxed">
                            No. You do not need to live in or have a physical office in the state where you form your business.
                            However, every state requires you to designate a <strong class="text-white">registered agent</strong>
                            — a person or company with a physical street address in that state who is available during business
                            hours to receive legal documents on behalf of your company. CORPIUS provides registered agent service
                            in all 50 states, so you can form your business in any state regardless of where you are located.
                        </p>
                    </details>

                    <details class="border border-white/10 rounded-xl p-6 group" style="background-color: #0f2540;">
                        <summary class="flex justify-between items-center cursor-pointer text-white font-semibold text-lg list-none">
                            Is CORPIUS pricing inclusive of government filing fees?
                            <span class="ml-4 text-yellow-400 text-2xl group-open:rotate-45 transition-transform">+</span>
                        </summary>
                        <p class="mt-4 text-gray-300 leading-relaxed">
                            Yes. CORPIUS packages include all standard state government filing fees. Unlike some competitors who
                            advertise low base prices and then charge state fees separately, our displayed prices are
                            <strong class="text-white">all-inclusive</strong> — no surprise fees at checkout. The only additional
                            costs that may apply are for optional add-on services (such as Certificate of Good Standing, foreign
                            qualification in additional states, or expedited state processing where charged by the state directly).
                        </p>
                    </details>

                    <details class="border border-white/10 rounded-xl p-6 group" style="background-color: #0f2540;">
                        <summary class="flex justify-between items-center cursor-pointer text-white font-semibold text-lg list-none">
                            Can CORPIUS help with post-formation compliance?
                            <span class="ml-4 text-yellow-400 text-2xl group-open:rotate-45 transition-transform">+</span>
                        </summary>
                        <p class="mt-4 text-gray-300 leading-relaxed">
                            Yes. Business formation is just the first step. Every state requires ongoing compliance — annual
                            reports, franchise taxes, registered agent maintenance, and benefit filings. CORPIUS provides a
                            compliance dashboard that tracks all deadlines for your entity across every state where you are
                            registered. We also offer registered agent renewal, annual report filing services, and EIN amendment
                            services to keep your business in good standing year after year.
                        </p>
                    </details>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="py-20" style="background-color: #0b1e33;">
            <div class="max-w-4xl mx-auto text-center px-4 sm:px-6 lg:px-8">
                <h2 class="text-3xl lg:text-4xl font-bold text-white mb-6">
                    {{ __('marketing.ready_to_get_started') }}
                </h2>
                <p class="text-xl text-gray-300 mb-8">
                    {{ __('marketing.choose_structure_and_start') }}
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center items-center mb-6">
                    <button type="button" @click="openAuth()" class="bg-yellow-400 hover:bg-yellow-500 text-white px-8 py-4 rounded-lg text-lg font-semibold transition-all duration-300 shadow-lg hover:shadow-xl inline-block">
                        {{ __('marketing.start_business_today') }}
                    </button>
                    <Link :href="route('marketing.contact')" class="bg-transparent border-2 border-yellow-400 hover:bg-yellow-400/10 text-yellow-400 px-8 py-4 rounded-lg text-lg font-semibold transition-all duration-300 shadow-lg hover:shadow-xl inline-block">
                        {{ __('marketing.talk_to_expert') }}
                    </Link>
                </div>
                <p class="text-sm text-gray-400 mt-4">
                    {{ __('marketing.formation_time') }}
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