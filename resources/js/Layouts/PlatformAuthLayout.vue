<script setup>
import { onMounted, onBeforeUnmount } from 'vue'
import PlatformLogo from '@/Components/platform/PlatformLogo.vue'
import { authTestimonial } from '@/data/platform'

/**
 * Split-screen chrome shared by the platform auth screens: a dark branded panel
 * on the left (hidden below lg) and the form slot on the right.
 */
defineProps({
    heading: { type: String, required: true },
    body: { type: String, required: true },
})

/**
 * Inertia does not re-render <body> on client-side navigation, and app.blade.php
 * hard-codes the storefront's colours there. Claiming them on mount keeps the
 * platform palette correct when arriving from /store.
 */
const BODY_CLASS = 'bg-white font-sans antialiased'

onMounted(() => {
    document.body.className = BODY_CLASS
})

onBeforeUnmount(() => {
    document.body.style.overflow = ''
})
</script>

<template>
    <div class="platform-scope bg-white text-ink min-h-screen grid lg:grid-cols-2">
        <!-- Left branded panel -->
        <div class="hidden lg:flex flex-col justify-between bg-ink relative overflow-hidden p-10 xl:p-14">
            <div class="absolute -right-24 -top-24 w-80 h-80 rounded-full bg-primary/20" />
            <div class="absolute -left-16 bottom-0 w-64 h-64 rounded-full bg-accent/10" />

            <PlatformLogo variant="footer" class="relative z-10" />

            <div class="relative z-10 max-w-md">
                <h2 class="text-white text-[28px] xl:text-[32px] font-extrabold leading-tight">
                    {{ heading }}
                </h2>
                <p class="text-gray-400 text-[15px] mt-3 leading-relaxed">{{ body }}</p>
            </div>

            <div class="relative z-10 bg-white/5 border border-white/10 rounded-2xl p-5 max-w-md">
                <div class="flex text-amber-400 text-[13px] mb-2.5">★★★★★</div>
                <p class="text-gray-300 text-[13.5px] leading-relaxed mb-4">
                    {{ authTestimonial.quote }}
                </p>
                <div class="flex items-center gap-3">
                    <div
                        class="w-9 h-9 rounded-full bg-primary/20 flex items-center justify-center text-primary font-bold text-[12.5px]"
                    >
                        {{ authTestimonial.initials }}
                    </div>
                    <div>
                        <p class="text-white text-[13px] font-bold">{{ authTestimonial.name }}</p>
                        <p class="text-gray-500 text-[11.5px]">{{ authTestimonial.role }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right form panel -->
        <div class="flex flex-col items-center justify-center p-6 sm:p-10">
            <div class="w-full max-w-sm">
                <!-- PlatformLogo is already a link to /, so just centre it. -->
                <div class="flex lg:hidden justify-center mb-8">
                    <PlatformLogo variant="drawer" />
                </div>

                <slot />
            </div>
        </div>
    </div>
</template>