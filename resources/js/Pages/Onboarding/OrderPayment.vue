<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const props = defineProps({ user: Object });

// ── Steps ─────────────────────────────────────────────────────────────────────
const currentStep = ref(1);
const steps = [
    { number: 1, name: 'Service Selection' },
    { number: 2, name: 'Business Details'  },
    { number: 3, name: 'Add-ons & Speed'   },
    { number: 4, name: 'Required Documents'},
];

// ── Service catalog ───────────────────────────────────────────────────────────
const services = [
    { id: 'C-Corporation',      name: 'C-Corporation',                description: 'Best for growth and venture capital funding',                          price: 399  },
    { id: 'S-Corporation',      name: 'S-Corporation',                description: 'Tax benefits with pass-through taxation',                              price: 880  },
    { id: 'LLC',                name: 'LLC Formation',                description: 'Flexible structure with liability protection',                         price: 1480 },
    { id: 'Nonprofit',          name: 'Nonprofit Organization',       description: 'Tax-exempt charitable organization',                                   price: 499  },
    { id: 'Green Card Lottery', name: 'Green Card Lottery',           description: 'Professional DV Lottery application assistance',                       price: 49   },
    { id: 'Income Tax',         name: 'Income Tax Filing & Planning', description: 'Professional income tax service for both corporations and individuals', price: 499  },
];

// ── Service-specific add-ons ─────────────────────────────────────────────────
const addonCatalog = [
    { id: 'registered_agent',   name: 'Registered Agent (1 year)',            price: 125, services: ['C-Corporation', 'S-Corporation', 'LLC', 'Nonprofit'] },
    { id: 'ein',                name: 'EIN Application',                      price: 0,   services: ['C-Corporation', 'S-Corporation', 'LLC', 'Nonprofit'] },
    { id: 'corporate_kit',      name: 'Corporate Kit & Seal',                 price: 85,  services: ['C-Corporation', 'S-Corporation', 'LLC', 'Nonprofit'] },
    { id: 'compliance',         name: 'Compliance Calendar',                   price: 50,  services: ['C-Corporation', 'S-Corporation', 'LLC', 'Nonprofit'] },
    { id: 'operating_agreement',name: 'Operating Agreement / By-Laws',        price: 99,  services: ['C-Corporation', 'S-Corporation', 'LLC'] },
    { id: 'annual_report',      name: 'Annual Report Filing (1st Year)',      price: 149, services: ['C-Corporation', 'S-Corporation', 'LLC', 'Nonprofit'] },
    { id: 'apostille',          name: 'Apostille / Document Authentication',  price: 149, services: ['C-Corporation', 'S-Corporation', 'LLC', 'Nonprofit', 'Green Card Lottery'] },
    { id: 'good_standing',      name: 'Certificate of Good Standing',         price: 99,  services: ['C-Corporation', 'S-Corporation', 'LLC', 'Nonprofit'] },
    { id: 'mail_forwarding',    name: 'USPS Mail Forwarding (1 Year)',        price: 99,  services: ['C-Corporation', 'S-Corporation', 'LLC', 'Nonprofit'] },
    { id: 'itin',               name: 'ITIN Application (Non-US Resident)',   price: 249, services: ['C-Corporation', 'S-Corporation', 'LLC', 'Income Tax'] },
    { id: 'tax_consult',        name: 'Tax Consultation (30 min)',            price: 149, services: ['C-Corporation', 'S-Corporation', 'LLC', 'Nonprofit', 'Income Tax'] },
    { id: 'banking',            name: 'Business Bank Account Assistance',      price: 49,  services: ['C-Corporation', 'S-Corporation', 'LLC', 'Nonprofit'] },
    { id: 'gc_premium',         name: 'Green Card Premium Review',             price: 79,  services: ['Green Card Lottery'] },
    { id: 'gc_notification',    name: 'Result Notification Service',           price: 29,  services: ['Green Card Lottery'] },
    { id: 'tax_bookkeeping',    name: 'Monthly Bookkeeping (3 months)',        price: 299, services: ['Income Tax'] },
    { id: 'tax_amendment',      name: 'Prior Year Tax Amendment',              price: 199, services: ['Income Tax'] },
];

const defaultAddonsByService = {
    'C-Corporation': ['ein'],
    'S-Corporation': ['ein'],
    'LLC': ['ein'],
    'Nonprofit': ['ein'],
    'Green Card Lottery': [],
    'Income Tax': [],
};

// ── Document types (extracted to avoid apostrophe parsing issues in template)
const docTypes = [
    { key: 'passport',        label: 'Passport',         upload: 'Upload Passport' },
    { key: 'id_card',         label: 'ID Card',          upload: 'Upload ID Card'  },
    { key: 'drivers_license', label: "Driver's License", upload: 'Upload License'  },
];

// ── US States ─────────────────────────────────────────────────────────────────
const US_STATES = [
    'Alabama','Alaska','Arizona','Arkansas','California','Colorado','Connecticut',
    'Delaware','Florida','Georgia','Hawaii','Idaho','Illinois','Indiana','Iowa',
    'Kansas','Kentucky','Louisiana','Maine','Maryland','Massachusetts','Michigan',
    'Minnesota','Mississippi','Missouri','Montana','Nebraska','Nevada',
    'New Hampshire','New Jersey','New Mexico','New York','North Carolina',
    'North Dakota','Ohio','Oklahoma','Oregon','Pennsylvania','Rhode Island',
    'South Carolina','South Dakota','Tennessee','Texas','Utah','Vermont',
    'Virginia','Washington','West Virginia','Wisconsin','Wyoming',
];

// ── Form ──────────────────────────────────────────────────────────────────────
const form = useForm({
    serviceType:      '',
    state:            '',
    businessName:     '',
    businessPurpose:  '',
    speedOption:      'economic',
    addons:           ['ein'],
    applicantName:    (props.user?.first_name ?? '') + (props.user?.last_name ? ' ' + props.user.last_name : ''),
    applicantEmail:   props.user?.email ?? '',
    applicantPhone:   props.user?.phone ?? '',
    applicantDob:     '',
    applicantSsn:     '',
    applicantAddress: '',
    applicantCity:    '',
    applicantZip:     '',
    applicantCountry: '',
    documents: { passport: null, id_card: null, drivers_license: null, photos: [] },
});

// ── Computed ──────────────────────────────────────────────────────────────────
const selectedService = computed(() => services.find(s => s.id === form.serviceType));
const isGreenCard     = computed(() => form.serviceType === 'Green Card Lottery');
const isIncomeTax     = computed(() => form.serviceType === 'Income Tax');
const availableAddons = computed(() => addonCatalog.filter(a => a.services.includes(form.serviceType)));

watch(() => form.serviceType, () => {
    const allowedAddonIds = new Set(availableAddons.value.map(a => a.id));
    const preserved = form.addons.filter(id => allowedAddonIds.has(id));
    const defaults = (defaultAddonsByService[form.serviceType] ?? []).filter(id => allowedAddonIds.has(id));
    form.addons = [...new Set([...defaults, ...preserved])];
});

const canProceed = computed(() => {
    if (currentStep.value === 1) return !!form.serviceType;
    if (currentStep.value === 2) {
        if (isGreenCard.value || isIncomeTax.value) return !!(form.applicantName && form.applicantPhone);
        return !!(form.state && form.businessName && form.applicantName && form.applicantPhone && form.applicantAddress && form.applicantCity && form.applicantZip);
    }
    if (currentStep.value === 3) return true;
    if (currentStep.value === 4) {
        const hasId = !!(form.documents.passport || form.documents.id_card || form.documents.drivers_license);
        if (isGreenCard.value) return hasId && form.documents.photos.length >= 2;
        return hasId;
    }
    return false;
});

// ── Navigation ────────────────────────────────────────────────────────────────
const nextStep = () => {
    if (!canProceed.value) return;
    if (currentStep.value === 4) { submitOrder(); return; }
    currentStep.value++;
    window.scrollTo({ top: 0, behavior: 'smooth' });
};
const prevStep = () => {
    if (currentStep.value > 1) { currentStep.value--; window.scrollTo({ top: 0, behavior: 'smooth' }); }
};

// ── Toggle add-on ─────────────────────────────────────────────────────────────
const toggleAddon = (id) => {
    const i = form.addons.indexOf(id);
    if (i > -1) form.addons.splice(i, 1);
    else form.addons.push(id);
};

// ── File handling ─────────────────────────────────────────────────────────────
const handleFile = (e, type) => {
    const file = e.target.files[0];
    if (!file) return;
    const max = type === 'photos' ? 10 * 1024 * 1024 : 20 * 1024 * 1024;
    if (file.size > max) { alert(`File too large (max ${type === 'photos' ? '10' : '20'}MB)`); return; }
    if (type === 'photos') { if (form.documents.photos.length < 2) form.documents.photos.push(file); }
    else form.documents[type] = file;
};
const removeFile  = (type) => { form.documents[type] = null; };
const removePhoto = (i)    => { form.documents.photos.splice(i, 1); };

// ── Submit ────────────────────────────────────────────────────────────────────
const submitOrder = () => {
    form.post(route('onboarding.save-order'), { forceFormData: true });
};
</script>

<template>
    <div class="min-h-screen bg-[#0b1e33]">
        <Head title="Select Service | CORPIUS" />

        <!-- Top bar -->
        <div class="sticky top-0 z-10 bg-[#071525]/90 backdrop-blur border-b border-white/[0.06] px-6 py-3 flex items-center justify-between">
            <Link href="/"><img src="/logo.png" alt="CORPIUS" class="h-8 w-auto" /></Link>
            <button type="button" @click="router.post(route('logout'))"
                class="text-[13px] text-gray-500 hover:text-gray-300 transition-colors">Sign out</button>
        </div>

        <div class="max-w-3xl mx-auto px-6 py-10">

            <!-- ── Outer onboarding progress (Personal Info / Order / Review) ── -->
            <div class="flex items-center gap-0 mb-8 justify-center">
                <template v-for="(s, idx) in [{n:1,l:'Personal Info'},{n:2,l:'Order'},{n:3,l:'Review'}]" :key="s.n">
                    <div class="flex flex-col items-center">
                        <div :class="['w-10 h-10 rounded-full flex items-center justify-center text-[14px] font-bold border-2 transition-all',
                            s.n < 2 ? 'border-[#d4a02f] bg-[#d4a02f] text-[#0b1e33]' :
                            s.n === 2 ? 'border-[#d4a02f] text-[#d4a02f]' :
                            'border-white/20 text-gray-600']">
                            <svg v-if="s.n < 2" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            <span v-else>{{ s.n }}</span>
                        </div>
                        <span :class="['text-[11px] font-semibold mt-1.5 uppercase tracking-wider', s.n <= 2 ? 'text-[#d4a02f]' : 'text-gray-600']">{{ s.l }}</span>
                    </div>
                    <div v-if="idx < 2" :class="['w-20 h-[2px] mx-1 mb-5 rounded', s.n < 2 ? 'bg-[#d4a02f]' : 'bg-white/10']" />
                </template>
            </div>

            <!-- Header -->
            <div class="mb-6">
                <p class="text-[13px] text-gray-400">Complete the steps below to start your incorporation</p>
            </div>

            <!-- ── Inner 4-step indicator ─────────────────────────────────────── -->
            <div class="flex items-center mb-8">
                <template v-for="(step, i) in steps" :key="step.number">
                    <div class="flex flex-col items-center">
                        <div :class="['w-10 h-10 rounded-full flex items-center justify-center font-bold text-[14px] border-2 transition-all',
                            currentStep > step.number  ? 'bg-[#d4a02f] border-[#d4a02f] text-[#0b1e33]' :
                            currentStep === step.number ? 'border-[#d4a02f] text-[#d4a02f] bg-transparent' :
                            'border-white/20 text-gray-600 bg-transparent']">
                            <svg v-if="currentStep > step.number" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span v-else>{{ step.number }}</span>
                        </div>
                        <span :class="['text-[11px] font-semibold mt-1.5 whitespace-nowrap',
                            currentStep >= step.number ? 'text-[#d4a02f]' : 'text-gray-600']">{{ step.name }}</span>
                    </div>
                    <div v-if="i < steps.length - 1"
                        :class="['flex-1 h-[2px] mx-2 mb-5 rounded transition-all',
                            currentStep > step.number ? 'bg-[#d4a02f]' : 'bg-white/10']" />
                </template>
            </div>

            <!-- ═══════════════════════════════════════════════════ -->
            <!-- STEP 1 — SERVICE SELECTION                          -->
            <!-- ═══════════════════════════════════════════════════ -->
            <div v-if="currentStep === 1" class="rounded-2xl border border-white/[0.07] bg-[#0d2540] p-8">
                <h2 class="text-[18px] font-bold text-white mb-6">Choose Your Service</h2>

                <div class="grid grid-cols-2 gap-4">
                    <button v-for="svc in services" :key="svc.id" type="button"
                        @click="form.serviceType = svc.id"
                        :class="['relative rounded-xl border p-5 text-left transition-all',
                            form.serviceType === svc.id
                                ? 'border-[#d4a02f] bg-[#d4a02f]/5'
                                : 'border-white/[0.08] bg-white/[0.02] hover:border-white/20 hover:bg-white/[0.04]']">
                        <div v-if="form.serviceType === svc.id"
                            class="absolute top-3 right-3 w-6 h-6 rounded-full bg-[#d4a02f] flex items-center justify-center">
                            <svg class="w-3.5 h-3.5 text-[#0b1e33]" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <div :class="['w-10 h-10 rounded-xl flex items-center justify-center mb-3', form.serviceType === svc.id ? 'bg-[#d4a02f]/20' : 'bg-white/[0.07]']">
                            <svg v-if="svc.id === 'Green Card Lottery'"
                                :class="['w-5 h-5', form.serviceType === svc.id ? 'text-[#d4a02f]' : 'text-gray-400']"
                                fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582"/>
                            </svg>
                            <svg v-else-if="svc.id === 'Income Tax'"
                                :class="['w-5 h-5', form.serviceType === svc.id ? 'text-[#d4a02f]' : 'text-gray-400']"
                                fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>
                            </svg>
                            <svg v-else
                                :class="['w-5 h-5', form.serviceType === svc.id ? 'text-[#d4a02f]' : 'text-gray-400']"
                                fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/>
                            </svg>
                        </div>
                        <div :class="['text-[15px] font-bold mb-1', form.serviceType === svc.id ? 'text-[#d4a02f]' : 'text-white']">{{ svc.name }}</div>
                        <div class="text-[12px] text-gray-500 leading-snug mb-3">{{ svc.description }}</div>
                        <div class="text-[20px] font-extrabold text-[#d4a02f]">${{ svc.price }}</div>
                    </button>
                </div>

                <div class="flex justify-between mt-8">
                    <a :href="route('onboarding.personal-info')"
                        class="flex items-center gap-2 h-11 px-5 rounded-xl border border-white/10 text-[13px] text-gray-400 hover:text-white hover:bg-white/5 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                        Previous
                    </a>
                    <button type="button" @click="nextStep" :disabled="!canProceed"
                        class="flex items-center gap-2 h-11 px-7 rounded-xl bg-[#d4a02f] text-[13px] font-bold text-[#0b1e33] hover:bg-[#e6b84a] transition disabled:opacity-40 shadow-lg shadow-[#d4a02f]/20">
                        Next <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </div>

            <!-- ═══════════════════════════════════════════════════ -->
            <!-- STEP 2 — BUSINESS DETAILS                           -->
            <!-- ═══════════════════════════════════════════════════ -->
            <div v-else-if="currentStep === 2" class="rounded-2xl border border-white/[0.07] bg-[#0d2540] p-8">
                <h2 class="text-[18px] font-bold text-white mb-6">Business Details</h2>

                <div class="space-y-5">
                    <div v-if="!isGreenCard && !isIncomeTax">
                        <label class="block text-[11px] font-bold uppercase tracking-widest text-gray-400 mb-2">State of Incorporation</label>
                        <div class="relative">
                            <select v-model="form.state"
                                class="w-full h-12 rounded-xl border border-white/[0.10] bg-[#0b2240] px-4 text-[14px] text-white outline-none focus:border-[#d4a02f]/50 appearance-none cursor-pointer">
                                <option value="">Select state…</option>
                                <option v-for="s in US_STATES" :key="s" :value="s">{{ s }}</option>
                            </select>
                            <svg class="pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>

                    <div v-if="!isGreenCard && !isIncomeTax">
                        <label class="block text-[11px] font-bold uppercase tracking-widest text-gray-400 mb-2">Business Name <span class="text-red-400">*</span></label>
                        <input type="text" v-model="form.businessName" placeholder="Enter your business name"
                            class="w-full h-12 rounded-xl border border-white/[0.10] bg-[#0b2240] px-4 text-[14px] text-white placeholder-gray-600 outline-none focus:border-[#d4a02f]/50" />
                    </div>

                    <div v-if="!isGreenCard && !isIncomeTax">
                        <label class="block text-[11px] font-bold uppercase tracking-widest text-gray-400 mb-2">Business Purpose</label>
                        <textarea v-model="form.businessPurpose" rows="3" placeholder="Describe your business activities…"
                            class="w-full rounded-xl border border-white/[0.10] bg-[#0b2240] px-4 py-3 text-[14px] text-white placeholder-gray-600 outline-none focus:border-[#d4a02f]/50 resize-none" />
                    </div>

                    <div>
                        <div class="flex items-center gap-2 mb-4">
                            <svg class="w-4 h-4 text-[#d4a02f]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            <span class="text-[14px] font-semibold text-white">Personal Information</span>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[11px] font-bold uppercase tracking-widest text-gray-400 mb-2">Full Name <span class="text-red-400">*</span></label>
                                <input type="text" v-model="form.applicantName" placeholder="Full name"
                                    class="w-full h-12 rounded-xl border border-white/[0.10] bg-[#0b2240] px-4 text-[14px] text-white placeholder-gray-600 outline-none focus:border-[#d4a02f]/50" />
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold uppercase tracking-widest text-gray-400 mb-2">Email Address</label>
                                <input type="email" v-model="form.applicantEmail" placeholder="email@example.com"
                                    class="w-full h-12 rounded-xl border border-white/[0.10] bg-[#0b2240] px-4 text-[14px] text-white placeholder-gray-600 outline-none focus:border-[#d4a02f]/50" />
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold uppercase tracking-widest text-gray-400 mb-2">Phone Number <span class="text-red-400">*</span></label>
                                <input type="tel" v-model="form.applicantPhone" placeholder="+1 (555) 000-0000"
                                    class="w-full h-12 rounded-xl border border-white/[0.10] bg-[#0b2240] px-4 text-[14px] text-white placeholder-gray-600 outline-none focus:border-[#d4a02f]/50" />
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold uppercase tracking-widest text-gray-400 mb-2">Date of Birth <span class="text-red-400">*</span></label>
                                <input type="date" v-model="form.applicantDob"
                                    class="w-full h-12 rounded-xl border border-white/[0.10] bg-[#0b2240] px-4 text-[14px] text-white outline-none focus:border-[#d4a02f]/50" />
                            </div>
                            <div class="col-span-2">
                                <label class="block text-[11px] font-bold uppercase tracking-widest text-gray-400 mb-2">SSN / ITIN <span class="text-gray-500 font-normal normal-case tracking-normal">(Encrypted &amp; Secure)</span></label>
                                <input type="text" v-model="form.applicantSsn" placeholder="XXX-XX-XXXX"
                                    class="w-full h-12 rounded-xl border border-white/[0.10] bg-[#0b2240] px-4 text-[14px] text-white placeholder-gray-600 outline-none focus:border-[#d4a02f]/50" />
                            </div>
                            <div class="col-span-2">
                                <label class="block text-[11px] font-bold uppercase tracking-widest text-gray-400 mb-2">Street Address <span class="text-red-400">*</span></label>
                                <input type="text" v-model="form.applicantAddress" placeholder="123 Main St"
                                    class="w-full h-12 rounded-xl border border-white/[0.10] bg-[#0b2240] px-4 text-[14px] text-white placeholder-gray-600 outline-none focus:border-[#d4a02f]/50" />
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold uppercase tracking-widest text-gray-400 mb-2">City <span class="text-red-400">*</span></label>
                                <input type="text" v-model="form.applicantCity" placeholder="City"
                                    class="w-full h-12 rounded-xl border border-white/[0.10] bg-[#0b2240] px-4 text-[14px] text-white placeholder-gray-600 outline-none focus:border-[#d4a02f]/50" />
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold uppercase tracking-widest text-gray-400 mb-2">Zip Code <span class="text-red-400">*</span></label>
                                <input type="text" v-model="form.applicantZip" placeholder="00000"
                                    class="w-full h-12 rounded-xl border border-white/[0.10] bg-[#0b2240] px-4 text-[14px] text-white placeholder-gray-600 outline-none focus:border-[#d4a02f]/50" />
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold uppercase tracking-widest text-gray-400 mb-2">Country</label>
                                <input type="text" v-model="form.applicantCountry" placeholder="United States"
                                    class="w-full h-12 rounded-xl border border-white/[0.10] bg-[#0b2240] px-4 text-[14px] text-white placeholder-gray-600 outline-none focus:border-[#d4a02f]/50" />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-between mt-8">
                    <button type="button" @click="prevStep"
                        class="flex items-center gap-2 h-11 px-5 rounded-xl border border-white/10 text-[13px] text-gray-400 hover:text-white hover:bg-white/5 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                        Previous
                    </button>
                    <button type="button" @click="nextStep" :disabled="!canProceed"
                        class="flex items-center gap-2 h-11 px-7 rounded-xl bg-[#d4a02f] text-[13px] font-bold text-[#0b1e33] hover:bg-[#e6b84a] transition disabled:opacity-40 shadow-lg shadow-[#d4a02f]/20">
                        Next <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </div>

            <!-- ═══════════════════════════════════════════════════ -->
            <!-- STEP 3 — PROCESSING SPEED & ADD-ONS                 -->
            <!-- ═══════════════════════════════════════════════════ -->
            <div v-else-if="currentStep === 3" class="rounded-2xl border border-white/[0.07] bg-[#0d2540] p-8">
                <h2 class="text-[18px] font-bold text-white mb-6">Processing Speed &amp; Add-ons</h2>

                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-widest text-gray-500 mb-3">Processing Speed</label>
                    <div class="grid grid-cols-2 gap-4 mb-7">
                        <button type="button" @click="form.speedOption = 'economic'"
                            :class="['rounded-xl border p-4 text-left transition-all',
                                form.speedOption === 'economic'
                                    ? 'border-[#d4a02f] bg-[#d4a02f]/5'
                                    : 'border-white/[0.08] bg-white/[0.02] hover:border-white/20']">
                            <div :class="['text-[14px] font-bold mb-1', form.speedOption === 'economic' ? 'text-[#d4a02f]' : 'text-white']">Economic (14–25 days)</div>
                            <div class="text-[20px] font-extrabold text-[#d4a02f]">+$99</div>
                        </button>
                        <button type="button" @click="form.speedOption = 'pro'"
                            :class="['rounded-xl border p-4 text-left transition-all',
                                form.speedOption === 'pro'
                                    ? 'border-[#d4a02f] bg-[#d4a02f]/5'
                                    : 'border-white/[0.08] bg-white/[0.02] hover:border-white/20']">
                            <div :class="['text-[14px] font-bold mb-1', form.speedOption === 'pro' ? 'text-[#d4a02f]' : 'text-white']">Pro (2–4 days)</div>
                            <div class="text-[20px] font-extrabold text-[#d4a02f]">+$149</div>
                        </button>
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-widest text-gray-500 mb-3">Add-On Services</label>
                    <div class="space-y-2">
                        <label v-for="addon in availableAddons" :key="addon.id"
                            :class="['flex items-center justify-between rounded-xl border px-4 py-3.5 cursor-pointer transition-all',
                                form.addons.includes(addon.id)
                                    ? 'border-[#d4a02f]/40 bg-[#d4a02f]/5'
                                    : 'border-white/[0.07] bg-white/[0.02] hover:border-white/15']">
                            <div class="flex items-center gap-3">
                                <input type="checkbox" :checked="form.addons.includes(addon.id)"
                                    @change="toggleAddon(addon.id)"
                                    class="h-4 w-4 rounded border-gray-600 bg-white/5 text-[#d4a02f] focus:ring-[#d4a02f]/30 cursor-pointer" />
                                <span class="text-[14px] text-white font-medium">{{ addon.name }}</span>
                            </div>
                            <span :class="['text-[13px] font-semibold', addon.price === 0 ? 'text-[#d4a02f]' : 'text-gray-300']">
                                {{ addon.price === 0 ? 'Included' : '$' + addon.price }}
                            </span>
                        </label>
                        <p v-if="availableAddons.length === 0" class="text-[13px] text-gray-400 rounded-xl border border-white/[0.07] bg-white/[0.02] px-4 py-3">
                            No extra services available for this selection.
                        </p>
                    </div>
                </div>

                <div class="flex justify-between mt-8">
                    <button type="button" @click="prevStep"
                        class="flex items-center gap-2 h-11 px-5 rounded-xl border border-white/10 text-[13px] text-gray-400 hover:text-white hover:bg-white/5 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                        Previous
                    </button>
                    <button type="button" @click="nextStep"
                        class="flex items-center gap-2 h-11 px-7 rounded-xl bg-[#d4a02f] text-[13px] font-bold text-[#0b1e33] hover:bg-[#e6b84a] transition shadow-lg shadow-[#d4a02f]/20">
                        Next <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </div>

            <!-- ═══════════════════════════════════════════════════ -->
            <!-- STEP 4 — REQUIRED DOCUMENTS                         -->
            <!-- ═══════════════════════════════════════════════════ -->
            <div v-else class="rounded-2xl border border-white/[0.07] bg-[#0d2540] p-8">
                <h2 class="text-[18px] font-bold text-white mb-2">
                    {{ isGreenCard ? 'Green Card Lottery' : 'Business Formation' }} Required Documents
                </h2>
                <p class="text-[13px] text-gray-400 mb-6">Upload the required documents to proceed with your application</p>

                <div class="rounded-xl border border-white/[0.07] bg-white/[0.03] p-4 flex gap-3 mb-7">
                    <div class="w-8 h-8 rounded-lg bg-blue-400/10 flex items-center justify-center flex-shrink-0 mt-0.5">
                        <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-[13px] font-semibold text-white mb-1">Document Requirements</p>
                        <p class="text-[12px] text-gray-400 mb-2">Please upload one identity document to verify your identity:</p>
                        <ul class="text-[12px] text-gray-400 space-y-0.5">
                            <li>• One identity document: Passport OR ID Card OR Driver's License</li>
                            <li v-if="isGreenCard">• Two recent photos (passport style, taken within last 6 months)</li>
                        </ul>
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-[14px] font-semibold text-white">Identity Document <span class="text-gray-500 font-normal">(Choose One)</span></h3>
                        <span class="text-[12px] text-[#d4a02f]">Driver's License preferred</span>
                    </div>
                    <div class="grid grid-cols-3 gap-4 mb-6">
                        <div v-for="doc in docTypes" :key="doc.key">
                            <p class="text-[12px] font-semibold text-gray-300 mb-2">{{ doc.label }}</p>
                            <div v-if="!form.documents[doc.key]"
                                class="relative rounded-xl border-2 border-dashed border-white/[0.10] bg-white/[0.02] p-6 flex flex-col items-center justify-center cursor-pointer transition-all hover:border-[#d4a02f]/40 hover:bg-[#d4a02f]/5">
                                <input type="file" @change="handleFile($event, doc.key)"
                                    accept=".jpg,.jpeg,.png,.pdf" class="absolute inset-0 opacity-0 cursor-pointer" />
                                <svg class="w-7 h-7 text-gray-600 mb-2" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                </svg>
                                <span class="text-[12px] text-gray-500">{{ doc.upload }}</span>
                            </div>
                            <div v-else class="rounded-xl border border-green-400/30 bg-green-400/5 p-3 flex items-center gap-3">
                                <svg class="w-5 h-5 text-green-400 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span class="text-[12px] text-green-400 truncate flex-1">{{ form.documents[doc.key]?.name }}</span>
                                <button type="button" @click="removeFile(doc.key)" class="text-gray-500 hover:text-red-400 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="isGreenCard" class="mb-6">
                    <h3 class="text-[14px] font-semibold text-white mb-3">Passport-Style Photos (2 Required)</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <template v-for="n in 2" :key="n">
                            <div v-if="!form.documents.photos[n - 1]"
                                class="relative rounded-xl border-2 border-dashed border-white/[0.10] bg-white/[0.02] p-6 flex flex-col items-center justify-center cursor-pointer hover:border-[#d4a02f]/40 hover:bg-[#d4a02f]/5 transition-all">
                                <input type="file" @change="handleFile($event, 'photos')" accept=".jpg,.jpeg,.png" class="absolute inset-0 opacity-0 cursor-pointer" />
                                <svg class="w-7 h-7 text-gray-600 mb-2" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                <span class="text-[12px] text-gray-500">Photo {{ n }}</span>
                            </div>
                            <div v-else class="rounded-xl border border-green-400/30 bg-green-400/5 p-3 flex items-center gap-3">
                                <svg class="w-5 h-5 text-green-400 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span class="text-[12px] text-green-400 truncate flex-1">{{ form.documents.photos[n - 1]?.name }}</span>
                                <button type="button" @click="removePhoto(n - 1)" class="text-gray-500 hover:text-red-400 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>

                <p v-if="form.errors.documents" class="text-[12px] text-red-400 mb-4">{{ form.errors.documents }}</p>

                <div class="flex justify-between mt-4">
                    <button type="button" @click="prevStep"
                        class="flex items-center gap-2 h-11 px-5 rounded-xl border border-white/10 text-[13px] text-gray-400 hover:text-white hover:bg-white/5 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                        Previous
                    </button>
                    <button type="button" @click="nextStep" :disabled="!canProceed || form.processing"
                        class="flex items-center gap-2 h-11 px-7 rounded-xl bg-violet-600 text-[13px] font-bold text-white hover:bg-violet-500 transition disabled:opacity-40 shadow-lg shadow-violet-600/30">
                        <svg v-if="form.processing" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                        {{ form.processing ? 'Submitting…' : 'Proceed to Payment' }}
                    </button>
                </div>
            </div>

        </div>
    </div>
</template>