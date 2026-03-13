<script setup>
import Globe3D from '@/Components/Globe3D.vue';
import { useTranslations } from '@/Composables/useTranslations';
import MarketingLayout from '@/Layouts/MarketingLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { onMounted } from 'vue';

const { __ } = useTranslations();

onMounted(() => {
    // Intersection Observer for scroll animations
    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-in');
                }
            });
        },
        { threshold: 0.1 }
    );

    document.querySelectorAll('.animate-on-scroll').forEach((el) => {
        observer.observe(el);
    });

    // Create particle background
    createParticles();
});

function createParticles() {
    const canvas = document.getElementById('particles-canvas');
    if (!canvas) return;
    
    const ctx = canvas.getContext('2d');
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;

    const particles = [];
    const particleCount = 80;

    class Particle {
        constructor() {
            this.x = Math.random() * canvas.width;
            this.y = Math.random() * canvas.height;
            this.size = Math.random() * 2 + 0.5;
            this.speedX = Math.random() * 0.5 - 0.25;
            this.speedY = Math.random() * 0.5 - 0.25;
            this.opacity = Math.random() * 0.5 + 0.2;
        }

        update() {
            this.x += this.speedX;
            this.y += this.speedY;

            if (this.x > canvas.width) this.x = 0;
            if (this.x < 0) this.x = canvas.width;
            if (this.y > canvas.height) this.y = 0;
            if (this.y < 0) this.y = canvas.height;
        }

        draw() {
            ctx.fillStyle = `rgba(250, 204, 21, ${this.opacity})`;
            ctx.beginPath();
            ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
            ctx.fill();
        }
    }

    for (let i = 0; i < particleCount; i++) {
        particles.push(new Particle());
    }

    function connectParticles() {
        for (let a = 0; a < particles.length; a++) {
            for (let b = a + 1; b < particles.length; b++) {
                const dx = particles[a].x - particles[b].x;
                const dy = particles[a].y - particles[b].y;
                const distance = Math.sqrt(dx * dx + dy * dy);

                if (distance < 120) {
                    ctx.strokeStyle = `rgba(250, 204, 21, ${0.15 * (1 - distance / 120)})`;
                    ctx.lineWidth = 0.5;
                    ctx.beginPath();
                    ctx.moveTo(particles[a].x, particles[a].y);
                    ctx.lineTo(particles[b].x, particles[b].y);
                    ctx.stroke();
                }
            }
        }
    }

    function animate() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        
        particles.forEach(particle => {
            particle.update();
            particle.draw();
        });
        
        connectParticles();
        requestAnimationFrame(animate);
    }

    animate();

    window.addEventListener('resize', () => {
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;
    });
}

const services = [
    {
        name: __('marketing.service_ccorp_name'),
        description: __('marketing.service_ccorp_desc'),
        icon: 'building-office',
        price: __('marketing.starting_at_ccorp'),
        features: [
            __('marketing.ccorp_feature_1'),
            __('marketing.ccorp_feature_2'),
            __('marketing.ccorp_feature_3'),
            __('marketing.ccorp_feature_4')
        ],
        route: 'services.c-corp'
    },
    {
        name: __('marketing.service_scorp_name'),
        description: __('marketing.service_scorp_desc'),
        icon: 'scale',
        price: __('marketing.starting_at_scorp'),
        features: [
            __('marketing.scorp_feature_1'),
            __('marketing.scorp_feature_2'),
            __('marketing.scorp_feature_3'),
            __('marketing.scorp_feature_4')
        ],
        route: 'services.s-corp'
    },
    {
        name: __('marketing.service_llc_name'),
        description: __('marketing.service_llc_desc'),
        icon: 'shield-check',
        price: __('marketing.starting_at_llc'),
        features: [
            __('marketing.llc_feature_1'),
            __('marketing.llc_feature_2'),
            __('marketing.llc_feature_3'),
            __('marketing.llc_feature_4')
        ],
        route: 'services.llc'
    },
    {
        name: __('marketing.service_nonprofit_name'),
        description: __('marketing.service_nonprofit_desc'),
        icon: 'heart',
        price: __('marketing.starting_at_399'),
        features: [
            __('marketing.nonprofit_feature_1'),
            __('marketing.nonprofit_feature_2'),
            __('marketing.nonprofit_feature_3'),
            __('marketing.nonprofit_feature_4')
        ],
        route: 'services.nonprofit'
    },
    {
        name: __('marketing.service_greencard_name'),
        description: __('marketing.service_greencard_desc'),
        icon: 'document-text',
        price: __('marketing.starting_at_greencard'),
        features: [
            __('marketing.greencard_feature_1'),
            __('marketing.greencard_feature_2'),
            __('marketing.greencard_feature_3'),
            __('marketing.greencard_feature_4')
        ],
        route: 'services.green-card'
    },
    {
        name: __('marketing.service_incometax_name'),
        description: __('marketing.service_incometax_desc'),
        icon: 'calculator',
        price: __('marketing.starting_at_incometax'),
        features: [
            __('marketing.incometax_feature_1'),
            __('marketing.incometax_feature_2'),
            __('marketing.incometax_feature_3'),
            __('marketing.incometax_feature_4')
        ],
        route: 'services.income-tax',
        isNew: true
    }
];

const testimonials = [
    {
        name: 'Sarah Johnson',
        company: 'TechStart LLC',
        content: ' CORPIUS made forming my LLC incredibly simple. The process was fast, professional, and they guided me through every step.',
        rating: 5
    },
    {
        name: 'Michael Chen',
        company: 'Green Future Nonprofit',
        content: 'Excellent service for nonprofit formation. They handled all the complex paperwork and helped us achieve tax-exempt status quickly.',
        rating: 5
    },
    {
        name: 'Jessica Rodriguez',
        company: 'Rodriguez Consulting Corp',
        content: 'Professional, efficient, and knowledgeable. I recommend  CORPIUS to anyone looking to incorporate their business.',
        rating: 5
    }
];

const stats = [
    { label: __('marketing.businesses_formed'), value: '2,500+' },
    { label: __('marketing.states_available'), value: '50' },
    { label: __('marketing.customer_satisfaction'), value: '99%' },
    { label: __('marketing.average_processing_time'), value: '3-7 Days' }
];
</script>

<template>
    <Head title="LLC &amp; Business Formation Services | CORPIUS">
        <meta head-key="description" name="description" content="Form your LLC, C-Corp, S-Corp, or Nonprofit with CORPIUS. Expert business formation, tax filing, and Green Card lottery services. Get started today." />
        <link head-key="canonical" rel="canonical" href="https://corpius.net/" />
        <meta head-key="og:title" property="og:title" content="LLC &amp; Business Formation Services | CORPIUS" />
        <meta head-key="og:description" property="og:description" content="Form your LLC, C-Corp, S-Corp, or Nonprofit with CORPIUS. Expert business formation, tax filing, and Green Card lottery services." />
        <meta head-key="og:url" property="og:url" content="https://corpius.net/" />
        <meta head-key="twitter:title" name="twitter:title" content="LLC &amp; Business Formation Services | CORPIUS" />
        <meta head-key="twitter:description" name="twitter:description" content="Form your LLC, C-Corp, S-Corp, or Nonprofit with CORPIUS. Expert business formation, tax filing, and Green Card lottery services." />
    </Head>

    <MarketingLayout>
        <!-- Hero Section -->
        <section class="relative overflow-hidden min-h-[85vh]" style="background-color: #0b1e33;">
            <!-- 3D Globe Background - Full Section -->
            <div class="absolute inset-0 w-full h-full">
                <Globe3D />
            </div>
            
            <!-- Dark Overlay for Text Readability -->
            <div class="absolute inset-0 bg-gradient-to-r from-black/80 via-black/60 to-transparent lg:to-black/20"></div>
            
            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-24 min-h-[85vh] flex items-center">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center w-full">
                    <!-- Hero Content -->
                    <div class="text-white animate-on-scroll fade-in-up z-10 cinematic-entrance">
                        <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold leading-tight mb-6">
                            <span class="text-yellow-400 inline-block transition-transform duration-300 gradient-text-shimmer">{{ __('marketing.home_title') }}</span>
                        </h1>
                        <p class="text-base sm:text-xl lg:text-2xl text-gray-300 mb-8 leading-relaxed">
                            {{ __('marketing.home_subtitle') }}
                        </p>
                        <div class="flex flex-col sm:flex-row gap-4">
                            <Link :href="route('register')" class="group relative bg-yellow-400 hover:bg-yellow-500 text-white px-8 py-4 rounded-lg text-lg font-semibold transition-all duration-500 text-center shadow-lg hover:shadow-2xl hover:shadow-yellow-400/50 transform hover:scale-105">
                                <span class="relative z-10">{{ __('marketing.get_started') }}</span>
                                <div class="absolute inset-0 bg-gradient-to-r from-yellow-500 to-yellow-600 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                            </Link>
                            <Link :href="route('marketing.contact')" class="border-2 border-yellow-400 text-yellow-400 hover:bg-yellow-400 hover:text-white px-8 py-4 rounded-lg text-lg font-semibold transition-all duration-500 text-center transform hover:scale-105">
                                {{ __('marketing.talk_to_expert') }}
                            </Link>
                        </div>

                        <!-- Trust Indicators -->
                        <div class="mt-8 sm:mt-12 flex flex-wrap items-center gap-3 sm:gap-6">
                            <div class="flex items-center group cursor-pointer">
                                <svg class="h-6 w-6 text-yellow-400 mr-2 transform group-hover:scale-125 transition-transform duration-300" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span class="text-gray-300 group-hover:text-white transition-colors duration-300">{{ __('marketing.money_back_guarantee') }}</span>
                            </div>
                            <div class="flex items-center group cursor-pointer">
                                <svg class="h-6 w-6 text-yellow-400 mr-2 transform group-hover:scale-125 transition-transform duration-300" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                                </svg>
                                <span class="text-gray-300 group-hover:text-white transition-colors duration-300">{{ __('marketing.secure_confidential') }}</span>
                            </div>
                            <div class="flex items-center group cursor-pointer">
                                <svg class="h-6 w-6 text-yellow-400 mr-2 transform group-hover:scale-125 transition-transform duration-300" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span class="text-gray-300 group-hover:text-white transition-colors duration-300">{{ __('marketing.expert_support') }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Empty space for globe (it's now in background) -->
                    <div class="hidden lg:block"></div>
                </div>
            </div>
        </section>

        <!-- Stats Section -->
        <section class="py-16" style="background-color: #0b1e33;">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-8">
                    <div v-for="stat in stats" :key="stat.label" class="text-center">
                        <div class="text-2xl sm:text-3xl lg:text-4xl font-bold text-yellow-400 mb-1 sm:mb-2">{{ stat.value }}</div>
                        <div class="text-gray-300 text-sm sm:text-base">{{ stat.label }}</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Services Section -->
        <section class="py-20" style="background-color: #0b1e33;">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-white mb-4">
                        {{ __('marketing.choose_business_structure') }}
                    </h2>
                    <p class="text-base sm:text-xl text-gray-300 max-w-3xl mx-auto">
                        {{ __('marketing.choose_business_structure_subtitle') }}
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div v-for="service in services" :key="service.name" class="flex flex-col border border-gray-600 rounded-xl p-8 hover:shadow-xl transition-all duration-300 hover:border-yellow-400 relative" style="background-color: rgba(255, 255, 255, 0.05); backdrop-filter: blur(10px);">
                        <!-- NEW Badge -->
                        <div v-if="service.isNew" class="absolute top-4 right-4 bg-green-500 text-white text-xs font-bold px-3 py-1 rounded-full">
                            NEW
                        </div>
                        
                        <div class="flex items-center mb-6">
                            <div class="bg-yellow-400 p-3 rounded-lg mr-4">
                                <svg v-if="service.icon === 'building-office'" class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                                <svg v-else-if="service.icon === 'scale'" class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16l3-3m-3 3l-3-3"/>
                                </svg>
                                <svg v-else-if="service.icon === 'shield-check'" class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                                <svg v-else-if="service.icon === 'heart'" class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                </svg>
                                <svg v-else-if="service.icon === 'document-text'" class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <svg v-else-if="service.icon === 'calculator'" class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-white mb-1">{{ service.name }}</h3>
                                <span class="text-yellow-400 font-semibold">{{ service.price }}</span>
                            </div>
                        </div>

                        <p class="text-gray-300 mb-6">{{ service.description }}</p>

                        <ul class="space-y-3 mb-8 flex-grow">
                            <li v-for="feature in service.features" :key="feature" class="flex items-center">
                                <svg class="h-5 w-5 text-green-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                <span class="text-gray-200">{{ feature }}</span>
                            </li>
                        </ul>

                        <Link :href="route(service.route)" class="w-full bg-yellow-400 hover:bg-yellow-500 text-black py-3 px-6 rounded-lg font-semibold transition-colors duration-300 text-center block mt-auto">
                            {{ __('marketing.learn_more_about', { name: service.name }) }}
                        </Link>
                    </div>
                </div>
            </div>
        </section>

        <!-- Why Choose Us Section -->
        <section class="py-20" style="background-color: #0b1e33;">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-white mb-4">
                        {{ __('marketing.why_choose_us') }}
                    </h2>
                    <p class="text-base sm:text-xl text-gray-300">
                        {{ __('marketing.why_choose_us_subtitle') }}
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="text-center">
                        <div class="w-28 h-28 mx-auto mb-6">
                            <img src="/images/1.png" alt="Lightning Fast" class="w-full h-full object-contain" />
                        </div>
                        <h3 class="text-xl font-bold text-white mb-4">{{ __('marketing.lightning_fast') }}</h3>
                        <p class="text-gray-300">{{ __('marketing.lightning_fast_desc') }}</p>
                    </div>

                    <div class="text-center">
                        <div class="w-28 h-28 mx-auto mb-6">
                            <img src="/images/2.png" alt="Transparent Pricing" class="w-full h-full object-contain" />
                        </div>
                        <h3 class="text-xl font-bold text-white mb-4">{{ __('marketing.transparent_pricing') }}</h3>
                        <p class="text-gray-300">{{ __('marketing.transparent_pricing_desc') }}</p>
                    </div>

                    <div class="text-center">
                        <div class="w-28 h-28 mx-auto mb-6">
                            <img src="/images/3.png" alt="Expert Support" class="w-full h-full object-contain" />
                        </div>
                        <h3 class="text-xl font-bold text-white mb-4">{{ __('marketing.expert_support') }}</h3>
                        <p class="text-gray-300">{{ __('marketing.expert_support_desc') }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Testimonials Section -->
        <section class="py-20" style="background-color: #0b1e33;">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-white mb-4">
                        {{ __('marketing.what_clients_say') }}
                    </h2>
                    <p class="text-base sm:text-xl text-gray-300">
                        {{ __('marketing.what_clients_say_subtitle') }}
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div v-for="testimonial in testimonials" :key="testimonial.name" class="flex flex-col bg-transparent bg-opacity-10 backdrop-blur-sm rounded-xl p-8 border border-gray-600">
                        <div class="flex mb-4">
                            <svg v-for="i in testimonial.rating" :key="i" class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                        </div>
                        <p class="text-gray-300 mb-6 italic flex-grow">"{{ testimonial.content }}"</p>
                        <div class="flex items-center gap-4 mt-auto">
                            <img 
                                v-if="testimonial.name === 'Sarah Johnson'" 
                                src="/sarah-johnson.png" 
                                alt="Sarah Johnson" 
                                class="w-14 h-14 rounded-full object-cover flex-shrink-0 border-2 border-yellow-400"
                            />
                            <img 
                                v-else-if="testimonial.name === 'Michael Chen'" 
                                src="/michael-chen.png" 
                                alt="Michael Chen" 
                                class="w-14 h-14 rounded-full object-cover flex-shrink-0 border-2 border-yellow-400"
                            />
                            <img 
                                v-else-if="testimonial.name === 'Jessica Rodriguez'" 
                                src="/jessica-rodriguez.png" 
                                alt="Jessica Rodriguez" 
                                class="w-14 h-14 rounded-full object-cover flex-shrink-0 border-2 border-yellow-400"
                            />
                            <div class="flex-1">
                                <div class="font-semibold text-white text-base">{{ testimonial.name }}</div>
                                <div class="text-gray-400 text-sm">{{ testimonial.company }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <p class="text-center text-gray-500 text-xs mt-6">* Testimonials are representative client experiences. Results may vary. Individual outcomes depend on business type, state, and other factors.</p>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="py-20" style="background-color: #0b1e33;">
            <div class="max-w-4xl mx-auto text-center px-4 sm:px-6 lg:px-8">
                <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-white mb-6">
                    {{ __('marketing.ready_to_start') }}
                </h2>
                <p class="text-base sm:text-xl text-gray-300 mb-8">
                    {{ __('marketing.ready_to_start_subtitle') }}
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <Link :href="route('register')" class="bg-yellow-400 hover:bg-yellow-500 text-white px-8 py-4 rounded-lg text-lg font-semibold transition-all duration-300 shadow-lg hover:shadow-xl">
                        {{ __('marketing.get_started_today') }}
                    </Link>
                    <Link :href="route('marketing.contact')" class="border-2 border-white text-white hover:bg-transparent hover:text-white px-8 py-4 rounded-lg text-lg font-semibold transition-all duration-300">
                        {{ __('marketing.contact_us') }}
                    </Link>
                </div>
                <!-- Social Share -->
                <div class="mt-8 flex items-center justify-center gap-4">
                    <span class="text-sm text-gray-500">Share:</span>
                    <a href="https://twitter.com/intent/tweet?url=https://corpius.net&text=Form+your+LLC%2C+C-Corp%2C+S-Corp+or+Nonprofit+with+CORPIUS"
                       target="_blank" rel="noopener noreferrer"
                       class="text-gray-400 hover:text-yellow-400 transition-colors"
                       aria-label="Share CORPIUS on X (Twitter)">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.748l7.73-8.835L1.254 2.25H8.08l4.253 5.622 5.911-5.622Zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                    </a>
                    <a href="https://www.facebook.com/sharer/sharer.php?u=https://corpius.net"
                       target="_blank" rel="noopener noreferrer"
                       class="text-gray-400 hover:text-yellow-400 transition-colors"
                       aria-label="Share CORPIUS on Facebook">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                    </a>
                    <a href="https://www.linkedin.com/shareArticle?mini=true&url=https://corpius.net&title=CORPIUS+Business+Formation+Services"
                       target="_blank" rel="noopener noreferrer"
                       class="text-gray-400 hover:text-yellow-400 transition-colors"
                       aria-label="Share CORPIUS on LinkedIn">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                    </a>
                </div>
            </div>
        </section>
    </MarketingLayout>
</template>

<style scoped>
.bg-grid-pattern {
    background-image: 
        linear-gradient(rgba(255, 255, 255, 0.1) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255, 255, 255, 0.1) 1px, transparent 1px);
    background-size: 20px 20px;
}

@keyframes gradient-text-shimmer {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

@keyframes cinematic-entrance {
    0% { opacity: 0; transform: translateX(-50px); }
    100% { opacity: 1; transform: translateX(0); }
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

.cinematic-entrance {
    animation: cinematic-entrance 1.2s ease-out;
}

.cinematic-entrance > * {
    animation: cinematic-entrance 1.5s ease-out;
}
</style>