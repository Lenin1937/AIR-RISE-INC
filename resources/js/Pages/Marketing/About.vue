<script setup>
import { useTranslations } from '@/Composables/useTranslations';
import MarketingLayout from '@/Layouts/MarketingLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const { __ } = useTranslations();
</script>

<template>
    <Head title="About CORPIUS | Business Formation Experts">
        <meta head-key="description" name="description" content="CORPIUS is a trusted business formation company helping entrepreneurs launch LLCs, corporations, and nonprofits with expert guidance and support." />
        <link head-key="canonical" rel="canonical" href="https://corpius.net/about" />
        <meta head-key="og:title" property="og:title" content="About CORPIUS | Business Formation Experts" />
        <meta head-key="og:description" property="og:description" content="CORPIUS is a trusted business formation company helping entrepreneurs launch LLCs, corporations, and nonprofits with expert guidance and support." />
        <meta head-key="og:url" property="og:url" content="https://corpius.net/about" />
        <meta head-key="twitter:title" name="twitter:title" content="About CORPIUS | Business Formation Experts" />
        <meta head-key="twitter:description" name="twitter:description" content="CORPIUS helps entrepreneurs launch LLCs, corporations, and nonprofits with expert guidance and support." />
    </Head>

    <MarketingLayout>
        <!-- Hero Section -->
        <section class="bg-gradient-to-br from-navy-900 via-blue-900 to-navy-900 py-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h1 class="text-4xl lg:text-5xl font-bold text-yellow-400 mb-6 gradient-text-shimmer">
                    Trusted U.S. Business Formation — LLC, C-Corp, Nonprofit & More
                </h1>
                <p class="text-xl lg:text-2xl text-gray-300 mb-8 max-w-3xl mx-auto">
                    {{ __('marketing.about_hero_subtitle') }}
                </p>
            </div>
        </section>

        <!-- Mission Section -->
        <section class="py-20" style="background-color: #0b1e33;">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                    <div>
                        <h2 class="text-3xl lg:text-4xl font-bold text-white mb-6">
                            {{ __('marketing.our_mission') }}
                        </h2>
                        <p class="text-lg text-gray-300 mb-6">
                            {{ __('marketing.mission_text_1') }}
                        </p>
                        <p class="text-lg text-gray-300">
                            {{ __('marketing.mission_text_2') }}
                        </p>
                    </div>
                    <div class="bg-gradient-to-br from-yellow-400 to-yellow-500 rounded-xl p-8 shadow-lg">
                        <div class="grid grid-cols-2 gap-6">
                            <div class="text-center">
                                <div class="text-4xl font-bold text-navy-900">2,500+</div>
                                <div class="text-gray-700 text-sm font-medium mt-2">{{ __('marketing.businesses_formed') }}</div>
                            </div>
                            <div class="text-center">
                                <div class="text-4xl font-bold text-navy-900">50</div>
                                <div class="text-gray-700 text-sm font-medium mt-2">{{ __('marketing.states_covered') }}</div>
                            </div>
                            <div class="text-center">
                                <div class="text-4xl font-bold text-navy-900">99%</div>
                                <div class="text-gray-700 text-sm font-medium mt-2">{{ __('marketing.satisfaction_rate') }}</div>
                            </div>
                            <div class="text-center">
                                <div class="text-4xl font-bold text-navy-900">5+</div>
                                <div class="text-gray-700 text-sm font-medium mt-2">{{ __('marketing.years_experience') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Values Section -->
        <section class="py-20" style="background-color: #0b1e33;">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-3xl lg:text-4xl font-bold text-white mb-6">
                        {{ __('marketing.our_values') }}
                    </h2>
                    <p class="text-lg text-gray-300 max-w-3xl mx-auto">
                        {{ __('marketing.our_values_subtitle') }}
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="border border-gray-600 rounded-xl p-8 shadow-lg" style="background-color: rgba(255, 255, 255, 0.05); backdrop-filter: blur(10px);">
                        <div class="w-12 h-12 bg-yellow-400 rounded-lg flex items-center justify-center mb-6">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #0b1e33;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-white mb-4">{{ __('marketing.value_speed_title') }}</h3>
                        <p class="text-gray-300">
                            {{ __('marketing.value_speed_desc') }}
                        </p>
                    </div>

                    <div class="border border-gray-600 rounded-xl p-8 shadow-lg" style="background-color: rgba(255, 255, 255, 0.05); backdrop-filter: blur(10px);">
                        <div class="w-12 h-12 bg-yellow-400 rounded-lg flex items-center justify-center mb-6">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #0b1e33;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-white mb-4">{{ __('marketing.value_transparency_title') }}</h3>
                        <p class="text-gray-300">
                            {{ __('marketing.value_transparency_desc') }}
                        </p>
                    </div>

                    <div class="border border-gray-600 rounded-xl p-8 shadow-lg" style="background-color: rgba(255, 255, 255, 0.05); backdrop-filter: blur(10px);">
                        <div class="w-12 h-12 bg-yellow-400 rounded-lg flex items-center justify-center mb-6">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #0b1e33;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192L5.636 18.364M12 2.25a9.75 9.75 0 109.75 9.75A9.75 9.75 0 0012 2.25z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-white mb-4">{{ __('marketing.value_support_title') }}</h3>
                        <p class="text-gray-300">
                            {{ __('marketing.value_support_desc') }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- What You Can Expect Section -->
        <section class="py-20" style="background-color: #0b1e33;">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-3xl lg:text-4xl font-bold text-white mb-6">
                        What You Can Expect from <span class="text-yellow-400">CORPIUS</span>
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

                    <!-- Item 1 -->
                    <div class="flex items-start space-x-5 border border-gray-700 rounded-xl p-6 shadow-lg" style="background-color: rgba(255,255,255,0.04);">
                        <div class="flex-shrink-0 w-10 h-10 bg-yellow-400 rounded-full flex items-center justify-center mt-1">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" style="color: #0b1e33;">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-white mb-2">Formation Accuracy &amp; Compliance</h3>
                            <p class="text-gray-300 text-sm leading-relaxed">Every filing is completed following strict state and federal requirements to ensure your business is established correctly from day one.</p>
                        </div>
                    </div>

                    <!-- Item 2 -->
                    <div class="flex items-start space-x-5 border border-gray-700 rounded-xl p-6 shadow-lg" style="background-color: rgba(255,255,255,0.04);">
                        <div class="flex-shrink-0 w-10 h-10 bg-yellow-400 rounded-full flex items-center justify-center mt-1">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" style="color: #0b1e33;">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-white mb-2">End-to-End Support</h3>
                            <p class="text-gray-300 text-sm leading-relaxed">From entity selection to EIN, bylaws/operating agreements, tax setup, and post-formation compliance — our specialists guide you through every step.</p>
                        </div>
                    </div>

                    <!-- Item 3 -->
                    <div class="flex items-start space-x-5 border border-gray-700 rounded-xl p-6 shadow-lg" style="background-color: rgba(255,255,255,0.04);">
                        <div class="flex-shrink-0 w-10 h-10 bg-yellow-400 rounded-full flex items-center justify-center mt-1">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" style="color: #0b1e33;">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-white mb-2">Fast Turnaround &amp; Priority Handling</h3>
                            <p class="text-gray-300 text-sm leading-relaxed">Standard processing takes as little as 1–3 business days, with expedited options available for urgent cases.</p>
                        </div>
                    </div>

                    <!-- Item 4 -->
                    <div class="flex items-start space-x-5 border border-gray-700 rounded-xl p-6 shadow-lg" style="background-color: rgba(255,255,255,0.04);">
                        <div class="flex-shrink-0 w-10 h-10 bg-yellow-400 rounded-full flex items-center justify-center mt-1">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" style="color: #0b1e33;">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-white mb-2">Transparent Pricing With No Hidden Fees</h3>
                            <p class="text-gray-300 text-sm leading-relaxed">All-inclusive packages cover formation, EIN, registered agent service, and compliance reminders — eliminating unexpected costs.</p>
                        </div>
                    </div>

                    <!-- Item 5 -->
                    <div class="flex items-start space-x-5 border border-gray-700 rounded-xl p-6 shadow-lg" style="background-color: rgba(255,255,255,0.04);">
                        <div class="flex-shrink-0 w-10 h-10 bg-yellow-400 rounded-full flex items-center justify-center mt-1">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" style="color: #0b1e33;">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-white mb-2">Ongoing Compliance Monitoring</h3>
                            <p class="text-gray-300 text-sm leading-relaxed">We track important renewal deadlines, state updates, and federal filings to help you maintain good standing year-round.</p>
                        </div>
                    </div>

                    <!-- Item 6 -->
                    <div class="flex items-start space-x-5 border border-gray-700 rounded-xl p-6 shadow-lg" style="background-color: rgba(255,255,255,0.04);">
                        <div class="flex-shrink-0 w-10 h-10 bg-yellow-400 rounded-full flex items-center justify-center mt-1">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" style="color: #0b1e33;">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-white mb-2">Secure Document Management</h3>
                            <p class="text-gray-300 text-sm leading-relaxed">All formation files, certificates, tax IDs, and corporate records are stored securely and accessible anytime.</p>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        <!-- Why Choose Us Section -->
        <section class="py-20" style="background-color: #0b1e33;">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-3xl lg:text-4xl font-bold text-white mb-6">
                        {{ __('marketing.why_choose_us') }}
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="flex items-start space-x-4">
                        <div class="w-8 h-8 bg-yellow-400 rounded-full flex items-center justify-center flex-shrink-0 mt-1">
                            <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-white mb-2">{{ __('marketing.proven_track_record') }}</h3>
                            <p class="text-gray-300">{{ __('marketing.proven_track_record_desc') }}</p>
                        </div>
                    </div>

                    <div class="flex items-start space-x-4">
                        <div class="w-8 h-8 bg-yellow-400 rounded-full flex items-center justify-center flex-shrink-0 mt-1">
                            <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-white mb-2">{{ __('marketing.all_inclusive_packages') }}</h3>
                            <p class="text-gray-300">{{ __('marketing.all_inclusive_packages_desc') }}</p>
                        </div>
                    </div>

                    <div class="flex items-start space-x-4">
                        <div class="w-8 h-8 bg-yellow-400 rounded-full flex items-center justify-center flex-shrink-0 mt-1">
                            <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-white mb-2">{{ __('marketing.expert_guidance') }}</h3>
                            <p class="text-gray-300">{{ __('marketing.expert_guidance_desc') }}</p>
                        </div>
                    </div>

                    <div class="flex items-start space-x-4">
                        <div class="w-8 h-8 bg-yellow-400 rounded-full flex items-center justify-center flex-shrink-0 mt-1">
                            <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-white mb-2">{{ __('marketing.fast_turnaround') }}</h3>
                            <p class="text-gray-300">{{ __('marketing.fast_turnaround_desc') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Company Info Section -->
        <section class="py-12" style="background-color: #0b1e33;">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="border border-gray-700 rounded-xl p-8" style="background-color: rgba(255,255,255,0.04);">
                    <h2 class="text-2xl font-bold text-white mb-6 text-center">Our Company Info</h2>
                    <div class="flex flex-col md:flex-row items-center justify-center gap-6 text-gray-300">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-yellow-400 rounded-full flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #0b1e33;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            </div>
                            <span>50 Central Park S #24A, New York, NY 10019</span>
                        </div>
                        <div class="hidden md:block w-px h-8 bg-gray-700"></div>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-yellow-400 rounded-full flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #0b1e33;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            </div>
                            <a href="tel:+13473433353" class="hover:text-yellow-400 transition-colors">+1(347) 343-3353</a>
                        </div>
                        <div class="hidden md:block w-px h-8 bg-gray-700"></div>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-yellow-400 rounded-full flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #0b1e33;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            </div>
                            <span>Powered By <span class="text-yellow-400 font-semibold">AIR RISE INC</span> &amp; <span class="text-yellow-400 font-semibold">REVOLD AI</span></span>
                        </div>
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
                    {{ __('marketing.ready_to_start_subtitle') }}
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                    <Link :href="route('register')" class="bg-yellow-400 hover:bg-yellow-500 text-white px-8 py-4 rounded-lg text-lg font-semibold transition-all duration-300 shadow-lg hover:shadow-xl inline-block">
                        {{ __('marketing.get_started_today') }}
                    </Link>
                    <Link :href="route('marketing.contact')" class="bg-transparent border-2 border-yellow-400 hover:bg-yellow-400/10 text-yellow-400 px-8 py-4 rounded-lg text-lg font-semibold transition-all duration-300 shadow-lg hover:shadow-xl inline-block">
                        {{ __('marketing.talk_to_expert') }}
                    </Link>
                </div>
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