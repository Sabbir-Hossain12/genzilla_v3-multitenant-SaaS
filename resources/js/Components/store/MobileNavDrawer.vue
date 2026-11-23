<script setup>
import { computed } from 'vue'
import { usePage, router, Link } from '@inertiajs/vue3'
import axios from 'axios'
import { categories as fallbackCategories, moreLinks } from '@/data/store'
import { navOpen, closeMobileNav, openLoginModal } from '@/composables/store/useStoreUi'
import { resetCartOnLogout } from '@/composables/store/useCart'

const page = usePage()
const store = computed(() => page.props.store || {})
const storeName = computed(() => store.value.name || 'MediMart')
const storeLogo = computed(() => store.value.logo_url)
const storePhone = computed(() => store.value.phone || '09610-016778')
const storeEmail = computed(() => store.value.email || 'support@medimart.com')
const customer = computed(() => page.props.auth?.customer)

const categories = computed(() => {
    return page.props.categories && page.props.categories.length > 0
        ? page.props.categories
        : fallbackCategories
})

function handleAccountClick() {
    closeMobileNav()
    if (customer.value) {
        router.visit('/dashboard')
    } else {
        openLoginModal()
    }
}

function handleMobileLogout() {
    closeMobileNav()
    axios
        .post('/customer/auth/logout')
        .then((res) => {
            resetCartOnLogout(res.data?.fresh_session_token)
            window.dispatchEvent(
                new CustomEvent('medimart:toast', {
                    detail: '👋 Logged out successfully.',
                })
            )
            router.visit('/')
        })
        .catch(() => {
            resetCartOnLogout()
            router.visit('/')
        })
}

function getChildName(child) {
    return typeof child === 'object' && child !== null ? child.name : child
}

function getChildKey(child, idx) {
    if (typeof child === 'object' && child !== null) {
        return child.id ?? child.slug ?? child.name ?? idx
    }
    return child ?? idx
}

const quickLinks = [
    { icon: '📋', label: 'Upload Rx', href: '#upload-prescription' },
    { icon: '📦', label: 'Track Order', href: '#track-order' },
    { icon: '🎧', label: 'Help', href: '#help' },
]
</script>

<template>
    <div id="mobilenav-overlay" :class="{ open: navOpen }" @click.self="closeMobileNav">
        <aside id="mobilenav-drawer">
            <!-- Header -->
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 shrink-0">
                <a href="/" class="flex items-center gap-2">
                    <img
                        v-if="storeLogo"
                        :src="storeLogo"
                        :alt="storeName"
                        class="h-8 w-auto max-w-[140px] object-contain rounded-lg"
                    />
                    <template v-else>
                        <div class="w-8 h-8 rounded-lg bg-primary flex items-center justify-center">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                                <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <span class="text-xl font-extrabold text-primary">{{ storeName }}</span>
                    </template>
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

            <!-- Login / Account prompt -->
            <div class="px-5 py-4 border-b border-gray-100 shrink-0">
                <template v-if="customer">
                    <div class="bg-gray-50 border border-gray-200/70 rounded-2xl p-3.5">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-primary/10 text-primary flex items-center justify-center font-bold text-sm shrink-0">
                                {{ (customer.first_name || customer.name || 'U')[0].toUpperCase() }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-[14px] font-semibold text-gray-900 truncate">
                                    {{ customer.first_name || customer.name }}
                                </p>
                                <p class="text-[12px] text-gray-500 truncate">
                                    {{ customer.phone || customer.email }}
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2 mt-3 pt-3 border-t border-gray-200/60">
                            <Link
                                href="/dashboard"
                                class="flex items-center justify-center gap-1.5 py-2 px-3 rounded-xl bg-white border border-gray-200 text-gray-700 text-[13px] font-medium hover:text-primary hover:border-primary/30 transition-colors shadow-2xs"
                                @click="closeMobileNav"
                            >
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none">
                                    <rect x="3" y="3" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="2"/>
                                    <rect x="14" y="3" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="2"/>
                                    <rect x="14" y="14" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="2"/>
                                    <rect x="3" y="14" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="2"/>
                                </svg>
                                <span>Dashboard</span>
                            </Link>

                            <button
                                type="button"
                                @click="handleMobileLogout"
                                class="flex items-center justify-center gap-1.5 py-2 px-3 rounded-xl bg-red-50 text-red-600 text-[13px] font-medium hover:bg-red-100 transition-colors cursor-pointer"
                            >
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none">
                                    <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                <span>Logout</span>
                            </button>
                        </div>
                    </div>
                </template>
                <template v-else>
                    <button
                        type="button"
                        class="w-full flex items-center justify-between bg-primarylt rounded-xl px-4 py-3.5 hover:bg-primary/15 transition-colors cursor-pointer"
                        @click="handleAccountClick"
                    >
                        <span class="flex items-center gap-3">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" class="text-primary"><circle cx="12" cy="8" r="4" stroke="currentColor" stroke-width="2"/><path d="M4 20c0-4 3.58-7 8-7s8 3 8 7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                            <span class="text-[14px] font-semibold text-primary">Login / Sign Up</span>
                        </span>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" class="text-primary"><path d="M9 18l6-6-6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    </button>
                </template>
            </div>

            <!-- Scrollable content -->
            <div class="flex-1 overflow-y-auto">
                <!-- Quick links -->
                <div class="px-5 py-3 border-b border-gray-100 grid grid-cols-3 gap-2">
                    <a
                        v-for="link in quickLinks"
                        :key="link.label"
                        :href="link.href"
                        class="flex flex-col items-center gap-1.5 py-2 rounded-xl hover:bg-gray-50 transition-colors"
                    >
                        <span class="text-2xl">{{ link.icon }}</span>
                        <span class="text-[11.5px] font-medium text-gray-600 text-center leading-tight">{{ link.label }}</span>
                    </a>
                </div>

                <!-- Category accordion -->
                <div class="px-2 py-2">
                    <p class="px-3 pt-2 pb-1 text-[12px] font-bold text-gray-400 uppercase tracking-wider">Shop by Category</p>

                    <details v-for="cat in categories" :key="cat.slug || cat.name" class="group border-b border-gray-50">
                        <summary class="flex items-center gap-3 px-3 py-3.5 cursor-pointer">
                            <span class="text-lg">{{ cat.icon || '🏷️' }}</span>
                            <span class="flex-1 text-[14.5px] font-medium text-gray-800">{{ cat.name }}</span>
                            <svg class="accordion-chevron text-gray-400" width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </summary>
                        <div class="pb-3 pl-11 pr-3 space-y-0.5">
                            <Link
                                :href="cat.slug ? `/category/${cat.slug}` : '#'"
                                class="block py-2 text-[14px] font-semibold text-primary hover:underline"
                                @click="closeMobileNav"
                            >
                                All {{ cat.name }} &rarr;
                            </Link>
                            <template v-if="cat.children && cat.children.length > 0">
                                <Link
                                    v-for="(child, idx) in cat.children"
                                    :key="getChildKey(child, idx)"
                                    :href="cat.slug ? `/category/${cat.slug}/${child.slug || child}` : '#'"
                                    class="block py-2 text-[14px] text-gray-600 hover:text-primary truncate"
                                    @click="closeMobileNav"
                                >{{ getChildName(child) }}</Link>
                            </template>
                        </div>
                    </details>
                </div>

                <!-- More links -->
                <div class="px-5 py-4 border-t border-gray-100 space-y-2.5">
                    <a
                        v-for="ml in moreLinks"
                        :key="ml.label"
                        :href="ml.href"
                        class="block text-[14px] text-gray-600 hover:text-primary transition-colors"
                    >{{ ml.label }}</a>
                </div>

                <!-- Contact card -->
                <div class="p-5 border-t border-gray-100 bg-gray-50 m-3 rounded-2xl">
                    <p class="text-[12px] text-gray-400 uppercase font-semibold">Need help?</p>
                    <p class="text-[16px] font-bold text-primary mt-1">{{ storePhone }}</p>
                    <p class="text-[12px] text-gray-500 mt-0.5">{{ storeEmail }}</p>
                </div>
            </div>
        </aside>
    </div>
</template>
