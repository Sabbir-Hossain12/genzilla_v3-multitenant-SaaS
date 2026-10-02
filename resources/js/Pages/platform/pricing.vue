<script setup>
import { ref } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import PlatformLayout from '@/Layouts/PlatformLayout.vue'
import PlatformCheck from '@/Components/platform/PlatformCheck.vue'
import PlatformGlyph from '@/Components/platform/PlatformGlyph.vue'
import PlatformToggle from '@/Components/platform/PlatformToggle.vue'
import PlatformCtaBand from '@/Components/platform/PlatformCtaBand.vue'
import {
    pricingPlans,
    pricingComparison,
    pricingFaqs,
    feeCalloutShapes,
} from '@/data/platform'

/** false = monthly, true = yearly (20% off). */
const yearly = ref(false)
</script>

<template>
    <Head title="Pricing — Simple, transparent pricing" />

    <PlatformLayout>
        <!-- ════════════════════════════════════════
             PRICING HEADER
        ════════════════════════════════════════ -->
        <section class="grad-hero py-14 sm:py-20 text-center">
            <div class="max-w-3xl mx-auto px-4 sm:px-6">
                <h1 class="text-[32px] sm:text-5xl font-extrabold tracking-tight text-ink">
                    Simple, transparent pricing
                </h1>
                <p class="text-gray-500 text-[15px] sm:text-lg mt-4">
                    Start free for 14 days. No setup fees, no hidden costs — upgrade, downgrade,
                    or cancel anytime.
                </p>

                <div class="flex items-center justify-center gap-3 mt-8">
                    <span
                        class="text-[14px] font-semibold"
                        :class="yearly ? 'text-gray-400' : 'text-ink'"
                    >
                        Monthly
                    </span>
                    <PlatformToggle v-model="yearly" />
                    <span
                        class="text-[14px] font-medium"
                        :class="yearly ? 'text-ink' : 'text-gray-400'"
                    >
                        Yearly
                    </span>
                    <span class="bg-emerald-50 text-accentdark text-[11.5px] font-bold px-2.5 py-1 rounded-full">
                        Save 20%
                    </span>
                </div>
            </div>
        </section>

        <!-- ════════════════════════════════════════
             PRICING CARDS
        ════════════════════════════════════════ -->
        <section class="pb-16 sm:pb-20 -mt-4">
            <div class="max-w-6xl mx-auto px-4 sm:px-6">
                <div class="grid lg:grid-cols-3 gap-6 max-w-5xl mx-auto items-start">
                    <div
                        v-for="plan in pricingPlans"
                        :key="plan.name"
                        class="rounded-2xl p-7 relative"
                        :class="
                            plan.featured
                                ? 'bg-ink border border-ink lg:-mt-4 shadow-2xl'
                                : 'bg-white border border-gray-200'
                        "
                    >
                        <span
                            v-if="plan.featured"
                            class="absolute -top-3.5 left-1/2 -translate-x-1/2 bg-accent text-white text-[11.5px] font-bold px-3.5 py-1.5 rounded-full whitespace-nowrap"
                        >
                            MOST POPULAR
                        </span>

                        <p
                            class="text-[15px] font-bold mb-1"
                            :class="plan.featured ? 'text-gray-300' : 'text-gray-500'"
                        >
                            {{ plan.name }}
                        </p>
                        <p class="text-[13px] text-gray-400 mb-5">{{ plan.tagline }}</p>

                        <p :class="plan.featured ? 'text-white' : 'text-ink'">
                            <span class="text-4xl font-extrabold">
                                {{ yearly ? plan.yearly : plan.monthly }}
                            </span>
                            <span class="text-[14px] font-medium text-gray-400">/mo</span>
                        </p>
                        <p class="text-[12.5px] text-gray-400 mt-1 mb-6">{{ plan.fee }}</p>

                        <Link
                            v-if="plan.ctaTo"
                            :href="plan.ctaTo"
                            class="block text-center font-semibold text-[14px] py-3 rounded-xl transition-colors mb-6"
                            :class="
                                plan.featured
                                    ? 'bg-white text-ink font-bold hover:bg-gray-100'
                                    : 'border border-gray-300 text-ink hover:bg-gray-50'
                            "
                        >
                            {{ plan.ctaLabel }}
                        </Link>
                        <span
                            v-else
                            class="block text-center border border-gray-300 text-gray-400 font-semibold text-[14px] py-3 rounded-xl mb-6 cursor-default"
                            :title="`${plan.ctaLabel} — coming soon`"
                        >
                            {{ plan.ctaLabel }}
                        </span>

                        <ul
                            class="space-y-3 text-[13.5px]"
                            :class="plan.featured ? 'text-gray-300' : 'text-gray-600'"
                        >
                            <li
                                v-for="feature in plan.features"
                                :key="feature"
                                class="flex items-start gap-2.5"
                            >
                                <PlatformCheck />
                                {{ feature }}
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <!-- ════════════════════════════════════════
             TRANSACTION FEE CALLOUT
        ════════════════════════════════════════ -->
        <section class="pb-14">
            <div class="max-w-4xl mx-auto px-4 sm:px-6">
                <div
                    class="bg-primarylt border border-primary/10 rounded-2xl p-6 sm:p-7 flex flex-col sm:flex-row items-start gap-4"
                >
                    <div
                        class="w-11 h-11 rounded-xl bg-white flex items-center justify-center shrink-0"
                    >
                        <PlatformGlyph :shapes="feeCalloutShapes" :size="20" class="text-primary" />
                    </div>
                    <div>
                        <h3 class="text-[15.5px] font-bold text-ink mb-1.5">
                            How transaction fees work
                        </h3>
                        <p class="text-[13.5px] text-gray-600 leading-relaxed">
                            Every order has two components: a
                            <strong>card processing fee</strong> of 2.9% + $0.30 (charged by our
                            payment partners, same on every plan), and a
                            <strong>platform fee</strong> that decreases as you upgrade — 2.0% on
                            Basic, 1.0% on Pro, and negotiable on Enterprise. Digital products are
                            billed identically to physical products — there's no separate fee
                            structure for downloads, licenses, or subscriptions.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ════════════════════════════════════════
             COMPARISON TABLE
        ════════════════════════════════════════ -->
        <section class="pb-16 sm:pb-20">
            <div class="max-w-5xl mx-auto px-4 sm:px-6">
                <h2
                    class="text-2xl sm:text-[30px] font-extrabold tracking-tight text-ink text-center mb-10"
                >
                    Compare all features
                </h2>
                <div class="overflow-x-auto rounded-2xl border border-gray-200">
                    <table class="w-full text-left border-collapse min-w-[640px]">
                        <thead>
                            <tr class="bg-gray-50">
                                <th
                                    class="text-[13px] font-bold text-gray-500 uppercase tracking-wide px-5 py-4"
                                >
                                    Feature
                                </th>
                                <th
                                    v-for="plan in pricingPlans"
                                    :key="plan.name"
                                    class="text-[14px] font-bold px-5 py-4 text-center"
                                    :class="plan.featured ? 'text-primary' : 'text-ink'"
                                >
                                    {{ plan.name }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="text-[13.5px] text-gray-600">
                            <tr
                                v-for="row in pricingComparison"
                                :key="row.feature"
                                class="border-t border-gray-100"
                                :class="row.striped ? 'bg-gray-50/50' : ''"
                            >
                                <td class="px-5 py-3.5 font-medium text-gray-700">{{ row.feature }}</td>
                                <td
                                    v-for="key in ['basic', 'pro', 'enterprise']"
                                    :key="key"
                                    class="px-5 py-3.5 text-center"
                                >
                                    <span v-if="row[key] === true"><PlatformCheck /></span>
                                    <span v-else-if="row[key] === false" class="text-gray-300">—</span>
                                    <span
                                        v-else
                                        :class="key === 'pro' && row.emphasize ? 'font-semibold text-primary' : ''"
                                    >
                                        {{ row[key] }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- ════════════════════════════════════════
             PRICING FAQ
        ════════════════════════════════════════ -->
        <section class="pb-16 sm:pb-20">
            <div class="max-w-3xl mx-auto px-4 sm:px-6">
                <h2
                    class="text-2xl sm:text-[30px] font-extrabold tracking-tight text-ink text-center mb-8"
                >
                    Pricing questions
                </h2>
                <div class="space-y-2.5">
                    <details
                        v-for="faq in pricingFaqs"
                        :key="faq.question"
                        class="acc-item bg-white border border-gray-200 rounded-xl px-5"
                    >
                        <summary class="flex items-center justify-between gap-3 py-4 cursor-pointer">
                            <span class="text-[14.5px] font-semibold text-ink">
                                {{ faq.question }}
                            </span>
                            <svg
                                class="acc-chevron text-gray-400 shrink-0"
                                width="16"
                                height="16"
                                viewBox="0 0 24 24"
                                fill="none"
                            >
                                <path
                                    d="M6 9l6 6 6-6"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                        </summary>
                        <p class="text-[13.5px] text-gray-500 leading-relaxed pb-4">
                            {{ faq.answer }}
                        </p>
                    </details>
                </div>
            </div>
        </section>

        <!-- ════════════════════════════════════════
             FINAL CTA
        ════════════════════════════════════════ -->
        <PlatformCtaBand
            title="Still comparing options?"
            body="Talk to our team about which plan fits your business — no pressure, no sales script."
            cta-label="Talk to Sales"
        />
    </PlatformLayout>
</template>