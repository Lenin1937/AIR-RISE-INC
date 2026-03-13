<script setup>
import MarketingLayout from '@/Layouts/MarketingLayout.vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, reactive, ref } from 'vue';

const props = defineProps({ application: Object });

// ─── State ───────────────────────────────────────────────────────────────────
const step        = ref(props.application ? props.application.current_step : 1);
const token       = ref(props.application?.session_token ?? null);
const loading     = ref(false);
const error       = ref(null);
const totalSteps  = 9;

// Step 1 – package
const selectedPackage = ref(props.application?.package_type ?? null);

// Step 2 – email / consent
const emailForm = reactive({
    email:              props.application?.email ?? '',
    confirmed_not_govt: false,
    confirmed_tos:      false,
});

// Step 3 – applicant
const applicantForm = reactive({
    first_name:             props.application?.first_name ?? '',
    middle_name:            props.application?.middle_name ?? '',
    last_name:              props.application?.last_name ?? '',
    gender:                 props.application?.gender ?? '',
    date_of_birth:          props.application?.date_of_birth ?? '',
    city_of_birth:          props.application?.city_of_birth ?? '',
    country_of_birth:       props.application?.country_of_birth ?? '',
    country_of_eligibility: props.application?.country_of_eligibility ?? '',
    passport_number:        props.application?.passport_number ?? '',
    passport_country:       props.application?.passport_country ?? '',
    passport_expiry:        props.application?.passport_expiry ?? '',
    address_line_1:         props.application?.address_line_1 ?? '',
    address_line_2:         props.application?.address_line_2 ?? '',
    city:                   props.application?.city ?? '',
    state_province:         props.application?.state_province ?? '',
    postal_code:            props.application?.postal_code ?? '',
    country:                props.application?.country ?? '',
    phone:                  props.application?.phone ?? '',
    education_level:        props.application?.education_level ?? '',
});

// Step 4 – family
const familyMembers = ref(props.application?.family_members ?? []);
const addFamilyMember = (type) => {
    familyMembers.value.push({ type, first_name: '', last_name: '', date_of_birth: '', country_of_birth: '', gender: '' });
};
const removeFamilyMember = (i) => familyMembers.value.splice(i, 1);

// Step 5 – photos
const primaryPhoto      = ref(null);
const primaryPhotoUrl   = ref(null);
const photoUploading    = ref(false);
const photoError        = ref(null);

const onPrimaryPhotoChange = (e) => {
    const file = e.target.files[0];
    if (!file) return;
    primaryPhoto.value    = file;
    primaryPhotoUrl.value = URL.createObjectURL(file);
};

// Step 6 – documents
const documents     = ref([]);
const docUploading  = ref(false);

const docTypes = [
    { type: 'passport',              label: 'Passport (Primary Applicant)' },
    { type: 'passport_spouse',       label: 'Passport (Spouse)' },
    { type: 'education_certificate', label: 'Education Certificate' },
    { type: 'marriage_certificate',  label: 'Marriage Certificate' },
    { type: 'birth_certificate',     label: 'Birth Certificate (Child)' },
];
const selectedDocType = ref('');
const docFile         = ref(null);

const onDocChange = (e) => { docFile.value = e.target.files[0]; };

// Step 7 – review
const reviewForm = reactive({
    confirmed_accuracy:     false,
    confirmed_single_entry: false,
});

// ─── Packages ─────────────────────────────────────────────────────────────────
const packages = [
    {
        id: 'basic', label: 'Basic Application', price: '$49', features: [
            'Preparation of one DV Lottery entry',
            'Application accuracy check',
            'Photo verification',
            'Submission guidance',
        ],
    },
    {
        id: 'family', label: 'Family Application', price: '$89', badge: 'Popular', features: [
            'Includes spouse and children',
            'Full family application preparation',
            'Document verification',
            'Priority review',
        ],
    },
    {
        id: 'premium', label: 'Premium Application', price: '$149', features: [
            'Full application preparation',
            'Document verification & photo compliance',
            'Priority processing',
            'Support during selection process',
        ],
    },
];

// ─── Helpers ─────────────────────────────────────────────────────────────────
const progressPct = computed(() => Math.round(((step.value - 1) / (totalSteps - 1)) * 100));

const stepLabels = [
    'Package', 'Account', 'Applicant', 'Family',
    'Photos', 'Documents', 'Review', 'Payment', 'Confirm',
];

const post = async (url, data) => {
    error.value = null;
    loading.value = true;
    try {
        const res = await axios.post(url, data);
        return res.data;
    } catch (e) {
        error.value = e.response?.data?.message ?? e.response?.data?.errors
            ? Object.values(e.response.data.errors).flat().join(' ')
            : 'Something went wrong. Please try again.';
        throw e;
    } finally {
        loading.value = false;
    }
};

// ─── Step handlers ───────────────────────────────────────────────────────────

const selectPackage = async (pkg) => {
    selectedPackage.value = pkg;
    const data = await post('/services/green-card-lottery/start', { package_type: pkg });
    token.value = data.token;
    step.value  = 2;
    // Update URL without reload
    window.history.replaceState({}, '', `/services/green-card-lottery/apply?token=${token.value}`);
};

const submitEmail = async () => {
    await post('/services/green-card-lottery/save-email', {
        token:              token.value,
        email:              emailForm.email,
        confirmed_not_govt: emailForm.confirmed_not_govt ? '1' : '',
        confirmed_tos:      emailForm.confirmed_tos ? '1' : '',
    });
    step.value = 3;
};

const submitApplicant = async () => {
    await post('/services/green-card-lottery/save-applicant', {
        token: token.value,
        ...applicantForm,
    });
    step.value = 4;
};

const submitFamily = async () => {
    await post('/services/green-card-lottery/save-family', {
        token:   token.value,
        members: familyMembers.value,
    });
    step.value = 5;
};

const uploadPrimaryPhoto = async () => {
    if (!primaryPhoto.value) { step.value = 6; return; }
    photoError.value  = null;
    photoUploading.value = true;
    try {
        const fd = new FormData();
        fd.append('token',  token.value);
        fd.append('photo',  primaryPhoto.value);
        fd.append('target', 'primary');
        await axios.post('/services/green-card-lottery/upload-photo', fd, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });
        step.value = 6;
    } catch (e) {
        photoError.value = 'Photo upload failed. Please try again.';
    } finally {
        photoUploading.value = false;
    }
};

const uploadDocument = async () => {
    if (!docFile.value || !selectedDocType.value) return;
    docUploading.value = true;
    try {
        const fd = new FormData();
        fd.append('token',         token.value);
        fd.append('document',      docFile.value);
        fd.append('document_type', selectedDocType.value);
        fd.append('label',         docTypes.find(d => d.type === selectedDocType.value)?.label ?? selectedDocType.value);
        const res = await axios.post('/services/green-card-lottery/upload-document', fd, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });
        documents.value.push(res.data.document);
        docFile.value        = null;
        selectedDocType.value = '';
    } catch (e) {
        error.value = 'Document upload failed.';
    } finally {
        docUploading.value = false;
    }
};

const submitReview = async () => {
    await post('/services/green-card-lottery/submit-review', {
        token:                  token.value,
        confirmed_accuracy:     reviewForm.confirmed_accuracy ? '1' : '',
        confirmed_single_entry: reviewForm.confirmed_single_entry ? '1' : '',
    });
    step.value = 8;
};

const confirmPayment = async () => {
    const data = await post('/services/green-card-lottery/confirm-payment', {
        token:          token.value,
        payment_method: 'card',
    });
    window.location.href = data.redirect_url;
};
</script>

<template>
    <Head title="Apply — Green Card DV Lottery | CORPIUS">
        <meta name="robots" content="noindex, nofollow" />
    </Head>

    <MarketingLayout>
        <div class="min-h-screen py-12 px-4" style="background-color: #0b1e33;">
            <div class="max-w-3xl mx-auto">

                <!-- Header -->
                <div class="text-center mb-8">
                    <h1 class="text-3xl font-bold text-yellow-400 mb-2">DV Lottery Application</h1>
                    <p class="text-gray-400">Step {{ step }} of {{ totalSteps }} — {{ stepLabels[step - 1] }}</p>
                </div>

                <!-- Progress bar -->
                <div class="w-full bg-white/10 rounded-full h-2 mb-8">
                    <div class="bg-yellow-400 h-2 rounded-full transition-all duration-500"
                         :style="{ width: progressPct + '%' }"></div>
                </div>

                <!-- Step indicators -->
                <div class="hidden sm:flex justify-between mb-10">
                    <div v-for="(label, i) in stepLabels" :key="i"
                         class="flex flex-col items-center gap-1 flex-1">
                        <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold transition-all"
                             :class="i + 1 < step ? 'bg-yellow-400 text-[#0b1e33]'
                                    : i + 1 === step ? 'border-2 border-yellow-400 text-yellow-400'
                                    : 'bg-white/10 text-gray-500'">
                            <svg v-if="i + 1 < step" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span v-else>{{ i + 1 }}</span>
                        </div>
                        <span class="text-[10px] text-gray-500 text-center leading-tight">{{ label }}</span>
                    </div>
                </div>

                <!-- Error banner -->
                <div v-if="error" class="mb-6 bg-red-500/10 border border-red-500/30 rounded-xl p-4 text-red-400 text-sm">
                    {{ error }}
                </div>

                <!-- ── STEP 1: Package Selection ── -->
                <div v-if="step === 1">
                    <h2 class="text-2xl font-bold text-white mb-2">Choose Your Application Package</h2>
                    <p class="text-gray-400 mb-8">Select the package that fits your situation.</p>

                    <div class="grid gap-4">
                        <div v-for="pkg in packages" :key="pkg.id"
                             @click="selectPackage(pkg.id)"
                             class="relative cursor-pointer rounded-2xl border-2 p-6 transition-all"
                             :class="selectedPackage === pkg.id
                                 ? 'border-yellow-400 bg-yellow-400/5'
                                 : 'border-white/10 bg-white/5 hover:border-yellow-400/40'">
                            <div v-if="pkg.badge" class="absolute top-4 right-4 bg-yellow-400 text-[#0b1e33] text-xs font-bold px-3 py-1 rounded-full">{{ pkg.badge }}</div>
                            <div class="flex items-center justify-between mb-4">
                                <span class="text-lg font-bold text-white">{{ pkg.label }}</span>
                                <span class="text-2xl font-bold text-yellow-400">{{ pkg.price }}</span>
                            </div>
                            <ul class="space-y-2">
                                <li v-for="f in pkg.features" :key="f" class="flex items-start gap-2 text-gray-300 text-sm">
                                    <svg class="w-4 h-4 text-yellow-400 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ f }}
                                </li>
                            </ul>
                            <button class="mt-5 w-full py-3 rounded-lg font-semibold transition-all"
                                    :class="loading ? 'bg-yellow-400/50 text-white cursor-wait' : 'bg-yellow-400 hover:bg-yellow-500 text-[#0b1e33]'"
                                    :disabled="loading">
                                {{ loading && selectedPackage === pkg.id ? 'Starting…' : 'Continue' }}
                            </button>
                        </div>
                    </div>

                    <!-- Legal disclaimer -->
                    <div class="mt-8 bg-yellow-400/5 border border-yellow-400/20 rounded-xl p-4 text-sm text-gray-400">
                        <strong class="text-yellow-400">Important:</strong> The official DV Lottery entry is free and available on the U.S. Department of State website. CORPIUS charges a service fee for application preparation, document verification, and support only.
                    </div>
                </div>

                <!-- ── STEP 2: Email / Account ── -->
                <div v-if="step === 2">
                    <h2 class="text-2xl font-bold text-white mb-2">Continue Your Application</h2>
                    <p class="text-gray-400 mb-8">Enter your email to save your progress and receive updates.</p>

                    <form @submit.prevent="submitEmail" class="space-y-5">
                        <div>
                            <label class="block text-gray-300 text-sm font-medium mb-2">Email Address *</label>
                            <input v-model="emailForm.email" type="email" required
                                   class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white placeholder-gray-500 focus:outline-none focus:border-yellow-400"
                                   placeholder="your@email.com" />
                        </div>

                        <div class="space-y-3">
                            <label class="flex items-start gap-3 cursor-pointer group">
                                <input v-model="emailForm.confirmed_not_govt" type="checkbox" required
                                       class="mt-1 w-4 h-4 accent-yellow-400 flex-shrink-0" />
                                <span class="text-sm text-gray-300">I understand that CORPIUS is not affiliated with the U.S. government. The DV Lottery is a free government program.</span>
                            </label>
                            <label class="flex items-start gap-3 cursor-pointer group">
                                <input v-model="emailForm.confirmed_tos" type="checkbox" required
                                       class="mt-1 w-4 h-4 accent-yellow-400 flex-shrink-0" />
                                <span class="text-sm text-gray-300">I agree to the <a href="/legal/terms-of-service" target="_blank" class="text-yellow-400 underline">Terms of Service</a> and <a href="/legal/privacy-policy" target="_blank" class="text-yellow-400 underline">Privacy Policy</a>.</span>
                            </label>
                        </div>

                        <button type="submit" :disabled="loading"
                                class="w-full bg-yellow-400 hover:bg-yellow-500 text-[#0b1e33] py-3 rounded-xl font-bold transition-all">
                            {{ loading ? 'Saving…' : 'Continue →' }}
                        </button>
                    </form>
                </div>

                <!-- ── STEP 3: Primary Applicant ── -->
                <div v-if="step === 3">
                    <h2 class="text-2xl font-bold text-white mb-2">Primary Applicant Details</h2>
                    <p class="text-gray-400 mb-8">Enter your information exactly as shown on your passport.</p>

                    <form @submit.prevent="submitApplicant" class="space-y-6">
                        <!-- Personal -->
                        <div class="bg-white/5 rounded-2xl p-6 space-y-4">
                            <h3 class="font-semibold text-yellow-400">Personal Information</h3>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div>
                                    <label class="label-field">First Name *</label>
                                    <input v-model="applicantForm.first_name" required class="input-field" />
                                </div>
                                <div>
                                    <label class="label-field">Middle Name</label>
                                    <input v-model="applicantForm.middle_name" class="input-field" />
                                </div>
                                <div>
                                    <label class="label-field">Last Name *</label>
                                    <input v-model="applicantForm.last_name" required class="input-field" />
                                </div>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="label-field">Gender *</label>
                                    <select v-model="applicantForm.gender" required class="input-field">
                                        <option value="">Select…</option>
                                        <option value="male">Male</option>
                                        <option value="female">Female</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="label-field">Date of Birth *</label>
                                    <input v-model="applicantForm.date_of_birth" type="date" required class="input-field" />
                                </div>
                                <div>
                                    <label class="label-field">City of Birth *</label>
                                    <input v-model="applicantForm.city_of_birth" required class="input-field" />
                                </div>
                                <div>
                                    <label class="label-field">Country of Birth *</label>
                                    <input v-model="applicantForm.country_of_birth" required class="input-field" />
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="label-field">Country of Eligibility *</label>
                                    <input v-model="applicantForm.country_of_eligibility" required class="input-field" placeholder="Default: same as country of birth" />
                                </div>
                            </div>
                        </div>

                        <!-- Passport -->
                        <div class="bg-white/5 rounded-2xl p-6 space-y-4">
                            <h3 class="font-semibold text-yellow-400">Passport Information</h3>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div>
                                    <label class="label-field">Passport Number *</label>
                                    <input v-model="applicantForm.passport_number" required class="input-field" />
                                </div>
                                <div>
                                    <label class="label-field">Country of Issuance *</label>
                                    <input v-model="applicantForm.passport_country" required class="input-field" />
                                </div>
                                <div>
                                    <label class="label-field">Expiration Date *</label>
                                    <input v-model="applicantForm.passport_expiry" type="date" required class="input-field" />
                                </div>
                            </div>
                        </div>

                        <!-- Contact -->
                        <div class="bg-white/5 rounded-2xl p-6 space-y-4">
                            <h3 class="font-semibold text-yellow-400">Contact Information</h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="sm:col-span-2">
                                    <label class="label-field">Address Line 1 *</label>
                                    <input v-model="applicantForm.address_line_1" required class="input-field" />
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="label-field">Address Line 2</label>
                                    <input v-model="applicantForm.address_line_2" class="input-field" />
                                </div>
                                <div>
                                    <label class="label-field">City *</label>
                                    <input v-model="applicantForm.city" required class="input-field" />
                                </div>
                                <div>
                                    <label class="label-field">State / Province</label>
                                    <input v-model="applicantForm.state_province" class="input-field" />
                                </div>
                                <div>
                                    <label class="label-field">Postal Code</label>
                                    <input v-model="applicantForm.postal_code" class="input-field" />
                                </div>
                                <div>
                                    <label class="label-field">Country *</label>
                                    <input v-model="applicantForm.country" required class="input-field" />
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="label-field">Phone Number</label>
                                    <input v-model="applicantForm.phone" type="tel" class="input-field" />
                                </div>
                            </div>
                        </div>

                        <!-- Education -->
                        <div class="bg-white/5 rounded-2xl p-6">
                            <label class="label-field">Education Level *</label>
                            <select v-model="applicantForm.education_level" required class="input-field mt-2">
                                <option value="">Select education level…</option>
                                <option value="high_school">High School Diploma</option>
                                <option value="some_college">Some College</option>
                                <option value="bachelors">Bachelor's Degree</option>
                                <option value="masters">Master's Degree</option>
                                <option value="doctorate">Doctorate</option>
                            </select>
                        </div>

                        <button type="submit" :disabled="loading"
                                class="w-full bg-yellow-400 hover:bg-yellow-500 text-[#0b1e33] py-3 rounded-xl font-bold transition-all">
                            {{ loading ? 'Saving…' : 'Continue →' }}
                        </button>
                    </form>
                </div>

                <!-- ── STEP 4: Family Members ── -->
                <div v-if="step === 4">
                    <h2 class="text-2xl font-bold text-white mb-2">Family Information</h2>
                    <p class="text-gray-400 mb-8">Include your spouse and all unmarried children under 21 if applicable.</p>

                    <div class="space-y-4 mb-6">
                        <div v-for="(member, i) in familyMembers" :key="i"
                             class="bg-white/5 border border-white/10 rounded-2xl p-5">
                            <div class="flex items-center justify-between mb-4">
                                <span class="text-yellow-400 font-semibold capitalize">{{ member.type }}</span>
                                <button @click="removeFamilyMember(i)" class="text-red-400 hover:text-red-300 text-sm">Remove</button>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="label-field">First Name *</label>
                                    <input v-model="member.first_name" required class="input-field" />
                                </div>
                                <div>
                                    <label class="label-field">Last Name *</label>
                                    <input v-model="member.last_name" required class="input-field" />
                                </div>
                                <div>
                                    <label class="label-field">Date of Birth</label>
                                    <input v-model="member.date_of_birth" type="date" class="input-field" />
                                </div>
                                <div>
                                    <label class="label-field">Country of Birth</label>
                                    <input v-model="member.country_of_birth" class="input-field" />
                                </div>
                                <div>
                                    <label class="label-field">Gender</label>
                                    <select v-model="member.gender" class="input-field">
                                        <option value="">Select…</option>
                                        <option value="male">Male</option>
                                        <option value="female">Female</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-3 mb-8">
                        <button @click="addFamilyMember('spouse')"
                                class="flex-1 border border-yellow-400/40 text-yellow-400 hover:bg-yellow-400/10 py-3 rounded-xl text-sm font-semibold transition-all">
                            + Add Spouse
                        </button>
                        <button @click="addFamilyMember('child')"
                                class="flex-1 border border-yellow-400/40 text-yellow-400 hover:bg-yellow-400/10 py-3 rounded-xl text-sm font-semibold transition-all">
                            + Add Child
                        </button>
                    </div>

                    <button @click="submitFamily" :disabled="loading"
                            class="w-full bg-yellow-400 hover:bg-yellow-500 text-[#0b1e33] py-3 rounded-xl font-bold transition-all">
                        {{ loading ? 'Saving…' : 'Continue →' }}
                    </button>
                </div>

                <!-- ── STEP 5: Photo Upload ── -->
                <div v-if="step === 5">
                    <h2 class="text-2xl font-bold text-white mb-2">Upload Applicant Photo</h2>
                    <p class="text-gray-400 mb-4">Your photo must meet U.S. Diversity Visa photo requirements.</p>

                    <!-- Requirements -->
                    <div class="bg-yellow-400/5 border border-yellow-400/20 rounded-xl p-4 mb-8 text-sm text-gray-300">
                        <p class="font-semibold text-yellow-400 mb-2">Photo Requirements:</p>
                        <ul class="space-y-1 list-disc list-inside text-gray-400">
                            <li>Recent color photo, taken within the last 6 months</li>
                            <li>White or off-white background</li>
                            <li>Face clearly visible, looking directly at camera</li>
                            <li>JPEG or PNG format, min 600×600 pixels</li>
                            <li>File size: max 5 MB</li>
                        </ul>
                    </div>

                    <!-- Primary applicant photo -->
                    <div class="bg-white/5 rounded-2xl p-6 mb-4">
                        <h3 class="font-semibold text-white mb-4">Primary Applicant Photo <span class="text-yellow-400">*</span></h3>
                        <div class="flex items-start gap-6">
                            <div class="w-28 h-28 rounded-xl border-2 border-dashed border-white/20 overflow-hidden flex items-center justify-center bg-white/5 flex-shrink-0">
                                <img v-if="primaryPhotoUrl" :src="primaryPhotoUrl" class="w-full h-full object-cover" />
                                <svg v-else class="w-10 h-10 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <input type="file" accept="image/jpeg,image/jpg,image/png"
                                       @change="onPrimaryPhotoChange"
                                       class="block w-full text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-yellow-400 file:text-[#0b1e33] hover:file:bg-yellow-500 cursor-pointer" />
                                <p v-if="photoError" class="mt-2 text-red-400 text-sm">{{ photoError }}</p>
                                <p class="mt-2 text-xs text-gray-500">JPEG or PNG, max 5 MB</p>
                            </div>
                        </div>
                    </div>

                    <button @click="uploadPrimaryPhoto" :disabled="photoUploading || loading"
                            class="w-full bg-yellow-400 hover:bg-yellow-500 text-[#0b1e33] py-3 rounded-xl font-bold transition-all">
                        {{ photoUploading ? 'Uploading…' : 'Continue →' }}
                    </button>
                    <button @click="step = 6" class="w-full mt-3 text-gray-400 hover:text-white text-sm transition-colors">
                        Skip for now →
                    </button>
                </div>

                <!-- ── STEP 6: Documents ── -->
                <div v-if="step === 6">
                    <h2 class="text-2xl font-bold text-white mb-2">Upload Supporting Documents</h2>
                    <p class="text-gray-400 mb-8">These documents help our specialists verify your application accuracy.</p>

                    <!-- Uploaded documents list -->
                    <div v-if="documents.length" class="space-y-2 mb-6">
                        <div v-for="doc in documents" :key="doc.id"
                             class="flex items-center gap-3 bg-white/5 border border-white/10 rounded-xl p-3">
                            <svg class="w-5 h-5 text-yellow-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <span class="text-white text-sm flex-1">{{ doc.label }}</span>
                            <span class="text-gray-500 text-xs">{{ doc.original_name }}</span>
                        </div>
                    </div>

                    <!-- Upload form -->
                    <div class="bg-white/5 rounded-2xl p-6 mb-6">
                        <div class="grid gap-4">
                            <div>
                                <label class="label-field">Document Type</label>
                                <select v-model="selectedDocType" class="input-field mt-1">
                                    <option value="">Select document type…</option>
                                    <option v-for="d in docTypes" :key="d.type" :value="d.type">{{ d.label }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="label-field">Select File (PDF, JPEG, PNG — max 10 MB)</label>
                                <input type="file" accept=".pdf,image/jpeg,image/jpg,image/png"
                                       @change="onDocChange"
                                       class="mt-1 block w-full text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-yellow-400 file:text-[#0b1e33] hover:file:bg-yellow-500 cursor-pointer" />
                            </div>
                            <button @click="uploadDocument" :disabled="!docFile || !selectedDocType || docUploading"
                                    class="w-full border border-yellow-400 text-yellow-400 hover:bg-yellow-400 hover:text-[#0b1e33] py-2.5 rounded-xl font-semibold transition-all disabled:opacity-40">
                                {{ docUploading ? 'Uploading…' : 'Upload Document' }}
                            </button>
                        </div>
                    </div>

                    <button @click="step = 7"
                            class="w-full bg-yellow-400 hover:bg-yellow-500 text-[#0b1e33] py-3 rounded-xl font-bold transition-all">
                        Continue →
                    </button>
                    <button @click="step = 7" class="w-full mt-3 text-gray-400 hover:text-white text-sm transition-colors">
                        Skip — continue without documents
                    </button>
                </div>

                <!-- ── STEP 7: Review ── -->
                <div v-if="step === 7">
                    <h2 class="text-2xl font-bold text-white mb-2">Review Your Application</h2>
                    <p class="text-gray-400 mb-8">Please review your information before proceeding to payment.</p>

                    <!-- Summary -->
                    <div class="space-y-4 mb-8">
                        <div class="bg-white/5 border border-white/10 rounded-2xl p-5">
                            <h3 class="text-yellow-400 font-semibold mb-3">Primary Applicant</h3>
                            <div class="grid grid-cols-2 gap-2 text-sm">
                                <div class="text-gray-400">Name</div>
                                <div class="text-white">{{ applicantForm.first_name }} {{ applicantForm.last_name }}</div>
                                <div class="text-gray-400">Date of Birth</div>
                                <div class="text-white">{{ applicantForm.date_of_birth }}</div>
                                <div class="text-gray-400">Country of Birth</div>
                                <div class="text-white">{{ applicantForm.country_of_birth }}</div>
                                <div class="text-gray-400">Passport</div>
                                <div class="text-white">{{ applicantForm.passport_number }}</div>
                                <div class="text-gray-400">Education</div>
                                <div class="text-white capitalize">{{ applicantForm.education_level?.replace('_', ' ') }}</div>
                            </div>
                        </div>

                        <div v-if="familyMembers.length" class="bg-white/5 border border-white/10 rounded-2xl p-5">
                            <h3 class="text-yellow-400 font-semibold mb-3">Family Members ({{ familyMembers.length }})</h3>
                            <div v-for="m in familyMembers" :key="m.id ?? m.first_name" class="text-sm text-gray-300 mb-1">
                                <span class="capitalize text-gray-400">{{ m.type }}:</span> {{ m.first_name }} {{ m.last_name }}
                            </div>
                        </div>

                        <div v-if="documents.length" class="bg-white/5 border border-white/10 rounded-2xl p-5">
                            <h3 class="text-yellow-400 font-semibold mb-3">Uploaded Documents ({{ documents.length }})</h3>
                            <div v-for="d in documents" :key="d.id" class="text-sm text-gray-300 mb-1">✓ {{ d.label }}</div>
                        </div>
                    </div>

                    <!-- Confirmations -->
                    <div class="bg-white/5 rounded-2xl p-5 mb-6 space-y-3">
                        <label class="flex items-start gap-3 cursor-pointer">
                            <input v-model="reviewForm.confirmed_accuracy" type="checkbox"
                                   class="mt-1 w-4 h-4 accent-yellow-400 flex-shrink-0" />
                            <span class="text-sm text-gray-300">I confirm that all information provided is accurate and complete.</span>
                        </label>
                        <label class="flex items-start gap-3 cursor-pointer">
                            <input v-model="reviewForm.confirmed_single_entry" type="checkbox"
                                   class="mt-1 w-4 h-4 accent-yellow-400 flex-shrink-0" />
                            <span class="text-sm text-gray-300">I understand that submitting multiple DV Lottery entries in the same year may result in disqualification.</span>
                        </label>
                    </div>

                    <button @click="submitReview"
                            :disabled="!reviewForm.confirmed_accuracy || !reviewForm.confirmed_single_entry || loading"
                            class="w-full bg-yellow-400 hover:bg-yellow-500 text-[#0b1e33] py-3 rounded-xl font-bold transition-all disabled:opacity-40">
                        {{ loading ? 'Saving…' : 'Continue to Payment →' }}
                    </button>
                </div>

                <!-- ── STEP 8: Payment ── -->
                <div v-if="step === 8">
                    <h2 class="text-2xl font-bold text-white mb-2">Secure Payment</h2>
                    <p class="text-gray-400 mb-8">Complete your payment to submit your application for review.</p>

                    <!-- Order summary -->
                    <div class="bg-white/5 border border-white/10 rounded-2xl p-6 mb-6">
                        <h3 class="font-semibold text-white mb-4">Order Summary</h3>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-400">Application Package</span>
                                <span class="text-white capitalize">{{ selectedPackage }} Application</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-400">Service Fee</span>
                                <span class="text-yellow-400 font-semibold">
                                    {{ selectedPackage === 'basic' ? '$49' : selectedPackage === 'family' ? '$89' : '$149' }}
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-400">U.S. Government Fee</span>
                                <span class="text-green-400">$0 (Free)</span>
                            </div>
                            <div class="border-t border-white/10 pt-2 flex justify-between font-semibold">
                                <span class="text-white">Total</span>
                                <span class="text-yellow-400">
                                    {{ selectedPackage === 'basic' ? '$49' : selectedPackage === 'family' ? '$89' : '$149' }}
                                </span>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-4">
                            The U.S. government does not charge a fee to enter the Diversity Visa Lottery.
                            Your payment covers professional preparation and review services by CORPIUS.
                        </p>
                    </div>

                    <!-- Payment methods -->
                    <div class="space-y-3 mb-6">
                        <button @click="confirmPayment" :disabled="loading"
                                class="w-full flex items-center justify-center gap-3 bg-yellow-400 hover:bg-yellow-500 text-[#0b1e33] py-4 rounded-xl font-bold transition-all">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                            </svg>
                            {{ loading ? 'Processing…' : 'Pay & Submit Application' }}
                        </button>
                    </div>

                    <!-- Legal disclaimers -->
                    <div class="bg-white/5 border border-white/10 rounded-xl p-4 text-xs text-gray-500 space-y-2">
                        <p>✓ CORPIUS is a private service provider and is not affiliated with the U.S. government.</p>
                        <p>✓ The official DV Lottery entry is free on the U.S. Department of State website.</p>
                        <p>✓ Selection in the Diversity Visa Lottery is random and not guaranteed.</p>
                        <p>✓ Your payment covers application preparation, document verification, and support.</p>
                    </div>
                </div>

                <!-- ── STEP 9: Confirmation ── -->
                <div v-if="step === 9" class="text-center">
                    <div class="w-20 h-20 bg-yellow-400/10 border-2 border-yellow-400 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-10 h-10 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <h2 class="text-3xl font-bold text-white mb-3">Application Received!</h2>
                    <p class="text-gray-400 mb-8">Your application has been successfully submitted for review.</p>

                    <div class="bg-white/5 border border-white/10 rounded-2xl p-6 text-left mb-8">
                        <h3 class="font-semibold text-yellow-400 mb-4">What happens next:</h3>
                        <div class="space-y-3">
                            <div class="flex items-start gap-3">
                                <div class="w-6 h-6 bg-yellow-400 text-[#0b1e33] rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0 mt-0.5">1</div>
                                <p class="text-gray-300 text-sm">Our specialists will verify your application and documents.</p>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="w-6 h-6 bg-yellow-400 text-[#0b1e33] rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0 mt-0.5">2</div>
                                <p class="text-gray-300 text-sm">We'll contact you if any corrections are required.</p>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="w-6 h-6 bg-yellow-400 text-[#0b1e33] rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0 mt-0.5">3</div>
                                <p class="text-gray-300 text-sm">Once finalized, your application will be prepared for DV Lottery submission.</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-3">
                        <a href="/dashboard" class="flex-1 bg-yellow-400 hover:bg-yellow-500 text-[#0b1e33] py-3 rounded-xl font-bold transition-all text-center">
                            View Application Status
                        </a>
                        <a href="/contact" class="flex-1 border border-white/20 text-white hover:bg-white/5 py-3 rounded-xl font-semibold transition-all text-center">
                            Contact Support
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </MarketingLayout>
</template>

<style scoped>
.label-field {
    @apply block text-gray-300 text-sm font-medium mb-1;
}
.input-field {
    @apply w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white placeholder-gray-500 focus:outline-none focus:border-yellow-400 transition-colors;
}
select.input-field option {
    background-color: #0b1e33;
    color: white;
}
</style>
