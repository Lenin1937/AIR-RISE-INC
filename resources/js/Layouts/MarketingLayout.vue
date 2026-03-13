<script setup>
import ChatWidget from '@/Components/ChatWidget.vue';
import ClientOnly from '@/Components/ClientOnly.vue';
import LanguageSwitcher from '@/Components/LanguageSwitcher.vue';
import { useTranslations } from '@/Composables/useTranslations';
import { Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const { __ } = useTranslations();
const page = usePage();
const currentLocale = computed(() => page.props.locale || 'en');

defineProps({
    title: String,
});

const mobileMenuOpen = ref(false);
</script>

<template>
    <div class="min-h-screen" style="background-color: #0b1e33;">
        <!-- Navigation -->
        <nav class="fixed top-0 left-0 right-0 z-50 shadow-lg" style="background-color: #0b1e33;">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-20">

                    <!-- Logo — left -->
                    <Link :href="route('home')" class="flex items-center flex-shrink-0">
                        <img src="/logo.png" alt="CORPIUS Logo" class="h-12 w-auto">
                        <span class="ml-2 text-xl font-bold text-white">CORPIUS</span>
                    </Link>

                    <!-- Desktop Nav — center -->
                    <div class="hidden lg:flex items-center gap-x-1 mx-auto">
                        <Link :href="route('home')" class="text-gray-300 hover:text-yellow-400 px-3 py-2 text-lg font-medium whitespace-nowrap rounded-md hover:bg-white/[0.05] transition-colors">
                            Home
                        </Link>
                        <div class="relative group">
                            <button class="text-gray-300 hover:text-yellow-400 px-3 py-2 text-lg font-medium flex items-center whitespace-nowrap rounded-md hover:bg-white/[0.05] transition-colors">
                                Services
                                <svg class="ml-1 h-3 w-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div class="absolute left-0 top-full mt-1 w-56 bg-white rounded-xl shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50 border border-gray-100">
                                <div class="py-2">
                                    <Link :href="route('services.c-corp')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-yellow-50 hover:text-[#0b1e33]">C-Corporation</Link>
                                    <Link :href="route('services.s-corp')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-yellow-50 hover:text-[#0b1e33]">S-Corporation</Link>
                                    <Link :href="route('services.llc')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-yellow-50 hover:text-[#0b1e33]">LLC</Link>
                                    <Link :href="route('services.nonprofit')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-yellow-50 hover:text-[#0b1e33]">Nonprofit</Link>
                                    <Link :href="route('services.green-card')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-yellow-50 hover:text-[#0b1e33]">Green Card</Link>
                                    <Link :href="route('services.income-tax')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-yellow-50 hover:text-[#0b1e33]">Income Tax</Link>
                                </div>
                            </div>
                        </div>
                        <Link :href="route('marketing.pricing')" class="text-gray-300 hover:text-yellow-400 px-3 py-2 text-lg font-medium whitespace-nowrap rounded-md hover:bg-white/[0.05] transition-colors">
                            Pricing
                        </Link>
                        <Link :href="route('marketing.about')" class="text-gray-300 hover:text-yellow-400 px-3 py-2 text-lg font-medium whitespace-nowrap rounded-md hover:bg-white/[0.05] transition-colors">
                            About
                        </Link>
                        <Link :href="route('marketing.contact')" class="text-gray-300 hover:text-yellow-400 px-3 py-2 text-lg font-medium whitespace-nowrap rounded-md hover:bg-white/[0.05] transition-colors">
                            Contact
                        </Link>
                        <Link :href="route('marketing.knowledge-base.index')" class="text-gray-300 hover:text-yellow-400 px-3 py-2 text-lg font-medium whitespace-nowrap rounded-md hover:bg-white/[0.05] transition-colors">
                            Knowledge Base
                        </Link>
                        <Link :href="route('blog.index')" class="text-gray-300 hover:text-yellow-400 px-3 py-2 text-lg font-medium whitespace-nowrap rounded-md hover:bg-white/[0.05] transition-colors">
                            Blog
                        </Link>
                    </div>

                    <!-- Auth + Mobile burger — right -->
                    <div class="flex items-center gap-x-1 flex-shrink-0">
                        <div class="hidden lg:flex items-center gap-x-1">
                            <LanguageSwitcher :locale="currentLocale" buttonClass="text-gray-300 hover:text-yellow-400 text-lg" />
                            <Link :href="route('login')" class="text-gray-300 hover:text-yellow-400 px-3 py-2 text-lg font-medium whitespace-nowrap transition-colors">
                                Sign In
                            </Link>
                            <Link :href="route('register')" class="bg-yellow-400 hover:bg-yellow-500 text-[#0b1e33] px-5 py-2 rounded-lg text-lg font-semibold whitespace-nowrap transition-colors">
                                Get Started
                            </Link>
                        </div>
                        <button @click="mobileMenuOpen = !mobileMenuOpen" class="lg:hidden text-gray-300 hover:text-white p-2">
                            <svg v-if="!mobileMenuOpen" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                            <svg v-else class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                </div>

                <!-- Mobile Navigation -->
                <div v-show="mobileMenuOpen" class="lg:hidden border-t border-white/[0.08] bg-[#0a1a2e]">
                    <div class="px-2 pt-2 pb-4 space-y-1">
                        <Link :href="route('home')" @click="mobileMenuOpen = false" class="text-gray-300 hover:text-yellow-400 block px-3 py-2.5 rounded-lg text-base font-medium hover:bg-white/[0.05] transition">
                            Home
                        </Link>
                        <div class="space-y-1">
                            <div class="text-gray-400 px-3 py-1.5 text-xs font-semibold uppercase tracking-widest">Services</div>
                            <Link :href="route('services.c-corp')" @click="mobileMenuOpen = false" class="text-gray-300 hover:text-yellow-400 block px-5 py-2 rounded-lg text-sm hover:bg-white/[0.05] transition">
                                C-Corporation
                            </Link>
                            <Link :href="route('services.s-corp')" @click="mobileMenuOpen = false" class="text-gray-300 hover:text-yellow-400 block px-5 py-2 rounded-lg text-sm hover:bg-white/[0.05] transition">
                                S-Corporation
                            </Link>
                            <Link :href="route('services.llc')" @click="mobileMenuOpen = false" class="text-gray-300 hover:text-yellow-400 block px-5 py-2 rounded-lg text-sm hover:bg-white/[0.05] transition">
                                LLC
                            </Link>
                            <Link :href="route('services.nonprofit')" @click="mobileMenuOpen = false" class="text-gray-300 hover:text-yellow-400 block px-5 py-2 rounded-lg text-sm hover:bg-white/[0.05] transition">
                                Nonprofit
                            </Link>
                            <Link :href="route('services.green-card')" @click="mobileMenuOpen = false" class="text-gray-300 hover:text-yellow-400 block px-5 py-2 rounded-lg text-sm hover:bg-white/[0.05] transition">
                                Green Card Lottery
                            </Link>
                            <Link :href="route('services.income-tax')" @click="mobileMenuOpen = false" class="text-gray-300 hover:text-yellow-400 block px-5 py-2 rounded-lg text-sm hover:bg-white/[0.05] transition">
                                Income Tax Filing
                            </Link>
                        </div>
                        <Link :href="route('marketing.pricing')" @click="mobileMenuOpen = false" class="text-gray-300 hover:text-yellow-400 block px-3 py-2.5 rounded-lg text-base font-medium hover:bg-white/[0.05] transition">
                            Pricing
                        </Link>
                        <Link :href="route('marketing.about')" @click="mobileMenuOpen = false" class="text-gray-300 hover:text-yellow-400 block px-3 py-2.5 rounded-lg text-base font-medium hover:bg-white/[0.05] transition">
                            About
                        </Link>
                        <Link :href="route('marketing.contact')" @click="mobileMenuOpen = false" class="text-gray-300 hover:text-yellow-400 block px-3 py-2.5 rounded-lg text-base font-medium hover:bg-white/[0.05] transition">
                            Contact
                        </Link>
                        <Link :href="route('marketing.knowledge-base.index')" @click="mobileMenuOpen = false" class="text-gray-300 hover:text-yellow-400 block px-3 py-2.5 rounded-lg text-base font-medium hover:bg-white/[0.05] transition">
                            Knowledge Base
                        </Link>
                        <Link :href="route('blog.index')" @click="mobileMenuOpen = false" class="text-gray-300 hover:text-yellow-400 block px-3 py-2.5 rounded-lg text-base font-medium hover:bg-white/[0.05] transition">
                            Blog
                        </Link>
                        <div class="pt-3 border-t border-white/[0.08] flex flex-col gap-2">
                            <Link :href="route('login')" @click="mobileMenuOpen = false" class="text-gray-300 hover:text-yellow-400 block px-3 py-2.5 rounded-lg text-base font-medium hover:bg-white/[0.05] transition">
                                Sign In
                            </Link>
                            <Link :href="route('register')" @click="mobileMenuOpen = false" class="bg-yellow-400 hover:bg-yellow-500 text-[#0b1e33] block px-3 py-3 rounded-lg text-base font-semibold text-center transition">
                                Get Started
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="pt-20">
            <slot />
        </main>

        <!-- Footer -->
        <footer class="text-white" style="background-color: #0b1e33;">
            <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                    <!-- Company Info -->
                    <div class="md:col-span-1">
                        <div class="flex items-center mb-4">
                            <img src="/logo.png" alt="CORPIUS Logo" class="h-10 w-auto">
                            <span class="ml-3 text-xl font-bold">CORPIUS</span>
                        </div>
                        <p class="text-gray-300 text-sm">
                            Document preparation and filing services for U.S. business formation, helping entrepreneurs and established companies.
                        </p>
                    </div>

                    <!-- Services -->
                    <div>
                        <h3 class="text-sm font-semibold text-yellow-400 uppercase tracking-wider mb-4">{{ __('marketing.services') }}</h3>
                        <ul class="space-y-2">
                            <li><Link :href="route('services.c-corp')" class="text-gray-300 hover:text-yellow-400 text-sm">{{ __('marketing.c_corporation') }}</Link></li>
                            <li><Link :href="route('services.s-corp')" class="text-gray-300 hover:text-yellow-400 text-sm">{{ __('marketing.s_corporation') }}</Link></li>
                            <li><Link :href="route('services.llc')" class="text-gray-300 hover:text-yellow-400 text-sm">{{ __('marketing.llc') }}</Link></li>
                            <li><Link :href="route('services.nonprofit')" class="text-gray-300 hover:text-yellow-400 text-sm">{{ __('marketing.nonprofit') }}</Link></li>
                            <li><Link :href="route('services.green-card')" class="text-gray-300 hover:text-yellow-400 text-sm">{{ __('marketing.green_card') }}</Link></li>
                            <li><Link :href="route('services.income-tax')" class="text-gray-300 hover:text-yellow-400 text-sm">{{ __('marketing.income_tax') }}</Link></li>
                        </ul>
                    </div>

                    <!-- Company -->
                    <div>
                        <h3 class="text-sm font-semibold text-yellow-400 uppercase tracking-wider mb-4">{{ __('marketing.company') }}</h3>
                        <ul class="space-y-2">
                            <li><Link :href="route('marketing.about')" class="text-gray-300 hover:text-yellow-400 text-sm">{{ __('marketing.about') }}</Link></li>
                            <li><Link :href="route('marketing.pricing')" class="text-gray-300 hover:text-yellow-400 text-sm">{{ __('marketing.pricing') }}</Link></li>
                            <li><Link :href="route('marketing.contact')" class="text-gray-300 hover:text-yellow-400 text-sm">{{ __('marketing.contact') }}</Link></li>
                            <li><Link :href="route('marketing.knowledge-base.index')" class="text-gray-300 hover:text-yellow-400 text-sm">{{ __('marketing.knowledge_base') }}</Link></li>
                            <li><Link :href="route('blog.index')" class="text-gray-300 hover:text-yellow-400 text-sm">Blog</Link></li>
                            <li>
                                <a href="https://g.page/r/CfuWyOpPkRL5EBM/review" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 text-yellow-400 hover:text-yellow-300 text-sm font-medium">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M12.545 10.239v3.821h5.445c-.712 2.315-2.647 3.972-5.445 3.972a6.033 6.033 0 110-12.064c1.498 0 2.866.549 3.921 1.453l2.814-2.814A9.969 9.969 0 0012.545 2C7.021 2 2.543 6.477 2.543 12s4.478 10 10.002 10c8.396 0 10.249-7.85 9.426-11.748l-9.426-.013z"/></svg>
                                    Review Us on Google
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- Legal -->
                    <div>
                        <h3 class="text-sm font-semibold text-yellow-400 uppercase tracking-wider mb-4">{{ __('marketing.legal') }}</h3>
                        <ul class="space-y-2">
                            <li><Link :href="route('legal.privacy-policy')" class="text-gray-300 hover:text-yellow-400 text-sm">{{ __('marketing.privacy_policy') }}</Link></li>
                            <li><Link :href="route('legal.terms-of-service')" class="text-gray-300 hover:text-yellow-400 text-sm">{{ __('marketing.terms_of_service') }}</Link></li>
                            <li><Link :href="route('legal.cookie-policy')" class="text-gray-300 hover:text-yellow-400 text-sm">{{ __('marketing.cookie_policy') }}</Link></li>
                            <li><Link :href="route('legal.compliance')" class="text-gray-300 hover:text-yellow-400 text-sm">{{ __('marketing.compliance') }}</Link></li>
                            <li><Link :href="route('legal.refund-policy')" class="text-gray-300 hover:text-yellow-400 text-sm">{{ __('marketing.refund_policy') }}</Link></li>
                            <li><Link :href="route('legal.disclaimer')" class="text-gray-300 hover:text-yellow-400 text-sm">{{ __('marketing.legal_disclaimer') }}</Link></li>
                            <li><Link :href="route('legal.security-policy')" class="text-gray-300 hover:text-yellow-400 text-sm">{{ __('marketing.security_policy') }}</Link></li>
                            <li><Link :href="route('legal.incident-response-policy')" class="text-gray-300 hover:text-yellow-400 text-sm">{{ __('marketing.incident_response_policy') }}</Link></li>
                        </ul>
                    </div>
                </div>

                <!-- Legal Disclaimer -->
                <div class="mt-8 pt-8 border-t border-gray-700">
                    <div class="bg-gray-800/50 rounded-lg p-6 mb-6">
                        <h3 class="text-yellow-400 font-semibold text-sm uppercase tracking-wider mb-3">Important Legal Notice</h3>
                        <p class="text-gray-300 text-sm leading-relaxed">
                            <strong>CORPIUS is not a law firm and does not provide legal advice.</strong> We provide self-help document preparation services at your specific direction. Our services are designed to help you prepare and file necessary business formation documents, but we cannot provide legal advice, opinions, or recommendations about your legal rights, remedies, defenses, options, selection of forms, or strategies. Communication with CORPIUS does not create an attorney-client relationship. For legal advice specific to your situation, please consult a licensed attorney in your jurisdiction.
                        </p>
                    </div>
                </div>

                <!-- Bottom Footer -->
                <div class="pt-6 border-t border-gray-700">
                    <!-- Company Info -->
                    <div class="flex flex-col md:flex-row items-center justify-center gap-4 mb-6 text-sm text-gray-400">
                        <span class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-yellow-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            50 Central Park S #24A, New York, NY 10019
                        </span>
                        <span class="hidden md:block text-gray-600">|</span>
                        <a href="tel:+13473433353" class="flex items-center gap-1.5 hover:text-yellow-400 transition-colors">
                            <svg class="w-4 h-4 text-yellow-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            +1(347) 343-3353
                        </a>
                        <span class="hidden md:block text-gray-600">|</span>
                        <span class="text-gray-400">Powered By <span class="text-yellow-400 font-medium">AIR RISE INC</span> &amp; <span class="text-yellow-400 font-medium">REVOLD AI</span></span>
                    </div>
                    <div class="flex flex-col md:flex-row justify-between items-center">
                        <p class="text-gray-300 text-sm">
                            © {{ new Date().getFullYear() }} CORPIUS. All rights reserved.
                        </p>
                        <div class="flex space-x-4 mt-4 md:mt-0">
                            <a href="https://share.google/eY55t326aRXR2f1ly" target="_blank" rel="noopener noreferrer" class="text-gray-300 hover:text-yellow-400" aria-label="Review CORPIUS on Google">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M12.545 10.239v3.821h5.445c-.712 2.315-2.647 3.972-5.445 3.972a6.033 6.033 0 110-12.064c1.498 0 2.866.549 3.921 1.453l2.814-2.814A9.969 9.969 0 0012.545 2C7.021 2 2.543 6.477 2.543 12s4.478 10 10.002 10c8.396 0 10.249-7.85 9.426-11.748l-9.426-.013z"/></svg>
                            </a>
                            <a href="https://x.com/corpius_ny" target="_blank" rel="noopener noreferrer" class="text-gray-300 hover:text-yellow-400" aria-label="Follow CORPIUS on X (Twitter)">
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M6.29 18.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0020 3.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.073 4.073 0 01.8 7.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 010 16.407a11.616 11.616 0 006.29 1.84"/>
                                </svg>
                            </a>
                            <a href="https://www.facebook.com/corpius.ny/" target="_blank" rel="noopener noreferrer" class="text-gray-300 hover:text-yellow-400" aria-label="Follow CORPIUS on Facebook">
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M20 10C20 4.477 15.523 0 10 0S0 4.477 0 10c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V10h2.54V7.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V10h2.773l-.443 2.89h-2.33v6.988C16.343 19.128 20 14.991 20 10z" clip-rule="evenodd"/>
                                </svg>
                            </a>
                            <a href="https://www.linkedin.com/company/corpius" target="_blank" rel="noopener noreferrer" class="text-gray-300 hover:text-yellow-400" aria-label="Follow CORPIUS on LinkedIn">
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.338 16.338H13.67V12.16c0-.995-.017-2.277-1.387-2.277-1.39 0-1.601 1.086-1.601 2.207v4.248H8.014v-8.59h2.559v1.174h.037c.356-.675 1.227-1.387 2.526-1.387 2.703 0 3.203 1.778 3.203 4.092v4.711zM5.005 6.575a1.548 1.548 0 11-.003-3.096 1.548 1.548 0 01.003 3.096zm-1.337 9.763H6.34v-8.59H3.667v8.59zM17.668 1H2.328C1.595 1 1 1.581 1 2.298v15.403C1 18.418 1.595 19 2.328 19h15.34c.734 0 1.332-.582 1.332-1.299V2.298C19 1.581 18.402 1 17.668 1z" clip-rule="evenodd"/>
                                </svg>
                            </a>
                            <a href="https://www.trustpilot.com/review/corpius.net" target="_blank" rel="noopener noreferrer" class="text-gray-300 hover:text-yellow-400" aria-label="Review CORPIUS on Trustpilot">
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 17.027L17.09 20l-1.345-5.785L20 10.68l-5.887-.504L12 4.5 9.887 10.176 4 10.68l4.255 3.535L6.91 20 12 17.027z"/>
                                </svg>
                            </a>
                            <a href="https://www.quora.com/profile/James-Steward-124" target="_blank" rel="noopener noreferrer" class="text-gray-300 hover:text-yellow-400" aria-label="Follow CORPIUS on Quora">
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12.257 0C5.876 0 .71 5.166.71 11.547c0 6.38 5.166 11.546 11.547 11.546 1.534 0 2.994-.294 4.33-.826l.845 1.733h4.648l-2.014-4.076C21.443 17.884 22 15.174 22 12.547 22 5.166 16.637 0 12.257 0zm3.107 18.637l-.98-2.013c-.98.49-2.013.734-3.127.734-3.921 0-6.566-2.868-6.566-6.84 0-3.97 2.645-6.84 6.566-6.84 3.92 0 6.565 2.87 6.565 6.84 0 1.71-.49 3.23-1.26 4.37l.98 2.014-2.178 1.735z"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </footer>

        <!-- AI Chat Widget -->
        <ClientOnly><ChatWidget /></ClientOnly>
    </div>
</template>

<style>
/* Custom navy color */
.bg-navy-900 {
    background-color: #0b1e33;
}
.text-navy-900 {
    color: #0b1e33;
}
.border-navy-900 {
    border-color: #0b1e33;
}

/* Marketing site background */
.marketing-bg {
    background-color: #0b1e33;
}
</style>