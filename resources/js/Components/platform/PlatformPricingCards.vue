<script setup>
import { Link } from '@inertiajs/vue3'
import PlatformCheck from '@/Components/platform/PlatformCheck.vue'

/**
 * The pricing-page plan card grid, shared by the dedicated pricing page and the
 * landing page's pricing teaser so both stay pixel-identical.
 *
 * `yearly` is owned by the parent because the billing toggle lives in each
 * page's own section header.
 */
defineProps({
    plans: { type: Array, default: () => [] },
    yearly: { type: Boolean, default: false },
})
</script>

<template>
    <div class="grid lg:grid-cols-3 gap-6 max-w-5xl mx-auto items-start">
        <div
            v-for="plan in plans"
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
</template>
