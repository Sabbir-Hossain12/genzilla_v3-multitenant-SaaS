<script setup>
import { Link } from '@inertiajs/vue3'
import PlatformLogo from '@/Components/platform/PlatformLogo.vue'
import { navLinks } from '@/data/platform'
import { navOpen, closeMobileNav } from '@/composables/platform/usePlatformUi'
</script>

<template>
    <div id="mnav-overlay" :class="{ open: navOpen }" @click.self="closeMobileNav">
        <div id="mnav-panel">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <PlatformLogo variant="drawer" />
                <button
                    type="button"
                    class="w-9 h-9 rounded-full flex items-center justify-center text-gray-400 hover:bg-gray-100"
                    aria-label="Close menu"
                    @click="closeMobileNav"
                >
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                        <path
                            d="M18 6L6 18M6 6l12 12"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                        />
                    </svg>
                </button>
            </div>

            <nav class="flex-1 overflow-y-auto p-5 space-y-1">
                <template v-for="link in navLinks" :key="link.label">
                    <Link
                        v-if="link.to"
                        :href="link.to"
                        class="block px-3 py-3 rounded-xl text-[15px] font-medium text-gray-700 hover:bg-gray-50"
                        @click="closeMobileNav"
                    >
                        {{ link.label }}
                    </Link>
                    <span
                        v-else
                        class="block px-3 py-3 rounded-xl text-[15px] font-medium text-gray-300 cursor-default"
                        :title="`${link.label} — coming soon`"
                    >
                        {{ link.label }}
                    </span>
                </template>
            </nav>

            <div class="p-5 border-t border-gray-100 space-y-2.5">
                <Link
                    :href="route('login')"
                    class="block text-center border border-gray-200 text-gray-700 font-semibold text-[14.5px] py-3 rounded-xl"
                    @click="closeMobileNav"
                >
                    Log in
                </Link>
                <Link
                    :href="route('register')"
                    class="block text-center bg-primary hover:bg-primarydark text-white font-semibold text-[14.5px] py-3 rounded-xl"
                    @click="closeMobileNav"
                >
                    Start Free Trial
                </Link>
            </div>
        </div>
    </div>
</template>
