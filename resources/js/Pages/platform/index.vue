<script setup>
import { Head, Link } from '@inertiajs/vue3'
import PlatformLayout from '@/Layouts/PlatformLayout.vue'
import {
    features,
    hero,
    plans,
    stats,
    steps,
    testimonials,
    trustLogos,
} from '@/data/platform'

const chartBars = [
    { height: '40%', class: 'bg-primary/30' },
    { height: '65%', class: 'bg-primary/50' },
    { height: '45%', class: 'bg-primary/30' },
    { height: '80%', class: 'bg-primary' },
    { height: '55%', class: 'bg-primary/40' },
    { height: '90%', class: 'bg-accent' },
]
</script>

<template>
    <Head title="Shopwave — Launch Your Online Store in Minutes" />

    <PlatformLayout>
        <!-- ════════════════════════════════════════
             HERO
             ════════════════════════════════════════ -->
        <section class="grad-hero relative overflow-hidden">
            <div
                class="max-w-7xl mx-auto px-4 sm:px-6 pt-14 sm:pt-20 pb-16 sm:pb-24 text-center relative z-10"
            >
                <div
                    class="inline-flex items-center gap-2 bg-white border border-gray-200 rounded-full pl-1.5 pr-4 py-1.5 text-[13px] font-medium text-gray-600 shadow-sm mb-6 hover:border-primary transition-colors"
                >
                    <span class="bg-primary text-white text-[11px] font-bold px-2 py-0.5 rounded-full">
                        NEW
                    </span>
                    {{ hero.badge }}
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none">
                        <path
                            d="M9 18l6-6-6-6"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                    </svg>
                </div>

                <h1
                    class="text-[34px] leading-[1.15] sm:text-5xl lg:text-6xl font-extrabold tracking-tight text-ink max-w-4xl mx-auto"
                >
                    {{ hero.titleLead }}
                    <span class="text-primary">{{ hero.titleHighlight }}</span>{{ hero.titleTail }}
                </h1>
                <p class="text-[16px] sm:text-lg text-gray-500 max-w-2xl mx-auto mt-5">
                    {{ hero.subtitle }}
                </p>

                <div class="flex flex-col sm:flex-row items-center justify-center gap-3 mt-8">
                    <Link
                        :href="route('register')"
                        class="w-full sm:w-auto bg-primary hover:bg-primarydark text-white font-bold text-[15px] px-7 py-3.5 rounded-xl transition-colors shadow-lg shadow-primary/20"
                    >
                        Start Free 14-Day Trial
                    </Link>
                    <a
                        href="#demo"
                        class="w-full sm:w-auto flex items-center justify-center gap-2 border border-gray-200 bg-white text-ink font-semibold text-[15px] px-7 py-3.5 rounded-xl hover:border-gray-300 transition-colors"
                    >
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
<circle
                    cx="12"
                    cy="12"
                    r="10"
                    stroke="currentColor"
                    stroke-width="2"
                />
                <path d="M10 8l6 4-6 4V8z" fill="currentColor" />
                        </svg>
                        Watch Demo
                    </a>
                </div>
                <p class="text-[13px] text-gray-400 mt-4">{{ hero.note }}</p>

                <!-- Hero product mockup -->
                <div class="mt-14 max-w-4xl mx-auto">
                    <div
                        class="bg-white rounded-2xl border border-gray-200 shadow-2xl shadow-gray-300/40 overflow-hidden"
                    >
                        <div
                            class="flex items-center gap-1.5 px-4 py-3 border-b border-gray-100 bg-gray-50"
                        >
                            <span class="w-2.5 h-2.5 rounded-full bg-red-300"></span>
                            <span class="w-2.5 h-2.5 rounded-full bg-yellow-300"></span>
                            <span class="w-2.5 h-2.5 rounded-full bg-green-300"></span>
                            <span
                                class="ml-3 text-[12px] text-gray-400 bg-white border border-gray-200 rounded-md px-3 py-1"
                            >
                                {{ hero.url }}
                            </span>
                        </div>
                        <div class="grid grid-cols-4 min-h-[280px] sm:min-h-[360px]">
                            <div
                                class="hidden sm:block col-span-1 bg-gray-50 border-r border-gray-100 p-4 space-y-3"
                            >
                                <div class="h-8 rounded-lg bg-primarylt flex items-center px-3">
                                    <span class="w-3 h-3 rounded bg-primary"></span>
                                </div>
                                <div class="h-3 w-3/4 rounded bg-gray-200"></div>
                                <div class="h-3 w-2/3 rounded bg-gray-200"></div>
                                <div class="h-3 w-1/2 rounded bg-gray-200"></div>
                                <div class="h-3 w-3/4 rounded bg-gray-200"></div>
                            </div>
                            <div class="col-span-4 sm:col-span-3 p-5 sm:p-6">
                                <div class="flex items-center justify-between mb-5">
                                    <div class="h-4 w-28 rounded bg-gray-200"></div>
                                    <div class="h-8 w-24 rounded-lg bg-primary/90"></div>
                                </div>
                                <div class="grid grid-cols-3 gap-3 mb-5">
                                    <div class="h-20 rounded-xl bg-primarylt border border-primary/10 p-3">
                                        <div class="h-3 w-10 bg-primary/30 rounded mb-2"></div>
                                        <div class="h-5 w-16 bg-primary rounded"></div>
                                    </div>
                                    <div
                                        class="h-20 rounded-xl bg-emerald-50 border border-emerald-100 p-3"
                                    >
                                        <div class="h-3 w-10 bg-emerald-300 rounded mb-2"></div>
                                        <div class="h-5 w-14 bg-accent rounded"></div>
                                    </div>
                                    <div class="h-20 rounded-xl bg-amber-50 border border-amber-100 p-3">
                                        <div class="h-3 w-10 bg-amber-300 rounded mb-2"></div>
                                        <div class="h-5 w-12 bg-amber-400 rounded"></div>
                                    </div>
                                </div>
                                <div
                                    class="h-32 rounded-xl bg-gray-50 border border-gray-100 flex items-end gap-2 p-4"
                                >
                                    <div
                                        v-for="(bar, i) in chartBars"
                                        :key="i"
                                        class="w-full rounded-t"
                                        :class="bar.class"
                                        :style="{ height: bar.height }"
                                    ></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ════════════════════════════════════════
             TRUST BAR
             ════════════════════════════════════════ -->
        <section class="py-10 border-y border-gray-100 bg-white">
            <div class="max-w-6xl mx-auto px-4 sm:px-6">
                <p
                    class="text-center text-[12.5px] font-semibold text-gray-400 uppercase tracking-wider mb-6"
                >
                    Trusted by 10,000+ growing merchants
                </p>
                <div
                    class="flex flex-wrap items-center justify-center gap-x-10 gap-y-4 text-gray-300 font-extrabold text-xl sm:text-2xl tracking-tight"
                >
                    <span v-for="logo in trustLogos" :key="logo">{{ logo }}</span>
                </div>
            </div>
        </section>

        <!-- ════════════════════════════════════════
             STATS
             ════════════════════════════════════════ -->
        <section class="bg-ink py-14">
            <div
                class="max-w-6xl mx-auto px-4 sm:px-6 grid grid-cols-2 lg:grid-cols-4 gap-8 text-center"
            >
                <div v-for="stat in stats" :key="stat.label">
                    <p class="text-white text-3xl sm:text-4xl font-extrabold">{{ stat.value }}</p>
                    <p class="text-gray-400 text-[13.5px] mt-1">{{ stat.label }}</p>
                </div>
            </div>
        </section>

        <!-- ════════════════════════════════════════
             FEATURES GRID
             ════════════════════════════════════════ -->
        <section id="features" class="py-16 sm:py-20 bg-white scroll-mt-16">
            <div class="max-w-6xl mx-auto px-4 sm:px-6">
                <div class="text-center max-w-2xl mx-auto mb-12">
                    <p class="text-[13px] font-bold text-primary uppercase tracking-wider mb-2">
                        Everything included
                    </p>
                    <h2 class="text-2xl sm:text-[34px] font-extrabold tracking-tight text-ink">
                        One platform, every tool you need
                    </h2>
                    <p class="text-gray-500 text-[15px] mt-3">
                        No plugins to install, no third parties to stitch together. It just works.
                    </p>
                </div>

                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
                    <div
                        v-for="feature in features"
                        :key="feature.title"
                        class="border border-gray-100 rounded-2xl p-6 hover:shadow-lg hover:shadow-gray-100 transition-shadow"
                    >
                        <div
                            class="w-11 h-11 rounded-xl flex items-center justify-center mb-4"
                            :class="feature.iconClass"
                        >
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                                <component
                                    :is="shape.tag"
                                    v-for="(shape, i) in feature.shapes"
                                    :key="i"
                                    v-bind="shape.attrs"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                        </div>
                        <h3 class="text-[16px] font-bold text-ink mb-1.5">{{ feature.title }}</h3>
                        <p class="text-[13.5px] text-gray-500 leading-relaxed">{{ feature.body }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ════════════════════════════════════════
             HOW IT WORKS
             ════════════════════════════════════════ -->
        <section class="py-16 sm:py-20 bg-gray-50">
            <div class="max-w-5xl mx-auto px-4 sm:px-6">
                <div class="text-center max-w-2xl mx-auto mb-12">
                    <p class="text-[13px] font-bold text-primary uppercase tracking-wider mb-2">
                        Getting started
                    </p>
                    <h2 class="text-2xl sm:text-[34px] font-extrabold tracking-tight text-ink">
                        Live in three simple steps
                    </h2>
                </div>
                <div class="grid sm:grid-cols-3 gap-6 relative">
                    <div class="hidden sm:block absolute top-7 left-[16%] right-[16%] h-0.5 bg-gray-200"></div>
                    <div v-for="(step, i) in steps" :key="step.title" class="relative text-center">
                        <div
                            class="w-14 h-14 rounded-full bg-primary text-white flex items-center justify-center text-[18px] font-extrabold mx-auto mb-4 relative z-10"
                        >
                            {{ i + 1 }}
                        </div>
                        <h3 class="text-[16px] font-bold text-ink mb-1.5">{{ step.title }}</h3>
                        <p class="text-[13.5px] text-gray-500 max-w-[220px] mx-auto">{{ step.body }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ════════════════════════════════════════
             TESTIMONIALS
             ════════════════════════════════════════ -->
        <section class="py-16 sm:py-20 bg-white">
            <div class="max-w-6xl mx-auto px-4 sm:px-6">
                <div class="text-center max-w-2xl mx-auto mb-12">
                    <p class="text-[13px] font-bold text-primary uppercase tracking-wider mb-2">
                        Loved by merchants
                    </p>
                    <h2 class="text-2xl sm:text-[34px] font-extrabold tracking-tight text-ink">
                        Don't just take our word for it
                    </h2>
                </div>
                <div class="grid sm:grid-cols-3 gap-5">
                    <div
                        v-for="testimonial in testimonials"
                        :key="testimonial.name"
                        class="border border-gray-100 rounded-2xl p-6"
                    >
                        <div class="flex text-amber-400 text-[14px] mb-3">★★★★★</div>
                        <p class="text-[14px] text-gray-600 leading-relaxed mb-4">
                            "{{ testimonial.quote }}"
                        </p>
                        <div class="flex items-center gap-3">
                            <div
                                class="w-9 h-9 rounded-full flex items-center justify-center font-bold text-[13px]"
                                :class="testimonial.avatarClass"
                            >
                                {{ testimonial.initials }}
                            </div>
                            <div>
                                <p class="text-[13.5px] font-bold text-ink">{{ testimonial.name }}</p>
                                <p class="text-[12px] text-gray-400">{{ testimonial.role }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ════════════════════════════════════════
             PRICING TEASER
             ════════════════════════════════════════ -->
        <section id="pricing" class="py-16 sm:py-20 bg-gray-50 scroll-mt-16">
            <div class="max-w-6xl mx-auto px-4 sm:px-6">
                <div class="text-center max-w-2xl mx-auto mb-12">
                    <p class="text-[13px] font-bold text-primary uppercase tracking-wider mb-2">
                        Pricing
                    </p>
                    <h2 class="text-2xl sm:text-[34px] font-extrabold tracking-tight text-ink">
                        Plans that grow with you
                    </h2>
                </div>
                <div class="grid sm:grid-cols-3 gap-5 max-w-4xl mx-auto">
                    <div
                        v-for="plan in plans"
                        :key="plan.name"
                        class="rounded-2xl p-6 text-center relative"
                        :class="
                            plan.highlighted
                                ? 'bg-ink border border-ink scale-105 shadow-xl'
                                : 'bg-white border border-gray-200'
                        "
                    >
                        <span
                            v-if="plan.highlighted"
                            class="absolute -top-3 left-1/2 -translate-x-1/2 bg-accent text-white text-[11px] font-bold px-3 py-1 rounded-full"
                        >
                            MOST POPULAR
                        </span>
                        <p
                            class="text-[14px] font-bold"
                            :class="plan.highlighted ? 'text-gray-300' : 'text-gray-500'"
                        >
                            {{ plan.name }}
                        </p>
                        <p
                            class="text-3xl font-extrabold mt-2"
                            :class="plan.highlighted ? 'text-white' : 'text-ink'"
                        >
                            {{ plan.price }}<span
                                class="text-[14px] font-medium text-gray-400"
                            >{{ plan.suffix }}</span>
                        </p>
                        <p class="text-[12.5px] text-gray-400 mt-1">{{ plan.note }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ════════════════════════════════════════
             FINAL CTA
             ════════════════════════════════════════ -->
        <section class="bg-primary relative overflow-hidden">
            <div class="absolute -right-20 -top-20 w-80 h-80 rounded-full bg-white/10"></div>
            <div class="absolute -left-10 -bottom-20 w-60 h-60 rounded-full bg-white/10"></div>
            <div class="max-w-4xl mx-auto px-4 sm:px-6 py-16 sm:py-20 text-center relative z-10">
                <h2 class="text-2xl sm:text-[36px] font-extrabold tracking-tight text-white">
                    Ready to start selling?
                </h2>
                <p class="text-primarylt/90 text-[15px] sm:text-lg mt-3 max-w-xl mx-auto">
                    Join thousands of merchants running their store on Shopwave. Your first 14 days
                    are on us.
                </p>
                <div class="flex flex-col sm:flex-row items-center justify-center gap-3 mt-8">
                    <Link
                        :href="route('register')"
                        class="w-full sm:w-auto bg-white text-primary font-bold text-[15px] px-7 py-3.5 rounded-xl hover:bg-gray-50 transition-colors"
                    >
                        Start Free Trial
                    </Link>
                    <a
                        href="#pricing"
                        class="w-full sm:w-auto text-white font-semibold text-[15px] px-7 py-3.5 rounded-xl border border-white/30 hover:bg-white/10 transition-colors"
                    >
                        View Pricing
                    </a>
                </div>
            </div>
        </section>
    </PlatformLayout>
</template>
