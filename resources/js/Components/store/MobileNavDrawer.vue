<script setup>
import { categories, moreLinks } from '@/data/store'
import { navOpen, closeMobileNav, openLoginModal } from '@/composables/store/useStoreUi'

function goToLogin() {
    closeMobileNav()
    openLoginModal()
}

const quickLinks = [
    { icon: '📋', label: 'Upload Rx' },
    { icon: '📦', label: 'Track Order' },
    { icon: '🎧', label: 'Help' },
]
</script>

<template>
    <div id="mobilenav-overlay" :class="{ open: navOpen }" @click.self="closeMobileNav">
        <aside id="mobilenav-drawer">
            <!-- Header -->
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 shrink-0">
                <a href="#" class="flex items-center gap-2">
                    <div class="w-9 h-9 rounded-lg bg-primary flex items-center justify-center">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                            <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <span class="text-xl font-extrabold text-primary">Medi<span class="text-accent">Mart</span></span>
                </a>
                <button
                    type="button"
                    aria-label="Close menu"
                    class="w-9 h-9 rounded-full flex items-center justify-center text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-colors"
                    @click="closeMobileNav"
                >
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                </button>
            </div>

            <!-- Login prompt -->
            <div class="px-5 py-4 border-b border-gray-100 shrink-0">
                <button
                    type="button"
                    class="w-full flex items-center justify-between bg-primarylt rounded-xl px-4 py-3.5 hover:bg-primary/15 transition-colors"
                    @click="goToLogin"
                >
                    <span class="flex items-center gap-3">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" class="text-primary"><circle cx="12" cy="8" r="4" stroke="currentColor" stroke-width="2"/><path d="M4 20c0-4 3.58-7 8-7s8 3 8 7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                        <span class="text-[14px] font-semibold text-primary">Login / Sign Up</span>
                    </span>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" class="text-primary"><path d="M9 18l6-6-6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                </button>
            </div>

            <!-- Scrollable content -->
            <div class="flex-1 overflow-y-auto">
                <!-- Quick links -->
                <div class="px-5 py-3 border-b border-gray-100 grid grid-cols-3 gap-2">
                    <a
                        v-for="link in quickLinks"
                        :key="link.label"
                        href="#"
                        class="flex flex-col items-center gap-1.5 py-2 rounded-xl hover:bg-gray-50 transition-colors"
                    >
                        <span class="text-2xl">{{ link.icon }}</span>
                        <span class="text-[11.5px] font-medium text-gray-600 text-center leading-tight">{{ link.label }}</span>
                    </a>
                </div>

                <!-- Category accordion -->
                <div class="px-2 py-2">
                    <p class="px-3 pt-2 pb-1 text-[12px] font-bold text-gray-400 uppercase tracking-wider">Shop by Category</p>

                    <details v-for="cat in categories" :key="cat.name" class="group border-b border-gray-50">
                        <summary class="flex items-center gap-3 px-3 py-3.5 cursor-pointer">
                            <span class="text-lg">{{ cat.icon }}</span>
                            <span class="flex-1 text-[14.5px] font-medium text-gray-800">{{ cat.name }}</span>
                            <svg class="accordion-chevron text-gray-400" width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </summary>
                        <div class="pb-3 pl-11 pr-3 space-y-0.5">
                            <a
                                v-for="child in cat.children"
                                :key="child"
                                href="#"
                                class="block py-2 text-[14px] text-gray-600 hover:text-primary"
                            >{{ child }}</a>
                        </div>
                    </details>
                </div>

                <!-- More links -->
                <div class="px-2 py-2 border-t border-gray-100">
                    <p class="px-3 pt-2 pb-1 text-[12px] font-bold text-gray-400 uppercase tracking-wider">More</p>
                    <a
                        v-for="link in moreLinks"
                        :key="link"
                        href="#"
                        class="flex items-center px-3 py-3 text-[14.5px] text-gray-700 hover:text-primary"
                    >{{ link }}</a>
                </div>
            </div>

            <!-- Footer -->
            <div class="px-5 py-4 border-t border-gray-100 shrink-0">
                <p class="text-[12.5px] text-gray-400 flex items-center gap-2">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72c.127.96.362 1.903.7 2.81a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.338 1.85.573 2.81.7A2 2 0 0122 16.92z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    09610-016778
                </p>
            </div>
        </aside>
    </div>
</template>
