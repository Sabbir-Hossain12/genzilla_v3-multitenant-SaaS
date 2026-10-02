<script setup>
import { Link } from '@inertiajs/vue3'
import PlatformLogo from '@/Components/platform/PlatformLogo.vue'
import { navLinks } from '@/data/platform'
import { openMobileNav } from '@/composables/platform/usePlatformUi'
</script>

<template>
    <header class="sticky top-0 z-40 bg-white/80 backdrop-blur-md border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 h-16 sm:h-18 flex items-center justify-between">
            <PlatformLogo />

            <nav class="hidden lg:flex items-center gap-8">
                <template v-for="link in navLinks" :key="link.label">
                    <Link
                        v-if="link.to"
                        :href="link.to"
                        class="text-[14.5px] font-medium text-gray-600 hover:text-ink transition-colors"
                    >
                        {{ link.label }}
                    </Link>
                    <span
                        v-else
                        class="text-[14.5px] font-medium text-gray-300 cursor-default"
                        :title="`${link.label} — coming soon`"
                    >
                        {{ link.label }}
                    </span>
                </template>
            </nav>

            <div class="hidden lg:flex items-center gap-3">
                <Link
                    :href="route('login')"
                    class="text-[14.5px] font-semibold text-gray-700 hover:text-ink transition-colors px-2"
                >
                    Log in
                </Link>
                <Link
                    :href="route('register')"
                    class="bg-primary hover:bg-primarydark text-white text-[14.5px] font-semibold px-5 py-2.5 rounded-xl transition-colors"
                >
                    Start Free Trial
                </Link>
            </div>

            <button
                type="button"
                class="lg:hidden w-9 h-9 flex items-center justify-center text-gray-700"
                aria-label="Open menu"
                @click="openMobileNav"
            >
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <path
                        d="M3 6h18M3 12h18M3 18h18"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                    />
                </svg>
            </button>
        </div>
    </header>
</template>
