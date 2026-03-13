<script setup>
import MarketingLayout from '@/Layouts/MarketingLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({ application: Object });

const statusLabels = {
    draft:                'Draft',
    payment_pending:      'Payment Pending',
    in_review:            'Under Review',
    changes_requested:    'Changes Requested',
    ready_for_submission: 'Ready for Submission',
    submitted:            'Submitted',
    closed:               'Closed',
};

const statusColors = {
    draft:                'border-gray-500 text-gray-400',
    payment_pending:      'border-orange-500 text-orange-400',
    in_review:            'border-blue-500 text-blue-400',
    changes_requested:    'border-yellow-500 text-yellow-400',
    ready_for_submission: 'border-green-500 text-green-400',
    submitted:            'border-green-400 text-green-300',
    closed:               'border-gray-600 text-gray-500',
};
</script>

<template>
    <Head title="Application Confirmed | CORPIUS">
        <meta name="robots" content="noindex, nofollow" />
    </Head>

    <MarketingLayout>
        <div class="min-h-screen py-16 px-4" style="background-color: #0b1e33;">
            <div class="max-w-2xl mx-auto text-center">

                <!-- Icon -->
                <div class="w-24 h-24 bg-yellow-400/10 border-2 border-yellow-400 rounded-full flex items-center justify-center mx-auto mb-8">
                    <svg class="w-12 h-12 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>

                <h1 class="text-4xl font-bold text-white mb-4">Application Confirmed</h1>
                <p class="text-gray-400 text-lg mb-8">
                    Thank you! Your DV Lottery application has been received and is being reviewed by our specialists.
                </p>

                <!-- Status badge -->
                <div v-if="application" class="inline-flex items-center gap-2 border rounded-full px-4 py-1.5 mb-8"
                     :class="statusColors[application.status] ?? 'border-gray-500 text-gray-400'">
                    <span class="w-2 h-2 rounded-full bg-current"></span>
                    <span class="text-sm font-semibold">{{ statusLabels[application.status] ?? application.status }}</span>
                </div>

                <!-- Application reference -->
                <div v-if="application" class="bg-white/5 border border-white/10 rounded-2xl p-6 mb-8 text-left">
                    <h2 class="font-semibold text-yellow-400 mb-4">Application Summary</h2>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-400">Reference Token</span>
                            <span class="text-white font-mono text-xs">{{ application.session_token }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Applicant</span>
                            <span class="text-white">{{ application.first_name }} {{ application.last_name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Package</span>
                            <span class="text-white capitalize">{{ application.package_type }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Email</span>
                            <span class="text-white">{{ application.email }}</span>
                        </div>
                    </div>
                </div>

                <!-- Next steps -->
                <div class="bg-white/5 border border-white/10 rounded-2xl p-6 mb-8 text-left">
                    <h2 class="font-semibold text-yellow-400 mb-5">What Happens Next</h2>
                    <div class="space-y-4">
                        <div class="flex items-start gap-4">
                            <div class="w-8 h-8 bg-yellow-400 text-[#0b1e33] rounded-full flex items-center justify-center font-bold flex-shrink-0">1</div>
                            <div>
                                <p class="text-white font-medium">Application Review</p>
                                <p class="text-gray-400 text-sm mt-0.5">Our specialists will thoroughly review your application data and uploaded documents for accuracy and compliance.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4">
                            <div class="w-8 h-8 bg-yellow-400 text-[#0b1e33] rounded-full flex items-center justify-center font-bold flex-shrink-0">2</div>
                            <div>
                                <p class="text-white font-medium">Verification & Confirmation</p>
                                <p class="text-gray-400 text-sm mt-0.5">If any information requires correction or clarification, we will contact you via email before submission.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4">
                            <div class="w-8 h-8 bg-yellow-400 text-[#0b1e33] rounded-full flex items-center justify-center font-bold flex-shrink-0">3</div>
                            <div>
                                <p class="text-white font-medium">Ready for Submission</p>
                                <p class="text-gray-400 text-sm mt-0.5">Once approved, your application will be prepared and handed off for DV Lottery submission during the official entry period.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex flex-col sm:flex-row gap-3 mb-10">
                    <a href="/dashboard"
                       class="flex-1 bg-yellow-400 hover:bg-yellow-500 text-[#0b1e33] py-3 rounded-xl font-bold transition-all text-center">
                        View Application Status
                    </a>
                    <a href="/services/green-card-lottery"
                       class="flex-1 border border-white/20 text-white hover:bg-white/5 py-3 rounded-xl font-semibold transition-all text-center">
                        Back to Green Card
                    </a>
                    <a href="/contact"
                       class="flex-1 border border-white/20 text-white hover:bg-white/5 py-3 rounded-xl font-semibold transition-all text-center">
                        Contact Support
                    </a>
                </div>

                <!-- Legal disclaimers -->
                <div class="border-t border-white/10 pt-8 text-left space-y-2 text-xs text-gray-500">
                    <p>1. CORPIUS is a private service provider and is not affiliated with the U.S. government or the U.S. Department of State.</p>
                    <p>2. The official Diversity Visa (DV) Lottery entry is a free program administered by the U.S. Department of State at dvlottery.state.gov.</p>
                    <p>3. Selection in the DV Lottery is random and based solely on the U.S. government's qualification criteria. CORPIUS cannot guarantee selection or visa approval.</p>
                    <p>4. Your payment covers professional application preparation, document verification, and customer support services provided by CORPIUS only.</p>
                </div>

            </div>
        </div>
    </MarketingLayout>
</template>
