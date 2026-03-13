<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { computed, nextTick, onMounted, ref, watch } from 'vue';

const props = defineProps({
    orderData: Object,
    amount: { type: Number, default: 0 },
    serviceName: String,
    pricing: {
        type: Object,
        default: () => ({ subtotal: 0, state_fee: 0, processing_fee: 0, total: 0 })
    },
    usdcWalletAddress: { type: String, default: '' }
});

const billingFrequency = ref('one-time');
const paymentMethod    = ref('card');
const applePayAvailable = ref(false);
const applePayLoading   = ref(false);
const applePayNeedCard  = ref(false); // Device supports Apple Pay but no active card found
const googlePayLoading        = ref(false);
const googlePayError          = ref('');
const googlePayButtonMounted  = ref(false);
const cryptoLoading     = ref(false);

// ── Stripe Elements state ─────────────────────────────────────────────────────
const cardNumberMount          = ref(null);
const cardExpiryMount          = ref(null);
const cardCvcMount             = ref(null);
const stripeCardBrand          = ref('');
const stripeCardNumberError    = ref('');
const stripeCardExpiryError    = ref('');
const stripeCardCvcError       = ref('');
const stripeCardNumberComplete = ref(false);
const stripeCardExpiryComplete = ref(false);
const stripeCardCvcComplete    = ref(false);
const stripeElementsReady      = ref(false);
let   stripeInstance           = null;
let   stripeElements           = null;
let   cardNumberEl             = null;
let   cardExpiryEl             = null;
let   cardCvcEl                = null;

const stripeElementStyle = {
    base: {
        color: '#e2e8f0',
        fontSize: '13px',
        fontSmoothing: 'antialiased',
        '::placeholder': { color: '#4b5563' },
    },
    invalid: { color: '#f87171' },
};

const totalAmount    = computed(() => props.pricing?.total || props.amount || 0);
const subtotal       = computed(() => props.pricing?.subtotal || 0);
const stateFee       = computed(() => props.pricing?.state_fee || 0);
const processingFee  = computed(() => props.pricing?.processing_fee || 0);
const addonsBreakdown = computed(() => props.pricing?.addons_breakdown || {});

const form = useForm({
    cardNumber: '', expiryDate: '', cvv: '', cardName: '',
    address: '', apartment: '', city: '', state: '', country: 'United States',
    ...props.orderData,
});

// ── Card type detection ───────────────────────────────────────────────────────
const detectCardType = (number) => {
    const n = number.replace(/\s/g, '');
    if (/^4/.test(n)) return 'visa';
    if (/^5[1-5]/.test(n) || /^2(2[2-9][1-9]|[3-6]\d{2}|7[01]\d|720)/.test(n)) return 'mastercard';
    if (/^3[47]/.test(n)) return 'amex';
    if (/^6(?:011|5|4[4-9])/.test(n)) return 'discover';
    return '';
};

const cardType       = computed(() => detectCardType(form.cardNumber));
const cardMaxLength  = computed(() => cardType.value === 'amex' ? 17 : 19);
const cvvMaxLength   = computed(() => cardType.value === 'amex' ? 4 : 3);
const cvvPlaceholder = computed(() => cardType.value === 'amex' ? '4-digit code' : '3-digit code');

// ── Luhn validation ───────────────────────────────────────────────────────────
const luhnCheck = (num) => {
    const digits = num.replace(/\D/g, '');
    if (!digits.length) return false;
    let sum = 0, alt = false;
    for (let i = digits.length - 1; i >= 0; i--) {
        let n = parseInt(digits[i], 10);
        if (alt) { n *= 2; if (n > 9) n -= 9; }
        sum += n;
        alt = !alt;
    }
    return sum % 10 === 0;
};

// ── Touched tracking (validate on blur, then live) ────────────────────────────
const touched = ref({});
const markTouched = (field) => { touched.value[field] = true; };

const cardNumberValid = computed(() => {
    if (!touched.value.cardNumber) return null;
    const clean = form.cardNumber.replace(/\s/g, '');
    const required = cardType.value === 'amex' ? 15 : 16;
    if (clean.length < required) return false;
    return luhnCheck(clean);
});

const expiryValid = computed(() => {
    if (!touched.value.expiryDate) return null;
    if (!form.expiryDate || form.expiryDate.length < 5) return false;
    const [mm, yy] = form.expiryDate.split('/');
    const month = parseInt(mm, 10);
    const year  = 2000 + parseInt(yy, 10);
    if (month < 1 || month > 12) return false;
    return new Date(year, month) > new Date();
});

const cvvValid = computed(() => {
    if (!touched.value.cvv) return null;
    return form.cvv.length >= cvvMaxLength.value;
});

// ── Field border helper ───────────────────────────────────────────────────────
const fieldClass = (valid, error) => {
    if (error) return 'border-red-500/70 focus:border-red-500 focus:ring-red-500/20';
    if (valid === false) return 'border-red-500/70 focus:border-red-500 focus:ring-red-500/20';
    if (valid === true)  return 'border-emerald-500/60 focus:border-emerald-400/60 focus:ring-emerald-400/20';
    return 'border-white/[0.08] focus:border-amber-400/50 focus:ring-amber-400/20';
};

// ── Input handlers ────────────────────────────────────────────────────────────
const handleCardNumberInput = (e) => {
    const digits = e.target.value.replace(/\D/g, '');
    const max = cardType.value === 'amex' ? 15 : 16;
    const trimmed = digits.substr(0, max);
    if (cardType.value === 'amex') {
        form.cardNumber = trimmed.replace(/(\d{4})(\d{6})(\d{0,5})/, (_, a, b, c) => c ? `${a} ${b} ${c}` : b ? `${a} ${b}` : a);
    } else {
        form.cardNumber = (trimmed.match(/.{1,4}/g) || []).join(' ');
    }
    if (touched.value.cardNumber) touched.value = { ...touched.value };
};

const handleExpiryInput = (e) => {
    const raw = e.target.value.replace(/\D/g, '').substr(0, 4);
    form.expiryDate = raw.length > 2 ? raw.slice(0,2) + '/' + raw.slice(2) : raw;
};

const handleCvvInput = (e) => {
    form.cvv = e.target.value.replace(/\D/g, '').substr(0, cvvMaxLength.value);
};

// ── Address autocomplete data ─────────────────────────────────────────────────
const US_STATES = [
    'Alabama','Alaska','Arizona','Arkansas','California','Colorado','Connecticut',
    'Delaware','Florida','Georgia','Hawaii','Idaho','Illinois','Indiana','Iowa',
    'Kansas','Kentucky','Louisiana','Maine','Maryland','Massachusetts','Michigan',
    'Minnesota','Mississippi','Missouri','Montana','Nebraska','Nevada',
    'New Hampshire','New Jersey','New Mexico','New York','North Carolina',
    'North Dakota','Ohio','Oklahoma','Oregon','Pennsylvania','Rhode Island',
    'South Carolina','South Dakota','Tennessee','Texas','Utah','Vermont',
    'Virginia','Washington','West Virginia','Wisconsin','Wyoming',
    'District of Columbia',
];

const stateQuery   = ref(form.state || '');
const showStateDrop = ref(false);
const filteredStates = computed(() =>
    stateQuery.value.length < 1 ? US_STATES :
    US_STATES.filter(s => s.toLowerCase().startsWith(stateQuery.value.toLowerCase())).slice(0, 8)
);
const selectState = (s) => { form.state = s; stateQuery.value = s; showStateDrop.value = false; };
watch(() => form.state, v => { if (v !== stateQuery.value) stateQuery.value = v; });

// ── Stripe Elements mount / unmount ─────────────────────────────────────────
const mountStripeElements = async () => {
    if (stripeElementsReady.value) return;
    try {
        await loadStripeSdk();
        const stripeKey = page.props.stripe_key;
        if (!stripeKey) return;
        if (!stripeInstance) stripeInstance = window.Stripe(stripeKey);
        if (!stripeElements) stripeElements = stripeInstance.elements();

        cardNumberEl = stripeElements.create('cardNumber', { style: stripeElementStyle, showIcon: false });
        cardNumberEl.mount(cardNumberMount.value);
        cardNumberEl.on('change', (e) => {
            stripeCardBrand.value          = e.brand || '';
            stripeCardNumberError.value    = e.error?.message || '';
            stripeCardNumberComplete.value = e.complete;
        });

        cardExpiryEl = stripeElements.create('cardExpiry', { style: stripeElementStyle });
        cardExpiryEl.mount(cardExpiryMount.value);
        cardExpiryEl.on('change', (e) => {
            stripeCardExpiryError.value    = e.error?.message || '';
            stripeCardExpiryComplete.value = e.complete;
        });

        cardCvcEl = stripeElements.create('cardCvc', { style: stripeElementStyle });
        cardCvcEl.mount(cardCvcMount.value);
        cardCvcEl.on('change', (e) => {
            stripeCardCvcError.value    = e.error?.message || '';
            stripeCardCvcComplete.value = e.complete;
        });

        stripeElementsReady.value = true;
    } catch (err) {
        localErrors.value.payment = err.message;
    }
};

const unmountStripeElements = () => {
    if (cardNumberEl) { cardNumberEl.destroy(); cardNumberEl = null; }
    if (cardExpiryEl) { cardExpiryEl.destroy(); cardExpiryEl = null; }
    if (cardCvcEl)    { cardCvcEl.destroy();    cardCvcEl    = null; }
    stripeElements            = null;
    stripeElementsReady.value = false;
};

onMounted(() => nextTick(() => mountStripeElements()));

// ── Form submission with Stripe.js ───────────────────────────────────────────
const localErrors  = ref({});
const isProcessing = ref(false);

const loadStripeSdk = () => new Promise((resolve, reject) => {
    if (window.Stripe) { resolve(); return; }
    const s = document.createElement('script');
    s.src = 'https://js.stripe.com/v3/';
    s.onload  = resolve;
    s.onerror = () => reject(new Error('Failed to load Stripe.js'));
    document.head.appendChild(s);
});

const loadGooglePaySdk = () => new Promise((resolve, reject) => {
    if (window.google?.payments?.api?.PaymentsClient) { resolve(); return; }
    const s = document.createElement('script');
    s.src = 'https://pay.google.com/gp/p/js/pay.js';
    s.async = true;
    s.onload  = resolve;
    s.onerror = () => reject(new Error('Failed to load Google Pay SDK.'));
    document.head.appendChild(s);
});

const submitPayment = async () => {
    localErrors.value = {};
    const errs = {};

    if (paymentMethod.value === 'card') {
        if (!stripeCardNumberComplete.value) errs.cardNumber = stripeCardNumberError.value || 'Enter your card number.';
        if (!stripeCardExpiryComplete.value) errs.expiryDate = stripeCardExpiryError.value || 'Enter your card expiry date.';
        if (!stripeCardCvcComplete.value)    errs.cvv        = stripeCardCvcError.value    || 'Enter your card CVC.';
        if (!form.cardName.trim())  errs.cardName = 'Enter the name on card.';
        if (!form.address.trim())   errs.address  = 'Enter a billing address.';
        if (!form.city.trim())      errs.city     = 'Enter a city.';
        if (!form.state.trim())     errs.state    = 'Enter a state / region.';
    }

    if (Object.keys(errs).length) { localErrors.value = errs; return; }

    isProcessing.value = true;

    try {
        // 1. Ensure Stripe.js + Elements are ready
        await mountStripeElements();
        if (!stripeInstance) throw new Error('Stripe is not configured.');
        const stripe = stripeInstance;

        // 2. Create a PaymentMethod via Stripe Elements (no raw card data ever touches our server)
        const { paymentMethod: pm, error: pmError } = await stripe.createPaymentMethod({
            type: 'card',
            card: cardNumberEl,
            billing_details: {
                name: form.cardName,
                address: {
                    line1:   form.address,
                    line2:   form.apartment || null,
                    city:    form.city,
                    state:   form.state,
                    country: 'US',
                },
            },
        });

        if (pmError) {
            localErrors.value.cardNumber = pmError.message;
            isProcessing.value = false;
            return;
        }

        // 4. Ask backend to create a PaymentIntent
        const intentRes = await fetch(route('orders.create-payment-intent'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
        });
        const intentData = await intentRes.json();

        if (!intentRes.ok || intentData.error) {
            localErrors.value.payment = intentData.error || 'Could not initiate payment.';
            isProcessing.value = false;
            return;
        }

        // 5. Confirm the payment (charges the card)
        const { error: confirmError } = await stripe.confirmCardPayment(intentData.client_secret, {
            payment_method: pm.id,
        });

        if (confirmError) {
            localErrors.value.payment = confirmError.message;
            isProcessing.value = false;
            return;
        }

        // 6. Notify backend — create the order record
        const confirmRes = await fetch(route('orders.process-payment'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                payment_intent_id: intentData.client_secret.split('_secret_')[0],
            }),
        });
        const confirmData = await confirmRes.json();

        if (!confirmRes.ok || confirmData.error) {
            localErrors.value.payment = confirmData.error || 'Order creation failed.';
            isProcessing.value = false;
            return;
        }

        localStorage.removeItem('corpius_order_draft');
        router.visit(confirmData.redirect || route('orders.index'));

    } catch (err) {
        localErrors.value.payment = err.message || 'An unexpected error occurred.';
        isProcessing.value = false;
    }
};

// ── PayPal integration ────────────────────────────────────────────────────────
const page = usePage();
const paypalLoading = ref(false);
const paypalError   = ref('');

const loadPayPalSdk = () => {
    return new Promise((resolve, reject) => {
        if (window.paypal) { resolve(); return; }
        const clientId = page.props.paypal_client_id;
        if (!clientId) { reject(new Error('PayPal client ID not configured.')); return; }
        const script = document.createElement('script');
        script.src = `https://www.paypal.com/sdk/js?client-id=${clientId}&currency=USD&intent=capture`;
        script.onload  = resolve;
        script.onerror = () => reject(new Error('Failed to load PayPal SDK.'));
        document.head.appendChild(script);
    });
};

const initPayPal = async () => {
    paypalLoading.value = true;
    paypalError.value   = '';
    try {
        await loadPayPalSdk();
        await nextTick();
        const container = document.getElementById('paypal-button-container');
        if (!container) return;
        container.innerHTML = '';

        window.paypal.Buttons({
            style: { color: 'gold', shape: 'rect', label: 'pay', height: 48 },

            createOrder: async () => {
                try {
                    const res = await fetch(route('billing.paypal.create-order'), {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            amount: totalAmount.value,
                            description: props.serviceName,
                            currency: 'USD',
                        }),
                    });
                    const data = await res.json();
                    if (data.error) {
                        paypalError.value = data.error;
                        throw new Error(data.error);
                    }
                    return data.order_id;
                } catch (err) {
                    paypalError.value = err.message || 'Failed to create PayPal order.';
                    throw err;
                }
            },

            onApprove: async (data) => {
                paypalLoading.value = true;
                const res = await fetch(route('orders.paypal-process'), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ paypal_order_id: data.orderID }),
                });
                const result = await res.json();
                if (result.success) {
                    localStorage.removeItem('corpius_order_draft');
                    router.visit(result.redirect || route('orders.index'));
                } else {
                    paypalError.value = result.error || 'Payment failed. Please try again.';
                    paypalLoading.value = false;
                }
            },

            onError: (err) => {
                if (!paypalError.value) {
                    paypalError.value = 'PayPal encountered an error. Please try again.';
                }
                paypalLoading.value = false;
            },

            onCancel: () => {
                paypalError.value = '';
                paypalLoading.value = false;
            },
        }).render('#paypal-button-container');
    } catch (e) {
        paypalError.value = e.message || 'Failed to load PayPal. Please refresh and try again.';
    } finally {
        paypalLoading.value = false;
    }
};

watch(paymentMethod, (val) => {
    if (val === 'card')       { nextTick(() => mountStripeElements()); }
    else                      { unmountStripeElements(); }
    if (val === 'paypal')     nextTick(() => initPayPal());
    if (val === 'apple_pay')  nextTick(() => initApplePay());
    if (val === 'google_pay') nextTick(() => initGooglePay());
    if (val === 'crypto')     nextTick(() => initCrypto());
});

// ── Apple Pay integration ─────────────────────────────────────────────────────
const initApplePay = async () => {
    applePayLoading.value = true;
    applePayAvailable.value = false;
    applePayNeedCard.value  = false;
    localErrors.value.payment = '';
    try {
        await loadStripeSdk();
        const stripeKey = page.props.stripe_key;
        if (!stripeKey) throw new Error('Stripe is not configured.');
        const stripe = window.Stripe(stripeKey);

        const paymentRequest = stripe.paymentRequest({
            country: 'US',
            currency: 'usd',
            total: {
                label: props.serviceName || 'CORPIUS Service',
                amount: Math.round(totalAmount.value * 100), // Convert to cents
            },
            requestPayerName: true,
            requestPayerEmail: true,
        });

        // Check if Apple Pay is available (Safari + Apple Wallet with active card)
        const result = await paymentRequest.canMakePayment();

        // Check if this browser/device has the Apple Pay JS API at all.
        // We intentionally do NOT call canMakePayments() here — it returns false on Macs
        // where iCloud sign-in or Touch ID aren't fully configured, even though the user
        // CAN use Apple Pay. The presence of ApplePaySession is enough to know it's Safari
        // on Apple hardware and we should show a helpful setup message rather than "not available".
        const isApplePayBrowser = typeof ApplePaySession !== 'undefined';

        if (result && result.applePay) {
            applePayAvailable.value = true;
            applePayLoading.value = false;
            const elements = stripe.elements();
            const prButton = elements.create('paymentRequestButton', {
                paymentRequest: paymentRequest,
                style: { paymentRequestButton: { type: 'buy', theme: 'dark', height: '48px' } }
            });

            await nextTick();
            const container = document.getElementById('apple-pay-button');
            if (container) {
                container.innerHTML = '';
                prButton.mount('#apple-pay-button');
            }

            paymentRequest.on('paymentmethod', async (ev) => {
                try {
                    // Create payment intent on backend
                    const intentRes = await fetch(route('orders.create-payment-intent'), {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                    });
                    const intentData = await intentRes.json();

                    if (!intentRes.ok || intentData.error) {
                        ev.complete('fail');
                        localErrors.value.payment = intentData.error || 'Payment failed.';
                        return;
                    }

                    // Confirm payment
                    const {error: confirmError} = await stripe.confirmCardPayment(
                        intentData.client_secret,
                        {payment_method: ev.paymentMethod.id},
                        {handleActions: false}
                    );

                    if (confirmError) {
                        ev.complete('fail');
                        localErrors.value.payment = confirmError.message;
                        return;
                    }

                    ev.complete('success');

                    // Notify backend
                    const confirmRes = await fetch(route('orders.process-payment'), {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            payment_intent_id: intentData.client_secret.split('_secret_')[0],
                            payment_method: ev.walletName === 'googlePay' ? 'google_pay' : 'apple_pay',
                        }),
                    });

                    const confirmData = await confirmRes.json();
                    if (!confirmRes.ok || confirmData.error) {
                        localErrors.value.payment = confirmData.error || 'Order creation failed.';
                        return;
                    }

                    localStorage.removeItem('corpius_order_draft');
                    router.visit(confirmData.redirect || route('orders.index'));
                } catch (err) {
                    ev.complete('fail');
                    localErrors.value.payment = err.message || 'An unexpected error occurred.';
                }
            });
        } else if (isApplePayBrowser) {
            // Safari on Apple device — Apple Pay JS API present but Stripe couldn't confirm
            // a ready card. Could be: no card in Wallet, iCloud not signed in, or Touch ID
            // not configured. Show setup instructions so the user can self-resolve.
            applePayNeedCard.value  = true;
            applePayLoading.value = false;
        } else {
            applePayAvailable.value = false;
            applePayLoading.value = false;
        }
    } catch (e) {
        applePayLoading.value = false;
        localErrors.value.payment = e.message || 'Failed to initialize Apple Pay.';
    }
};

// ── Google Pay integration ────────────────────────────────────────────────────
const initGooglePay = async () => {
    googlePayLoading.value = true;
    googlePayError.value = '';
    googlePayButtonMounted.value = false;
    try {
        await loadStripeSdk();
        await loadGooglePaySdk();
        const stripeKey = page.props.stripe_key;
        if (!stripeKey) throw new Error('Stripe is not configured.');
        const stripe = window.Stripe(stripeKey);

        const gpEnv = stripeKey.startsWith('pk_live') ? 'PRODUCTION' : 'TEST';
        const paymentsClient = new google.payments.api.PaymentsClient({ environment: gpEnv });

        const readyResponse = await paymentsClient.isReadyToPay({
            apiVersion: 2,
            apiVersionMinor: 0,
            allowedPaymentMethods: [{
                type: 'CARD',
                parameters: {
                    allowedAuthMethods: ['PAN_ONLY', 'CRYPTOGRAM_3DS'],
                    allowedCardNetworks: ['AMEX', 'DISCOVER', 'MASTERCARD', 'VISA'],
                },
            }],
        });

        if (!readyResponse.result) {
            throw new Error('Google Pay is not available on this device.');
        }

        googlePayLoading.value = false;
        googlePayButtonMounted.value = true;
        await nextTick();

        const container = document.getElementById('google-pay-button');
        if (!container) return;
        container.innerHTML = '';

        const button = paymentsClient.createButton({
            buttonColor: 'dark',
            buttonType: 'buy',
            buttonRadius: 12,
            buttonSizeMode: 'fill',
            onClick: async () => {
                googlePayError.value = '';
                try {
                    const paymentData = await paymentsClient.loadPaymentData({
                        apiVersion: 2,
                        apiVersionMinor: 0,
                        allowedPaymentMethods: [{
                            type: 'CARD',
                            parameters: {
                                allowedAuthMethods: ['PAN_ONLY', 'CRYPTOGRAM_3DS'],
                                allowedCardNetworks: ['AMEX', 'DISCOVER', 'MASTERCARD', 'VISA'],
                            },
                            tokenizationSpecification: {
                                type: 'PAYMENT_GATEWAY',
                                parameters: {
                                    gateway: 'stripe',
                                    'stripe:version': '2023-10-16',
                                    'stripe:publishableKey': stripeKey,
                                },
                            },
                        }],
                        merchantInfo: {
                            merchantId: gpEnv === 'PRODUCTION' ? '' : '12345678901234567890',
                            merchantName: 'CORPIUS',
                        },
                        transactionInfo: {
                            totalPriceStatus: 'FINAL',
                            totalPrice: totalAmount.value.toFixed(2),
                            currencyCode: 'USD',
                            countryCode: 'US',
                            totalPriceLabel: props.serviceName || 'CORPIUS Service',
                        },
                    });

                    const gpToken = JSON.parse(paymentData.paymentMethodData.tokenizationData.token);

                    // Create payment intent
                    const intentRes = await fetch(route('orders.create-payment-intent'), {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                    });
                    const intentData = await intentRes.json();
                    if (!intentRes.ok || intentData.error) {
                        googlePayError.value = intentData.error || 'Payment failed.';
                        return;
                    }

                    // Confirm via Stripe
                    const { error: confirmError, paymentIntent } = await stripe.confirmCardPayment(
                        intentData.client_secret,
                        { payment_method: { card: { token: gpToken.id } } }
                    );
                    if (confirmError) {
                        googlePayError.value = confirmError.message;
                        return;
                    }

                    // Notify backend
                    const confirmRes = await fetch(route('orders.process-payment'), {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            payment_intent_id: paymentIntent.id,
                            payment_method: 'google_pay',
                        }),
                    });
                    const confirmData = await confirmRes.json();
                    if (!confirmRes.ok || confirmData.error) {
                        googlePayError.value = confirmData.error || 'Order creation failed.';
                        return;
                    }

                    localStorage.removeItem('corpius_order_draft');
                    router.visit(confirmData.redirect || route('orders.index'));
                } catch (err) {
                    if (err.statusCode !== 'CANCELED') {
                        googlePayError.value = err.message || 'Google Pay payment failed.';
                    }
                }
            },
        });
        container.appendChild(button);
    } catch (e) {
        googlePayLoading.value = false;
        googlePayError.value = e.message || 'Failed to initialize Google Pay.';
    }
};

// ── Cryptocurrency integration ────────────────────────────────────────────────
const cryptoError = ref('');
const cryptoCopied = ref(false);
const cryptoScreenshot = ref(null);       // File object
const cryptoScreenshotPreview = ref(''); // Data-URL for preview
const cryptoScreenshotInput = ref(null); // <input ref>

const onCryptoScreenshot = (e) => {
    const file = e.target.files[0];
    if (!file) return;
    cryptoScreenshot.value = file;
    const reader = new FileReader();
    reader.onload = (ev) => { cryptoScreenshotPreview.value = ev.target.result; };
    reader.readAsDataURL(file);
};

const removeCryptoScreenshot = () => {
    cryptoScreenshot.value = null;
    cryptoScreenshotPreview.value = '';
    if (cryptoScreenshotInput.value) cryptoScreenshotInput.value.value = '';
};

const usdcWalletAddress = computed(() =>
    import.meta.env.VITE_USDC_WALLET_ADDRESS ||
    props.usdcWalletAddress ||
    page.props.usdc_wallet_address ||
    ''
);

const initCrypto = async () => {
    cryptoLoading.value = true;
    cryptoError.value = '';
    if (!usdcWalletAddress.value) {
        cryptoError.value = 'Crypto payment address is not configured. Please use another payment method.';
    }
    cryptoLoading.value = false;
};

const copyWalletAddress = async () => {
    try {
        await navigator.clipboard.writeText(usdcWalletAddress.value);
        cryptoCopied.value = true;
        setTimeout(() => { cryptoCopied.value = false; }, 2500);
    } catch {
        // fallback for older browsers
        const el = document.createElement('textarea');
        el.value = usdcWalletAddress.value;
        document.body.appendChild(el);
        el.select();
        document.execCommand('copy');
        document.body.removeChild(el);
        cryptoCopied.value = true;
        setTimeout(() => { cryptoCopied.value = false; }, 2500);
    }
};

const submitCryptoPayment = async () => {
    if (!cryptoScreenshot.value) {
        cryptoError.value = 'Please upload a screenshot of your payment transaction before submitting.';
        return;
    }
    cryptoLoading.value = true;
    cryptoError.value = '';
    try {
        const fd = new FormData();
        fd.append('amount', totalAmount.value);
        fd.append('currency', 'usdc');
        fd.append('description', props.serviceName || '');
        fd.append('wallet_address', usdcWalletAddress.value);
        fd.append('screenshot', cryptoScreenshot.value);
        fd.append('_token', document.querySelector('meta[name="csrf-token"]').content);

        const res = await fetch(route('orders.create-crypto-payment'), {
            method: 'POST',
            headers: { 'Accept': 'application/json' },
            body: fd,
        });

        const data = await res.json();

        if (data.error) {
            cryptoError.value = data.error;
            cryptoLoading.value = false;
            return;
        }

        // Clear draft — user has committed
        localStorage.removeItem('corpius_order_draft');

        if (data.redirect) {
            window.location.href = data.redirect;
        } else {
            window.location.href = route('orders.index');
        }
    } catch (err) {
        cryptoError.value = err.message || 'Failed to submit payment. Please try again.';
        cryptoLoading.value = false;
    }
};
</script>

<template>
    <Head title="Payment" />

    <AuthenticatedLayout>
        <div class="bg-[#07101e] rounded-2xl py-6 px-3 sm:px-6">
            <div class="max-w-6xl mx-auto">

                <!-- Back Button -->
                <button @click="$inertia.visit(route('orders.create'))"
                    class="flex items-center gap-2 text-gray-400 hover:text-amber-400 mb-8 transition-colors text-sm font-medium">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Back to order details
                </button>

                <div class="grid lg:grid-cols-5 gap-6">

                    <!-- ─── LEFT: Order Summary ─── -->
                    <div class="lg:col-span-2">
                        <div class="bg-[#0c1c30] border border-white/[0.07] rounded-2xl p-5 sm:p-7 lg:sticky lg:top-8">

                            <!-- Service icon + name -->
                            <div class="flex items-center gap-4 mb-7 pb-7 border-b border-white/[0.07]">
                                <div class="w-12 h-12 rounded-xl bg-amber-400/10 border border-amber-400/20 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-[11px] text-gray-500 uppercase tracking-widest font-medium mb-0.5">Service</p>
                                    <h2 class="text-white font-semibold text-[15px] leading-tight">{{ serviceName }}</h2>
                                </div>
                            </div>

                            <!-- Total amount hero -->
                            <div class="mb-7">
                                <p class="text-[11px] text-gray-500 uppercase tracking-widest font-medium mb-1">Total Due Today</p>
                                <div class="flex items-end gap-1">
                                    <span class="text-4xl font-bold text-white">${{ totalAmount.toFixed(2) }}</span>
                                    <span class="text-gray-500 text-sm mb-1">one-time</span>
                                </div>
                            </div>

                            <!-- Pricing Breakdown -->
                            <div class="space-y-3">
                                <div v-if="subtotal > 0" class="flex justify-between items-center">
                                    <span class="text-gray-400 text-sm">Service Fee</span>
                                    <span class="text-gray-200 text-sm font-medium">${{ subtotal.toFixed(2) }}</span>
                                </div>
                                <div v-if="processingFee > 0" class="flex justify-between items-center">
                                    <span class="text-gray-400 text-sm">Processing Speed</span>
                                    <span class="text-gray-200 text-sm font-medium">${{ processingFee.toFixed(2) }}</span>
                                </div>
                                <div v-for="(addon, key) in addonsBreakdown" :key="key" class="flex justify-between items-center">
                                    <span class="text-gray-400 text-sm">{{ addon.name }}</span>
                                    <span class="text-gray-200 text-sm font-medium">
                                        <template v-if="addon.price === 0">Free</template>
                                        <template v-else>${{ addon.price.toFixed(2) }}</template>
                                    </span>
                                </div>
                                <div v-if="stateFee > 0" class="flex justify-between items-center">
                                    <span class="text-gray-400 text-sm">State Filing Fee</span>
                                    <span class="text-gray-200 text-sm font-medium">${{ stateFee.toFixed(2) }}</span>
                                </div>

                                <div class="border-t border-white/[0.07] pt-3 mt-3 flex justify-between items-center">
                                    <span class="text-white font-semibold text-sm">Total</span>
                                    <span class="text-amber-400 font-bold text-lg">${{ totalAmount.toFixed(2) }}</span>
                                </div>
                            </div>

                            <!-- Trust badges -->
                            <div class="mt-7 pt-6 border-t border-white/[0.07] space-y-3">
                                <div class="flex items-center gap-3 text-xs text-gray-500">
                                    <svg class="w-4 h-4 text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                    </svg>
                                    256-bit SSL encrypted payment
                                </div>
                                <div class="flex items-center gap-3 text-xs text-gray-500">
                                    <svg class="w-4 h-4 text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                                    </svg>
                                    Money-back guarantee
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ─── RIGHT: Payment Form ─── -->
                    <div class="lg:col-span-3">
                        <div class="bg-[#0c1c30] border border-white/[0.07] rounded-2xl p-4 sm:p-7 lg:p-9">

                            <h2 class="text-white font-semibold text-xl mb-7">Payment Details</h2>

                            <form @submit.prevent="submitPayment" class="space-y-6">

                                <!-- Payment Method Toggle -->
                                <div>
                                    <p class="text-[11px] text-gray-500 uppercase tracking-widest font-medium mb-3">Payment Method</p>
                                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
                                        <!-- Card -->
                                        <button type="button"
                                            :class="['py-2.5 px-4 rounded-xl border text-sm font-medium transition-all flex items-center justify-center gap-2',
                                                paymentMethod === 'card'
                                                    ? 'border-amber-400/60 bg-amber-400/10 text-amber-300'
                                                    : 'border-white/[0.08] bg-white/[0.02] text-gray-400 hover:border-white/20']"
                                            @click="paymentMethod = 'card'">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                            </svg>
                                            Card
                                        </button>

                                        <!-- PayPal -->
                                        <button type="button"
                                            :class="['py-2.5 px-4 rounded-xl border text-sm font-medium transition-all flex items-center justify-center gap-2',
                                                paymentMethod === 'paypal'
                                                    ? 'border-amber-400/60 bg-amber-400/10 text-amber-300'
                                                    : 'border-white/[0.08] bg-white/[0.02] text-gray-400 hover:border-white/20']"
                                            @click="paymentMethod = 'paypal'">
                                            <!-- PayPal logo -->
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-5 w-5" fill="none">
                                                <path d="M7.076 21.337H2.47a.641.641 0 0 1-.633-.74L4.944.901C5.026.382 5.474 0 5.998 0h7.46c2.57 0 4.578.543 5.69 1.81 1.01 1.15 1.304 2.42 1.012 4.287-.023.143-.047.288-.077.437-.983 5.05-4.349 6.797-8.647 6.797h-2.19c-.524 0-.968.382-1.05.9l-1.12 7.106zm14.146-14.42c-.028.145-.058.29-.086.436C20.13 12.41 16.764 14 12.466 14H10.28c-.526 0-.97.386-1.05.906l-1.42 8.995h3.29c.46 0 .851-.333.924-.788l.038-.196.734-4.653.047-.258a.934.934 0 0 1 .924-.788h.582c3.765 0 6.712-1.53 7.573-5.953.36-1.847.174-3.39-.7-4.47z" fill="#009cde"/>
                                            </svg>
                                            PayPal
                                        </button>

                                        <!-- Apple Pay -->
                                        <button type="button"
                                            :class="['py-2.5 px-4 rounded-xl border text-sm font-medium transition-all flex items-center justify-center gap-2',
                                                paymentMethod === 'apple_pay'
                                                    ? 'border-amber-400/60 bg-amber-400/10 text-amber-300'
                                                    : 'border-white/[0.08] bg-white/[0.02] text-gray-400 hover:border-white/20']"
                                            @click="paymentMethod = 'apple_pay'">
                                            <!-- Apple Pay logo -->
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 50 20" class="h-5 w-12">
                                                <path d="M9.3 3.8C8.7 4.5 7.7 5 6.8 4.9c-.1-.9.3-1.8.9-2.4.6-.7 1.7-1.2 2.5-1.2.1 1-.3 1.9-.9 2.5zm.9 1.4c-1.4-.1-2.6.8-3.3.8-.7 0-1.7-.8-2.9-.8C2.5 5.3 1 6.2.3 7.6c-1.4 2.4-.4 6 1 7.9.7.9 1.5 2 2.5 2 1 0 1.4-.6 2.6-.6 1.2 0 1.5.6 2.6.6 1.1 0 1.8-.9 2.5-1.9.7-1.1 1-2.2 1-2.2s-2.1-.8-2.1-3.1c0-2 1.7-3 1.7-3-.9-1.4-2.4-1.8-2.9-1.8v.7z" fill="#fff"/>
                                                <path d="M19.5 5.4h-2.2V14h1.4v-3h.8c1.9 0 3.1-1.2 3.1-2.8 0-1.6-1.1-2.8-3.1-2.8zm.1 4.4h-.9V6.5h.9c1.2 0 1.8.6 1.8 1.6 0 1.1-.6 1.7-1.8 1.7zm7.6-1.2c-.9 0-1.6.5-1.9 1.3h-.1V8.7h-1.2V14h1.3v-2.9c0-.9.5-1.5 1.4-1.5.9 0 1.3.5 1.3 1.5V14h1.3v-3.2c0-1.5-.8-2.2-2.1-2.2zm6.5 5c-.7.3-1.3.4-1.9.4-1.7 0-2.8-1.1-2.8-2.8 0-1.6 1.1-2.7 2.7-2.7 1 0 1.9.4 2.4 1.6l-1.2.5c-.3-.6-.7-.9-1.2-.9-.8 0-1.4.6-1.4 1.5 0 1 .6 1.6 1.5 1.6.5 0 .9-.2 1.3-.4l.6.9v.3zm3.1-5h-1.3V14H38V8.6h-1.2zm-.6-2.8c-.4 0-.8.4-.8.9 0 .4.3.8.8.8s.8-.4.8-.8c0-.5-.3-.9-.8-.9zM43.5 14l-2.3-5.4h1.4l1.5 3.9 1.5-3.9H47L43.7 21h-1.4l1.2-3z" fill="#fff"/>
                                            </svg>
                                            Apple Pay
                                        </button>

                                        <!-- Google Pay -->
                                        <button type="button"
                                            :class="['py-2.5 px-4 rounded-xl border text-sm font-medium transition-all flex items-center justify-center gap-2',
                                                paymentMethod === 'google_pay'
                                                    ? 'border-amber-400/60 bg-amber-400/10 text-amber-300'
                                                    : 'border-white/[0.08] bg-white/[0.02] text-gray-400 hover:border-white/20']"
                                            @click="paymentMethod = 'google_pay'">
                                            <!-- Google Pay logo -->
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 41 17" class="h-4 w-12">
                                                <path d="M19.526 2.635v4.083h2.518c.6 0 1.096-.202 1.488-.605.403-.402.605-.882.605-1.437 0-.544-.202-1.018-.605-1.422-.392-.413-.888-.62-1.488-.62h-2.518zm0 5.52v4.736h-1.504V1.198h3.99c1.013 0 1.873.337 2.582 1.012.72.675 1.08 1.497 1.08 2.466 0 .991-.36 1.819-1.08 2.482-.698.665-1.559.996-2.583.996h-2.485v.001zm7.668 2.287c0 .392.166.718.499.979.332.26.722.39 1.168.39.633 0 1.196-.234 1.692-.701.497-.469.744-1.019.744-1.65-.469-.37-1.123-.555-1.962-.555-.61 0-1.12.148-1.528.442-.409.294-.613.657-.613 1.095zm1.946-5.815c1.112 0 1.989.297 2.633.89.642.594.964 1.408.964 2.442v4.932h-1.439v-1.11h-.065c-.622.914-1.45 1.372-2.486 1.372-.882 0-1.621-.262-2.215-.784-.594-.523-.891-1.176-.891-1.96 0-.828.313-1.486.94-1.976s1.463-.735 2.51-.735c.892 0 1.629.163 2.206.49v-.344c0-.522-.207-.966-.621-1.33a2.132 2.132 0 0 0-1.455-.547c-.84 0-1.504.353-1.995 1.062l-1.324-.834c.73-1.045 1.81-1.568 3.238-1.568zm9.776.[16]h1.505l-5.08 11.653H33.85l1.892-4.109-3.354-7.544h1.593l2.416 5.768h.032l2.353-5.768z" fill="#5f6368"/>
                                                <path d="M13.448 7.134c0-.473-.04-.93-.116-1.37H6.988v2.588h3.634a3.11 3.11 0 0 1-1.344 2.042v1.68h2.169c1.27-1.17 2.001-2.9 2.001-4.94z" fill="#4285f4"/>
                                                <path d="M6.988 13.7c1.816 0 3.339-.595 4.453-1.616l-2.168-1.681c-.603.406-1.38.643-2.285.643-1.754 0-3.244-1.182-3.776-2.773H.978v1.731A6.728 6.728 0 0 0 6.988 13.7z" fill="#34a853"/>
                                                <path d="M3.212 8.273a4.034 4.034 0 0 1 0-2.569V3.974H.978A6.678 6.678 0 0 0 .261 6.99a6.678 6.678 0 0 0 .717 3.014l2.234-1.731z" fill="#fbbc04"/>
                                                <path d="M6.988 2.931c.992 0 1.88.34 2.58 1.008l1.92-1.918C10.321.928 8.804.262 6.988.262a6.728 6.728 0 0 0-6.01 3.712l2.234 1.731c.532-1.59 2.022-2.774 3.776-2.774z" fill="#ea4335"/>
                                            </svg>
                                            Google Pay
                                        </button>

                                        <!-- Crypto -->
                                        <button type="button"
                                            :class="['py-2.5 px-4 rounded-xl border text-sm font-medium transition-all flex items-center justify-center gap-2',
                                                paymentMethod === 'crypto'
                                                    ? 'border-amber-400/60 bg-amber-400/10 text-amber-300'
                                                    : 'border-white/[0.08] bg-white/[0.02] text-gray-400 hover:border-white/20']"
                                            @click="paymentMethod = 'crypto'">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            Crypto
                                        </button>
                                    </div>
                                </div>

                                <!-- Card Form -->
                                <div v-if="paymentMethod === 'card'" class="space-y-4">

                                    <!-- Accepted cards (highlight active) -->
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="text-[10px] text-gray-600 uppercase tracking-wider mr-1">Accepted</span>
                                        <div class="flex gap-1.5">
                                            <div :class="['h-7 w-11 rounded overflow-hidden bg-white flex items-center justify-center transition-all p-1', stripeCardBrand === 'visa' ? 'ring-2 ring-amber-400/60 scale-110' : 'opacity-50 hover:opacity-70']">
                                                <img src="/images/payment/visa.svg" alt="Visa" class="h-full w-full object-contain" />
                                            </div>
                                            <div :class="['h-7 w-11 rounded overflow-hidden bg-white flex items-center justify-center transition-all p-0.5', stripeCardBrand === 'mastercard' ? 'ring-2 ring-amber-400/60 scale-110' : 'opacity-50 hover:opacity-70']">
                                                <img src="/images/payment/mastercard.svg" alt="Mastercard" class="h-full w-full object-contain" />
                                            </div>
                                            <div :class="['h-7 w-11 rounded overflow-hidden bg-white flex items-center justify-center transition-all p-1', stripeCardBrand === 'amex' ? 'ring-2 ring-amber-400/60 scale-110' : 'opacity-50 hover:opacity-70']">
                                                <img src="/images/payment/amex.svg" alt="Amex" class="h-full w-full object-contain" />
                                            </div>
                                            <div :class="['h-7 w-11 rounded overflow-hidden bg-white flex items-center justify-center transition-all p-1', stripeCardBrand === 'discover' ? 'ring-2 ring-amber-400/60 scale-110' : 'opacity-50 hover:opacity-70']">
                                                <img src="/images/payment/discover.svg" alt="Discover" class="h-full w-full object-contain" />
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Card Number -->
                                    <div>
                                        <label class="block text-xs text-gray-400 font-medium mb-1.5">Card Number</label>
                                        <div class="relative">
                                            <div ref="cardNumberMount"
                                                :class="['w-full rounded-xl bg-[#0a1628] border px-4 py-3 transition',
                                                    (stripeCardNumberError || localErrors.cardNumber) ? 'border-red-500/70' : (stripeCardNumberComplete ? 'border-emerald-500/60' : 'border-white/[0.08]')]">
                                            </div>
                                            <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none">
                                                <img v-if="stripeCardBrand === 'visa'" src="/images/payment/visa.svg" alt="Visa" class="h-5 w-9 object-contain" />
                                                <img v-else-if="stripeCardBrand === 'mastercard'" src="/images/payment/mastercard.svg" alt="Mastercard" class="h-6 w-9 object-contain" />
                                                <img v-else-if="stripeCardBrand === 'amex'" src="/images/payment/amex.svg" alt="Amex" class="h-5 w-9 object-contain" />
                                                <img v-else-if="stripeCardBrand === 'discover'" src="/images/payment/discover.svg" alt="Discover" class="h-5 w-9 object-contain" />
                                                <svg v-else class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                                </svg>
                                            </div>
                                        </div>
                                        <p v-if="stripeCardNumberError || localErrors.cardNumber" class="text-red-400 text-xs mt-1 flex items-center gap-1">
                                            <svg class="w-3 h-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                            {{ stripeCardNumberError || localErrors.cardNumber }}
                                        </p>
                                        <p v-else-if="stripeCardNumberComplete" class="text-emerald-400 text-xs mt-1 flex items-center gap-1">
                                            <svg class="w-3 h-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                            Valid card
                                        </p>
                                    </div>

                                    <!-- Expiry + CVC -->
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-xs text-gray-400 font-medium mb-1.5">Expiry Date</label>
                                            <div ref="cardExpiryMount"
                                                :class="['w-full rounded-xl bg-[#0a1628] border px-4 py-3 transition',
                                                    (stripeCardExpiryError || localErrors.expiryDate) ? 'border-red-500/70' : (stripeCardExpiryComplete ? 'border-emerald-500/60' : 'border-white/[0.08]')]">
                                            </div>
                                            <p v-if="stripeCardExpiryError || localErrors.expiryDate" class="text-red-400 text-xs mt-1 flex items-center gap-1">
                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                                {{ stripeCardExpiryError || localErrors.expiryDate }}
                                            </p>
                                        </div>
                                        <div>
                                            <label class="block text-xs text-gray-400 font-medium mb-1.5">CVC / CVV</label>
                                            <div class="relative">
                                                <div ref="cardCvcMount"
                                                    :class="['w-full rounded-xl bg-[#0a1628] border px-4 py-3 transition',
                                                        (stripeCardCvcError || localErrors.cvv) ? 'border-red-500/70' : (stripeCardCvcComplete ? 'border-emerald-500/60' : 'border-white/[0.08]')]">
                                                </div>
                                                <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-600 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                                </svg>
                                            </div>
                                            <p v-if="stripeCardCvcError || localErrors.cvv" class="text-red-400 text-xs mt-1 flex items-center gap-1">
                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                                {{ stripeCardCvcError || localErrors.cvv }}
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Name on Card -->
                                    <div>
                                        <label class="block text-xs text-gray-400 font-medium mb-1.5">Name on Card</label>
                                        <input type="text" v-model="form.cardName" placeholder="Full name as on card"
                                            autocomplete="cc-name"
                                            :class="['w-full h-11 px-4 rounded-xl bg-[#0a1628] border text-[13px] text-gray-200 placeholder-gray-600 focus:outline-none focus:ring-1 transition', (localErrors.cardName || form.errors.cardName) ? 'border-red-500/70 focus:border-red-500 focus:ring-red-500/20' : 'border-white/[0.08] focus:border-amber-400/50 focus:ring-amber-400/20']" />
                                        <p v-if="localErrors.cardName || form.errors.cardName" class="text-red-400 text-xs mt-1 flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                            {{ localErrors.cardName || form.errors.cardName }}
                                        </p>
                                    </div>

                                    <!-- Divider -->
                                    <div class="flex items-center gap-3 py-1">
                                        <div class="flex-1 border-t border-white/[0.06]"></div>
                                        <span class="text-[11px] text-gray-600 uppercase tracking-wider">Billing Address</span>
                                        <div class="flex-1 border-t border-white/[0.06]"></div>
                                    </div>

                                    <!-- Street Address -->
                                    <div>
                                        <label class="block text-xs text-gray-400 font-medium mb-1.5">Street Address</label>
                                        <input type="text" v-model="form.address" placeholder="Street address or PO box"
                                            autocomplete="street-address"
                                            :class="['w-full h-11 px-4 rounded-xl bg-[#0a1628] border text-[13px] text-gray-200 placeholder-gray-600 focus:outline-none focus:ring-1 transition', (localErrors.address || form.errors.address) ? 'border-red-500/70 focus:border-red-500 focus:ring-red-500/20' : 'border-white/[0.08] focus:border-amber-400/50 focus:ring-amber-400/20']" />
                                        <p v-if="localErrors.address || form.errors.address" class="text-red-400 text-xs mt-1 flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                            {{ localErrors.address || form.errors.address }}
                                        </p>
                                    </div>

                                    <!-- Apartment -->
                                    <div>
                                        <input type="text" v-model="form.apartment"
                                            placeholder="Apt., suite, unit, building (Optional)"
                                            autocomplete="address-line2"
                                            class="w-full h-11 px-4 rounded-xl bg-[#0a1628] border border-white/[0.08] text-[13px] text-gray-200 placeholder-gray-600 focus:outline-none focus:border-amber-400/50 focus:ring-1 focus:ring-amber-400/20 transition" />
                                    </div>

                                    <!-- City + State -->
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <!-- City -->
                                        <div>
                                            <label class="block text-xs text-gray-400 font-medium mb-1.5">City</label>
                                            <input type="text" v-model="form.city" placeholder="City"
                                                autocomplete="address-level2"
                                                list="city-suggestions"
                                                :class="['w-full h-11 px-4 rounded-xl bg-[#0a1628] border text-[13px] text-gray-200 placeholder-gray-600 focus:outline-none focus:ring-1 transition', (localErrors.city || form.errors.city) ? 'border-red-500/70 focus:border-red-500 focus:ring-red-500/20' : 'border-white/[0.08] focus:border-amber-400/50 focus:ring-amber-400/20']" />
                                            <datalist id="city-suggestions">
                                                <option v-for="city in ['New York','Los Angeles','Chicago','Houston','Phoenix','Philadelphia','San Antonio','San Diego','Dallas','San Jose','Austin','Jacksonville','Fort Worth','Columbus','Charlotte','Indianapolis','San Francisco','Seattle','Denver','Nashville','Oklahoma City','El Paso','Las Vegas','Louisville','Memphis','Portland','Baltimore','Milwaukee','Albuquerque','Tucson','Fresno','Sacramento','Kansas City','Atlanta','Miami','Tampa','Minneapolis','New Orleans','Cleveland','Raleigh','Omaha','Virginia Beach','Colorado Springs','Long Beach']" :key="city" :value="city"/>
                                            </datalist>
                                            <p v-if="localErrors.city || form.errors.city" class="text-red-400 text-xs mt-1 flex items-center gap-1">
                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                                {{ localErrors.city || form.errors.city }}
                                            </p>
                                        </div>
                                        <!-- State dropdown with search -->
                                        <div class="relative">
                                            <label class="block text-xs text-gray-400 font-medium mb-1.5">State / Region</label>
                                            <input type="text" v-model="stateQuery"
                                                @focus="showStateDrop = true"
                                                @blur="() => { setTimeout(() => showStateDrop = false, 150); markTouched('state'); if (!US_STATES.includes(stateQuery)) form.state = stateQuery; }"
                                                @input="() => { showStateDrop = true; form.state = stateQuery; }"
                                                placeholder="State or region"
                                                autocomplete="address-level1"
                                                :class="['w-full h-11 px-4 rounded-xl bg-[#0a1628] border text-[13px] text-gray-200 placeholder-gray-600 focus:outline-none focus:ring-1 transition', (localErrors.state || form.errors.state) ? 'border-red-500/70 focus:border-red-500 focus:ring-red-500/20' : 'border-white/[0.08] focus:border-amber-400/50 focus:ring-amber-400/20']" />
                                            <!-- Dropdown -->
                                            <div v-if="showStateDrop && filteredStates.length"
                                                class="absolute z-50 top-full mt-1 w-full bg-[#0e1f35] border border-white/10 rounded-xl shadow-xl overflow-hidden max-h-44 overflow-y-auto">
                                                <button v-for="s in filteredStates" :key="s" type="button"
                                                    @mousedown.prevent="selectState(s)"
                                                    class="w-full text-left px-4 py-2.5 text-[13px] text-gray-200 hover:bg-amber-400/10 hover:text-amber-300 transition">
                                                    {{ s }}
                                                </button>
                                            </div>
                                            <p v-if="localErrors.state || form.errors.state" class="text-red-400 text-xs mt-1 flex items-center gap-1">
                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                                {{ localErrors.state || form.errors.state }}
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Country -->
                                    <div>
                                        <label class="block text-xs text-gray-400 font-medium mb-1.5">Country</label>
                                        <select v-model="form.country" autocomplete="country-name"
                                            class="w-full h-11 px-4 rounded-xl bg-[#0a1628] border border-white/[0.08] text-[13px] text-gray-200 focus:outline-none focus:border-amber-400/50 focus:ring-1 focus:ring-amber-400/20 transition appearance-none cursor-pointer">
                                            <option>United States</option>
                                            <option>Canada</option>
                                            <option>United Kingdom</option>
                                            <option>Australia</option>
                                            <option>Other</option>
                                        </select>
                                    </div>

                                    <!-- Payment error message -->
                                    <p v-if="localErrors.payment" class="text-red-400 text-sm text-center bg-red-500/10 border border-red-500/30 rounded-lg px-4 py-2">
                                        {{ localErrors.payment }}
                                    </p>

                                    <!-- Submit Button -->
                                    <button type="submit" :disabled="isProcessing"
                                        class="w-full h-12 mt-2 rounded-xl bg-amber-400 hover:bg-amber-300 text-[#07101e] font-bold text-sm tracking-wide transition-all shadow-lg shadow-amber-400/20 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                                        <svg v-if="!isProcessing" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                        </svg>
                                        <svg v-else class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                        </svg>
                                        <span>{{ isProcessing ? 'Processing Payment...' : `Pay $${totalAmount.toFixed(2)}` }}</span>
                                    </button>

                                    <p class="text-center text-[11px] text-gray-600">
                                        By completing this payment you agree to our
                                        <a href="#" class="text-gray-500 hover:text-amber-400 transition underline">Terms of Service</a>
                                    </p>
                                </div>

                                <!-- PayPal Button -->
                                <div v-else-if="paymentMethod === 'paypal'" class="py-4">

                                    <!-- Loading spinner -->
                                    <div v-if="paypalLoading" class="flex flex-col items-center justify-center py-10 gap-3">
                                        <svg class="w-8 h-8 animate-spin text-amber-400" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                        </svg>
                                        <p class="text-gray-500 text-sm">Processing payment…</p>
                                    </div>

                                    <!-- Error message -->
                                    <div v-if="paypalError" class="mb-4 bg-red-500/10 border border-red-500/30 rounded-xl px-4 py-3 text-red-400 text-sm text-center">
                                        {{ paypalError }}
                                    </div>

                                    <!-- PayPal button container -->
                                    <div id="paypal-button-container" class="min-h-[52px]"></div>

                                    <p class="text-center text-[11px] text-gray-600 mt-4">
                                        You will complete payment securely in the PayPal window.
                                    </p>
                                </div>

                                <!-- Apple Pay -->
                                <div v-else-if="paymentMethod === 'apple_pay'" class="py-4">

                                    <!-- Loading spinner -->
                                    <div v-if="applePayLoading" class="flex flex-col items-center justify-center py-10 gap-3">
                                        <svg class="w-8 h-8 animate-spin text-amber-400" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                        </svg>
                                        <p class="text-gray-500 text-sm">Checking availability…</p>
                                    </div>

                                    <!-- Error message -->
                                    <div v-else-if="localErrors.payment" class="mb-4 bg-red-500/10 border border-red-500/30 rounded-xl px-4 py-3 text-red-400 text-sm text-center">
                                        {{ localErrors.payment }}
                                    </div>

                                    <!-- Device supports Apple Pay but not ready (no card / iCloud / Touch ID) -->
                                    <div v-else-if="applePayNeedCard" class="space-y-4">
                                        <div class="bg-amber-500/10 border border-amber-500/30 rounded-xl p-5 text-center">
                                            <svg class="w-10 h-10 text-amber-400 mx-auto mb-3" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M17.05 20.28c-.98.95-2.05.88-3.08.4-1.09-.5-2.08-.48-3.24 0-1.44.62-2.2.44-3.06-.4C2.79 15.25 3.51 7.59 9.05 7.31c1.35.07 2.29.74 3.08.8 1.18-.24 2.31-.93 3.57-.84 1.51.12 2.65.72 3.4 1.8-3.12 1.87-2.38 5.98.48 7.13-.57 1.5-1.31 2.99-2.54 4.09l.01-.01zM12.03 7.25c-.15-2.23 1.66-4.07 3.74-4.25.29 2.58-2.34 4.5-3.74 4.25z"/>
                                            </svg>
                                            <p class="text-white text-sm font-semibold mb-1">Apple Pay needs to be set up</p>
                                            <p class="text-amber-300 text-xs">Your browser supports Apple Pay, but it's not ready yet.</p>
                                        </div>
                                        <div class="bg-white/[0.04] border border-white/[0.08] rounded-xl p-4 space-y-3 text-xs text-gray-400">
                                            <p class="font-medium text-gray-300">To enable Apple Pay on this Mac:</p>
                                            <ol class="space-y-2 list-decimal list-outside pl-4">
                                                <li>Open <strong class="text-white">System Settings</strong> → <strong class="text-white">Apple ID</strong> and make sure you're signed in to iCloud</li>
                                                <li>Go to <strong class="text-white">System Settings</strong> → <strong class="text-white">Wallet &amp; Apple Pay</strong></li>
                                                <li>Click <strong class="text-white">Add Card</strong> and follow the steps</li>
                                                <li>Return here and click <button type="button" class="text-amber-400 underline underline-offset-2 hover:text-amber-300 font-medium" @click="initApplePay()">Try Again</button></li>
                                            </ol>
                                            <p class="pt-1 text-gray-500">On iPhone or iPad? Open <strong class="text-white">Settings</strong> → <strong class="text-white">Wallet &amp; Apple Pay</strong> → Add Card, then come back and tap <button type="button" class="text-amber-400 underline underline-offset-2 hover:text-amber-300 font-medium" @click="initApplePay()">Try Again</button>.</p>
                                        </div>
                                        <p class="text-xs text-gray-500 text-center">Prefer to type your card? <button type="button" class="text-amber-400 underline underline-offset-2 hover:text-amber-300" @click="paymentMethod = 'card'">Pay with Card</button> instead.</p>
                                    </div>

                                    <!-- Not available on this browser/device -->
                                    <div v-else-if="!applePayAvailable" class="space-y-4">
                                        <div class="bg-white/[0.04] border border-white/[0.08] rounded-xl p-5 text-center">
                                            <svg class="w-10 h-10 text-gray-400 mx-auto mb-3" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M17.05 20.28c-.98.95-2.05.88-3.08.4-1.09-.5-2.08-.48-3.24 0-1.44.62-2.2.44-3.06-.4C2.79 15.25 3.51 7.59 9.05 7.31c1.35.07 2.29.74 3.08.8 1.18-.24 2.31-.93 3.57-.84 1.51.12 2.65.72 3.4 1.8-3.12 1.87-2.38 5.98.48 7.13-.57 1.5-1.31 2.99-2.54 4.09l.01-.01zM12.03 7.25c-.15-2.23 1.66-4.07 3.74-4.25.29 2.58-2.34 4.5-3.74 4.25z"/>
                                            </svg>
                                            <p class="text-gray-300 text-sm font-medium mb-1">Apple Pay</p>
                                            <p class="text-gray-500 text-xs">Not available on this browser or device.</p>
                                        </div>
                                        <div class="space-y-2 text-xs text-gray-500">
                                            <p class="font-medium text-gray-400">Requirements for Apple Pay:</p>
                                            <ul class="space-y-1 list-disc list-inside">
                                                <li>Safari browser on iPhone, iPad, or Mac</li>
                                                <li>A card added to Apple Wallet / iCloud Keychain</li>
                                            </ul>
                                            <p class="pt-1">On Android or Chrome? Use the <button type="button" class="text-amber-400 underline underline-offset-2 hover:text-amber-300" @click="paymentMethod = 'google_pay'">Google Pay</button> tab instead.</p>
                                        </div>
                                    </div>

                                    <!-- Apple Pay button container -->
                                    <div v-else>
                                        <div id="apple-pay-button" class="min-h-[48px]"></div>
                                        <div class="mt-4 space-y-2">
                                            <div class="flex items-center gap-3 text-xs text-gray-500">
                                                <svg class="w-4 h-4 text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                Secure one-tap checkout with Touch ID or Face ID
                                            </div>
                                            <div class="flex items-center gap-3 text-xs text-gray-500">
                                                <svg class="w-4 h-4 text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                                </svg>
                                                Your card details are never shared with us
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Google Pay -->
                                <div v-else-if="paymentMethod === 'google_pay'" class="py-4">

                                    <!-- Loading spinner -->
                                    <div v-if="googlePayLoading" class="flex flex-col items-center justify-center py-10 gap-3">
                                        <svg class="w-8 h-8 animate-spin text-amber-400" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                        </svg>
                                        <p class="text-gray-500 text-sm">Checking availability…</p>
                                    </div>

                                    <!-- Error -->
                                    <div v-else-if="googlePayError" class="mb-4 bg-red-500/10 border border-red-500/30 rounded-xl px-4 py-3 text-red-400 text-sm text-center">
                                        {{ googlePayError }}
                                    </div>

                                    <!-- Not available -->
                                    <div v-else-if="!googlePayButtonMounted && !googlePayLoading && !googlePayError" class="space-y-4">
                                        <div class="bg-white/[0.04] border border-white/[0.08] rounded-xl p-5 text-center">
                                            <svg class="w-10 h-10 text-gray-400 mx-auto mb-3" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M12.545 10.239v3.821h5.445c-.712 2.315-2.647 3.972-5.445 3.972a6.033 6.033 0 110-12.064c1.498 0 2.866.549 3.921 1.453l2.814-2.814A9.969 9.969 0 0012.545 2C7.021 2 2.543 6.477 2.543 12s4.478 10 10.002 10c8.396 0 10.249-7.85 9.426-11.748l-9.426-.013z"/>
                                            </svg>
                                            <p class="text-gray-300 text-sm font-medium mb-1">Google Pay</p>
                                            <p class="text-gray-500 text-xs">Not available on this browser or device.</p>
                                        </div>
                                        <div class="space-y-2 text-xs text-gray-500">
                                            <p class="font-medium text-gray-400">Requirements for Google Pay:</p>
                                            <ul class="space-y-1 list-disc list-inside">
                                                <li>Chrome browser (desktop or Android)</li>
                                                <li>A Google account with a saved payment card</li>
                                            </ul>
                                            <p class="pt-1">On iPhone/Safari? Use the <button type="button" class="text-amber-400 underline underline-offset-2 hover:text-amber-300" @click="paymentMethod = 'apple_pay'">Apple Pay</button> tab instead.</p>
                                        </div>
                                    </div>

                                    <!-- Google Pay button container -->
                                    <div v-else>
                                        <div id="google-pay-button" class="min-h-[48px] w-full"></div>
                                        <div class="mt-4 space-y-2">
                                            <div class="flex items-center gap-3 text-xs text-gray-500">
                                                <svg class="w-4 h-4 text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                Secure checkout with your Google account
                                            </div>
                                            <div class="flex items-center gap-3 text-xs text-gray-500">
                                                <svg class="w-4 h-4 text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                                </svg>
                                                Your card details are never shared with us
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Cryptocurrency Payment -->
                                <div v-else-if="paymentMethod === 'crypto'" class="py-4">
                                    
                                    <!-- Loading spinner -->
                                    <div v-if="cryptoLoading" class="flex flex-col items-center justify-center py-10 gap-3">
                                        <svg class="w-8 h-8 animate-spin text-amber-400" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                        </svg>
                                        <p class="text-gray-500 text-sm">Loading…</p>
                                    </div>

                                    <!-- Error message -->
                                    <div v-else-if="cryptoError" class="mb-4 bg-red-500/10 border border-red-500/30 rounded-xl px-4 py-3 text-red-400 text-sm text-center">
                                        {{ cryptoError }}
                                    </div>

                                    <!-- Wallet address payment -->
                                    <div v-else class="space-y-5">

                                        <!-- USDC logo + header -->
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-full bg-blue-500/20 border border-blue-400/30 flex items-center justify-center flex-shrink-0">
                                                <span class="text-[15px] font-extrabold text-blue-400">$</span>
                                            </div>
                                            <div>
                                                <p class="text-sm font-semibold text-white">Pay with USDC</p>
                                                <p class="text-xs text-gray-500">USD Coin · Stablecoin (1 USDC = $1 USD)</p>
                                            </div>
                                        </div>

                                        <!-- ⚠️ Network warning -->
                                        <div class="flex items-start gap-3 bg-orange-500/10 border border-orange-400/40 rounded-xl px-4 py-3">
                                            <svg class="w-5 h-5 text-orange-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                                            </svg>
                                            <div>
                                                <p class="text-sm font-semibold text-orange-300">Ethereum Network (ERC20) Only</p>
                                                <p class="text-xs text-gray-400 mt-0.5 leading-relaxed">Send USDC only on the <strong class="text-orange-300">Ethereum</strong> network. Sending via any other network (Polygon, BSC, Arbitrum, etc.) to this address will result in <strong class="text-red-400">permanent loss of funds</strong>.</p>
                                            </div>
                                        </div>

                                        <!-- Amount to send -->
                                        <div class="bg-[#0a1628] border border-white/[0.08] rounded-xl px-5 py-4 space-y-2.5">
                                            <div class="flex justify-between items-center text-sm">
                                                <span class="text-gray-400">Order Total (USD)</span>
                                                <span class="text-white font-semibold">${{ totalAmount.toFixed(2) }}</span>
                                            </div>
                                            <div class="flex justify-between items-center text-sm border-t border-white/[0.06] pt-2.5">
                                                <span class="text-gray-400">Amount to Send (USDC)</span>
                                                <span class="text-blue-300 font-bold text-base">{{ totalAmount.toFixed(2) }} USDC</span>
                                            </div>
                                            <div class="flex justify-between items-center text-sm">
                                                <span class="text-gray-400">Network</span>
                                                <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-300 bg-emerald-400/10 border border-emerald-400/20 rounded-full px-2.5 py-0.5">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                                    Ethereum (ERC20)
                                                </span>
                                            </div>
                                        </div>

                                        <!-- Wallet address -->
                                        <div>
                                            <p class="text-xs text-gray-400 font-medium mb-2">Send USDC to this address:</p>
                                            <div class="flex items-center gap-2 bg-[#0a1628] border border-white/[0.10] rounded-xl px-4 py-3">
                                                <span class="flex-1 font-mono text-[12px] text-amber-300 break-all select-all leading-relaxed">{{ usdcWalletAddress }}</span>
                                                <button
                                                    type="button"
                                                    @click="copyWalletAddress"
                                                    class="flex-shrink-0 flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition-all"
                                                    :class="cryptoCopied ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-400/30' : 'bg-white/[0.06] text-gray-300 hover:bg-white/[0.10] border border-white/[0.08]'">
                                                    <svg v-if="!cryptoCopied" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                                    </svg>
                                                    <svg v-else class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                    </svg>
                                                    {{ cryptoCopied ? 'Copied!' : 'Copy' }}
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Instructions -->
                                        <div class="bg-white/[0.02] border border-white/[0.06] rounded-xl px-4 py-4 space-y-2">
                                            <p class="text-xs font-semibold text-gray-300 mb-3">How to pay:</p>
                                            <div class="flex items-start gap-2.5 text-xs text-gray-400">
                                                <span class="w-5 h-5 rounded-full bg-amber-400/20 text-amber-400 text-[10px] font-bold flex items-center justify-center flex-shrink-0">1</span>
                                                Copy the wallet address above.
                                            </div>
                                            <div class="flex items-start gap-2.5 text-xs text-gray-400">
                                                <span class="w-5 h-5 rounded-full bg-amber-400/20 text-amber-400 text-[10px] font-bold flex items-center justify-center flex-shrink-0">2</span>
                                                Open your crypto wallet and send exactly <strong class="text-white">{{ totalAmount.toFixed(2) }} USDC</strong> on the <strong class="text-orange-300">Ethereum</strong> network.
                                            </div>
                                            <div class="flex items-start gap-2.5 text-xs text-gray-400">
                                                <span class="w-5 h-5 rounded-full bg-amber-400/20 text-amber-400 text-[10px] font-bold flex items-center justify-center flex-shrink-0">3</span>
                                                Upload a screenshot of your completed transaction below and click <strong class="text-white">"I've Sent the Payment"</strong>.
                                            </div>
                                        </div>

                                        <!-- ── Screenshot Upload ── -->
                                        <div>
                                            <p class="text-xs font-semibold text-gray-300 mb-2">
                                                Payment Screenshot <span class="text-red-400">*</span>
                                                <span class="text-gray-500 font-normal ml-1">(required)</span>
                                            </p>

                                            <!-- Preview -->
                                            <div v-if="cryptoScreenshotPreview" class="mb-3 relative">
                                                <img :src="cryptoScreenshotPreview" alt="Payment screenshot preview"
                                                    class="w-full max-h-56 object-contain rounded-xl border border-white/[0.10] bg-[#0a1628]" />
                                                <button type="button" @click="removeCryptoScreenshot"
                                                    class="absolute top-2 right-2 w-7 h-7 rounded-full bg-red-500/80 hover:bg-red-500 flex items-center justify-center transition">
                                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                    </svg>
                                                </button>
                                                <p class="mt-1.5 text-xs text-emerald-400 flex items-center gap-1">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                    </svg>
                                                    {{ cryptoScreenshot?.name }}
                                                </p>
                                            </div>

                                            <!-- Drop zone -->
                                            <div v-else
                                                @click="cryptoScreenshotInput.click()"
                                                class="border-2 border-dashed border-white/[0.12] hover:border-amber-400/50 rounded-xl px-4 py-6 cursor-pointer flex flex-col items-center gap-2 transition bg-white/[0.01] hover:bg-amber-400/[0.03]">
                                                <svg class="w-8 h-8 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                                <p class="text-sm text-gray-400">Click to upload screenshot</p>
                                                <p class="text-xs text-gray-600">PNG, JPG or WEBP · max 10 MB</p>
                                            </div>

                                            <!-- Hidden input -->
                                            <input ref="cryptoScreenshotInput" type="file" accept="image/*"
                                                class="hidden" @change="onCryptoScreenshot" />
                                        </div>

                                        <!-- Confirm sent button -->
                                        <button
                                            type="button"
                                            @click="submitCryptoPayment"
                                            :disabled="cryptoLoading || !cryptoScreenshot"
                                            class="w-full h-12 rounded-xl bg-amber-400 hover:bg-amber-300 text-[#07101e] font-bold text-sm tracking-wide transition-all shadow-lg shadow-amber-400/20 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                                            <svg v-if="cryptoLoading" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                            </svg>
                                            <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            {{ cryptoLoading ? 'Submitting…' : "I've Sent the Payment" }}
                                        </button>

                                        <!-- Trust info -->
                                        <div class="space-y-2">
                                            <div class="flex items-center gap-2 text-xs text-gray-500">
                                                <svg class="w-4 h-4 text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                                </svg>
                                                Verified on the Ethereum blockchain
                                            </div>
                                            <div class="flex items-center gap-2 text-xs text-gray-500">
                                                <svg class="w-4 h-4 text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                No price volatility — 1 USDC always equals $1 USD
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </form>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
input[type="number"]::-webkit-inner-spin-button,
input[type="number"]::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}
input[type="number"] {
    -moz-appearance: textfield;
}
select {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%234b5563'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 0.75rem center;
    background-size: 1.5em 1.5em;
    padding-right: 2.5rem;
}
</style>
