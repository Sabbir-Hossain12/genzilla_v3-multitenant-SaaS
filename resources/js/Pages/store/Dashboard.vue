<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import axios from 'axios'

const props = defineProps({
    store: {
        type: Object,
        default: () => ({}),
    },
    customer: {
        type: Object,
        default: () => ({
            name: 'Sabbir Hossain',
            first_name: 'Sabbir',
            last_name: 'Hossain',
            phone: '+880 1926241906',
            email: 'sabbir@example.com',
            membership: '🏅 Gold Member',
            avatar: 'https://loremflickr.com/200/200/portrait,man?lock=501',
        }),
    },
    stats: {
        type: Object,
        default: () => ({
            total_orders: 24,
            wallet_balance: 320.00,
            wishlist_count: 8,
            reward_points: 1240,
        }),
    },
    orders: {
        type: Array,
        default: () => [],
    },
    addresses: {
        type: Array,
        default: () => [],
    },
    wishlist: {
        type: Array,
        default: () => [],
    },
    transactions: {
        type: Array,
        default: () => [],
    },
})

// Active Tab state
const currentTab = ref('overview')

function setTab(tab) {
    currentTab.value = tab
    window.scrollTo({ top: 0, behavior: 'smooth' })
}

// Brand helper
const storeName = computed(() => props.store?.name || 'MediMart')
const currencySymbol = computed(() => (props.store?.currency === 'USD' ? '$' : '৳'))

// Orders filter
const orderFilter = ref('All')
const filterOptions = ['All', 'Processing', 'Delivered', 'Cancelled']

const ordersList = ref([...props.orders])

const filteredOrders = computed(() => {
    if (orderFilter.value === 'All') return ordersList.value
    if (orderFilter.value === 'Processing') {
        return ordersList.value.filter(
            (o) => o.status === 'Processing' || o.status === 'Out for Delivery' || o.status === 'Pending'
        )
    }
    return ordersList.value.filter((o) => o.status === orderFilter.value)
})

// Addresses
const addressesList = ref([...props.addresses])

function setDefaultAddress(id) {
    addressesList.value = addressesList.value.map((a) => ({
        ...a,
        is_default: a.id === id,
    }))
    showToast('Address set as default.')
}

function deleteAddress(id) {
    addressesList.value = addressesList.value.filter((a) => a.id !== id)
    showToast('Address removed.')
}

// Wishlist
const wishlistList = ref([...props.wishlist])

function removeWishlist(id) {
    wishlistList.value = wishlistList.value.filter((item) => item.id !== id)
    showToast('Removed item from wishlist.')
}

function addToCartItem(item) {
    showToast(`Added ${item.name} to cart!`)
}

// Settings form
const customerProfile = ref({ ...props.customer })
const form = ref({
    fullName: props.customer?.name || `${props.customer?.first_name || ''} ${props.customer?.last_name || ''}`.trim() || 'Customer',
    phone: props.customer?.phone || '',
    email: props.customer?.email || '',
    dob: '',
    currentPassword: '',
    newPassword: '',
})

function saveSettings() {
    const parts = form.value.fullName.trim().split(' ')
    const firstName = parts[0] || 'Customer'
    const lastName = parts.slice(1).join(' ') || ''

    axios.put('/customer/profile', {
        first_name: firstName,
        last_name: lastName,
        phone: form.value.phone,
        email: form.value.email,
    })
        .then((res) => {
            customerProfile.value.name = `${firstName} ${lastName}`.trim()
            customerProfile.value.first_name = firstName
            customerProfile.value.last_name = lastName
            customerProfile.value.phone = form.value.phone
            customerProfile.value.email = form.value.email
            showToast('✅ Profile settings saved successfully!')
        })
        .catch((err) => {
            showToast(err.response?.data?.message || 'Failed to update profile.')
        })
}

function updatePassword() {
    if (!form.value.currentPassword || !form.value.newPassword) {
        showToast('Please fill out both password fields.')
        return
    }

    axios.put('/customer/password', {
        current_password: form.value.currentPassword,
        password: form.value.newPassword,
    })
        .then(() => {
            form.value.currentPassword = ''
            form.value.newPassword = ''
            showToast('🔒 Password changed successfully!')
        })
        .catch((err) => {
            showToast(err.response?.data?.message || 'Failed to update password.')
        })
}

function logout() {
    axios.post('/customer/auth/logout')
        .then(() => {
            window.dispatchEvent(new CustomEvent('medimart:toast', { detail: '👋 Logged out successfully.' }))
            router.visit('/')
        })
        .catch(() => {
            router.visit('/')
        })
}

// Toast notification
const toast = ref('')
let toastTimer = null

function showToast(msg) {
    toast.value = msg
    clearTimeout(toastTimer)
    toastTimer = setTimeout(() => {
        toast.value = ''
    }, 2800)
}

function onWindowToast(event) {
    if (event.detail) showToast(event.detail)
}

onMounted(() => {
    document.body.className = 'bg-gray-50 text-gray-800 font-sans min-h-screen'
    window.addEventListener('medimart:toast', onWindowToast)
})

onBeforeUnmount(() => {
    window.removeEventListener('medimart:toast', onWindowToast)
    clearTimeout(toastTimer)
})
</script>

<template>
    <Head :title="`My Account — ${storeName}`" />

    <div class="min-h-screen bg-gray-50 flex flex-col">
        <!-- ════════════════════════════════════════
             MINI HEADER
        ════════════════════════════════════════ -->
        <header class="bg-white border-b border-gray-100 sticky top-0 z-20 shadow-xs">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
                <Link href="/" class="flex items-center gap-2 group">
                    <template v-if="store.logo_url">
                        <img :src="store.logo_url" :alt="storeName" class="h-9 w-auto max-w-[140px] object-contain" />
                    </template>
                    <template v-else>
                        <div class="w-9 h-9 rounded-lg bg-primary flex items-center justify-center text-white shadow-xs">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                                <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <span class="text-xl font-extrabold text-primary">{{ storeName }}</span>
                    </template>
                </Link>

                <Link href="/" class="flex items-center gap-1.5 text-[14px] font-semibold text-gray-500 hover:text-primary transition-colors">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    Back to Shop
                </Link>
            </div>
        </header>

        <!-- ════════════════════════════════════════
             MAIN CONTENT & SIDEBAR
        ════════════════════════════════════════ -->
        <main class="max-w-6xl mx-auto px-4 sm:px-6 py-6 sm:py-8 flex-1 w-full">
            <div class="flex flex-col lg:flex-row gap-5">

                <!-- ════════════════════════════════════════
                     SIDEBAR
                ════════════════════════════════════════ -->
                <aside class="lg:w-64 shrink-0">
                    <!-- Profile card -->
                    <div class="bg-white rounded-2xl border border-gray-100 p-5 flex lg:flex-col items-center lg:text-center gap-4 lg:gap-0 mb-4 shadow-xs">
                        <div class="w-16 h-16 lg:w-20 lg:h-20 rounded-full overflow-hidden shrink-0 lg:mb-3 ring-2 ring-primarylt">
                            <img :src="customerProfile.avatar || customer.avatar" :alt="customerProfile.name" class="w-full h-full object-cover"/>
                        </div>
                        <div class="min-w-0">
                            <p class="text-[15px] font-bold text-gray-900 truncate">{{ customerProfile.name }}</p>
                            <p class="text-[13px] text-gray-400 truncate">{{ customerProfile.phone }}</p>
                            <span class="hidden lg:inline-block mt-2 text-[11.5px] font-semibold text-accent bg-accentlt px-2.5 py-1 rounded-full">
                                {{ customerProfile.membership || customer.membership || '🏅 Member' }}
                            </span>
                        </div>
                    </div>

                    <!-- Desktop navigation -->
                    <nav class="hidden lg:block bg-white rounded-2xl border border-gray-100 p-2 space-y-1 shadow-xs">
                        <button
                            type="button"
                            @click="setTab('overview')"
                            class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-[14px] transition-colors cursor-pointer text-left"
                            :class="currentTab === 'overview' ? 'bg-primarylt text-primary font-semibold' : 'text-gray-600 hover:bg-gray-50'"
                        >
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><rect x="3" y="3" width="7" height="9" rx="1.5" stroke="currentColor" stroke-width="2"/><rect x="14" y="3" width="7" height="5" rx="1.5" stroke="currentColor" stroke-width="2"/><rect x="14" y="12" width="7" height="9" rx="1.5" stroke="currentColor" stroke-width="2"/><rect x="3" y="16" width="7" height="5" rx="1.5" stroke="currentColor" stroke-width="2"/></svg>
                            Dashboard
                        </button>

                        <button
                            type="button"
                            @click="setTab('orders')"
                            class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-[14px] transition-colors cursor-pointer text-left"
                            :class="currentTab === 'orders' ? 'bg-primarylt text-primary font-semibold' : 'text-gray-600 hover:bg-gray-50'"
                        >
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M20 7h-3V6a4 4 0 00-8 0v1H6a1 1 0 00-1 1v11a2 2 0 002 2h10a2 2 0 002-2V8a1 1 0 00-1-1z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            My Orders
                        </button>

                        <button
                            type="button"
                            @click="setTab('addresses')"
                            class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-[14px] transition-colors cursor-pointer text-left"
                            :class="currentTab === 'addresses' ? 'bg-primarylt text-primary font-semibold' : 'text-gray-600 hover:bg-gray-50'"
                        >
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M21 10c0 6-9 12-9 12S3 16 3 10a9 9 0 0118 0z" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="10" r="3" stroke="currentColor" stroke-width="2"/></svg>
                            Saved Addresses
                        </button>

                        <button
                            type="button"
                            @click="setTab('wishlist')"
                            class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-[14px] transition-colors cursor-pointer text-left"
                            :class="currentTab === 'wishlist' ? 'bg-primarylt text-primary font-semibold' : 'text-gray-600 hover:bg-gray-50'"
                        >
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M20.8 4.6a5.5 5.5 0 00-7.8 0L12 5.6l-1-1a5.5 5.5 0 00-7.8 7.8l1 1L12 21l7.8-7.6 1-1a5.5 5.5 0 000-7.8z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            Wishlist
                        </button>

                        <button
                            type="button"
                            @click="setTab('wallet')"
                            class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-[14px] transition-colors cursor-pointer text-left"
                            :class="currentTab === 'wallet' ? 'bg-primarylt text-primary font-semibold' : 'text-gray-600 hover:bg-gray-50'"
                        >
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M21 12V7H5a2 2 0 010-4h14v4M3 5v14a2 2 0 002 2h16v-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M18 12a2 2 0 100 4 2 2 0 000-4z" stroke="currentColor" stroke-width="2"/></svg>
                            {{ storeName }} Cash
                        </button>

                        <button
                            type="button"
                            @click="setTab('settings')"
                            class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-[14px] transition-colors cursor-pointer text-left"
                            :class="currentTab === 'settings' ? 'bg-primarylt text-primary font-semibold' : 'text-gray-600 hover:bg-gray-50'"
                        >
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 11-2.83 2.83l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 11-2.83-2.83l.06-.06A1.65 1.65 0 004.6 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 112.83-2.83l.06.06A1.65 1.65 0 009 4.6a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09A1.65 1.65 0 0015 4.6a1.65 1.65 0 001.82-.33l.06-.06a2 2 0 112.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            Account Settings
                        </button>

                        <div class="pt-1 mt-1 border-t border-gray-100">
                            <button
                                type="button"
                                @click="logout"
                                class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-[14px] text-red-500 hover:bg-red-50 transition-colors cursor-pointer text-left"
                            >
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                Logout
                            </button>
                        </div>
                    </nav>

                    <!-- Mobile tab scrollbar -->
                    <nav class="lg:hidden flex gap-2 overflow-x-auto no-scrollbar pb-1">
                        <button
                            type="button"
                            @click="setTab('overview')"
                            class="shrink-0 text-[13px] font-semibold px-4 py-2 rounded-full border transition-colors cursor-pointer"
                            :class="currentTab === 'overview' ? 'bg-primarylt text-primary border-primary/30' : 'text-gray-600 border-gray-200 bg-white'"
                        >Dashboard</button>
                        <button
                            type="button"
                            @click="setTab('orders')"
                            class="shrink-0 text-[13px] font-semibold px-4 py-2 rounded-full border transition-colors cursor-pointer"
                            :class="currentTab === 'orders' ? 'bg-primarylt text-primary border-primary/30' : 'text-gray-600 border-gray-200 bg-white'"
                        >Orders</button>
                        <button
                            type="button"
                            @click="setTab('addresses')"
                            class="shrink-0 text-[13px] font-semibold px-4 py-2 rounded-full border transition-colors cursor-pointer"
                            :class="currentTab === 'addresses' ? 'bg-primarylt text-primary border-primary/30' : 'text-gray-600 border-gray-200 bg-white'"
                        >Addresses</button>
                        <button
                            type="button"
                            @click="setTab('wishlist')"
                            class="shrink-0 text-[13px] font-semibold px-4 py-2 rounded-full border transition-colors cursor-pointer"
                            :class="currentTab === 'wishlist' ? 'bg-primarylt text-primary border-primary/30' : 'text-gray-600 border-gray-200 bg-white'"
                        >Wishlist</button>
                        <button
                            type="button"
                            @click="setTab('wallet')"
                            class="shrink-0 text-[13px] font-semibold px-4 py-2 rounded-full border transition-colors cursor-pointer"
                            :class="currentTab === 'wallet' ? 'bg-primarylt text-primary border-primary/30' : 'text-gray-600 border-gray-200 bg-white'"
                        >Wallet</button>
                        <button
                            type="button"
                            @click="setTab('settings')"
                            class="shrink-0 text-[13px] font-semibold px-4 py-2 rounded-full border transition-colors cursor-pointer"
                            :class="currentTab === 'settings' ? 'bg-primarylt text-primary border-primary/30' : 'text-gray-600 border-gray-200 bg-white'"
                        >Settings</button>
                        <button
                            type="button"
                            @click="logout"
                            class="shrink-0 text-[13px] font-semibold px-4 py-2 rounded-full border border-red-200 text-red-500 bg-white transition-colors cursor-pointer"
                        >Logout</button>
                    </nav>
                </aside>

                <!-- ════════════════════════════════════════
                     TAB CONTENT AREA
                ════════════════════════════════════════ -->
                <div class="flex-1 min-w-0 mt-4 lg:mt-0">

                    <!-- ── TAB: OVERVIEW ── -->
                    <div v-show="currentTab === 'overview'" class="space-y-5">
                        <!-- Welcome Banner -->
                        <div class="bg-gradient-to-r from-primary to-teal-600 rounded-2xl p-5 sm:p-6 flex items-center justify-between overflow-hidden relative shadow-sm">
                            <div class="absolute right-0 top-0 w-40 h-40 rounded-full bg-white/10 -mr-10 -mt-10 pointer-events-none"></div>
                            <div class="relative z-10">
                                <h1 class="text-white text-xl sm:text-2xl font-extrabold">Welcome back, {{ customerProfile.first_name || 'Friend' }} 👋</h1>
                                <p class="text-white/80 text-[13.5px] mt-1">Here's what's happening with your account today.</p>
                            </div>
                            <button
                                type="button"
                                @click="setTab('settings')"
                                class="relative z-10 hidden sm:inline-block bg-white text-primary text-[13.5px] font-bold px-4 py-2.5 rounded-xl hover:bg-gray-50 transition-colors shadow-xs cursor-pointer"
                            >Edit Profile</button>
                        </div>

                        <!-- 4 Stat cards -->
                        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                            <div class="bg-white rounded-2xl border border-gray-100 p-4 shadow-xs">
                                <div class="w-9 h-9 rounded-lg bg-primarylt flex items-center justify-center mb-2">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" class="text-primary"><path d="M20 7h-3V6a4 4 0 00-8 0v1H6a1 1 0 00-1 1v11a2 2 0 002 2h10a2 2 0 002-2V8a1 1 0 00-1-1z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </div>
                                <p class="text-[20px] font-extrabold text-gray-900">{{ stats.total_orders || ordersList.length }}</p>
                                <p class="text-[12.5px] text-gray-400">Total Orders</p>
                            </div>

                            <div class="bg-white rounded-2xl border border-gray-100 p-4 shadow-xs">
                                <div class="w-9 h-9 rounded-lg bg-orange-50 flex items-center justify-center mb-2">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" class="text-accent"><path d="M21 12V7H5a2 2 0 010-4h14v4M3 5v14a2 2 0 002 2h16v-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </div>
                                <p class="text-[20px] font-extrabold text-gray-900">{{ currencySymbol }}{{ stats.wallet_balance || 320 }}</p>
                                <p class="text-[12.5px] text-gray-400">{{ storeName }} Cash</p>
                            </div>

                            <div class="bg-white rounded-2xl border border-gray-100 p-4 shadow-xs">
                                <div class="w-9 h-9 rounded-lg bg-rose-50 flex items-center justify-center mb-2">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" class="text-rose-500"><path d="M20.8 4.6a5.5 5.5 0 00-7.8 0L12 5.6l-1-1a5.5 5.5 0 00-7.8 7.8l1 1L12 21l7.8-7.6 1-1a5.5 5.5 0 000-7.8z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </div>
                                <p class="text-[20px] font-extrabold text-gray-900">{{ stats.wishlist_count || wishlistList.length }}</p>
                                <p class="text-[12.5px] text-gray-400">Wishlist Items</p>
                            </div>

                            <div class="bg-white rounded-2xl border border-gray-100 p-4 shadow-xs">
                                <div class="w-9 h-9 rounded-lg bg-violet-50 flex items-center justify-center mb-2">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" class="text-violet-500"><path d="M12 2l2.9 6.3 6.9.8-5 4.8 1.3 6.9L12 17.5 5.9 20.8l1.3-6.9-5-4.8 6.9-.8L12 2z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </div>
                                <p class="text-[20px] font-extrabold text-gray-900">{{ stats.reward_points?.toLocaleString() || '1,240' }}</p>
                                <p class="text-[12.5px] text-gray-400">Reward Points</p>
                            </div>
                        </div>

                        <!-- Recent Orders -->
                        <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-xs">
                            <div class="flex items-center justify-between mb-4">
                                <h2 class="text-[16px] font-bold text-gray-900">Recent Orders</h2>
                                <button
                                    type="button"
                                    @click="setTab('orders')"
                                    class="text-[13.5px] font-semibold text-primary hover:underline cursor-pointer"
                                >View all</button>
                            </div>

                            <div class="space-y-3">
                                <div
                                    v-for="order in ordersList.slice(0, 3)"
                                    :key="order.id"
                                    class="flex items-center justify-between gap-3 border border-gray-100 rounded-xl p-4 hover:border-gray-200 transition-colors"
                                >
                                    <div class="min-w-0">
                                        <p class="text-[14px] font-bold text-gray-900">#{{ order.id }}</p>
                                        <p class="text-[12.5px] text-gray-400 mt-0.5">{{ order.date }} · {{ order.items }} item{{ order.items > 1 ? 's' : '' }}</p>
                                    </div>
                                    <div class="flex items-center gap-3 shrink-0">
                                        <span class="text-[12px] font-semibold px-2.5 py-1 rounded-md" :class="order.color">{{ order.status }}</span>
                                        <span class="text-[14.5px] font-bold text-gray-800 hidden sm:inline">{{ currencySymbol }}{{ order.total?.toLocaleString() }}</span>
                                        <button
                                            type="button"
                                            @click="showToast(`Viewing details for #${order.id}`)"
                                            class="text-[13px] font-semibold text-primary hover:underline whitespace-nowrap cursor-pointer"
                                        >
                                            {{ order.status === 'Out for Delivery' ? 'Track' : 'View' }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Address + Prescription preview -->
                        <div class="grid sm:grid-cols-2 gap-5">
                            <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-xs">
                                <div class="flex items-center justify-between mb-3">
                                    <h2 class="text-[15px] font-bold text-gray-900">Default Address</h2>
                                    <button
                                        type="button"
                                        @click="setTab('addresses')"
                                        class="text-[13px] font-semibold text-primary hover:underline cursor-pointer"
                                    >Manage</button>
                                </div>
                                <span class="inline-block bg-primarylt text-primary text-[11.5px] font-semibold px-2 py-0.5 rounded-md mb-2">Home</span>
                                <p class="text-[14px] font-bold text-gray-900">{{ addressesList[0]?.name || customerProfile.name }}</p>
                                <p class="text-[13px] text-gray-500 mt-0.5 leading-snug">
                                    {{ addressesList[0]?.address || '549 Ashkona, Apartment: Bhuiyan Manjil, Dakshin Khan, Dhaka' }}
                                </p>
                            </div>

                            <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-xs">
                                <div class="flex items-center justify-between mb-3">
                                    <h2 class="text-[15px] font-bold text-gray-900">Active Prescriptions</h2>
                                    <a href="/#upload-prescription" class="text-[13px] font-semibold text-primary hover:underline">Upload New</a>
                                </div>
                                <div class="flex items-center gap-3 py-1.5">
                                    <div class="w-9 h-9 rounded-lg bg-blue-50 flex items-center justify-center shrink-0">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" class="text-blue-500"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M17 8l-5-5-5 5M12 3v12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-[13.5px] font-medium text-gray-800 truncate">Prescription_Sep2026.pdf</p>
                                        <p class="text-[12px] text-gray-400">Uploaded Sep 18 · Reviewed</p>
                                    </div>
                                    <span class="text-[11.5px] font-semibold text-green-600 bg-green-50 px-2 py-0.5 rounded-md shrink-0">Verified</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ── TAB: ORDERS ── -->
                    <div v-show="currentTab === 'orders'" class="space-y-4">
                        <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-xs">
                            <div class="flex items-center justify-between mb-4">
                                <h2 class="text-[16px] font-bold text-gray-900">My Orders</h2>
                                <span class="text-[13px] text-gray-400">{{ filteredOrders.length }} orders found</span>
                            </div>

                            <!-- Filter buttons -->
                            <div class="flex gap-2 overflow-x-auto no-scrollbar mb-4 pb-1">
                                <button
                                    v-for="opt in filterOptions"
                                    :key="opt"
                                    type="button"
                                    @click="orderFilter = opt"
                                    class="text-[13px] font-semibold px-3.5 py-1.5 rounded-full border transition-colors whitespace-nowrap cursor-pointer"
                                    :class="orderFilter === opt ? 'bg-primary text-white border-primary shadow-xs' : 'border-gray-200 text-gray-500 hover:border-primary hover:text-primary'"
                                >
                                    {{ opt }}
                                </button>
                            </div>

                            <div v-if="filteredOrders.length > 0" class="space-y-3">
                                <div
                                    v-for="o in filteredOrders"
                                    :key="o.id"
                                    class="flex items-center justify-between gap-3 border border-gray-100 rounded-xl p-4 hover:border-gray-200 transition-colors"
                                >
                                    <div class="min-w-0">
                                        <p class="text-[14px] font-bold text-gray-900">#{{ o.id }}</p>
                                        <p class="text-[12.5px] text-gray-400 mt-0.5">{{ o.date }} · {{ o.items }} item{{ o.items > 1 ? 's' : '' }}</p>
                                    </div>
                                    <div class="flex items-center gap-3 shrink-0">
                                        <span class="text-[12px] font-semibold px-2.5 py-1 rounded-md" :class="o.color">{{ o.status }}</span>
                                        <span class="text-[14.5px] font-bold text-gray-800 hidden sm:inline">{{ currencySymbol }}{{ o.total?.toLocaleString() }}</span>
                                        <button
                                            type="button"
                                            @click="showToast(`Viewing details for #${o.id}`)"
                                            class="text-[13px] font-semibold text-primary hover:underline whitespace-nowrap cursor-pointer"
                                        >
                                            {{ o.status === 'Out for Delivery' ? 'Track' : 'View' }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="text-center py-12 text-gray-400 text-[14px]">
                                No orders found in this category.
                            </div>
                        </div>
                    </div>

                    <!-- ── TAB: ADDRESSES ── -->
                    <div v-show="currentTab === 'addresses'" class="space-y-4">
                        <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-xs">
                            <div class="flex items-center justify-between mb-4">
                                <h2 class="text-[16px] font-bold text-gray-900">Saved Addresses</h2>
                                <button
                                    type="button"
                                    @click="showToast('Add new address form opening...')"
                                    class="flex items-center gap-1.5 bg-primary hover:bg-primary2 text-white text-[13px] font-semibold px-3.5 py-2 rounded-xl transition-colors cursor-pointer"
                                >
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                                    Add New
                                </button>
                            </div>

                            <div class="grid sm:grid-cols-2 gap-4">
                                <div
                                    v-for="addr in addressesList"
                                    :key="addr.id"
                                    class="border border-gray-100 rounded-xl p-4 relative flex flex-col justify-between hover:border-gray-200 transition-colors"
                                >
                                    <div>
                                        <span
                                            class="inline-block text-[11.5px] font-semibold px-2 py-0.5 rounded-md mb-2"
                                            :class="addr.is_default ? 'bg-primarylt text-primary' : 'bg-gray-100 text-gray-600'"
                                        >
                                            {{ addr.title || 'Address' }}{{ addr.is_default ? ' · Default' : '' }}
                                        </span>
                                        <p class="text-[14px] font-bold text-gray-900">{{ addr.name }}</p>
                                        <p class="text-[13px] text-gray-500 mt-0.5">{{ addr.phone }}</p>
                                        <p class="text-[13px] text-gray-500 mt-1 leading-snug">{{ addr.address }}</p>
                                    </div>

                                    <div class="flex items-center gap-3 mt-4 pt-3 border-t border-gray-50">
                                        <button
                                            type="button"
                                            @click="showToast(`Editing ${addr.title}...`)"
                                            class="text-[13px] font-semibold text-primary hover:underline cursor-pointer"
                                        >Edit</button>
                                        <button
                                            type="button"
                                            @click="deleteAddress(addr.id)"
                                            class="text-[13px] font-semibold text-red-500 hover:underline cursor-pointer"
                                        >Delete</button>
                                        <button
                                            v-if="!addr.is_default"
                                            type="button"
                                            @click="setDefaultAddress(addr.id)"
                                            class="text-[13px] font-semibold text-gray-400 hover:text-primary hover:underline ml-auto cursor-pointer"
                                        >Set as Default</button>
                                    </div>
                                </div>

                                <button
                                    type="button"
                                    @click="showToast('Add new address modal opening...')"
                                    class="border-2 border-dashed border-gray-200 rounded-xl p-4 flex flex-col items-center justify-center gap-2 text-gray-400 hover:border-primary hover:text-primary transition-colors min-h-[140px] cursor-pointer"
                                >
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                                    <span class="text-[13.5px] font-semibold">Add New Address</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- ── TAB: WISHLIST ── -->
                    <div v-show="currentTab === 'wishlist'" class="space-y-4">
                        <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-xs">
                            <div class="flex items-center justify-between mb-4">
                                <h2 class="text-[16px] font-bold text-gray-900">My Wishlist</h2>
                                <span class="text-[13px] text-gray-400">{{ wishlistList.length }} items</span>
                            </div>

                            <div v-if="wishlistList.length > 0" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
                                <div
                                    v-for="item in wishlistList"
                                    :key="item.id"
                                    class="border border-gray-100 rounded-xl overflow-hidden group hover:border-gray-200 transition-colors"
                                >
                                    <div class="relative bg-gray-50 aspect-square flex items-center justify-center overflow-hidden">
                                        <img :src="item.img" :alt="item.name" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"/>
                                        <button
                                            type="button"
                                            aria-label="Remove from wishlist"
                                            @click="removeWishlist(item.id)"
                                            class="absolute top-2 right-2 w-7 h-7 bg-white/90 rounded-full flex items-center justify-center text-rose-500 shadow-sm hover:scale-110 transition-transform cursor-pointer"
                                        >
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M20.8 4.6a5.5 5.5 0 00-7.8 0L12 5.6l-1-1a5.5 5.5 0 00-7.8 7.8l1 1L12 21l7.8-7.6 1-1a5.5 5.5 0 000-7.8z"/></svg>
                                        </button>
                                    </div>
                                    <div class="p-2.5">
                                        <p class="text-[12.5px] font-medium text-gray-800 leading-snug line-clamp-2 mb-1">{{ item.name }}</p>
                                        <div class="flex items-center justify-between">
                                            <span class="text-[14px] font-bold text-primary">{{ currencySymbol }}{{ item.price?.toLocaleString() }}</span>
                                            <button
                                                type="button"
                                                @click="addToCartItem(item)"
                                                class="w-7 h-7 bg-primary hover:bg-primary2 text-white rounded-full flex items-center justify-center text-sm font-bold transition-colors cursor-pointer shadow-xs"
                                            >+</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="text-center py-12 text-gray-400 text-[14px]">
                                Your wishlist is empty.
                            </div>
                        </div>
                    </div>

                    <!-- ── TAB: WALLET ── -->
                    <div v-show="currentTab === 'wallet'" class="space-y-4">
                        <!-- Wallet Card -->
                        <div class="bg-gradient-to-r from-primary to-teal-600 rounded-2xl p-6 relative overflow-hidden shadow-sm">
                            <div class="absolute right-0 top-0 w-40 h-40 rounded-full bg-white/10 -mr-10 -mt-10 pointer-events-none"></div>
                            <p class="text-white/80 text-[13px] font-medium relative z-10">{{ storeName }} Cash Balance</p>
                            <p class="text-white text-4xl font-extrabold mt-1 relative z-10">
                                {{ currencySymbol }}{{ (stats.wallet_balance || 320.00).toFixed(2) }}
                            </p>
                            <p class="text-white/70 text-[12.5px] mt-2 relative z-10">Cash back is auto-applied at checkout on eligible orders.</p>
                        </div>

                        <!-- Transactions History -->
                        <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-xs">
                            <h2 class="text-[15px] font-bold text-gray-900 mb-4">Transaction History</h2>
                            <div class="space-y-3">
                                <div
                                    v-for="tx in transactions"
                                    :key="tx.id"
                                    class="flex items-center justify-between py-2 border-b border-gray-50 last:border-0"
                                >
                                    <div>
                                        <p class="text-[13.5px] font-medium text-gray-800">{{ tx.desc }}</p>
                                        <p class="text-[12px] text-gray-400 mt-0.5">{{ tx.date }}</p>
                                    </div>
                                    <span class="text-[14px] font-bold" :class="tx.positive ? 'text-green-600' : 'text-red-500'">
                                        {{ tx.positive ? '+' : '' }}{{ currencySymbol }}{{ Math.abs(tx.amount) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ── TAB: SETTINGS ── -->
                    <div v-show="currentTab === 'settings'" class="space-y-4">
                        <div class="bg-white rounded-2xl border border-gray-100 p-5 sm:p-6 shadow-xs">
                            <h2 class="text-[16px] font-bold text-gray-900 mb-5">Account Settings</h2>
                            <div class="grid sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[13px] font-semibold text-gray-700 mb-1.5">Full Name</label>
                                    <input
                                        v-model="form.fullName"
                                        type="text"
                                        class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-[14px] focus:outline-none focus:border-primary transition-colors"
                                    />
                                </div>
                                <div>
                                    <label class="block text-[13px] font-semibold text-gray-700 mb-1.5">Phone Number</label>
                                    <input
                                        v-model="form.phone"
                                        type="tel"
                                        class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-[14px] focus:outline-none focus:border-primary transition-colors"
                                    />
                                </div>
                                <div>
                                    <label class="block text-[13px] font-semibold text-gray-700 mb-1.5">Email Address</label>
                                    <input
                                        v-model="form.email"
                                        type="email"
                                        placeholder="you@example.com"
                                        class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-[14px] focus:outline-none focus:border-primary transition-colors"
                                    />
                                </div>
                                <div>
                                    <label class="block text-[13px] font-semibold text-gray-700 mb-1.5">Date of Birth</label>
                                    <input
                                        v-model="form.dob"
                                        type="date"
                                        class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-[14px] focus:outline-none focus:border-primary transition-colors"
                                    />
                                </div>
                            </div>
                            <button
                                type="button"
                                @click="saveSettings"
                                class="mt-5 bg-primary hover:bg-primary2 text-white font-semibold text-[14px] px-6 py-2.5 rounded-xl transition-colors cursor-pointer shadow-xs"
                            >Save Changes</button>

                            <div class="border-t border-gray-100 mt-6 pt-5">
                                <h3 class="text-[14.5px] font-bold text-gray-900 mb-3">Change Password</h3>
                                <div class="grid sm:grid-cols-2 gap-4 max-w-2xl">
                                    <input
                                        v-model="form.currentPassword"
                                        type="password"
                                        placeholder="Current password"
                                        class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-[14px] focus:outline-none focus:border-primary transition-colors"
                                    />
                                    <input
                                        v-model="form.newPassword"
                                        type="password"
                                        placeholder="New password"
                                        class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-[14px] focus:outline-none focus:border-primary transition-colors"
                                    />
                                </div>
                                <button
                                    type="button"
                                    @click="updatePassword"
                                    class="mt-4 border border-primary text-primary font-semibold text-[13.5px] px-5 py-2.5 rounded-xl hover:bg-primarylt transition-colors cursor-pointer"
                                >Update Password</button>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </main>

        <!-- Toast notification -->
        <Transition
            enter-active-class="transition-opacity duration-300 ease-out"
            enter-from-class="opacity-0"
            leave-active-class="transition-opacity duration-300 ease-in"
            leave-to-class="opacity-0"
        >
            <div
                v-if="toast"
                class="fixed bottom-10 left-1/2 -translate-x-1/2 bg-gray-900 text-white px-5 py-3 rounded-xl text-[14px] font-semibold z-[200] shadow-lg whitespace-nowrap"
            >{{ toast }}</div>
        </Transition>
    </div>
</template>
