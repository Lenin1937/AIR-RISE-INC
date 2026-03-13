<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed, onMounted, ref, watch } from 'vue';

const currentStep = ref(1);

// ─── Draft persistence ───────────────────────────────────────────────────────
const DRAFT_KEY = 'corpius_order_draft';
const hasDraft  = ref(false);   // true while the restored-draft banner is visible

let draftSaveTimer = null; // for debounced backend save

const saveDraft = () => {
    try {
        localStorage.setItem(DRAFT_KEY, JSON.stringify({
            step:             currentStep.value,
            serviceType:      form.serviceType,
            state:            form.state,
            businessName:     form.businessName,
            businessPurpose:  form.businessPurpose,
            speedOption:      form.speedOption,
            addons:           [...form.addons],
            applicantName:    form.applicantName,
            applicantEmail:   form.applicantEmail,
            applicantPhone:   form.applicantPhone,
            applicantDob:     form.applicantDob,
            applicantSsn:     form.applicantSsn,
            applicantAddress: form.applicantAddress,
            applicantCity:    form.applicantCity,
            applicantZip:     form.applicantZip,
            applicantCountry: form.applicantCountry,
            paymentMethod:    form.paymentMethod,
        }));
    } catch (_) {}
    // Debounced backend save (fires 3 s after the last change)
    clearTimeout(draftSaveTimer);
    draftSaveTimer = setTimeout(() => {
        window.axios.post(route('orders.draft.save'), {
            step:             currentStep.value,
            serviceType:      form.serviceType,
            state:            form.state,
            businessName:     form.businessName,
            businessPurpose:  form.businessPurpose,
            speedOption:      form.speedOption,
            addons:           [...form.addons],
            applicantName:    form.applicantName,
            applicantEmail:   form.applicantEmail,
            applicantPhone:   form.applicantPhone,
            applicantDob:     form.applicantDob,
            applicantSsn:     form.applicantSsn,
            applicantAddress: form.applicantAddress,
            applicantCity:    form.applicantCity,
            applicantZip:     form.applicantZip,
            applicantCountry: form.applicantCountry,
            paymentMethod:    form.paymentMethod,
        }).catch(() => {});
    }, 3000);
};

const clearDraft = () => {
    try { localStorage.removeItem(DRAFT_KEY); } catch (_) {}
    clearTimeout(draftSaveTimer);
    hasDraft.value = false;
    window.axios.delete(route('orders.draft.delete')).catch(() => {});
};

const discardDraft = () => {
    clearDraft();
    // Reset to defaults
    currentStep.value        = 1;
    form.serviceType         = '';
    form.state               = '';
    form.businessName        = '';
    form.businessPurpose     = '';
    form.speedOption         = 'economic';
    form.addons              = ['ein', 'corporate_kit'];
    form.applicantName       = '';
    form.applicantEmail      = '';
    form.applicantPhone      = '';
    form.applicantDob        = '';
    form.applicantSsn        = '';
    form.applicantAddress    = '';
    form.applicantCity       = '';
    form.applicantZip        = '';
    form.applicantCountry    = '';
    form.paymentMethod       = 'stripe';
};

// Shared helper: apply a draft object d onto the form
const applyDraft = (d) => {
    if (d.step)             currentStep.value        = d.step;
    if (d.serviceType)      form.serviceType         = d.serviceType;
    if (d.state)            form.state               = d.state;
    if (d.businessName)     form.businessName        = d.businessName;
    if (d.businessPurpose)  form.businessPurpose     = d.businessPurpose;
    if (d.speedOption)      form.speedOption         = d.speedOption;
    if (Array.isArray(d.addons)) form.addons         = d.addons;
    if (d.applicantName)    form.applicantName       = d.applicantName;
    if (d.applicantEmail)   form.applicantEmail      = d.applicantEmail;
    if (d.applicantPhone)   form.applicantPhone      = d.applicantPhone;
    if (d.applicantDob)     form.applicantDob        = d.applicantDob;
    if (d.applicantSsn)     form.applicantSsn        = d.applicantSsn;
    if (d.applicantAddress) form.applicantAddress    = d.applicantAddress;
    if (d.applicantCity)    form.applicantCity       = d.applicantCity;
    if (d.applicantZip)     form.applicantZip        = d.applicantZip;
    if (d.applicantCountry) form.applicantCountry    = d.applicantCountry;
    if (d.paymentMethod)    form.paymentMethod       = d.paymentMethod;
};

onMounted(async () => {
    // 1. Try localStorage first (faster, no network round-trip)
    try {
        const raw = localStorage.getItem(DRAFT_KEY);
        if (raw) {
            const d = JSON.parse(raw);
            if (d.serviceType || d.businessName || d.applicantName) {
                applyDraft(d);
                hasDraft.value = true;
                return; // local draft restored; skip server fetch
            }
        }
    } catch (_) {}
    // 2. Fallback: load draft from server (different device or cleared storage)
    try {
        const res = await window.axios.get(route('orders.draft.get'));
        const d = res.data?.draft;
        if (d && (d.serviceType || d.businessName || d.applicantName)) {
            applyDraft(d);
            hasDraft.value = true;
        }
    } catch (_) {}
});

// Auto-save whenever any field or step changes
watch(
    () => ({
        step:             currentStep.value,
        serviceType:      form.serviceType,
        state:            form.state,
        businessName:     form.businessName,
        businessPurpose:  form.businessPurpose,
        speedOption:      form.speedOption,
        addons:           [...form.addons],
        applicantName:    form.applicantName,
        applicantEmail:   form.applicantEmail,
        applicantPhone:   form.applicantPhone,
        applicantDob:     form.applicantDob,
        applicantSsn:     form.applicantSsn,
        applicantAddress: form.applicantAddress,
        applicantCity:    form.applicantCity,
        applicantZip:     form.applicantZip,
        applicantCountry: form.applicantCountry,
        paymentMethod:    form.paymentMethod,
    }),
    saveDraft,
    { deep: true },
);

const form = useForm({
    serviceType: '',
    state: '',
    businessName: '',
    businessPurpose: '',
    speedOption: 'economic',
    addons: ['ein', 'corporate_kit'],
    // Personal Information
    applicantName: '',
    applicantEmail: '',
    applicantPhone: '',
    applicantDob: '',
    applicantSsn: '',
    applicantAddress: '',
    applicantCity: '',
    applicantZip: '',
    applicantCountry: '',
    documents: {
        passport: null,
        id_card: null,
        drivers_license: null,
        photos: []
    },
    paymentMethod: 'stripe',
    paymentScreenshot: null
});

const US_STATES = [
    'Alabama', 'Alaska', 'Arizona', 'Arkansas', 'California', 'Colorado', 'Connecticut',
    'Delaware', 'Florida', 'Georgia', 'Hawaii', 'Idaho', 'Illinois', 'Indiana', 'Iowa',
    'Kansas', 'Kentucky', 'Louisiana', 'Maine', 'Maryland', 'Massachusetts', 'Michigan',
    'Minnesota', 'Mississippi', 'Missouri', 'Montana', 'Nebraska', 'Nevada', 'New Hampshire',
    'New Jersey', 'New Mexico', 'New York', 'North Carolina', 'North Dakota', 'Ohio',
    'Oklahoma', 'Oregon', 'Pennsylvania', 'Rhode Island', 'South Carolina', 'South Dakota',
    'Tennessee', 'Texas', 'Utah', 'Vermont', 'Virginia', 'Washington', 'West Virginia',
    'Wisconsin', 'Wyoming'
];

const steps = [
    { number: 1, name: 'Service Selection' },
    { number: 2, name: 'Business Details' },
    { number: 3, name: 'Add-ons & Speed' },
    { number: 4, name: 'Required Documents' },
];

const services = [
    {
        id: 'C-Corporation',
        name: 'C-Corporation',
        description: 'Best for growth and venture capital funding',
        price: 399,
    },
    {
        id: 'S-Corporation',
        name: 'S-Corporation',
        description: 'Tax benefits with pass-through taxation',
        price: 880,
    },
    {
        id: 'LLC',
        name: 'LLC Formation',
        description: 'Flexible structure with liability protection',
        price: 1480,
    },
    {
        id: 'Nonprofit',
        name: 'Nonprofit Organization',
        description: 'Tax-exempt charitable organization',
        price: 499,
    },
    {
        id: 'Green Card Lottery',
        name: 'Green Card Lottery',
        description: 'Professional DV Lottery application assistance',
        price: 49,
    },
    {
        id: 'Income Tax',
        name: 'Income Tax Filing & Planning',
        description: 'Professional income tax service for both corporations and individuals',
        price: 499,
    },
];

const addons = [
    { id: 'registered_agent', name: 'Registered Agent (1 year)', price: 125 },
    { id: 'ein', name: 'EIN Application', price: 0 },
    { id: 'corporate_kit', name: 'Corporate Kit & Seal', price: 85 },
    { id: 'compliance', name: 'Compliance Calendar', price: 50 },
];

const calculateTotal = computed(() => {
    const service = services.find(s => s.id === form.serviceType);
    const serviceFee = service?.price || 0;
    
    // Speed fee only applies to business formation services
    const speedFee = form.serviceType === 'Green Card Lottery' ? 0 :
        (form.speedOption === 'pro' ? 149 : form.speedOption === 'economic' ? 99 : 0);
    
    const addonsFee = addons
        .filter(a => form.addons.includes(a.id))
        .reduce((sum, a) => sum + a.price, 0);
    
    // State fee only applies to business formation services
    const stateFee = form.serviceType === 'Green Card Lottery' ? 0 : 125;
    
    return serviceFee + speedFee + addonsFee + stateFee;
});

const selectedService = computed(() => {
    return services.find(s => s.id === form.serviceType);
});

const canProceed = computed(() => {
    switch (currentStep.value) {
        case 1:
            return form.serviceType !== '';
        case 2:
            if (form.serviceType === 'Green Card Lottery') {
                return true; // Green Card doesn't need state/business name
            }
            return form.state && form.businessName
                && form.applicantName && form.applicantDob
                && form.applicantPhone && form.applicantAddress
                && form.applicantCity && form.applicantZip;
        case 3:
            return true;
        case 4:
            // Document validation
            if (form.serviceType === 'Green Card Lottery') {
                // Green Card needs: Passport/ID/License + 2 photos
                const hasIdentity = form.documents.passport || form.documents.id_card || form.documents.drivers_license;
                const hasPhotos = form.documents.photos.length >= 2;
                return hasIdentity && hasPhotos;
            } else {
                // Business formation: Passport/ID/License required
                return form.documents.passport || form.documents.id_card || form.documents.drivers_license;
            }
        default:
            return false;
    }
});

const nextStep = () => {
    if (!canProceed.value) return;
    if (currentStep.value === 4) {
        submitOrder();
        return;
    }
    if (currentStep.value < 4) {
        currentStep.value++;
    }
};

const prevStep = () => {
    if (currentStep.value > 1) {
        currentStep.value--;
    }
};

const toggleAddon = (addonId) => {
    const index = form.addons.indexOf(addonId);
    if (index > -1) {
        form.addons.splice(index, 1);
    } else {
        form.addons.push(addonId);
    }
};

const handleFileUpload = (event, docType) => {
    const file = event.target.files[0];
    if (file) {
        // Check file size (20MB for documents, 10MB for photos)
        const maxSize = docType === 'photos' ? 10 * 1024 * 1024 : 20 * 1024 * 1024;
        if (file.size > maxSize) {
            const maxSizeMB = docType === 'photos' ? '10MB' : '20MB';
            alert(`File size must not exceed ${maxSizeMB}. Current file size: ${(file.size / 1024 / 1024).toFixed(2)}MB`);
            return;
        }

        if (docType === 'photos') {
            // For photos, add to array
            if (form.documents.photos.length < 2) {
                form.documents.photos.push(file);
            }
        } else {
            // For single documents
            form.documents[docType] = file;
        }
    }
};

const handlePaymentScreenshotUpload = (event) => {
    const file = event.target.files[0];
    if (file) {
        // Check file size (10MB max for payment screenshots)
        const maxSize = 10 * 1024 * 1024;
        if (file.size > maxSize) {
            alert(`Payment screenshot file size must not exceed 10MB. Current file size: ${(file.size / 1024 / 1024).toFixed(2)}MB`);
            return;
        }

        // Check if it's an image file
        if (!file.type.startsWith('image/')) {
            alert('Please upload an image file (JPG, PNG, GIF, etc.)');
            return;
        }

        form.paymentScreenshot = file;
    }
};

const removePaymentScreenshot = () => {
    if (form.paymentScreenshot && form.paymentScreenshot instanceof File) {
        // Clean up the preview URL to prevent memory leaks
        const previewUrl = getFilePreview(form.paymentScreenshot);
        if (previewUrl) {
            URL.revokeObjectURL(previewUrl);
        }
    }
    form.paymentScreenshot = null;
};

const removePhoto = (index) => {
    const photo = form.documents.photos[index];
    if (photo && photo instanceof File) {
        // Clean up the preview URL to prevent memory leaks
        const previewUrl = getFilePreview(photo);
        if (previewUrl) {
            URL.revokeObjectURL(previewUrl);
        }
    }
    form.documents.photos.splice(index, 1);
};

const removeDocument = (docType) => {
    const file = form.documents[docType];
    if (file && file instanceof File) {
        // Clean up the preview URL to prevent memory leaks
        const previewUrl = getFilePreview(file);
        if (previewUrl) {
            URL.revokeObjectURL(previewUrl);
        }
    }
    form.documents[docType] = null;
};

const getDocumentRequirements = computed(() => {
    if (form.serviceType === 'Green Card Lottery') {
        return {
            title: 'Green Card Lottery Required Documents',
            description: 'Please upload the following documents for your Green Card Lottery application:',
            requirements: [
                'One identity document: Passport OR ID Card OR Driver\'s License',
                'Two recent photos (passport style, taken within last 6 months)'
            ]
        };
    } else {
        return {
            title: 'Business Formation Required Documents',
            description: 'Please upload one identity document to verify your identity:',
            requirements: [
                'One identity document: Passport OR ID Card OR Driver\'s License'
            ]
        };
    }
});

const getPayPalInfo = computed(() => {
    return {
        email: 'nyairrise@gmail.com',
        username: 'AIR RISE INC'
    };
});

const getBankInfo = computed(() => {
    return {
        account_name: 'AIR RISE INC',
        account_number: '0123456789',
        branch_name: 'Main Branch',
        routing_number: '021000021'
    };
});

// Truncate long filenames for display and provide full name in a tooltip
const truncateName = (name, length = 28) => {
    if (!name) return '';
    try {
        return name.length > length ? name.slice(0, length - 3) + '...' : name;
    } catch (e) {
        return name;
    }
};

// Create preview URL for uploaded files
const getFilePreview = (file) => {
    if (!file) return null;
    if (file instanceof File && file.type.startsWith('image/')) {
        return URL.createObjectURL(file);
    }
    return null;
};

// Check if file is an image
const isImageFile = (file) => {
    if (!file) return false;
    return file instanceof File && file.type.startsWith('image/');
};

// ─── Input formatters ───────────────────────────────────────────────────────
const formatPhone = (e) => {
    // Strip everything except digits
    let digits = e.target.value.replace(/\D/g, '');
    // Remove leading 1 if user typed it so we always control the +1 prefix
    if (digits.startsWith('1')) digits = digits.slice(1);
    digits = digits.slice(0, 10);
    let out = '';
    if (digits.length === 0) {
        out = '';
    } else if (digits.length <= 3) {
        out = `+1(${digits}`;
    } else if (digits.length <= 6) {
        out = `+1(${digits.slice(0,3)}) ${digits.slice(3)}`;
    } else {
        out = `+1(${digits.slice(0,3)}) ${digits.slice(3,6)}-${digits.slice(6)}`;
    }
    form.applicantPhone = out;
    e.target.value      = out;
};

const formatSsn = (e) => {
    let digits = e.target.value.replace(/\D/g, '').slice(0, 9);
    if (digits.length > 5)      digits = digits.slice(0,3) + '-' + digits.slice(3,5) + '-' + digits.slice(5);
    else if (digits.length > 3) digits = digits.slice(0,3) + '-' + digits.slice(3);
    form.applicantSsn = digits;
    e.target.value    = digits;   // keep cursor-friendly
};

const formatDob = (e) => {
    let digits = e.target.value.replace(/\D/g, '').slice(0, 8);
    let out = '';
    if (digits.length > 4)      out = digits.slice(0,2) + '/' + digits.slice(2,4) + '/' + digits.slice(4);
    else if (digits.length > 2) out = digits.slice(0,2) + '/' + digits.slice(2);
    else                        out = digits;
    form.applicantDob = out;
    e.target.value    = out;
};

const formatZip = (e) => {
    const digits = e.target.value.replace(/\D/g, '').slice(0, 5);
    form.applicantZip = digits;
    e.target.value    = digits;
};

const showSuccess = ref(false);

const isProcessingPayment = ref(false);

const getPostSubmitRoute = (page) => {
    const status = page?.props?.auth?.user?.registration_status;
    if (status === 'pending_approval' || status === 'rejected') {
        return route('onboarding.review');
    }
    return route('orders.index');
};

const submitOrder = async () => {
    // Refresh CSRF token to prevent 419 on stale/timed-out sessions
    try {
        const csrfRes = await fetch('/csrf-token');
        const csrfData = await csrfRes.json();
        const meta = document.head.querySelector('meta[name="csrf-token"]');
        if (meta) meta.setAttribute('content', csrfData.token);
        window.axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfData.token;
    } catch (_) { /* non-fatal — proceed with current token */ }

    // Validate required fields
    if (!form.serviceType) {
        alert('Please select a service type');
        return;
    }
    
    // Business name is only required for business formation services, not for Green Card
    if (form.serviceType !== 'Green Card Lottery' && !form.businessName) {
        alert('Please enter a business name');
        return;
    }
    
    // State is required for business formation services, optional for Green Card
    if (form.serviceType !== 'Green Card Lottery' && !form.state) {
        alert('Please select a state');
        return;
    }
    
    // For Stripe payment, redirect to checkout
    if (form.paymentMethod === 'stripe') {
        isProcessingPayment.value = true;
        // Create Stripe checkout session and redirect
        form.post(route('orders.checkout'), {
            onSuccess: (page) => {
                // Draft is kept until actual payment succeeds (cleared in Payment.vue)
                // Redirect to Stripe Checkout URL
                if (page.props.checkoutUrl) {
                    window.location.href = page.props.checkoutUrl;
                }
            },
            onError: (errors) => {
                isProcessingPayment.value = false;
                console.error('Checkout creation failed:', errors);
                alert('Failed to create payment session. Please try again.');
            },
        });
        return;
    }
    
    // For manual payment methods (bank transfer), require screenshot
    if (!form.paymentScreenshot) {
        alert('Please upload a payment screenshot before submitting your order');
        return;
    }
    
    form.post(route('orders.store'), {
        onSuccess: (page) => {
            clearDraft();
            const redirectRoute = getPostSubmitRoute(page);
            if (redirectRoute === route('onboarding.review')) {
                router.visit(redirectRoute);
                return;
            }

            // Show success message first
            showSuccess.value = true;
            
            // Redirect after showing success message
            setTimeout(() => {
                router.visit(redirectRoute);
            }, 3000);
        },
        onError: () => {
            alert('Order submission failed. Please try again.');
        },
    });
};
</script>

<template>
    <Head title="Create New Order" />
    <AuthenticatedLayout>

        <!-- Success Modal -->
        <div v-if="showSuccess" class="fixed inset-0 bg-black/70 flex items-center justify-center z-50 p-4">
            <div class="w-full max-w-md rounded-2xl bg-[#0c1c30] border border-white/[0.08] p-8 text-center shadow-2xl">
                <div class="w-16 h-16 rounded-full bg-green-400/20 flex items-center justify-center mx-auto mb-5">
                    <svg style="width:28px;height:28px" class="text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <h3 class="text-[20px] font-bold text-white mb-2">Order Successfully Submitted!</h3>
                <p class="text-[13px] text-gray-400 mb-6 leading-relaxed">Your order has been received and we will begin processing it shortly. Redirecting you now…</p>
                <div class="flex items-center justify-center gap-2 text-gray-400">
                    <svg class="animate-spin" style="width:18px;height:18px" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                    </svg>
                    <span class="text-[13px]">Redirecting…</span>
                </div>
            </div>
        </div>

        <div class="max-w-4xl mx-auto space-y-7">

            <!-- Header -->
            <div>
                <h1 class="text-[22px] font-bold text-white tracking-tight">Create New Order</h1>
                <p class="mt-0.5 text-[13px] text-gray-400">Complete the steps below to start your incorporation</p>
            </div>

            <!-- Draft restored banner -->
            <div v-if="hasDraft"
                 class="flex items-center justify-between gap-3 rounded-xl border border-amber-400/30 bg-amber-400/10 px-4 py-3">
                <div class="flex items-center gap-2.5">
                    <svg class="flex-shrink-0 text-amber-400" style="width:16px;height:16px" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="text-[13px] text-amber-300 font-medium">Your previous progress has been restored. You can continue where you left off.</span>
                </div>
                <div class="flex items-center gap-3 flex-shrink-0">
                    <button type="button" @click="hasDraft = false"
                            class="text-[12px] text-amber-400/70 hover:text-amber-300 transition">
                        Dismiss
                    </button>
                    <button type="button" @click="discardDraft"
                            class="text-[12px] text-gray-500 hover:text-gray-300 transition">
                        Start fresh
                    </button>
                </div>
            </div>

            <!-- Progress Steps -->
            <div class="flex items-center">
                <template v-for="(step, index) in steps" :key="step.number">
                    <div class="flex flex-col items-center flex-shrink-0">
                        <div :class="['w-9 h-9 rounded-full flex items-center justify-center text-[13px] font-bold border-2 transition',
                            currentStep > step.number ? 'bg-amber-400 border-amber-400 text-[#07101e]' :
                            currentStep === step.number ? 'bg-amber-400 border-amber-400 text-[#07101e] shadow-lg shadow-amber-400/30' :
                            'bg-transparent border-white/[0.15] text-gray-500']">
                            <svg v-if="currentStep > step.number" style="width:14px;height:14px" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span v-else>{{ step.number }}</span>
                        </div>
                        <span :class="['text-[10px] mt-1.5 font-medium text-center leading-tight hidden sm:block',
                            currentStep >= step.number ? 'text-amber-400' : 'text-gray-600']">{{ step.name }}</span>
                    </div>
                    <div v-if="index < steps.length - 1" :class="['flex-1 h-[2px] mx-2 transition',
                        currentStep > step.number ? 'bg-amber-400' : 'bg-white/[0.07]']"/>
                </template>
            </div>

            <!-- Step Content Card -->
            <div class="rounded-2xl border border-white/[0.07] bg-[#0c1c30] overflow-hidden" style="box-shadow:0 0 40px 0 rgba(244,184,64,.06)">

                <!-- ───── STEP 1: Service Selection ───── -->
                <div v-if="currentStep === 1" class="p-4 sm:p-7">
                    <h2 class="text-[18px] font-bold text-white mb-6">Choose Your Service</h2>
                    <div class="grid sm:grid-cols-2 gap-4">
                        <button
                            v-for="service in services"
                            :key="service.id"
                            @click="form.serviceType = service.id"
                            :class="['relative p-5 rounded-2xl border-2 text-left transition',
                                form.serviceType === service.id
                                    ? 'border-amber-400/60 bg-amber-400/[0.06] shadow-lg shadow-amber-400/10'
                                    : 'border-white/[0.07] bg-white/[0.02] hover:border-amber-400/30']"
                        >
                            <div class="flex items-start justify-between mb-3">
                                <div :class="['w-10 h-10 rounded-xl flex items-center justify-center',
                                    service.id === 'Green Card Lottery' ? 'bg-green-500/20' : 'bg-blue-500/20']">
                                    <svg v-if="service.id === 'Green Card Lottery'" style="width:18px;height:18px" class="text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <svg v-else style="width:18px;height:18px" class="text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                    </svg>
                                </div>
                                <div v-if="form.serviceType === service.id" class="w-5 h-5 rounded-full bg-amber-400 flex items-center justify-center">
                                    <svg style="width:11px;height:11px" class="text-[#07101e]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                            </div>
                            <h3 class="text-[15px] font-bold text-white mb-1">{{ service.name }}</h3>
                            <p class="text-[12px] text-gray-400 mb-3 leading-relaxed">{{ service.description }}</p>
                            <p class="text-[20px] font-bold text-amber-400">${{ service.price }}</p>
                        </button>
                    </div>
                </div>

                <!-- ───── STEP 2: Business / Application Details ───── -->
                <div v-if="currentStep === 2" class="p-4 sm:p-7">
                    <h2 class="text-[18px] font-bold text-white mb-6">
                        {{ form.serviceType === 'Green Card Lottery' ? 'Application Details' : 'Business Details' }}
                    </h2>

                    <!-- Green Card info -->
                    <div v-if="form.serviceType === 'Green Card Lottery'" class="rounded-xl border border-green-400/20 bg-green-400/[0.05] p-5">
                        <div class="flex items-start gap-3">
                            <div class="w-9 h-9 rounded-xl bg-green-400/20 flex items-center justify-center flex-shrink-0">
                                <svg style="width:16px;height:16px" class="text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-[14px] font-bold text-white mb-2">Green Card Lottery Application</h3>
                                <p class="text-[12px] text-gray-300 mb-2">Our experts will handle your complete DV Lottery application including:</p>
                                <ul class="text-[12px] text-gray-400 space-y-1">
                                    <li>• Professional photo editing and compliance check</li>
                                    <li>• Error-free form completion and submission</li>
                                    <li>• Application status monitoring</li>
                                    <li>• Expert review and quality assurance</li>
                                </ul>
                                <p class="text-[12px] text-green-400 mt-3 font-medium">We'll contact you within 24 hours to collect your personal information and photos.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Business formation fields -->
                    <div v-else class="space-y-5">
                        <div>
                            <label class="block text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-2">State of Incorporation</label>
                            <select v-model="form.state"
                                class="w-full h-10 px-3.5 rounded-xl bg-[#0a1628] border border-white/[0.08] text-[13px] text-gray-200 focus:outline-none focus:border-amber-400/50 focus:ring-1 focus:ring-amber-400/20 transition">
                                <option value="">Select a state</option>
                                <option v-for="st in US_STATES" :key="st" :value="st">{{ st }}</option>
                            </select>
                        </div>
                        <div v-if="form.serviceType !== 'Green Card Lottery'">
                            <label class="block text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-2">Business Name <span class="text-red-400">*</span></label>
                            <input type="text" v-model="form.businessName" placeholder="Your Business Inc."
                                class="w-full h-10 px-3.5 rounded-xl bg-white/[0.04] border border-white/[0.08] text-[13px] text-gray-200 placeholder-gray-600 focus:outline-none focus:border-amber-400/50 focus:ring-1 focus:ring-amber-400/20 transition"/>
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-2">Business Purpose</label>
                            <textarea v-model="form.businessPurpose" rows="4" placeholder="Describe your business activities…"
                                class="w-full px-3.5 py-3 rounded-xl bg-white/[0.04] border border-white/[0.08] text-[13px] text-gray-200 placeholder-gray-600 focus:outline-none focus:border-amber-400/50 focus:ring-1 focus:ring-amber-400/20 transition resize-none"/>
                        </div>

                        <!-- Personal Information -->
                        <div class="pt-2 border-t border-white/[0.06]">
                            <h3 class="text-[13px] font-bold text-gray-300 mb-4 flex items-center gap-2">
                                <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                Personal Information
                            </h3>
                            <div class="space-y-4">
                                <!-- Full Name -->
                                <div class="grid sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-2">Full Name <span class="text-red-400">*</span></label>
                                        <input type="text" v-model="form.applicantName" placeholder="John Michael Doe"
                                            class="w-full h-10 px-3.5 rounded-xl bg-white/[0.04] border border-white/[0.08] text-[13px] text-gray-200 placeholder-gray-600 focus:outline-none focus:border-amber-400/50 focus:ring-1 focus:ring-amber-400/20 transition"/>
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-2">Email Address</label>
                                        <input type="email" v-model="form.applicantEmail" placeholder="john@example.com"
                                            class="w-full h-10 px-3.5 rounded-xl bg-white/[0.04] border border-white/[0.08] text-[13px] text-gray-200 placeholder-gray-600 focus:outline-none focus:border-amber-400/50 focus:ring-1 focus:ring-amber-400/20 transition"/>
                                    </div>
                                </div>
                                <div class="grid sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-2">Phone Number <span class="text-red-400">*</span></label>
                                        <input type="tel" :value="form.applicantPhone" @input="formatPhone" placeholder="+1(347) 417-3445" maxlength="15" inputmode="tel"
                                            class="w-full h-10 px-3.5 rounded-xl bg-white/[0.04] border border-white/[0.08] text-[13px] text-gray-200 placeholder-gray-600 focus:outline-none focus:border-amber-400/50 focus:ring-1 focus:ring-amber-400/20 transition"/>
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-2">Date of Birth <span class="text-red-400">*</span></label>
                                        <input type="text" :value="form.applicantDob" @input="formatDob" placeholder="MM/DD/YYYY" maxlength="10" autocomplete="bday"
                                            class="w-full h-10 px-3.5 rounded-xl bg-white/[0.04] border border-white/[0.08] text-[13px] text-gray-200 placeholder-gray-600 focus:outline-none focus:border-amber-400/50 focus:ring-1 focus:ring-amber-400/20 transition"/>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-2">
                                        SSN / ITIN
                                        <span class="ml-1 text-[10px] text-gray-500 normal-case tracking-normal">(Encrypted &amp; Secure)</span>
                                    </label>
                                    <input type="password" :value="form.applicantSsn" @input="formatSsn" placeholder="XXX-XX-XXXX" maxlength="11" autocomplete="off" inputmode="numeric"
                                        class="w-full h-10 px-3.5 rounded-xl bg-white/[0.04] border border-white/[0.08] text-[13px] text-gray-200 placeholder-gray-600 focus:outline-none focus:border-amber-400/50 focus:ring-1 focus:ring-amber-400/20 transition"/>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-2">Street Address <span class="text-red-400">*</span></label>
                                    <input type="text" v-model="form.applicantAddress" placeholder="123 Main Street, Apt 4B"
                                        class="w-full h-10 px-3.5 rounded-xl bg-white/[0.04] border border-white/[0.08] text-[13px] text-gray-200 placeholder-gray-600 focus:outline-none focus:border-amber-400/50 focus:ring-1 focus:ring-amber-400/20 transition"/>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                    <div>
                                        <label class="block text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-2">City <span class="text-red-400">*</span></label>
                                        <input type="text" v-model="form.applicantCity" placeholder="New York"
                                            class="w-full h-10 px-3.5 rounded-xl bg-white/[0.04] border border-white/[0.08] text-[13px] text-gray-200 placeholder-gray-600 focus:outline-none focus:border-amber-400/50 focus:ring-1 focus:ring-amber-400/20 transition"/>
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-2">ZIP Code <span class="text-red-400">*</span></label>
                                        <input type="text" :value="form.applicantZip" @input="formatZip" placeholder="10001" maxlength="5" inputmode="numeric"
                                            class="w-full h-10 px-3.5 rounded-xl bg-white/[0.04] border border-white/[0.08] text-[13px] text-gray-200 placeholder-gray-600 focus:outline-none focus:border-amber-400/50 focus:ring-1 focus:ring-amber-400/20 transition"/>
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-2">Country</label>
                                        <input type="text" v-model="form.applicantCountry" placeholder="United States"
                                            class="w-full h-10 px-3.5 rounded-xl bg-white/[0.04] border border-white/[0.08] text-[13px] text-gray-200 placeholder-gray-600 focus:outline-none focus:border-amber-400/50 focus:ring-1 focus:ring-amber-400/20 transition"/>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ───── STEP 3: Add-ons & Speed ───── -->
                <div v-if="currentStep === 3" class="p-4 sm:p-7">
                    <h2 class="text-[18px] font-bold text-white mb-6">
                        {{ form.serviceType === 'Green Card Lottery' ? 'Service Options' : 'Processing Speed & Add-ons' }}
                    </h2>

                    <!-- Speed (not for Green Card) -->
                    <div v-if="form.serviceType !== 'Green Card Lottery'" class="mb-7">
                        <h3 class="text-[13px] font-bold text-gray-400 uppercase tracking-wider mb-4">Processing Speed</h3>
                        <div class="grid sm:grid-cols-2 gap-4">
                            <button @click="form.speedOption = 'economic'"
                                :class="['p-5 rounded-2xl border-2 text-left transition', form.speedOption === 'economic' ? 'border-amber-400/60 bg-amber-400/[0.06]' : 'border-white/[0.07] bg-white/[0.02] hover:border-amber-400/30']">
                                <div class="text-[14px] font-bold text-white mb-1">Economic (14–25 days)</div>
                                <div class="text-[22px] font-bold text-amber-400">+$99</div>
                            </button>
                            <button @click="form.speedOption = 'pro'"
                                :class="['p-5 rounded-2xl border-2 text-left transition', form.speedOption === 'pro' ? 'border-amber-400/60 bg-amber-400/[0.06]' : 'border-white/[0.07] bg-white/[0.02] hover:border-amber-400/30']">
                                <div class="text-[14px] font-bold text-white mb-1">Pro (2–4 days)</div>
                                <div class="text-[22px] font-bold text-amber-400">+$149</div>
                            </button>
                        </div>
                    </div>

                    <!-- Green Card timeline -->
                    <div v-if="form.serviceType === 'Green Card Lottery'" class="mb-7 rounded-xl border border-green-400/20 bg-green-400/[0.05] p-5">
                        <h3 class="text-[13px] font-bold text-white mb-4">Service Timeline</h3>
                        <div class="space-y-3">
                            <div v-for="item in [['Day 1:','We contact you for information and photos'],['Day 2–3:','Professional photo editing and form preparation'],['Day 4:','Quality review and submission to U.S. State Department'],['Ongoing:','Application status monitoring and updates']]" :key="item[0]" class="flex items-start gap-3">
                                <div class="w-5 h-5 rounded-full bg-green-400/20 flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <svg style="width:11px;height:11px" class="text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <span class="text-[12px] text-gray-300"><strong class="text-white">{{ item[0] }}</strong> {{ item[1] }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Add-ons -->
                    <div>
                        <h3 class="text-[13px] font-bold text-gray-400 uppercase tracking-wider mb-4">{{ form.serviceType === 'Green Card Lottery' ? 'Additional Services' : 'Add-on Services' }}</h3>
                        <div class="space-y-3">
                            <label v-for="addon in addons" :key="addon.id"
                                class="flex items-center justify-between p-4 rounded-xl border border-white/[0.07] bg-white/[0.02] hover:border-amber-400/30 cursor-pointer transition">
                                <div class="flex items-center gap-3">
                                    <input type="checkbox" :checked="form.addons.includes(addon.id)" @change="toggleAddon(addon.id)"
                                        class="w-4 h-4 rounded border-white/20 accent-amber-400"/>
                                    <span class="text-[13px] font-medium text-gray-200">{{ addon.name }}</span>
                                </div>
                                <span class="text-[13px] font-bold text-amber-400">{{ addon.price === 0 ? 'Included' : `$${addon.price}` }}</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- ───── STEP 4: Required Documents ───── -->
                <div v-if="currentStep === 4" class="p-4 sm:p-7">
                    <h2 class="text-[18px] font-bold text-white mb-6">{{ getDocumentRequirements.title }}</h2>

                    <div class="rounded-xl border border-blue-400/20 bg-blue-400/[0.05] p-4 mb-6">
                        <div class="flex items-start gap-3">
                            <div class="w-7 h-7 rounded-lg bg-blue-400/20 flex items-center justify-center flex-shrink-0">
                                <svg style="width:14px;height:14px" class="text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-[13px] font-bold text-white mb-1">Document Requirements</h3>
                                <p class="text-[12px] text-gray-400 mb-2">{{ getDocumentRequirements.description }}</p>
                                <ul class="text-[12px] text-gray-400 space-y-1">
                                    <li v-for="req in getDocumentRequirements.requirements" :key="req">• {{ req }}</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <!-- Identity documents -->
                        <div>
                            <h3 class="text-[13px] font-bold text-white mb-1">Identity Document (Choose One)</h3>
                            <p class="text-[11px] text-amber-400 mb-4">Driver's License preferred</p>
                            <div class="grid sm:grid-cols-3 gap-4">
                                <!-- Passport -->
                                <div class="rounded-xl border border-white/[0.07] bg-white/[0.02] p-4">
                                    <h4 class="text-[12px] font-bold text-white mb-3">Passport</h4>
                                    <div v-if="!form.documents.passport">
                                        <label class="cursor-pointer flex flex-col items-center justify-center h-28 border-2 border-dashed border-white/[0.1] rounded-xl hover:border-amber-400/40 transition">
                                            <svg style="width:20px;height:20px" class="text-gray-500 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                            <span class="text-[11px] text-gray-500">Upload Passport</span>
                                            <input type="file" @change="handleFileUpload($event, 'passport')" accept=".jpg,.jpeg,.png,.pdf" class="hidden" />
                                        </label>
                                    </div>
                                    <div v-else class="space-y-2">
                                        <div v-if="isImageFile(form.documents.passport)">
                                            <img :src="getFilePreview(form.documents.passport)" alt="Passport preview" class="w-full h-24 object-cover rounded-lg border border-white/[0.07]"/>
                                        </div>
                                        <div class="flex items-center justify-between p-2.5 rounded-lg bg-green-400/10 border border-green-400/20">
                                            <div class="flex items-center gap-2 min-w-0">
                                                <svg style="width:13px;height:13px" class="text-green-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                                <span class="text-[11px] text-green-400 truncate">{{ truncateName(form.documents.passport.name) }}</span>
                                            </div>
                                            <button @click="removeDocument('passport')" class="text-red-400 hover:text-red-300 flex-shrink-0 ml-2">
                                                <svg style="width:13px;height:13px" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- ID Card -->
                                <div class="rounded-xl border border-white/[0.07] bg-white/[0.02] p-4">
                                    <h4 class="text-[12px] font-bold text-white mb-3">ID Card</h4>
                                    <div v-if="!form.documents.id_card">
                                        <label class="cursor-pointer flex flex-col items-center justify-center h-28 border-2 border-dashed border-white/[0.1] rounded-xl hover:border-amber-400/40 transition">
                                            <svg style="width:20px;height:20px" class="text-gray-500 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                            <span class="text-[11px] text-gray-500">Upload ID Card</span>
                                            <input type="file" @change="handleFileUpload($event, 'id_card')" accept=".jpg,.jpeg,.png,.pdf" class="hidden" />
                                        </label>
                                    </div>
                                    <div v-else class="space-y-2">
                                        <div v-if="isImageFile(form.documents.id_card)">
                                            <img :src="getFilePreview(form.documents.id_card)" alt="ID Card preview" class="w-full h-24 object-cover rounded-lg border border-white/[0.07]"/>
                                        </div>
                                        <div class="flex items-center justify-between p-2.5 rounded-lg bg-green-400/10 border border-green-400/20">
                                            <div class="flex items-center gap-2 min-w-0">
                                                <svg style="width:13px;height:13px" class="text-green-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                                <span class="text-[11px] text-green-400 truncate">{{ truncateName(form.documents.id_card.name) }}</span>
                                            </div>
                                            <button @click="removeDocument('id_card')" class="text-red-400 hover:text-red-300 flex-shrink-0 ml-2">
                                                <svg style="width:13px;height:13px" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Driver's License -->
                                <div class="rounded-xl border border-white/[0.07] bg-white/[0.02] p-4">
                                    <h4 class="text-[12px] font-bold text-white mb-3">Driver's License</h4>
                                    <div v-if="!form.documents.drivers_license">
                                        <label class="cursor-pointer flex flex-col items-center justify-center h-28 border-2 border-dashed border-white/[0.1] rounded-xl hover:border-amber-400/40 transition">
                                            <svg style="width:20px;height:20px" class="text-gray-500 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                            <span class="text-[11px] text-gray-500">Upload License</span>
                                            <input type="file" @change="handleFileUpload($event, 'drivers_license')" accept=".jpg,.jpeg,.png,.pdf" class="hidden" />
                                        </label>
                                    </div>
                                    <div v-else class="space-y-2">
                                        <div v-if="isImageFile(form.documents.drivers_license)">
                                            <img :src="getFilePreview(form.documents.drivers_license)" alt="License preview" class="w-full h-24 object-cover rounded-lg border border-white/[0.07]"/>
                                        </div>
                                        <div class="flex items-center justify-between p-2.5 rounded-lg bg-green-400/10 border border-green-400/20">
                                            <div class="flex items-center gap-2 min-w-0">
                                                <svg style="width:13px;height:13px" class="text-green-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                                <span class="text-[11px] text-green-400 truncate">{{ truncateName(form.documents.drivers_license.name) }}</span>
                                            </div>
                                            <button @click="removeDocument('drivers_license')" class="text-red-400 hover:text-red-300 flex-shrink-0 ml-2">
                                                <svg style="width:13px;height:13px" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Photos (Green Card only) -->
                        <div v-if="form.serviceType === 'Green Card Lottery'">
                            <h3 class="text-[13px] font-bold text-white mb-4">Photos (2 Required)</h3>
                            <div class="rounded-xl border border-white/[0.07] bg-white/[0.02] p-5">
                                <p class="text-[12px] text-gray-400 mb-4">Please upload 2 recent passport-style photos taken within the last 6 months.</p>
                                <div class="grid sm:grid-cols-2 gap-4 mb-4">
                                    <div v-for="(photo, index) in form.documents.photos" :key="`photo-${index}`">
                                        <div v-if="isImageFile(photo)" class="mb-2">
                                            <img :src="getFilePreview(photo)" alt="Photo preview" class="w-full h-28 object-cover rounded-lg border border-white/[0.07]"/>
                                        </div>
                                        <div class="flex items-center justify-between p-2.5 rounded-lg bg-green-400/10 border border-green-400/20">
                                            <div class="flex items-center gap-2 min-w-0">
                                                <svg style="width:13px;height:13px" class="text-green-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"/></svg>
                                                <span class="text-[11px] text-green-400 truncate">{{ truncateName(photo.name) }}</span>
                                            </div>
                                            <button @click="removePhoto(index)" class="text-red-400 hover:text-red-300 flex-shrink-0 ml-2">
                                                <svg style="width:13px;height:13px" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div v-if="form.documents.photos.length < 2">
                                    <label class="cursor-pointer flex flex-col items-center justify-center h-28 border-2 border-dashed border-white/[0.1] rounded-xl hover:border-amber-400/40 transition">
                                        <svg style="width:20px;height:20px" class="text-gray-500 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        <span class="text-[11px] text-gray-500">Upload Photo {{ form.documents.photos.length + 1 }}</span>
                                        <input type="file" @change="handleFileUpload($event, 'photos')" accept=".jpg,.jpeg,.png" class="hidden" />
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ───── STEP 5: Review & Payment ───── -->
                <div v-if="currentStep === 5" class="p-4 sm:p-7">
                    <h2 class="text-[18px] font-bold text-white mb-6">Review Your Order</h2>

                    <!-- Order Summary Card -->
                    <div class="relative overflow-hidden rounded-2xl p-6 mb-7" style="background:linear-gradient(135deg,#1a0e3d 0%,#0c1c30 50%,#07101e 100%);border:1px solid rgba(244,184,64,.2);box-shadow:0 0 40px 0 rgba(244,184,64,.12)">
                        <div class="absolute top-0 right-0 w-48 h-48 rounded-full opacity-10 pointer-events-none" style="background:radial-gradient(circle,#f4b840,transparent);transform:translate(30%,-30%)"/>
                        <div class="mb-5">
                            <h3 class="text-[14px] font-bold text-white mb-0.5">Your Order</h3>
                            <p class="text-[12px] text-gray-400">Review your selection and pricing</p>
                        </div>

                        <!-- Selected service -->
                        <div class="flex items-start gap-4 p-4 rounded-xl bg-white/[0.06] border border-white/[0.08] mb-5">
                            <div class="w-10 h-10 rounded-xl bg-amber-400/20 flex items-center justify-center flex-shrink-0">
                                <svg style="width:18px;height:18px" class="text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </div>
                            <div class="flex-1">
                                <div class="text-[14px] font-bold text-white">{{ selectedService?.name }}</div>
                                <div class="text-[12px] text-gray-400 mt-0.5">{{ selectedService?.description }}</div>
                            </div>
                            <div class="text-[16px] font-bold text-amber-400">${{ selectedService?.price }}</div>
                        </div>

                        <!-- Line items -->
                        <div class="space-y-2 mb-5">
                            <div v-if="form.serviceType !== 'Green Card Lottery'" class="flex justify-between text-[12px]">
                                <span class="text-gray-400">Processing Speed ({{ form.speedOption === 'economic' ? 'Economic' : 'Pro' }})</span>
                                <span class="text-white font-semibold">${{ form.speedOption === 'economic' ? 99 : 149 }}</span>
                            </div>
                            <div v-if="form.serviceType !== 'Green Card Lottery'" class="flex justify-between text-[12px]">
                                <span class="text-gray-400">State Filing Fee</span>
                                <span class="text-white font-semibold">$125</span>
                            </div>
                            <template v-for="addonId in form.addons" :key="addonId">
                                <div class="flex justify-between text-[12px]">
                                    <span class="text-gray-400">{{ addons.find(a => a.id === addonId)?.name }}</span>
                                    <span class="text-white font-semibold">${{ addons.find(a => a.id === addonId)?.price }}</span>
                                </div>
                            </template>
                            <div v-if="form.serviceType !== 'Green Card Lottery'" class="pt-3 border-t border-white/[0.08] text-[11px] text-gray-500 space-y-1">
                                <div><span class="text-gray-400">Business Name:</span> {{ form.businessName }}</div>
                                <div><span class="text-gray-400">State:</span> {{ form.state }}</div>
                            </div>
                        </div>

                        <!-- Total -->
                        <div class="border-t border-white/[0.15] pt-4 flex justify-between items-center">
                            <span class="text-[14px] font-bold text-white">Total due today</span>
                            <span class="text-[26px] font-bold text-amber-400">${{ calculateTotal }}</span>
                        </div>

                        <!-- Docs verified -->
                        <div class="mt-4 pt-4 border-t border-white/[0.08] flex items-center gap-2">
                            <div class="w-5 h-5 rounded-full bg-green-400/20 flex items-center justify-center">
                                <svg style="width:11px;height:11px" class="text-green-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            </div>
                            <span class="text-[12px] text-green-400 font-medium">Documents Uploaded & Verified</span>
                        </div>
                    </div>

                    <!-- Payment Method Selection -->
                    <h3 class="text-[14px] font-bold text-white mb-4">Choose Payment Method</h3>
                    <div class="space-y-3 mb-7">
                        <!-- Stripe -->
                        <button @click="form.paymentMethod = 'stripe'" type="button"
                            :class="['w-full p-5 rounded-2xl border-2 text-left transition',
                                form.paymentMethod === 'stripe' ? 'border-[#635bff]/60 bg-[#635bff]/[0.08]' : 'border-white/[0.07] bg-white/[0.02] hover:border-white/[0.12]']">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-4">
                                    <div :class="['w-11 h-11 rounded-xl flex items-center justify-center transition', form.paymentMethod === 'stripe' ? 'bg-[#635bff]' : 'bg-white/[0.06]']">
                                        <svg style="width:18px;height:18px" class="text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                    </div>
                                    <div>
                                        <div class="text-[14px] font-bold text-white">Credit or Debit Card</div>
                                        <div class="text-[12px] text-gray-400">Visa, Mastercard, Amex accepted</div>
                                    </div>
                                </div>
                                <div :class="['w-5 h-5 rounded-full border-2 flex items-center justify-center transition',
                                    form.paymentMethod === 'stripe' ? 'border-[#635bff] bg-[#635bff]' : 'border-white/[0.2]']">
                                    <svg v-if="form.paymentMethod === 'stripe'" style="width:10px;height:10px" class="text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                </div>
                            </div>
                        </button>

                    </div><!-- end payment methods -->

                    <!-- Bank transfer details -->
                    <div v-if="form.paymentMethod === 'bank_transfer'" class="mb-7 rounded-xl border border-blue-400/20 bg-blue-400/[0.05] p-5">
                        <h3 class="text-[13px] font-bold text-white mb-4">Account Payment Details</h3>
                        <div class="space-y-3">
                            <div v-for="[label, val] in [['Account Name', getBankInfo.account_name], ['Account Number', getBankInfo.account_number], ['Branch', getBankInfo.branch_name], ['Routing Number', getBankInfo.routing_number]]" :key="label" class="flex items-center gap-3">
                                <span class="text-[12px] text-gray-400 w-32 flex-shrink-0">{{ label }}</span>
                                <span class="font-mono text-[12px] text-white bg-white/[0.05] border border-white/[0.08] px-3 py-1 rounded-lg">{{ val }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Payment screenshot (bank transfer) -->
                    <div v-if="form.paymentMethod === 'bank_transfer'" class="mb-7">
                        <h3 class="text-[13px] font-bold text-white mb-4">Upload Payment Screenshot</h3>
                        <div class="rounded-xl border border-white/[0.07] bg-white/[0.02] p-5">
                            <div v-if="!form.paymentScreenshot">
                                <input type="file" @change="handlePaymentScreenshotUpload" accept="image/*" class="hidden" id="paymentScreenshot"/>
                                <label for="paymentScreenshot" class="cursor-pointer flex flex-col items-center justify-center py-10 border-2 border-dashed border-white/[0.1] rounded-xl hover:border-amber-400/40 transition">
                                    <div class="w-12 h-12 rounded-xl bg-white/[0.04] flex items-center justify-center mb-3">
                                        <svg style="width:20px;height:20px" class="text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                                    </div>
                                    <span class="text-[13px] font-semibold text-white mb-1">Upload Payment Screenshot</span>
                                    <span class="text-[11px] text-gray-500 mb-4">Click to select an image file</span>
                                    <span class="inline-flex items-center gap-2 h-8 px-4 rounded-xl bg-amber-400 text-[12px] font-semibold text-[#07101e]">Choose File</span>
                                    <p class="text-[10px] text-gray-600 mt-3">JPG, PNG, GIF — max 10MB</p>
                                </label>
                            </div>
                            <div v-else class="space-y-4">
                                <div class="flex items-center justify-between p-3 rounded-xl bg-green-400/10 border border-green-400/20">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-lg bg-green-400/20 flex items-center justify-center">
                                            <svg style="width:16px;height:16px" class="text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        </div>
                                        <div>
                                            <div class="text-[12px] font-semibold text-green-400">Payment Screenshot Uploaded</div>
                                            <div class="text-[11px] text-gray-500">{{ truncateName(form.paymentScreenshot.name) }} ({{ (form.paymentScreenshot.size/1024/1024).toFixed(2) }}MB)</div>
                                        </div>
                                    </div>
                                    <button @click="removePaymentScreenshot" class="text-red-400 hover:text-red-300">
                                        <svg style="width:14px;height:14px" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                                <div v-if="isImageFile(form.paymentScreenshot)" class="text-center">
                                    <img :src="getFilePreview(form.paymentScreenshot)" :alt="form.paymentScreenshot.name" class="max-w-full max-h-64 rounded-xl border border-white/[0.07] mx-auto"/>
                                </div>
                                <div class="text-center">
                                    <input type="file" @change="handlePaymentScreenshotUpload" accept="image/*" class="hidden" id="replacePaymentScreenshot"/>
                                    <label for="replacePaymentScreenshot" class="inline-flex items-center gap-2 h-8 px-4 rounded-xl border border-white/[0.08] bg-white/[0.03] text-[12px] font-semibold text-gray-300 hover:text-white cursor-pointer transition">Replace Screenshot</label>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3 flex items-start gap-3 p-3 rounded-xl bg-amber-400/[0.06] border border-amber-400/20">
                            <svg style="width:16px;height:16px" class="text-amber-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <div>
                                <div class="text-[12px] font-bold text-amber-400">Payment Screenshot Required</div>
                                <p class="text-[11px] text-gray-400 mt-0.5">Please upload a screenshot of your payment confirmation to proceed with your order.</p>
                            </div>
                        </div>
                    </div>

                    <p class="text-[12px] text-gray-500">By submitting this order, you agree to our Terms of Service and Privacy Policy.</p>
                </div>
            </div>

            <!-- Navigation Buttons -->
            <div class="flex justify-between items-center pb-6">
                <button @click="prevStep" :disabled="currentStep === 1"
                    class="inline-flex items-center gap-2 h-10 px-5 rounded-xl border border-white/[0.09] bg-white/[0.04] text-[13px] font-semibold text-gray-300 hover:text-white hover:border-white/[0.15] transition disabled:opacity-40 disabled:cursor-not-allowed">
                    <svg style="width:14px;height:14px" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    Previous
                </button>

                <button @click="nextStep" :disabled="!canProceed || form.processing || isProcessingPayment"
                    :class="['inline-flex items-center gap-2 h-10 px-6 rounded-xl text-[13px] font-bold transition disabled:opacity-40 disabled:cursor-not-allowed shadow-lg',
                        currentStep === 4 ? 'bg-[#635bff] hover:bg-[#5469d4] text-white shadow-[#635bff]/20' : 'bg-amber-400 hover:bg-amber-300 text-[#07101e] shadow-amber-400/20']">
                    <svg v-if="form.processing || isProcessingPayment" class="animate-spin" style="width:14px;height:14px" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                    </svg>
                    <span>{{ (form.processing || isProcessingPayment) ? 'Redirecting…' : (currentStep === 4 ? 'Proceed to Payment' : 'Next') }}</span>
                    <svg v-if="!form.processing && !isProcessingPayment" style="width:14px;height:14px" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path v-if="currentStep === 4" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>

        </div>
    </AuthenticatedLayout>
</template>
