<script setup>
import { computed } from 'vue'
import { Head } from '@inertiajs/vue3'
import PlatformLayout from '@/Layouts/PlatformLayout.vue'
import PlatformGlyph from '@/Components/platform/PlatformGlyph.vue'
import PlatformCheck from '@/Components/platform/PlatformCheck.vue'
import PlatformSectionHeading from '@/Components/platform/PlatformSectionHeading.vue'
import PlatformCtaBand from '@/Components/platform/PlatformCtaBand.vue'
import {
    useCases as fallbackUseCases,
    featureGrid as fallbackFeatureGrid,
    businessTypes,
    integrationLogos,
} from '@/data/platform'

/**
 * CMS use cases / features replace the copy; the static module still supplies
 * icon glyphs and colours, and everything falls back when the CMS is empty.
 */
const props = defineProps({
    useCases: { type: Array, default: () => [] },
    featureGrid: { type: Array, default: () => [] },
})

const useCasesView = computed(() => {
    if (!props.useCases.length) return fallbackUseCases

    return props.useCases.map((item, index) => {
        const base = fallbackUseCases[index % fallbackUseCases.length]
        return {
            iconClass: base.iconClass,
            shapes: base.shapes,
            title: item.title,
            body: item.body,
            badge: item.badge,
            badgeType: item.badgeType,
            bgColor: item.bgColor,
            points: [],
        }
    })
})

const featureGridView = computed(() => {
    const source = props.featureGrid.length ? props.featureGrid : fallbackFeatureGrid
    return source.map((item, index) => ({
        ...fallbackFeatureGrid[index % fallbackFeatureGrid.length],
        title: item.title,
        body: item.body,
    }))
})

function badgeClass(type) {
    return (
        {
            success: 'bg-emerald-50 text-accentdark',
            info: 'bg-blue-50 text-blue-600',
            warning: 'bg-amber-50 text-amber-600',
            danger: 'bg-rose-50 text-rose-600',
        }[type] || 'bg-primarylt text-primary'
    )
}
</script>

<template>
    <Head title="Features — Everything you need to sell online" />

    <PlatformLayout>
        <!-- ════════════════════════════════════════
             HEADER
        ════════════════════════════════════════ -->
        <section class="grad-hero py-14 sm:py-20 text-center">
            <div class="max-w-3xl mx-auto px-4 sm:px-6">
                <h1 class="text-[32px] sm:text-5xl font-extrabold tracking-tight text-ink">
                    Everything you need to sell online
                </h1>
                <p class="text-gray-500 text-[15px] sm:text-lg mt-4">
                    Whether you ship boxes or deliver downloads, Shopwave has the tools built in
                    — not bolted on.
                </p>
            </div>
        </section>

        <!-- ════════════════════════════════════════
             USE CASES: PHYSICAL vs DIGITAL
        ════════════════════════════════════════ -->
        <section id="use-cases" class="py-16 sm:py-20 scroll-mt-16">
            <div class="max-w-6xl mx-auto px-4 sm:px-6">
                <PlatformSectionHeading
                    eyebrow="Use cases"
                    title="Built for physical and digital goods alike"
                />

                <div class="grid lg:grid-cols-2 gap-6">
                    <div
                        v-for="useCase in useCasesView"
                        :key="useCase.title"
                        class="border border-gray-200 rounded-2xl p-7 sm:p-8"
                        :style="useCase.bgColor ? { backgroundColor: useCase.bgColor } : null"
                    >
                        <div
                            class="w-12 h-12 rounded-xl flex items-center justify-center mb-5"
                            :class="useCase.iconClass"
                        >
                            <PlatformGlyph :shapes="useCase.shapes" :size="22" />
                        </div>
                        <span
                            v-if="useCase.badge"
                            class="inline-block text-[11px] font-bold px-2 py-0.5 rounded-full mb-2"
                            :class="badgeClass(useCase.badgeType)"
                        >
                            {{ useCase.badge }}
                        </span>
                        <h3 class="text-[20px] font-extrabold text-ink mb-2">{{ useCase.title }}</h3>
                        <p class="text-[14px] text-gray-500 leading-relaxed mb-6">
                            {{ useCase.body }}
                        </p>
                        <ul
                            v-if="useCase.points && useCase.points.length"
                            class="space-y-3.5 text-[13.5px] text-gray-600"
                        >
                            <li
                                v-for="point in useCase.points"
                                :key="point.strong"
                                class="flex items-start gap-2.5"
                            >
                                <PlatformCheck
                                    :color-class="useCase.iconClass.split(' ')[1]"
                                />
                                <span
                                    ><strong class="text-ink">{{ point.strong }}</strong> —
                                    {{ point.text }}</span
                                >
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <!-- ════════════════════════════════════════
             FULL FEATURE GRID
        ════════════════════════════════════════ -->
        <section class="py-16 sm:py-20 bg-gray-50">
            <div class="max-w-6xl mx-auto px-4 sm:px-6">
                <PlatformSectionHeading
                    eyebrow="Full feature set"
                    title="Every tool, one dashboard"
                />

                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
                    <div
                        v-for="feature in featureGridView"
                        :key="feature.title"
                        class="bg-white border border-gray-100 rounded-2xl p-6"
                    >
                        <div
                            class="w-10 h-10 rounded-lg flex items-center justify-center mb-3.5"
                            :class="feature.iconClass"
                        >
                            <PlatformGlyph :shapes="feature.shapes" :size="18" />
                        </div>
                        <h3 class="text-[15px] font-bold text-ink mb-1">{{ feature.title }}</h3>
                        <p class="text-[13px] text-gray-500 leading-relaxed">{{ feature.body }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ════════════════════════════════════════
             CUSTOMER STORIES
        ════════════════════════════════════════ -->
        <section class="py-16 sm:py-20">
            <div class="max-w-6xl mx-auto px-4 sm:px-6">
                <PlatformSectionHeading
                    eyebrow="Who uses Shopwave"
                    title="Built for every kind of business"
                />

                <div class="grid sm:grid-cols-3 gap-5">
                    <div
                        v-for="business in businessTypes"
                        :key="business.title"
                        class="rounded-2xl overflow-hidden border border-gray-100"
                    >
                        <div
                            class="h-36 flex items-center justify-center text-5xl"
                            :class="business.coverClass"
                        >
                            {{ business.emoji }}
                        </div>
                        <div class="p-5">
                            <span
                                class="text-[11px] font-bold px-2 py-0.5 rounded-full"
                                :class="business.badgeClass"
                            >
                                {{ business.badge }}
                            </span>
                            <h3 class="text-[15.5px] font-bold text-ink mt-2 mb-1.5">
                                {{ business.title }}
                            </h3>
                            <p class="text-[13px] text-gray-500 leading-relaxed">
                                {{ business.body }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ════════════════════════════════════════
             INTEGRATIONS
        ════════════════════════════════════════ -->
        <section class="py-14 bg-gray-50 border-y border-gray-100">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 text-center">
                <p class="text-[12.5px] font-semibold text-gray-400 uppercase tracking-wider mb-6">
                    Works with the tools you already use
                </p>
                <div
                    class="flex flex-wrap items-center justify-center gap-x-10 gap-y-4 text-gray-300 font-extrabold text-lg sm:text-xl tracking-tight"
                >
                    <span
                        v-for="logo in integrationLogos"
                        :key="logo.name"
                        :class="logo.hideOnMobile ? 'hidden sm:inline' : ''"
                    >
                        {{ logo.name }}
                    </span>
                </div>
            </div>
        </section>

        <!-- ════════════════════════════════════════
             FINAL CTA
        ════════════════════════════════════════ -->
        <PlatformCtaBand
            tone="primary"
            title="See it in action"
            body="Start your free trial and explore every feature — no credit card, no commitment."
            cta-label="Start Free Trial"
            cta-to="/register"
        />
    </PlatformLayout>
</template>