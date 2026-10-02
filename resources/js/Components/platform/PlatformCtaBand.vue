<script setup>
import { Link } from '@inertiajs/vue3'

/**
 * Shared closing call-to-action band.
 *
 * `tone="primary"` is the indigo variant with a decorative blob, `tone="ink"`
 * is the dark variant. A null `ctaTo` renders an inert placeholder so we never
 * link to a page that does not exist yet.
 */
defineProps({
    tone: { type: String, default: 'ink' },
    title: { type: String, required: true },
    body: { type: String, required: true },
    ctaLabel: { type: String, required: true },
    ctaTo: { type: String, default: null },
})
</script>

<template>
    <section
        class="relative overflow-hidden"
        :class="tone === 'primary' ? 'bg-primary' : 'bg-ink'"
    >
        <div
            v-if="tone === 'primary'"
            class="absolute -right-16 -top-16 w-72 h-72 rounded-full bg-white/10"
        />
        <div
            class="max-w-3xl mx-auto px-4 sm:px-6 py-16 sm:py-20 text-center relative z-10"
        >
            <h2 class="text-2xl sm:text-[34px] font-extrabold tracking-tight text-white">
                {{ title }}
            </h2>
            <p
                class="text-[15px] mt-3 max-w-lg mx-auto"
                :class="tone === 'primary' ? 'text-primarylt/90' : 'text-gray-400'"
            >
                {{ body }}
            </p>
            <Link
                v-if="ctaTo"
                :href="ctaTo"
                class="inline-block mt-7 font-bold text-[15px] px-7 py-3.5 rounded-xl transition-colors"
                :class="
                    tone === 'primary'
                        ? 'bg-white text-primary hover:bg-gray-50'
                        : 'bg-white text-ink hover:bg-gray-100'
                "
            >
                {{ ctaLabel }}
            </Link>
            <span
                v-else
                class="inline-block mt-7 font-bold text-[15px] px-7 py-3.5 rounded-xl bg-white/10 text-white/50 cursor-default"
                :title="`${ctaLabel} — coming soon`"
            >
                {{ ctaLabel }}
            </span>
        </div>
    </section>
</template>