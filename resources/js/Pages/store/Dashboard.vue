<script setup>
import { ref, computed } from 'vue'
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
            name: '',
            first_name: '',
            last_name: '',
            phone: '',
            email: '',
            membership: '',
            avatar: null,
            joined_date: '',
        }),
    },
    stats: {
        type: Object,
        default: () => ({
            total_orders: 0,
            total_spent: 0,
            active_prescriptions: 0,
            wishlist_count: 0,
            saved_addresses_count: 0,
            coupons_count: 0,
        }),
    },
    orders: {
        type: Array,
        default: () => [],
    },
    prescriptions: {
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
    coupons: {
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

// Store & Currency helpers
const storeName = computed(() => props.store?.name || 'MediMart')
const currencySymbol = computed(() => (props.store?.currency === 'USD' ? '$' : '৳'))

// ══════════════════════════════════════════
// PROFILE & AVATAR UPLOAD
// ══════════════════════════════════════════
const customerProfile = ref({ ...props.customer })
const avatarInput = ref(null)
const avatarUploading = ref(false)
const avatarImgError = ref(false)

function triggerAvatarSelect() {
    avatarInput.value?.click()
}

function handleAvatarUpload(e) {
    const file = e.target.files[0]
    if (!file) return

    if (!file.type.startsWith('image/')) {
        showToast('Please select a valid image file (JPG, PNG, WEBP).')
        return
    }

    if (file.size > 3 * 1024 * 1024) {
        showToast('Photo size should be less than 3MB.')
        return
    }

    avatarUploading.value = true
    const fd = new FormData()
    fd.append('avatar', file)

    axios.post('/customer/avatar', fd, {
        headers: { 'Content-Type': 'multipart/form-data' },
    })
        .then((res) => {
            avatarUploading.value = false
            if (res.data.avatar_url) {
                avatarImgError.value = false
                customerProfile.value.avatar = res.data.avatar_url
            }
            showToast('📸 Profile photo updated successfully!')
        })
        .catch((err) => {
            avatarUploading.value = false
            showToast(err.response?.data?.message || 'Failed to upload photo.')
        })
}

// ══════════════════════════════════════════
// ORDERS STATE & DETAILS MODAL
// ══════════════════════════════════════════
const orderFilter = ref('All')
const filterOptions = ['All', 'Processing', 'Delivered', 'Cancelled']
const ordersList = ref([...props.orders])

const filteredOrders = computed(() => {
    if (orderFilter.value === 'All') return ordersList.value
    if (orderFilter.value === 'Processing') {
        return ordersList.value.filter(
            (o) => o.status === 'Processing' || o.status === 'Out for Delivery' || o.status === 'Pending Review' || o.raw_status === 'pending' || o.raw_status === 'processing'
        )
    }
    return ordersList.value.filter((o) => o.status === orderFilter.value || o.raw_status === orderFilter.value.toLowerCase())
})

const selectedOrder = ref(null)

function openOrderModal(order) {
    selectedOrder.value = order
}

function closeOrderModal() {
    selectedOrder.value = null
}

// ══════════════════════════════════════════
// PRESCRIPTIONS STATE & MODALS
// ══════════════════════════════════════════
const prescriptionsList = ref([...props.prescriptions])
const rxFilter = ref('all')
const filteredPrescriptions = computed(() => {
    if (rxFilter.value === 'all') return prescriptionsList.value
    return prescriptionsList.value.filter((r) => r.status === rxFilter.value)
})

const selectedRxImage = ref(null)
function openRxLightbox(url) {
    if (url) selectedRxImage.value = url
}
function closeRxLightbox() {
    selectedRxImage.value = null
}

// Upload Prescription Modal
const showUploadRxModal = ref(false)
const rxUploading = ref(false)
const rxUploadForm = ref({
    patient_notes: '',
    delivery_address: '',
    customer_name: props.customer?.name || '',
    customer_phone: props.customer?.phone || '',
    file: null,
    filePreview: null,
})

function handleRxFileChange(e) {
    const file = e.target.files[0]
    if (!file) return
    rxUploadForm.value.file = file
    if (file.type.startsWith('image/')) {
        const reader = new FileReader()
        reader.onload = (event) => {
            rxUploadForm.value.filePreview = event.target.result
        }
        reader.readAsDataURL(file)
    } else {
        rxUploadForm.value.filePreview = null
    }
}

function submitPrescription() {
    if (!rxUploadForm.value.file) {
        showToast('Please select a prescription image or document.')
        return
    }

    rxUploading.value = true
    const formData = new FormData()
    formData.append('prescription_file', rxUploadForm.value.file)
    formData.append('patient_notes', rxUploadForm.value.patient_notes)
    formData.append('delivery_address', rxUploadForm.value.delivery_address || (addressesList.value[0]?.formatted || ''))
    formData.append('customer_name', rxUploadForm.value.customer_name)
    formData.append('customer_phone', rxUploadForm.value.customer_phone)

    axios.post('/customer/prescriptions', formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
    })
        .then((res) => {
            rxUploading.value = false
            if (res.data.prescription) {
                prescriptionsList.value.unshift(res.data.prescription)
            }
            showUploadRxModal.value = false
            rxUploadForm.value.file = null
            rxUploadForm.value.filePreview = null
            rxUploadForm.value.patient_notes = ''
            showToast('✅ Prescription uploaded! Pharmacist will review shortly.')
            setTab('prescriptions')
        })
        .catch((err) => {
            rxUploading.value = false
            showToast(err.response?.data?.message || 'Failed to upload prescription.')
        })
}

// ══════════════════════════════════════════
// ADDRESSES STATE & MODALS
// ══════════════════════════════════════════
const addressesList = ref([...props.addresses])
const showAddAddressModal = ref(false)
const addressLoading = ref(false)
const newAddressForm = ref({
    title: 'Home',
    first_name: props.customer?.first_name || '',
    last_name: props.customer?.last_name || '',
    phone: props.customer?.phone || '',
    address_1: '',
    address_2: '',
    state_district: 'Dhaka',
    zip: '',
    country: 'Bangladesh',
    is_default: false,
})

function setDefaultAddress(id) {
    axios.post(`/customer/addresses/${id}/default`)
        .then(() => {
            addressesList.value = addressesList.value.map((a) => ({
                ...a,
                is_default: a.id === id,
            }))
            showToast('Address set as default.')
        })
        .catch(() => {
            addressesList.value = addressesList.value.map((a) => ({
                ...a,
                is_default: a.id === id,
            }))
            showToast('Address set as default.')
        })
}

function deleteAddress(id) {
    if (!confirm('Are you sure you want to remove this address?')) return

    axios.delete(`/customer/addresses/${id}`)
        .then(() => {
            addressesList.value = addressesList.value.filter((a) => a.id !== id)
            showToast('Address removed.')
        })
        .catch(() => {
            addressesList.value = addressesList.value.filter((a) => a.id !== id)
            showToast('Address removed.')
        })
}

function saveNewAddress() {
    if (!newAddressForm.value.address_1.trim() || !newAddressForm.value.phone.trim()) {
        showToast('Please provide street address and contact phone.')
        return
    }

    addressLoading.value = true
    axios.post('/customer/addresses', newAddressForm.value)
        .then((res) => {
            addressLoading.value = false
            if (res.data.address) {
                if (res.data.address.is_default) {
                    addressesList.value = addressesList.value.map((a) => ({ ...a, is_default: false }))
                }
                addressesList.value.unshift(res.data.address)
            }
            showAddAddressModal.value = false
            newAddressForm.value.address_1 = ''
            newAddressForm.value.address_2 = ''
            showToast('✅ Delivery address saved!')
        })
        .catch((err) => {
            addressLoading.value = false
            showToast(err.response?.data?.message || 'Failed to save address.')
        })
}

// ══════════════════════════════════════════
// WISHLIST STATE
// ══════════════════════════════════════════
const wishlistList = ref([...props.wishlist])

function removeWishlist(id) {
    axios.delete(`/customer/wishlist/${id}`).catch(() => {})
    wishlistList.value = wishlistList.value.filter((item) => item.id !== id && item.product_id !== id)
    showToast('Removed item from wishlist.')
}

function addToCartItem(item) {
    showToast(`Added ${item.name} to cart!`)
}

// ══════════════════════════════════════════
// TRANSACTIONS & COUPONS STATE
// ══════════════════════════════════════════
const transactionsList = ref([...props.transactions])
const couponsList = ref([...props.coupons])

function copyCoupon(code) {
    navigator.clipboard?.writeText(code)
    showToast(`🎟️ Copied code: ${code}`)
}

// ══════════════════════════════════════════
// SETTINGS FORM STATE
// ══════════════════════════════════════════
const form = ref({
    fullName: props.customer?.name || `${props.customer?.first_name || ''} ${props.customer?.last_name || ''}`.trim() || 'Customer',
    phone: props.customer?.phone || '',
    email: props.customer?.email || '',
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
        .then(() => {
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
    }, 3200)
}
</script>

<template>
    <Head :title="`${storeName} — Customer Dashboard`" />

    <!-- Hidden file input for Avatar upload -->
    <input
        ref="avatarInput"
        type="file"
        accept="image/*"
        class="hidden"
        @change="handleAvatarUpload"
    />

    <div class="min-h-screen bg-[#F8FAFC] text-gray-800 font-sans flex flex-col antialiased overflow-x-hidden">
        <!-- ════════════════════════════════════════
             TOP NAVIGATION BAR (RESPONSIVE)
        ════════════════════════════════════════ -->
        <header class="bg-white border-b border-gray-100 sticky top-0 z-30 shadow-2xs">
            <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 h-14 sm:h-16 flex items-center justify-between gap-2">
                <!-- Brand logo & back to store -->
                <div class="flex items-center gap-2 sm:gap-3.5 min-w-0">
                    <Link href="/" class="flex items-center gap-2 group shrink-0">
                        <img v-if="store.logo_url" :src="store.logo_url" :alt="storeName" class="h-7 sm:h-9 w-auto object-contain max-w-[100px] sm:max-w-[140px]" />
                        <div v-else class="flex items-center gap-1.5 sm:gap-2">
                            <div class="w-7 h-7 sm:w-9 sm:h-9 rounded-lg sm:rounded-xl bg-primary flex items-center justify-center text-white font-extrabold text-sm sm:text-lg shadow-xs">
                                💊
                            </div>
                            <span class="text-sm sm:text-lg font-extrabold text-gray-900 tracking-tight truncate max-w-[100px] sm:max-w-none">{{ storeName }}</span>
                        </div>
                    </Link>

                    <span class="hidden md:inline-block text-gray-300">/</span>
                    <span class="hidden md:inline-block text-[13px] font-semibold text-gray-500 truncate">Patient &amp; Customer Portal</span>
                </div>

                <!-- Right actions (Touch-friendly, compact on mobile) -->
                <div class="flex items-center gap-1.5 sm:gap-2.5 shrink-0">
                    <button
                        type="button"
                        @click="showUploadRxModal = true"
                        class="flex items-center gap-1 text-xs sm:text-[13px] font-bold text-white bg-primary hover:bg-primary2 px-2.5 sm:px-3.5 py-1.5 sm:py-2 rounded-xl transition-colors cursor-pointer shadow-xs shrink-0"
                    >
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M17 8l-5-5-5 5M12 3v12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        <span class="hidden sm:inline">Upload </span><span>Rx</span>
                    </button>

                    <Link
                        href="/"
                        title="Back to Storefront"
                        class="flex items-center gap-1 text-xs sm:text-[13px] font-semibold text-gray-600 hover:text-primary px-2.5 sm:px-3 py-1.5 sm:py-2 rounded-xl border border-gray-200 hover:border-primary/30 transition-colors shrink-0"
                    >
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        <span class="hidden sm:inline">Store</span>
                    </Link>

                    <button
                        type="button"
                        @click="logout"
                        title="Logout"
                        class="text-xs sm:text-[13px] font-semibold text-red-500 hover:text-red-600 hover:bg-red-50 p-1.5 sm:px-2.5 sm:py-2 rounded-xl transition-colors cursor-pointer flex items-center gap-1 shrink-0"
                    >
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        <span class="hidden sm:inline">Logout</span>
                    </button>
                </div>
            </div>
        </header>

        <!-- ════════════════════════════════════════
             STICKY MOBILE TAB BAR (< LG)
             (Keeps tabs accessible anywhere while scrolling long pages)
        ════════════════════════════════════════ -->
        <div class="lg:hidden sticky top-14 z-20 bg-white/95 backdrop-blur-md border-b border-gray-200/70 shadow-2xs px-3 sm:px-6 py-2">
            <nav class="flex gap-1.5 overflow-x-auto no-scrollbar scroll-smooth touch-pan-x">
                <button
                    type="button"
                    @click="setTab('overview')"
                    class="shrink-0 text-xs font-bold px-3 py-1.5 rounded-xl border transition-all cursor-pointer flex items-center gap-1.5"
                    :class="currentTab === 'overview' ? 'bg-primary text-white border-primary shadow-xs' : 'text-gray-600 border-gray-200 bg-white active:bg-gray-100'"
                >
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><rect x="3" y="3" width="7" height="9" rx="1.5" stroke="currentColor" stroke-width="2"/><rect x="14" y="3" width="7" height="5" rx="1.5" stroke="currentColor" stroke-width="2"/><rect x="14" y="12" width="7" height="9" rx="1.5" stroke="currentColor" stroke-width="2"/><rect x="3" y="16" width="7" height="5" rx="1.5" stroke="currentColor" stroke-width="2"/></svg>
                    <span>Dashboard</span>
                </button>

                <button
                    type="button"
                    @click="setTab('orders')"
                    class="shrink-0 text-xs font-bold px-3 py-1.5 rounded-xl border transition-all cursor-pointer flex items-center gap-1.5"
                    :class="currentTab === 'orders' ? 'bg-primary text-white border-primary shadow-xs' : 'text-gray-600 border-gray-200 bg-white active:bg-gray-100'"
                >
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><path d="M20 7h-3V6a4 4 0 00-8 0v1H6a1 1 0 00-1 1v11a2 2 0 002 2h10a2 2 0 002-2V8a1 1 0 00-1-1z" stroke="currentColor" stroke-width="2"/></svg>
                    <span>Orders</span>
                    <span class="text-[10px] px-1.5 py-0.2 rounded-full font-extrabold" :class="currentTab === 'orders' ? 'bg-white text-primary' : 'bg-gray-100 text-gray-700'">{{ ordersList.length }}</span>
                </button>

                <button
                    type="button"
                    @click="setTab('prescriptions')"
                    class="shrink-0 text-xs font-bold px-3 py-1.5 rounded-xl border transition-all cursor-pointer flex items-center gap-1.5"
                    :class="currentTab === 'prescriptions' ? 'bg-primary text-white border-primary shadow-xs' : 'text-gray-600 border-gray-200 bg-white active:bg-gray-100'"
                >
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><path d="M9 12h6M12 9v6M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="2"/></svg>
                    <span>Prescriptions</span>
                    <span v-if="stats.active_prescriptions > 0" class="text-[10px] px-1.5 py-0.2 rounded-full font-extrabold bg-amber-500 text-white">{{ stats.active_prescriptions }}</span>
                </button>

                <button
                    type="button"
                    @click="setTab('addresses')"
                    class="shrink-0 text-xs font-bold px-3 py-1.5 rounded-xl border transition-all cursor-pointer flex items-center gap-1.5"
                    :class="currentTab === 'addresses' ? 'bg-primary text-white border-primary shadow-xs' : 'text-gray-600 border-gray-200 bg-white active:bg-gray-100'"
                >
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><path d="M21 10c0 6-9 12-9 12S3 16 3 10a9 9 0 0118 0z" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="10" r="3" stroke="currentColor" stroke-width="2"/></svg>
                    <span>Addresses</span>
                    <span class="text-[10px] px-1.5 py-0.2 rounded-full font-extrabold" :class="currentTab === 'addresses' ? 'bg-white text-primary' : 'bg-gray-100 text-gray-700'">{{ addressesList.length }}</span>
                </button>

                <button
                    type="button"
                    @click="setTab('transactions')"
                    class="shrink-0 text-xs font-bold px-3 py-1.5 rounded-xl border transition-all cursor-pointer flex items-center gap-1.5"
                    :class="currentTab === 'transactions' ? 'bg-primary text-white border-primary shadow-xs' : 'text-gray-600 border-gray-200 bg-white active:bg-gray-100'"
                >
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><rect x="2" y="5" width="20" height="14" rx="2" stroke="currentColor" stroke-width="2"/><path d="M2 10h20" stroke="currentColor" stroke-width="2"/></svg>
                    <span>Payments</span>
                </button>

                <button
                    type="button"
                    @click="setTab('coupons')"
                    class="shrink-0 text-xs font-bold px-3 py-1.5 rounded-xl border transition-all cursor-pointer flex items-center gap-1.5"
                    :class="currentTab === 'coupons' ? 'bg-primary text-white border-primary shadow-xs' : 'text-gray-600 border-gray-200 bg-white active:bg-gray-100'"
                >
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z" stroke="currentColor" stroke-width="2"/></svg>
                    <span>Coupons</span>
                    <span v-if="couponsList.length > 0" class="text-[10px] px-1.5 py-0.2 rounded-full font-extrabold bg-emerald-500 text-white">{{ couponsList.length }}</span>
                </button>

                <button
                    type="button"
                    @click="setTab('wishlist')"
                    class="shrink-0 text-xs font-bold px-3 py-1.5 rounded-xl border transition-all cursor-pointer flex items-center gap-1.5"
                    :class="currentTab === 'wishlist' ? 'bg-primary text-white border-primary shadow-xs' : 'text-gray-600 border-gray-200 bg-white active:bg-gray-100'"
                >
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><path d="M20.8 4.6a5.5 5.5 0 00-7.8 0L12 5.6l-1-1a5.5 5.5 0 00-7.8 7.8l1 1L12 21l7.8-7.6 1-1a5.5 5.5 0 000-7.8z" stroke="currentColor" stroke-width="2"/></svg>
                    <span>Wishlist</span>
                    <span class="text-[10px] px-1.5 py-0.2 rounded-full font-extrabold" :class="currentTab === 'wishlist' ? 'bg-white text-primary' : 'bg-gray-100 text-gray-700'">{{ wishlistList.length }}</span>
                </button>

                <button
                    type="button"
                    @click="setTab('settings')"
                    class="shrink-0 text-xs font-bold px-3 py-1.5 rounded-xl border transition-all cursor-pointer flex items-center gap-1.5"
                    :class="currentTab === 'settings' ? 'bg-primary text-white border-primary shadow-xs' : 'text-gray-600 border-gray-200 bg-white active:bg-gray-100'"
                >
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 11-2.83 2.83l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 11-2.83-2.83l.06-.06A1.65 1.65 0 004.6 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 112.83-2.83l.06.06A1.65 1.65 0 009 4.6a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09A1.65 1.65 0 0015 4.6a1.65 1.65 0 001.82-.33l.06-.06a2 2 0 112.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z" stroke="currentColor" stroke-width="2"/></svg>
                    <span>Settings</span>
                </button>
            </nav>
        </div>

        <!-- ════════════════════════════════════════
             MAIN CONTENT LAYOUT (MOBILE-FIRST)
        ════════════════════════════════════════ -->
        <main class="flex-1 max-w-7xl w-full mx-auto px-3 sm:px-6 lg:px-8 py-3.5 sm:py-6 lg:py-8">
            <div class="grid lg:grid-cols-12 gap-4 sm:gap-6 items-start">
                <!-- ── SIDEBAR / PROFILE CARD ── -->
                <aside class="lg:col-span-3">
                    <!-- Profile Card with Avatar Upload Trigger -->
                    <div class="bg-white rounded-2xl border border-gray-100 p-3.5 sm:p-5 flex lg:flex-col items-center lg:text-center gap-3 sm:gap-4 lg:gap-0 shadow-xs relative">
                        <!-- Avatar with Camera Badge -->
                        <div class="relative group shrink-0 lg:mb-3">
                            <div
                                class="w-14 h-14 sm:w-16 sm:h-16 lg:w-20 lg:h-20 rounded-full overflow-hidden ring-2 ring-primarylt bg-primary/10 flex items-center justify-center cursor-pointer relative"
                                @click="triggerAvatarSelect"
                                title="Click to change profile photo"
                            >
                                <img
                                    v-if="customerProfile.avatar && !avatarImgError"
                                    :src="customerProfile.avatar"
                                    :alt="customerProfile.name"
                                    @error="avatarImgError = true"
                                    class="w-full h-full object-cover transition-transform group-hover:scale-105 duration-200"
                                />
                                <span v-else class="text-xl sm:text-2xl font-bold text-primary">
                                    {{ customerProfile.first_name?.[0] || 'C' }}
                                </span>

                                <!-- Uploading spinner overlay -->
                                <div v-if="avatarUploading" class="absolute inset-0 bg-black/50 flex items-center justify-center">
                                    <svg class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                </div>
                            </div>

                            <!-- Camera Action Badge button -->
                            <button
                                type="button"
                                @click.stop="triggerAvatarSelect"
                                class="absolute -bottom-1 -right-1 w-6 h-6 sm:w-7 sm:h-7 rounded-full bg-primary hover:bg-primary2 text-white flex items-center justify-center shadow-md transition-transform hover:scale-110 cursor-pointer"
                                title="Change Profile Photo"
                            >
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none"><path d="M23 19a2 2 0 01-2 2H3a2 2 0 01-2-2V8a2 2 0 012-2h4l2-3h6l2 3h4a2 2 0 012 2z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><circle cx="12" cy="13" r="4" stroke="currentColor" stroke-width="2"/></svg>
                            </button>
                        </div>

                        <!-- Name and info -->
                        <div class="min-w-0 flex-1 lg:flex-initial">
                            <p class="text-sm sm:text-base font-bold text-gray-900 truncate">{{ customerProfile.name }}</p>
                            <p class="text-xs sm:text-[13px] text-gray-400 truncate">{{ customerProfile.phone }}</p>
                            <span class="inline-block mt-1 text-[11px] font-semibold text-accent bg-accentlt px-2 py-0.5 rounded-full">
                                {{ customerProfile.membership || '🏅 Verified Member' }}
                            </span>
                        </div>
                    </div>

                    <!-- Desktop navigation (Hidden on < lg) -->
                    <nav class="hidden lg:block bg-white rounded-2xl border border-gray-100 p-2 space-y-1 shadow-xs mt-4">
                        <button
                            type="button"
                            @click="setTab('overview')"
                            class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-[14px] transition-colors cursor-pointer text-left"
                            :class="currentTab === 'overview' ? 'bg-primarylt text-primary font-semibold' : 'text-gray-600 hover:bg-gray-50'"
                        >
                            <div class="flex items-center gap-3">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><rect x="3" y="3" width="7" height="9" rx="1.5" stroke="currentColor" stroke-width="2"/><rect x="14" y="3" width="7" height="5" rx="1.5" stroke="currentColor" stroke-width="2"/><rect x="14" y="12" width="7" height="9" rx="1.5" stroke="currentColor" stroke-width="2"/><rect x="3" y="16" width="7" height="5" rx="1.5" stroke="currentColor" stroke-width="2"/></svg>
                                <span>Dashboard</span>
                            </div>
                        </button>

                        <button
                            type="button"
                            @click="setTab('orders')"
                            class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-[14px] transition-colors cursor-pointer text-left"
                            :class="currentTab === 'orders' ? 'bg-primarylt text-primary font-semibold' : 'text-gray-600 hover:bg-gray-50'"
                        >
                            <div class="flex items-center gap-3">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M20 7h-3V6a4 4 0 00-8 0v1H6a1 1 0 00-1 1v11a2 2 0 002 2h10a2 2 0 002-2V8a1 1 0 00-1-1z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                <span>My Orders</span>
                            </div>
                            <span class="text-[11.5px] font-bold px-2 py-0.5 rounded-full" :class="currentTab === 'orders' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-600'">
                                {{ ordersList.length }}
                            </span>
                        </button>

                        <button
                            type="button"
                            @click="setTab('prescriptions')"
                            class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-[14px] transition-colors cursor-pointer text-left"
                            :class="currentTab === 'prescriptions' ? 'bg-primarylt text-primary font-semibold' : 'text-gray-600 hover:bg-gray-50'"
                        >
                            <div class="flex items-center gap-3">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M9 12h6M12 9v6M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                <span>My Prescriptions</span>
                            </div>
                            <span v-if="stats.active_prescriptions > 0" class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-amber-500 text-white">
                                {{ stats.active_prescriptions }}
                            </span>
                            <span v-else class="text-[11.5px] font-semibold text-gray-400">
                                {{ prescriptionsList.length }}
                            </span>
                        </button>

                        <button
                            type="button"
                            @click="setTab('addresses')"
                            class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-[14px] transition-colors cursor-pointer text-left"
                            :class="currentTab === 'addresses' ? 'bg-primarylt text-primary font-semibold' : 'text-gray-600 hover:bg-gray-50'"
                        >
                            <div class="flex items-center gap-3">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M21 10c0 6-9 12-9 12S3 16 3 10a9 9 0 0118 0z" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="10" r="3" stroke="currentColor" stroke-width="2"/></svg>
                                <span>Saved Addresses</span>
                            </div>
                            <span class="text-[11.5px] text-gray-400 font-semibold">{{ addressesList.length }}</span>
                        </button>

                        <button
                            type="button"
                            @click="setTab('transactions')"
                            class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-[14px] transition-colors cursor-pointer text-left"
                            :class="currentTab === 'transactions' ? 'bg-primarylt text-primary font-semibold' : 'text-gray-600 hover:bg-gray-50'"
                        >
                            <div class="flex items-center gap-3">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><rect x="2" y="5" width="20" height="14" rx="2" stroke="currentColor" stroke-width="2"/><path d="M2 10h20" stroke="currentColor" stroke-width="2"/></svg>
                                <span>Payments &amp; Transactions</span>
                            </div>
                            <span class="text-[11.5px] text-gray-400 font-semibold">{{ transactionsList.length }}</span>
                        </button>

                        <button
                            type="button"
                            @click="setTab('coupons')"
                            class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-[14px] transition-colors cursor-pointer text-left"
                            :class="currentTab === 'coupons' ? 'bg-primarylt text-primary font-semibold' : 'text-gray-600 hover:bg-gray-50'"
                        >
                            <div class="flex items-center gap-3">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z" stroke="currentColor" stroke-width="2"/><circle cx="7" cy="7" r="1.5" fill="currentColor"/></svg>
                                <span>Coupons &amp; Offers</span>
                            </div>
                            <span v-if="couponsList.length > 0" class="text-[11px] font-bold px-2 py-0.5 rounded-full bg-emerald-500 text-white">
                                {{ couponsList.length }}
                            </span>
                        </button>

                        <button
                            type="button"
                            @click="setTab('wishlist')"
                            class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-[14px] transition-colors cursor-pointer text-left"
                            :class="currentTab === 'wishlist' ? 'bg-primarylt text-primary font-semibold' : 'text-gray-600 hover:bg-gray-50'"
                        >
                            <div class="flex items-center gap-3">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M20.8 4.6a5.5 5.5 0 00-7.8 0L12 5.6l-1-1a5.5 5.5 0 00-7.8 7.8l1 1L12 21l7.8-7.6 1-1a5.5 5.5 0 000-7.8z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                <span>Wishlist</span>
                            </div>
                            <span class="text-[11.5px] text-gray-400 font-semibold">{{ wishlistList.length }}</span>
                        </button>

                        <button
                            type="button"
                            @click="setTab('settings')"
                            class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-[14px] transition-colors cursor-pointer text-left"
                            :class="currentTab === 'settings' ? 'bg-primarylt text-primary font-semibold' : 'text-gray-600 hover:bg-gray-50'"
                        >
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 11-2.83 2.83l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 11-2.83-2.83l.06-.06A1.65 1.65 0 004.6 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 112.83-2.83l.06.06A1.65 1.65 0 009 4.6a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09A1.65 1.65 0 0015 4.6a1.65 1.65 0 001.82-.33l.06-.06a2 2 0 112.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            <span>Account Settings</span>
                        </button>
                    </nav>
                </aside>

                <!-- ── MAIN CONTENT AREA ── -->
                <section class="lg:col-span-9 min-w-0">
                    <!-- ════════════════════════════════════════
                         TAB 1: OVERVIEW / DASHBOARD
                    ════════════════════════════════════════ -->
                    <div v-show="currentTab === 'overview'" class="space-y-3.5 sm:space-y-6">
                        <!-- Welcome Header with responsive mobile typography -->
                        <div class="bg-gradient-to-r from-primary/10 via-primarylt/30 to-white border border-primary/15 rounded-2xl sm:rounded-3xl p-3.5 sm:p-6 lg:p-7 relative overflow-hidden">
                            <div class="relative z-10 max-w-xl">
                                <span class="inline-block text-[11px] sm:text-xs font-bold uppercase tracking-wider text-primary bg-white/90 backdrop-blur-xs px-2.5 sm:px-3 py-0.5 sm:py-1 rounded-full mb-1.5 sm:mb-2 shadow-2xs">
                                    🏥 {{ storeName }} Patient Portal
                                </span>
                                <h1 class="text-lg sm:text-2xl lg:text-3xl font-extrabold text-gray-900 tracking-tight leading-snug">
                                    Welcome, {{ customerProfile.first_name || 'Customer' }}!
                                </h1>
                                <p class="text-xs sm:text-sm text-gray-600 mt-1 leading-relaxed">
                                    Track medicine deliveries, check prescription review statuses, and manage your health supplies.
                                </p>

                                <div class="flex flex-col sm:flex-row gap-2 sm:gap-3 mt-3 sm:mt-4">
                                    <button
                                        type="button"
                                        @click="showUploadRxModal = true"
                                        class="w-full sm:w-auto justify-center inline-flex items-center gap-2 bg-primary hover:bg-primary2 text-white font-bold text-xs sm:text-[13.5px] px-4 py-2.5 rounded-xl transition-colors cursor-pointer shadow-xs"
                                    >
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M17 8l-5-5-5 5M12 3v12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        <span>Upload Prescription</span>
                                    </button>

                                    <button
                                        type="button"
                                        @click="setTab('orders')"
                                        class="w-full sm:w-auto justify-center inline-flex items-center gap-1.5 bg-white hover:bg-gray-50 text-gray-700 font-semibold text-xs sm:text-[13.5px] px-4 py-2.5 rounded-xl border border-gray-200 transition-colors cursor-pointer shadow-2xs"
                                    >
                                        <span>View Orders</span>
                                        <span>→</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- 4 Dynamic Stat Cards (Fluid 2x2 grid on mobile) -->
                        <div class="grid grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-4">
                            <!-- Total Orders & Spent -->
                            <div class="bg-white rounded-2xl border border-gray-100 p-3 sm:p-4 shadow-xs min-w-0">
                                <div class="w-7 h-7 sm:w-9 sm:h-9 rounded-lg bg-primarylt flex items-center justify-center mb-1.5 sm:mb-2">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" class="text-primary"><path d="M20 7h-3V6a4 4 0 00-8 0v1H6a1 1 0 00-1 1v11a2 2 0 002 2h10a2 2 0 002-2V8a1 1 0 00-1-1z" stroke="currentColor" stroke-width="2"/></svg>
                                </div>
                                <p class="text-lg sm:text-2xl font-extrabold text-gray-900 truncate">{{ stats.total_orders }}</p>
                                <p class="text-[10.5px] sm:text-xs text-gray-400 truncate">Orders · {{ currencySymbol }}{{ stats.total_spent?.toLocaleString() }}</p>
                            </div>

                            <!-- Prescriptions -->
                            <div class="bg-white rounded-2xl border border-gray-100 p-3 sm:p-4 shadow-xs cursor-pointer hover:border-amber-200 transition-colors min-w-0" @click="setTab('prescriptions')">
                                <div class="w-7 h-7 sm:w-9 sm:h-9 rounded-lg bg-amber-50 flex items-center justify-center mb-1.5 sm:mb-2">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" class="text-amber-600"><path d="M9 12h6M12 9v6M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke="currentColor" stroke-width="2"/></svg>
                                </div>
                                <p class="text-lg sm:text-2xl font-extrabold text-amber-700 truncate">{{ stats.active_prescriptions }}</p>
                                <p class="text-[10.5px] sm:text-xs text-gray-400 truncate">Active Prescriptions</p>
                            </div>

                            <!-- Saved Addresses -->
                            <div class="bg-white rounded-2xl border border-gray-100 p-3 sm:p-4 shadow-xs cursor-pointer hover:border-blue-200 transition-colors min-w-0" @click="setTab('addresses')">
                                <div class="w-7 h-7 sm:w-9 sm:h-9 rounded-lg bg-blue-50 flex items-center justify-center mb-1.5 sm:mb-2">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" class="text-blue-600"><path d="M21 10c0 6-9 12-9 12S3 16 3 10a9 9 0 0118 0z" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="10" r="3" stroke="currentColor" stroke-width="2"/></svg>
                                </div>
                                <p class="text-lg sm:text-2xl font-extrabold text-gray-900 truncate">{{ addressesList.length }}</p>
                                <p class="text-[10.5px] sm:text-xs text-gray-400 truncate">Saved Addresses</p>
                            </div>

                            <!-- Available Store Coupons -->
                            <div class="bg-white rounded-2xl border border-gray-100 p-3 sm:p-4 shadow-xs cursor-pointer hover:border-emerald-200 transition-colors min-w-0" @click="setTab('coupons')">
                                <div class="w-7 h-7 sm:w-9 sm:h-9 rounded-lg bg-emerald-50 flex items-center justify-center mb-1.5 sm:mb-2">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" class="text-emerald-600"><path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z" stroke="currentColor" stroke-width="2"/><circle cx="7" cy="7" r="1.5" fill="currentColor"/></svg>
                                </div>
                                <p class="text-lg sm:text-2xl font-extrabold text-emerald-700 truncate">{{ couponsList.length }}</p>
                                <p class="text-[10.5px] sm:text-xs text-gray-400 truncate">Store Coupons</p>
                            </div>
                        </div>

                        <!-- Recent Orders Preview -->
                        <div class="bg-white rounded-2xl border border-gray-100 p-3.5 sm:p-5 shadow-xs">
                            <div class="flex items-center justify-between mb-3.5">
                                <h2 class="text-sm sm:text-base font-bold text-gray-900">Recent Orders</h2>
                                <button
                                    type="button"
                                    @click="setTab('orders')"
                                    class="text-xs sm:text-[13px] font-semibold text-primary hover:underline cursor-pointer"
                                >
                                    View all ({{ ordersList.length }})
                                </button>
                            </div>

                            <div v-if="ordersList.length === 0" class="text-center py-6 sm:py-8">
                                <p class="text-gray-400 text-xs sm:text-sm">No orders placed yet.</p>
                                <Link href="/" class="inline-block mt-2 text-xs sm:text-sm font-semibold text-primary hover:underline">Explore Medicines &amp; Products →</Link>
                            </div>

                            <div v-else class="space-y-2.5 sm:space-y-3">
                                <div
                                    v-for="order in ordersList.slice(0, 4)"
                                    :key="order.id"
                                    class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 sm:gap-3 border border-gray-100 rounded-xl p-3 sm:p-4 hover:border-gray-200 transition-colors"
                                >
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-1.5 sm:gap-2 flex-wrap">
                                            <p class="text-sm sm:text-base font-bold text-gray-900">#{{ order.id }}</p>
                                            <span v-if="order.prescription_status" class="text-[10px] font-semibold px-1.5 py-0.2 rounded bg-purple-50 text-purple-700 border border-purple-200">
                                                💊 Rx
                                            </span>
                                            <span class="text-[11px] font-semibold px-2 py-0.2 rounded-md sm:hidden" :class="order.color">{{ order.status }}</span>
                                        </div>
                                        <p class="text-[11.5px] sm:text-xs text-gray-400 mt-0.5 truncate">
                                            {{ order.date }} · {{ order.items_count }} item{{ order.items_count > 1 ? 's' : '' }} · {{ order.payment_method }}
                                        </p>
                                    </div>

                                    <div class="flex items-center justify-between sm:justify-end gap-2.5 shrink-0 pt-2 sm:pt-0 border-t sm:border-t-0 border-gray-50">
                                        <div class="hidden sm:flex items-center gap-1.5">
                                            <span class="text-[11.5px] font-semibold px-2 py-0.5 rounded-md" :class="order.color">{{ order.status }}</span>
                                            <span class="text-[11.5px] font-semibold px-2 py-0.5 rounded-md" :class="order.payment_color">{{ order.payment_status }}</span>
                                        </div>
                                        <span class="text-sm sm:text-base font-bold text-gray-900">{{ currencySymbol }}{{ order.total?.toLocaleString() }}</span>
                                        <button
                                            type="button"
                                            @click="openOrderModal(order)"
                                            class="text-xs sm:text-[12.5px] font-semibold text-primary hover:underline whitespace-nowrap cursor-pointer px-2.5 py-1 rounded-lg hover:bg-primarylt transition-colors"
                                        >
                                            Details
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 2-Column: Quick Address + Active Prescriptions -->
                        <div class="grid sm:grid-cols-2 gap-3.5 sm:gap-5">
                            <!-- Default Address -->
                            <div class="bg-white rounded-2xl border border-gray-100 p-3.5 sm:p-5 shadow-xs flex flex-col justify-between">
                                <div>
                                    <div class="flex items-center justify-between mb-2 sm:mb-3">
                                        <h2 class="text-sm sm:text-base font-bold text-gray-900">Default Address</h2>
                                        <button
                                            type="button"
                                            @click="setTab('addresses')"
                                            class="text-xs sm:text-[12.5px] font-semibold text-primary hover:underline cursor-pointer"
                                        >Manage</button>
                                    </div>
                                    <span class="inline-block bg-primarylt text-primary text-[10.5px] sm:text-[11px] font-semibold px-2 py-0.5 rounded-md mb-1.5">
                                        {{ addressesList[0]?.title || 'Home' }}
                                    </span>
                                    <p class="text-sm sm:text-base font-bold text-gray-900">{{ addressesList[0]?.name || customerProfile.name }}</p>
                                    <p class="text-xs sm:text-[13px] text-gray-500 mt-0.5 leading-snug">
                                        {{ addressesList[0]?.formatted || addressesList[0]?.address_1 || 'No address added yet.' }}
                                    </p>
                                </div>
                                <button
                                    type="button"
                                    @click="showAddAddressModal = true"
                                    class="mt-3.5 text-xs sm:text-[12.5px] font-semibold text-primary hover:underline text-left cursor-pointer"
                                >
                                    + Add another address
                                </button>
                            </div>

                            <!-- Active Prescriptions -->
                            <div class="bg-white rounded-2xl border border-gray-100 p-3.5 sm:p-5 shadow-xs flex flex-col justify-between">
                                <div>
                                    <div class="flex items-center justify-between mb-2 sm:mb-3">
                                        <h2 class="text-sm sm:text-base font-bold text-gray-900">Recent Prescriptions</h2>
                                        <button
                                            type="button"
                                            @click="setTab('prescriptions')"
                                            class="text-xs sm:text-[12.5px] font-semibold text-primary hover:underline cursor-pointer"
                                        >View all</button>
                                    </div>

                                    <div v-if="prescriptionsList.length === 0" class="text-center py-3">
                                        <p class="text-gray-400 text-xs">No prescriptions uploaded yet.</p>
                                    </div>

                                    <div v-else class="space-y-2">
                                        <div
                                            v-for="rx in prescriptionsList.slice(0, 2)"
                                            :key="rx.id"
                                            class="flex items-center gap-2.5 sm:gap-3 py-1 border-b border-gray-50 last:border-none"
                                        >
                                            <div
                                                class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-gray-100 overflow-hidden shrink-0 cursor-pointer border border-gray-200"
                                                @click="openRxLightbox(rx.image_url)"
                                            >
                                                <img v-if="rx.image_url" :src="rx.image_url" :alt="rx.prescription_number" class="w-full h-full object-cover"/>
                                                <div v-else class="w-full h-full flex items-center justify-center text-xs text-gray-400">Rx</div>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-xs sm:text-[13.5px] font-bold text-gray-800 truncate">#{{ rx.prescription_number }}</p>
                                                <p class="text-[11px] sm:text-xs text-gray-400">{{ rx.date }}</p>
                                            </div>
                                            <span class="text-[10.5px] font-semibold px-2 py-0.5 rounded-md shrink-0" :class="rx.color">
                                                {{ rx.status_badge }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <button
                                    type="button"
                                    @click="showUploadRxModal = true"
                                    class="mt-3.5 text-xs sm:text-[12.5px] font-semibold text-primary hover:underline text-left cursor-pointer"
                                >
                                    + Upload prescription scan
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- ════════════════════════════════════════
                         TAB 2: MY ORDERS
                    ════════════════════════════════════════ -->
                    <div v-show="currentTab === 'orders'" class="space-y-3.5 sm:space-y-4">
                        <div class="bg-white rounded-2xl border border-gray-100 p-3.5 sm:p-5 shadow-xs">
                            <div class="flex items-center justify-between mb-3.5">
                                <div>
                                    <h2 class="text-base sm:text-lg font-bold text-gray-900">My Orders</h2>
                                    <p class="text-xs sm:text-[12.5px] text-gray-400">{{ filteredOrders.length }} orders found in your account</p>
                                </div>
                            </div>

                            <!-- Filter buttons -->
                            <div class="flex flex-wrap gap-1.5 sm:gap-2 pb-3 mb-4 border-b border-gray-100">
                                <button
                                    v-for="opt in filterOptions"
                                    :key="opt"
                                    type="button"
                                    @click="orderFilter = opt"
                                    class="text-xs sm:text-[13px] font-semibold px-3 py-1.5 rounded-xl border transition-colors cursor-pointer"
                                    :class="orderFilter === opt ? 'bg-primary text-white border-primary shadow-xs' : 'text-gray-600 border-gray-200 bg-white hover:bg-gray-50'"
                                >
                                    {{ opt }}
                                </button>
                            </div>

                            <!-- Orders list -->
                            <div v-if="filteredOrders.length === 0" class="text-center py-10">
                                <p class="text-gray-400 text-xs sm:text-sm">No orders matching this filter.</p>
                            </div>

                            <div v-else class="space-y-3 sm:space-y-3.5">
                                <div
                                    v-for="order in filteredOrders"
                                    :key="order.id"
                                    class="border border-gray-100 hover:border-gray-200 rounded-2xl p-3.5 sm:p-5 transition-all shadow-2xs"
                                >
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-2.5 sm:pb-3 border-b border-gray-100">
                                        <div>
                                            <div class="flex items-center gap-1.5 sm:gap-2 flex-wrap">
                                                <p class="text-sm sm:text-base font-extrabold text-gray-900">#{{ order.id }}</p>
                                                <span v-if="order.prescription_status" class="text-[10px] font-semibold px-2 py-0.5 rounded-md bg-purple-50 text-purple-700 border border-purple-200">
                                                    💊 Rx Verified
                                                </span>
                                            </div>
                                            <p class="text-[11.5px] sm:text-xs text-gray-400 mt-0.5">Placed on {{ order.date }} · Method: {{ order.payment_method }}</p>
                                        </div>

                                        <div class="flex items-center gap-1.5 sm:gap-2 flex-wrap">
                                            <span class="text-[11px] sm:text-[11.5px] font-bold px-2.5 py-0.5 rounded-lg" :class="order.color">{{ order.status }}</span>
                                            <span class="text-[11px] sm:text-[11.5px] font-bold px-2 py-0.5 rounded-lg" :class="order.payment_color">{{ order.payment_status }}</span>
                                        </div>
                                    </div>

                                    <!-- Products preview snippet -->
                                    <div class="py-2.5 sm:py-3 space-y-2">
                                        <div v-for="item in order.items.slice(0, 3)" :key="item.id" class="flex items-center justify-between text-xs sm:text-[13.5px] gap-2">
                                            <div class="flex items-center gap-1.5 min-w-0">
                                                <span class="text-gray-400">·</span>
                                                <span class="font-medium text-gray-800 truncate">{{ item.name }}</span>
                                                <span v-if="item.variant" class="text-gray-400 text-xs shrink-0">({{ item.variant }})</span>
                                                <span v-if="item.requires_prescription" class="text-[10px] bg-amber-50 text-amber-700 font-bold px-1.5 py-0.2 rounded border border-amber-200 shrink-0">Rx</span>
                                            </div>
                                            <span class="font-semibold text-gray-700 shrink-0 text-xs sm:text-[13px]">Qty: {{ item.quantity }} · {{ currencySymbol }}{{ item.line_total?.toLocaleString() }}</span>
                                        </div>
                                        <p v-if="order.items.length > 3" class="text-[11.5px] text-gray-400 font-medium">
                                            + {{ order.items.length - 3 }} more item{{ (order.items.length - 3) > 1 ? 's' : '' }}
                                        </p>
                                    </div>

                                    <!-- Footer info & button (responsive stack on mobile) -->
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 pt-2.5 sm:pt-3 border-t border-gray-100">
                                        <div>
                                            <span class="text-xs text-gray-400">Total: </span>
                                            <span class="text-sm sm:text-base font-extrabold text-gray-900">{{ currencySymbol }}{{ order.total?.toLocaleString() }}</span>
                                        </div>

                                        <button
                                            type="button"
                                            @click="openOrderModal(order)"
                                            class="w-full sm:w-auto text-center justify-center inline-flex items-center gap-1.5 text-xs sm:text-[13px] font-bold text-primary bg-primarylt hover:bg-primary/20 px-3.5 py-2 rounded-xl transition-colors cursor-pointer"
                                        >
                                            <span>View Receipt &amp; Tracking</span>
                                            <span>→</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ════════════════════════════════════════
                         TAB 3: MY PRESCRIPTIONS
                    ════════════════════════════════════════ -->
                    <div v-show="currentTab === 'prescriptions'" class="space-y-3.5 sm:space-y-4">
                        <div class="bg-white rounded-2xl border border-gray-100 p-3.5 sm:p-5 shadow-xs">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
                                <div>
                                    <h2 class="text-base sm:text-lg font-bold text-gray-900">My Prescriptions</h2>
                                    <p class="text-xs sm:text-[12.5px] text-gray-400">Prescription uploads reviewed by {{ storeName }} licensed pharmacists</p>
                                </div>

                                <button
                                    type="button"
                                    @click="showUploadRxModal = true"
                                    class="w-full sm:w-auto justify-center inline-flex items-center gap-1.5 text-xs sm:text-[13px] font-bold text-white bg-primary hover:bg-primary2 px-4 py-2.5 rounded-xl transition-colors cursor-pointer shadow-xs"
                                >
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    <span>Upload Prescription</span>
                                </button>
                            </div>

                            <!-- Rx Filters -->
                            <div class="flex flex-wrap gap-1.5 sm:gap-2 pb-3 mb-4 border-b border-gray-100">
                                <button
                                    type="button"
                                    @click="rxFilter = 'all'"
                                    class="text-xs sm:text-[13px] font-semibold px-3 py-1.5 rounded-xl border transition-colors cursor-pointer"
                                    :class="rxFilter === 'all' ? 'bg-primary text-white border-primary shadow-xs' : 'text-gray-600 border-gray-200 bg-white'"
                                >
                                    All ({{ prescriptionsList.length }})
                                </button>
                                <button
                                    type="button"
                                    @click="rxFilter = 'pending_review'"
                                    class="text-xs sm:text-[13px] font-semibold px-3 py-1.5 rounded-xl border transition-colors cursor-pointer"
                                    :class="rxFilter === 'pending_review' ? 'bg-amber-600 text-white border-amber-600 shadow-xs' : 'text-gray-600 border-gray-200 bg-white'"
                                >
                                    Under Review
                                </button>
                                <button
                                    type="button"
                                    @click="rxFilter = 'converted_to_order'"
                                    class="text-xs sm:text-[13px] font-semibold px-3 py-1.5 rounded-xl border transition-colors cursor-pointer"
                                    :class="rxFilter === 'converted_to_order' ? 'bg-emerald-600 text-white border-emerald-600 shadow-xs' : 'text-gray-600 border-gray-200 bg-white'"
                                >
                                    Approved
                                </button>
                                <button
                                    type="button"
                                    @click="rxFilter = 'rejected'"
                                    class="text-xs sm:text-[13px] font-semibold px-3 py-1.5 rounded-xl border transition-colors cursor-pointer"
                                    :class="rxFilter === 'rejected' ? 'bg-rose-600 text-white border-rose-600 shadow-xs' : 'text-gray-600 border-gray-200 bg-white'"
                                >
                                    Declined
                                </button>
                            </div>

                            <!-- Prescriptions List -->
                            <div v-if="filteredPrescriptions.length === 0" class="text-center py-10">
                                <p class="text-gray-800 font-bold text-sm">No prescriptions found.</p>
                                <button
                                    type="button"
                                    @click="showUploadRxModal = true"
                                    class="mt-3 text-[13px] font-bold text-primary hover:underline cursor-pointer"
                                >
                                    + Upload Doctor's Prescription
                                </button>
                            </div>

                            <div v-else class="space-y-3 sm:space-y-3.5">
                                <div
                                    v-for="rx in filteredPrescriptions"
                                    :key="rx.id"
                                    class="border border-gray-100 hover:border-gray-200 rounded-2xl p-3 sm:p-5 transition-all shadow-2xs"
                                >
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                        <div class="flex items-start gap-2.5 sm:gap-3">
                                            <!-- Prescription image thumbnail -->
                                            <div
                                                class="w-12 h-12 sm:w-16 sm:h-16 rounded-xl bg-gray-50 border border-gray-200 overflow-hidden shrink-0 cursor-pointer hover:opacity-80 transition-opacity"
                                                @click="openRxLightbox(rx.image_url)"
                                                title="View full prescription scan"
                                            >
                                                <img v-if="rx.image_url" :src="rx.image_url" :alt="rx.prescription_number" class="w-full h-full object-cover" />
                                                <div v-else class="w-full h-full flex items-center justify-center text-xs font-bold text-gray-400">Rx</div>
                                            </div>

                                            <div class="min-w-0">
                                                <div class="flex items-center gap-1.5 sm:gap-2 flex-wrap">
                                                    <p class="text-sm sm:text-base font-extrabold text-gray-900">#{{ rx.prescription_number }}</p>
                                                    <span class="text-[10.5px] sm:text-[11px] font-bold px-2 py-0.5 rounded-md" :class="rx.color">
                                                        {{ rx.status_label }}
                                                    </span>
                                                </div>
                                                <p class="text-[11px] sm:text-xs text-gray-400 mt-0.5">Uploaded on {{ rx.date }}</p>

                                                <p v-if="rx.patient_notes" class="text-xs sm:text-[13px] text-gray-600 mt-1 italic">
                                                    "{{ rx.patient_notes }}"
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Converted order or view details -->
                                        <div class="flex flex-col sm:items-end gap-1.5 shrink-0 pt-2 sm:pt-0 border-t sm:border-t-0 border-gray-50">
                                            <button
                                                type="button"
                                                @click="openRxLightbox(rx.image_url)"
                                                class="text-xs sm:text-[12.5px] font-semibold text-primary hover:underline text-left sm:text-right cursor-pointer"
                                            >
                                                🔍 View Prescription Scan
                                            </button>

                                            <div v-if="rx.order_number" class="text-[11px] sm:text-[11.5px] font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-lg border border-emerald-200">
                                                Order: <strong class="font-extrabold">#{{ rx.order_number }}</strong>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Pharmacist review feedback -->
                                    <div v-if="rx.pharmacist_notes" class="mt-2.5 bg-blue-50/70 border border-blue-100 rounded-xl p-2.5 sm:p-3 text-xs sm:text-[12.5px] text-blue-900">
                                        <strong>👨‍⚕️ Pharmacist:</strong> {{ rx.pharmacist_notes }}
                                    </div>

                                    <div v-if="rx.rejection_reason" class="mt-2.5 bg-rose-50/70 border border-rose-100 rounded-xl p-2.5 sm:p-3 text-xs sm:text-[12.5px] text-rose-900">
                                        <strong>⚠️ Decline Reason:</strong> {{ rx.rejection_reason }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ════════════════════════════════════════
                         TAB 4: SAVED ADDRESSES
                    ════════════════════════════════════════ -->
                    <div v-show="currentTab === 'addresses'" class="space-y-3.5 sm:space-y-4">
                        <div class="bg-white rounded-2xl border border-gray-100 p-3.5 sm:p-5 shadow-xs">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 mb-4">
                                <div>
                                    <h2 class="text-base sm:text-lg font-bold text-gray-900">Saved Delivery Addresses</h2>
                                    <p class="text-xs sm:text-[12.5px] text-gray-400">Manage where medicines &amp; healthcare supplies are dispatched</p>
                                </div>

                                <button
                                    type="button"
                                    @click="showAddAddressModal = true"
                                    class="w-full sm:w-auto justify-center text-xs sm:text-[13px] font-bold text-white bg-primary hover:bg-primary2 px-3.5 py-2.5 rounded-xl transition-colors cursor-pointer shadow-xs"
                                >
                                    + Add New Address
                                </button>
                            </div>

                            <div v-if="addressesList.length === 0" class="text-center py-10">
                                <p class="text-gray-400 text-xs sm:text-sm">No saved addresses yet.</p>
                                <button
                                    type="button"
                                    @click="showAddAddressModal = true"
                                    class="mt-2.5 text-xs sm:text-sm font-semibold text-primary hover:underline cursor-pointer"
                                >
                                    + Add your first address
                                </button>
                            </div>

                            <div v-else class="grid sm:grid-cols-2 gap-3 sm:gap-4">
                                <div
                                    v-for="addr in addressesList"
                                    :key="addr.id"
                                    class="border rounded-2xl p-3.5 sm:p-5 flex flex-col justify-between transition-colors relative"
                                    :class="addr.is_default ? 'border-primary/40 bg-primarylt/10 shadow-xs' : 'border-gray-200 hover:border-gray-300 bg-white'"
                                >
                                    <div>
                                        <div class="flex items-center justify-between mb-1.5">
                                            <span class="text-[11px] sm:text-[11.5px] font-bold px-2.5 py-0.5 rounded-full" :class="addr.is_default ? 'bg-primary text-white' : 'bg-gray-100 text-gray-700'">
                                                {{ addr.title || 'Home' }}
                                            </span>
                                            <span v-if="addr.is_default" class="text-[11px] font-semibold text-primary">
                                                ✓ Default
                                            </span>
                                        </div>

                                        <p class="text-sm sm:text-base font-bold text-gray-900 mt-1">{{ addr.name || customerProfile.name }}</p>
                                        <p class="text-xs sm:text-[12.5px] text-gray-500 font-medium">{{ addr.phone || customerProfile.phone }}</p>
                                        <p class="text-xs sm:text-[13px] text-gray-600 mt-1.5 leading-snug">
                                            {{ addr.formatted || addr.address_1 }}
                                        </p>
                                    </div>

                                    <div class="flex items-center justify-between pt-3 mt-3 border-t border-gray-100 text-xs">
                                        <button
                                            v-if="!addr.is_default"
                                            type="button"
                                            @click="setDefaultAddress(addr.id)"
                                            class="font-semibold text-primary hover:underline cursor-pointer py-1"
                                        >
                                            Set as default
                                        </button>
                                        <span v-else class="text-gray-400 italic">Primary address</span>

                                        <button
                                            type="button"
                                            @click="deleteAddress(addr.id)"
                                            class="font-semibold text-red-500 hover:underline cursor-pointer py-1"
                                        >
                                            Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ════════════════════════════════════════
                         TAB 5: TRANSACTIONS / PAYMENTS
                    ════════════════════════════════════════ -->
                    <div v-show="currentTab === 'transactions'" class="space-y-3.5 sm:space-y-4">
                        <div class="bg-white rounded-2xl border border-gray-100 p-3.5 sm:p-5 shadow-xs">
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <h2 class="text-base sm:text-lg font-bold text-gray-900">Payments &amp; Transactions</h2>
                                    <p class="text-xs text-gray-400">Payment receipts and refund logs recorded in {{ storeName }}</p>
                                </div>
                                <span class="text-xs font-bold text-gray-700 bg-gray-100 px-2.5 py-1 rounded-full shrink-0">
                                    {{ transactionsList.length }} Records
                                </span>
                            </div>

                            <div v-if="transactionsList.length === 0" class="text-center py-10">
                                <p class="text-gray-400 text-xs sm:text-sm">No payment records logged yet.</p>
                            </div>

                            <div v-else class="space-y-2.5">
                                <div
                                    v-for="trx in transactionsList"
                                    :key="trx.id"
                                    class="border border-gray-100 hover:border-gray-200 rounded-xl p-3 sm:p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 transition-colors"
                                >
                                    <div class="flex items-start gap-2.5 min-w-0">
                                        <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl flex items-center justify-center shrink-0" :class="trx.positive ? 'bg-emerald-50 text-emerald-600' : 'bg-blue-50 text-blue-600'">
                                            <svg v-if="trx.positive" width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2"/></svg>
                                            <svg v-else width="16" height="16" viewBox="0 0 24 24" fill="none"><rect x="2" y="5" width="20" height="14" rx="2" stroke="currentColor" stroke-width="2"/><path d="M2 10h20" stroke="currentColor" stroke-width="2"/></svg>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-xs sm:text-[13.5px] font-bold text-gray-900 truncate">{{ trx.desc }}</p>
                                            <p class="text-[11px] sm:text-xs text-gray-400 mt-0.5 truncate">
                                                Trx: <strong class="font-mono text-gray-600">{{ trx.transaction_id }}</strong> · {{ trx.date }} · {{ trx.method }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-between sm:justify-end gap-2.5 shrink-0 pt-2 sm:pt-0 border-t sm:border-t-0 border-gray-50">
                                        <span class="text-[10.5px] sm:text-[11px] font-bold px-2 py-0.5 rounded-md" :class="trx.status_color || 'bg-gray-100 text-gray-700'">
                                            {{ trx.status }}
                                        </span>
                                        <span class="text-sm sm:text-base font-extrabold" :class="trx.positive ? 'text-emerald-600' : 'text-gray-900'">
                                            {{ trx.positive ? '+' : '' }}{{ currencySymbol }}{{ trx.amount?.toLocaleString() }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ════════════════════════════════════════
                         TAB 6: COUPONS & OFFERS
                    ════════════════════════════════════════ -->
                    <div v-show="currentTab === 'coupons'" class="space-y-3.5 sm:space-y-4">
                        <div class="bg-white rounded-2xl border border-gray-100 p-3.5 sm:p-5 shadow-xs">
                            <div class="mb-4">
                                <h2 class="text-base sm:text-lg font-bold text-gray-900">Active Coupons &amp; Promotions</h2>
                                <p class="text-xs text-gray-400">Apply these discount codes during checkout to save on medicines</p>
                            </div>

                            <div v-if="couponsList.length === 0" class="text-center py-10">
                                <p class="text-gray-400 text-xs sm:text-sm">No active store coupons right now. Check back soon!</p>
                            </div>

                            <div v-else class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
                                <div
                                    v-for="coupon in couponsList"
                                    :key="coupon.id"
                                    class="border border-emerald-100 bg-gradient-to-br from-emerald-50/40 via-white to-white rounded-2xl p-3.5 sm:p-4 flex flex-col justify-between shadow-2xs relative"
                                >
                                    <div>
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="text-[10px] sm:text-[10.5px] font-bold uppercase tracking-wider text-emerald-800 bg-emerald-100 px-2 py-0.5 rounded-md">
                                                Store Offer
                                            </span>
                                            <span class="text-[11px] text-gray-400">{{ coupon.expires }}</span>
                                        </div>

                                        <p class="text-lg sm:text-xl font-extrabold text-emerald-700">
                                            {{ coupon.type === 'percentage' ? `${coupon.value}% OFF` : `${currencySymbol}${coupon.value} OFF` }}
                                        </p>
                                        <p class="text-xs text-gray-600 mt-1">
                                            Min. spend: {{ currencySymbol }}{{ coupon.min_order_amount?.toLocaleString() || '0' }}
                                        </p>
                                    </div>

                                    <div class="pt-3 mt-3 border-t border-dashed border-emerald-200/80 flex items-center justify-between">
                                        <span class="font-mono text-xs sm:text-[13px] font-bold text-gray-800 bg-gray-100 px-2 py-0.5 rounded-lg">
                                            {{ coupon.code }}
                                        </span>
                                        <button
                                            type="button"
                                            @click="copyCoupon(coupon.code)"
                                            class="text-xs font-bold text-emerald-700 hover:text-emerald-800 hover:underline cursor-pointer"
                                        >
                                            Copy Code
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ════════════════════════════════════════
                         TAB 7: WISHLIST
                    ════════════════════════════════════════ -->
                    <div v-show="currentTab === 'wishlist'" class="space-y-3.5 sm:space-y-4">
                        <div class="bg-white rounded-2xl border border-gray-100 p-3.5 sm:p-5 shadow-xs">
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <h2 class="text-base sm:text-lg font-bold text-gray-900">My Wishlist</h2>
                                    <p class="text-xs text-gray-400">{{ wishlistList.length }} items saved</p>
                                </div>
                            </div>

                            <div v-if="wishlistList.length === 0" class="text-center py-10">
                                <p class="text-gray-400 text-xs sm:text-sm">Your wishlist is currently empty.</p>
                                <Link href="/" class="inline-block mt-2 text-xs sm:text-sm font-semibold text-primary hover:underline">Explore Products →</Link>
                            </div>

                            <div v-else class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-2.5 sm:gap-4">
                                <div
                                    v-for="item in wishlistList"
                                    :key="item.id"
                                    class="border border-gray-100 hover:border-gray-200 rounded-2xl p-2.5 sm:p-3 flex flex-col justify-between transition-colors group shadow-2xs"
                                >
                                    <div>
                                        <div class="aspect-square bg-gray-50 rounded-xl overflow-hidden mb-2">
                                            <img :src="item.img" :alt="item.name" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" />
                                        </div>
                                        <p class="text-xs sm:text-[13px] font-bold text-gray-900 line-clamp-2 leading-snug">{{ item.name }}</p>
                                        <p class="text-xs sm:text-sm font-extrabold text-primary mt-1">{{ currencySymbol }}{{ item.price }}</p>
                                    </div>

                                    <div class="pt-2 mt-2 border-t border-gray-100 flex items-center justify-between text-xs">
                                        <button
                                            type="button"
                                            @click="addToCartItem(item)"
                                            class="font-bold text-primary hover:underline cursor-pointer"
                                        >
                                            + Cart
                                        </button>
                                        <button
                                            type="button"
                                            @click="removeWishlist(item.id)"
                                            class="text-gray-400 hover:text-red-500 cursor-pointer"
                                        >
                                            Remove
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ════════════════════════════════════════
                         TAB 8: ACCOUNT SETTINGS
                    ════════════════════════════════════════ -->
                    <div v-show="currentTab === 'settings'" class="space-y-4 sm:space-y-6">
                        <!-- Profile Photo Upload Card -->
                        <div class="bg-white rounded-2xl border border-gray-100 p-4 sm:p-6 shadow-xs">
                            <h2 class="text-base sm:text-lg font-bold text-gray-900 mb-1">Profile Photo</h2>
                            <p class="text-xs sm:text-[12.5px] text-gray-400 mb-4">Upload your personal photo to personalize your patient profile</p>

                            <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                                <div class="relative w-20 h-20 sm:w-24 sm:h-24 rounded-full overflow-hidden ring-4 ring-primarylt bg-primary/10 flex items-center justify-center shrink-0">
                                    <img
                                        v-if="customerProfile.avatar && !avatarImgError"
                                        :src="customerProfile.avatar"
                                        alt="Profile Avatar"
                                        @error="avatarImgError = true"
                                        class="w-full h-full object-cover"
                                    />
                                    <span v-else class="text-3xl font-extrabold text-primary">
                                        {{ customerProfile.first_name?.[0] || 'C' }}
                                    </span>

                                    <div v-if="avatarUploading" class="absolute inset-0 bg-black/60 flex items-center justify-center">
                                        <svg class="animate-spin h-6 w-6 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                    </div>
                                </div>

                                <div class="space-y-2">
                                    <button
                                        type="button"
                                        :disabled="avatarUploading"
                                        @click="triggerAvatarSelect"
                                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-primary hover:bg-primary2 text-white font-bold text-xs sm:text-[13px] px-4 py-2.5 rounded-xl transition-colors cursor-pointer shadow-xs"
                                    >
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M23 19a2 2 0 01-2 2H3a2 2 0 01-2-2V8a2 2 0 012-2h4l2-3h6l2 3h4a2 2 0 012 2z" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="13" r="4" stroke="currentColor" stroke-width="2"/></svg>
                                        <span>{{ avatarUploading ? 'Uploading...' : 'Upload New Photo' }}</span>
                                    </button>
                                    <p class="text-xs text-gray-400">
                                        Supports JPG, PNG, WEBP or GIF. Maximum allowed size is 3MB.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Personal Details Form -->
                        <div class="bg-white rounded-2xl border border-gray-100 p-4 sm:p-6 shadow-xs">
                            <h2 class="text-base sm:text-lg font-bold text-gray-900 mb-4">Personal Profile</h2>

                            <div class="grid sm:grid-cols-2 gap-3.5 sm:gap-4">
                                <div>
                                    <label class="block text-xs sm:text-[13px] font-semibold text-gray-700 mb-1">Full Name</label>
                                    <input
                                        v-model="form.fullName"
                                        type="text"
                                        class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-base sm:text-sm focus:outline-none focus:border-primary"
                                    />
                                </div>
                                <div>
                                    <label class="block text-xs sm:text-[13px] font-semibold text-gray-700 mb-1">Phone Number</label>
                                    <input
                                        v-model="form.phone"
                                        type="text"
                                        class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-base sm:text-sm focus:outline-none focus:border-primary"
                                    />
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="block text-xs sm:text-[13px] font-semibold text-gray-700 mb-1">Email Address</label>
                                    <input
                                        v-model="form.email"
                                        type="email"
                                        class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-base sm:text-sm focus:outline-none focus:border-primary"
                                    />
                                </div>
                            </div>

                            <button
                                type="button"
                                @click="saveSettings"
                                class="w-full sm:w-auto mt-4 sm:mt-5 bg-primary hover:bg-primary2 text-white font-bold text-xs sm:text-[13.5px] px-5 py-2.5 rounded-xl transition-colors cursor-pointer shadow-xs justify-center flex items-center"
                            >
                                Save Profile Changes
                            </button>
                        </div>

                        <!-- Password Change Form -->
                        <div class="bg-white rounded-2xl border border-gray-100 p-4 sm:p-6 shadow-xs">
                            <h2 class="text-base sm:text-lg font-bold text-gray-900 mb-4">Change Password</h2>

                            <div class="grid sm:grid-cols-2 gap-3.5 sm:gap-4">
                                <div>
                                    <label class="block text-xs sm:text-[13px] font-semibold text-gray-700 mb-1">Current Password</label>
                                    <input
                                        v-model="form.currentPassword"
                                        type="password"
                                        placeholder="••••••••"
                                        class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-base sm:text-sm focus:outline-none focus:border-primary"
                                    />
                                </div>
                                <div>
                                    <label class="block text-xs sm:text-[13px] font-semibold text-gray-700 mb-1">New Password</label>
                                    <input
                                        v-model="form.newPassword"
                                        type="password"
                                        placeholder="At least 6 characters"
                                        class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-base sm:text-sm focus:outline-none focus:border-primary"
                                    />
                                </div>
                            </div>

                            <button
                                type="button"
                                @click="updatePassword"
                                class="w-full sm:w-auto mt-4 sm:mt-5 bg-gray-900 hover:bg-black text-white font-bold text-xs sm:text-[13.5px] px-5 py-2.5 rounded-xl transition-colors cursor-pointer shadow-xs justify-center flex items-center"
                            >
                                Update Password
                            </button>
                        </div>
                    </div>
                </section>
            </div>
        </main>

        <!-- ════════════════════════════════════════
             MODAL: ORDER DETAILS (RESPONSIVE RECEIPT)
        ════════════════════════════════════════ -->
        <div
            v-if="selectedOrder"
            class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-black/60 backdrop-blur-xs overscroll-contain"
            @click.self="closeOrderModal"
        >
            <div class="bg-white rounded-2xl sm:rounded-3xl max-w-2xl w-full max-h-[88vh] sm:max-h-[92vh] flex flex-col p-4 sm:p-6 shadow-2xl relative">
                <!-- Header -->
                <div class="flex items-center justify-between pb-3.5 border-b border-gray-100 shrink-0">
                    <div>
                        <div class="flex items-center gap-1.5 sm:gap-2 flex-wrap">
                            <h3 class="text-base sm:text-xl font-extrabold text-gray-900">Order #{{ selectedOrder.id }}</h3>
                            <span class="text-[11px] sm:text-xs font-bold px-2 py-0.5 rounded-md" :class="selectedOrder.color">
                                {{ selectedOrder.status }}
                            </span>
                        </div>
                        <p class="text-xs text-gray-400 mt-0.5">{{ selectedOrder.date }}</p>
                    </div>

                    <button
                        type="button"
                        @click="closeOrderModal"
                        class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center text-gray-500 cursor-pointer text-xs sm:text-sm shrink-0"
                    >
                        ✕
                    </button>
                </div>

                <!-- Scrollable Body -->
                <div class="overflow-y-auto flex-1 py-3.5 space-y-4">
                    <!-- Products on Mobile: Responsive Stacked Cards (< SM) -->
                    <div class="sm:hidden space-y-2">
                        <h4 class="text-xs font-bold text-gray-900">Order Items</h4>
                        <div v-for="item in selectedOrder.items" :key="item.id" class="p-2.5 bg-gray-50 rounded-xl flex items-center justify-between gap-2 text-xs">
                            <div class="min-w-0">
                                <p class="font-bold text-gray-900 truncate">{{ item.name }}</p>
                                <p v-if="item.variant" class="text-gray-400 text-[11px]">{{ item.variant }}</p>
                                <p v-if="item.dosage_instructions" class="text-primary font-medium text-[11px]">💊 {{ item.dosage_instructions }}</p>
                            </div>
                            <div class="text-right shrink-0">
                                <p class="font-bold text-gray-900">{{ currencySymbol }}{{ item.line_total }}</p>
                                <p class="text-gray-500 text-[11px]">{{ item.quantity }} × {{ currencySymbol }}{{ item.unit_price }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Products on Tablet/Desktop: Clean Table (SM+) -->
                    <div class="hidden sm:block">
                        <h4 class="text-[13.5px] font-bold text-gray-900 mb-2">Order Line Items</h4>
                        <div class="border border-gray-100 rounded-xl overflow-hidden">
                            <table class="w-full text-left text-xs sm:text-[13px]">
                                <thead class="bg-gray-50 text-gray-500 text-[11px] sm:text-xs font-bold border-b border-gray-100">
                                    <tr>
                                        <th class="p-2.5 sm:p-3">Item</th>
                                        <th class="p-2.5 sm:p-3 text-center">Qty</th>
                                        <th class="p-2.5 sm:p-3 text-right">Price</th>
                                        <th class="p-2.5 sm:p-3 text-right">Total</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <tr v-for="item in selectedOrder.items" :key="item.id">
                                        <td class="p-2.5 sm:p-3">
                                            <p class="font-bold text-gray-900">{{ item.name }}</p>
                                            <p v-if="item.variant" class="text-gray-400 text-xs">{{ item.variant }}</p>
                                            <p v-if="item.dosage_instructions" class="text-xs text-primary font-medium mt-0.5">
                                                💊 {{ item.dosage_instructions }}
                                            </p>
                                        </td>
                                        <td class="p-2.5 sm:p-3 text-center font-bold">{{ item.quantity }}</td>
                                        <td class="p-2.5 sm:p-3 text-right text-gray-600">{{ currencySymbol }}{{ item.unit_price }}</td>
                                        <td class="p-2.5 sm:p-3 text-right font-bold text-gray-900">{{ currencySymbol }}{{ item.line_total }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Financial Breakdown -->
                    <div class="bg-gray-50 rounded-xl p-3 sm:p-4 space-y-1.5 text-xs sm:text-sm">
                        <div class="flex justify-between text-gray-600">
                            <span>Subtotal:</span>
                            <span>{{ currencySymbol }}{{ selectedOrder.subtotal?.toLocaleString() }}</span>
                        </div>
                        <div v-if="selectedOrder.discount_amount > 0" class="flex justify-between text-emerald-600 font-medium">
                            <span>Discount:</span>
                            <span>-{{ currencySymbol }}{{ selectedOrder.discount_amount?.toLocaleString() }}</span>
                        </div>
                        <div class="flex justify-between text-gray-600">
                            <span>Delivery Fee:</span>
                            <span>{{ currencySymbol }}{{ selectedOrder.shipping_charge?.toLocaleString() }}</span>
                        </div>
                        <div class="flex justify-between text-sm sm:text-base font-extrabold text-gray-900 pt-2 border-t border-gray-200">
                            <span>Grand Total:</span>
                            <span>{{ currencySymbol }}{{ selectedOrder.total?.toLocaleString() }}</span>
                        </div>
                    </div>

                    <!-- Shipping details -->
                    <div v-if="selectedOrder.shipping" class="text-xs sm:text-[13px] text-gray-600 bg-white border border-gray-100 rounded-xl p-3">
                        <h4 class="font-bold text-gray-900 text-xs sm:text-[13px] mb-1">Delivery Destination:</h4>
                        <p class="font-medium text-gray-800">{{ selectedOrder.shipping.name }} · {{ selectedOrder.shipping.phone }}</p>
                        <p>{{ selectedOrder.shipping.address }}, {{ selectedOrder.shipping.city }}</p>
                    </div>

                    <!-- Prescription Attachment (If Rx) -->
                    <div v-if="selectedOrder.prescription_image" class="border border-purple-100 bg-purple-50/40 rounded-xl p-3">
                        <h4 class="font-bold text-purple-900 text-xs sm:text-[13px] mb-2">Attached Doctor's Prescription</h4>
                        <div
                            class="w-16 h-16 sm:w-20 sm:h-20 rounded-xl border border-purple-200 overflow-hidden cursor-pointer"
                            @click="openRxLightbox(selectedOrder.prescription_image)"
                        >
                            <img :src="selectedOrder.prescription_image" alt="Prescription" class="w-full h-full object-cover" />
                        </div>
                        <p v-if="selectedOrder.pharmacist_rx_notes" class="text-xs text-blue-900 bg-blue-50 p-2.5 rounded-lg mt-2">
                            👨‍⚕️ Pharmacist Notes: {{ selectedOrder.pharmacist_rx_notes }}
                        </p>
                    </div>
                </div>

                <!-- Footer button -->
                <div class="pt-3 border-t border-gray-100 flex justify-end shrink-0">
                    <button
                        type="button"
                        @click="closeOrderModal"
                        class="w-full sm:w-auto bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs sm:text-sm px-5 py-2.5 rounded-xl cursor-pointer"
                    >
                        Close
                    </button>
                </div>
            </div>
        </div>

        <!-- ════════════════════════════════════════
             MODAL: UPLOAD PRESCRIPTION
        ════════════════════════════════════════ -->
        <div
            v-if="showUploadRxModal"
            class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-black/60 backdrop-blur-xs overscroll-contain"
            @click.self="showUploadRxModal = false"
        >
            <div class="bg-white rounded-2xl sm:rounded-3xl max-w-lg w-full max-h-[88vh] sm:max-h-[92vh] flex flex-col p-4 sm:p-7 shadow-2xl relative">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100 shrink-0">
                    <div>
                        <h3 class="text-base sm:text-lg font-extrabold text-gray-900">Upload Prescription</h3>
                        <p class="text-xs text-gray-400">Verified by our certified pharmacists</p>
                    </div>
                    <button
                        type="button"
                        @click="showUploadRxModal = false"
                        class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center text-gray-500 cursor-pointer text-xs"
                    >
                        ✕
                    </button>
                </div>

                <div class="overflow-y-auto flex-1 space-y-3.5 py-3.5">
                    <div>
                        <label class="block text-xs sm:text-[12.5px] font-semibold text-gray-700 mb-1">Prescription Scan or PDF *</label>
                        <input
                            type="file"
                            accept="image/*,application/pdf"
                            @change="handleRxFileChange"
                            class="w-full text-xs sm:text-sm text-gray-500 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-primarylt file:text-primary hover:file:bg-primary/20 cursor-pointer"
                        />
                        <div v-if="rxUploadForm.filePreview" class="mt-2 w-16 h-16 sm:w-20 sm:h-20 rounded-xl border border-gray-200 overflow-hidden">
                            <img :src="rxUploadForm.filePreview" alt="Preview" class="w-full h-full object-cover"/>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs sm:text-[12.5px] font-semibold text-gray-700 mb-1">Required Medicines / Notes</label>
                        <textarea
                            v-model="rxUploadForm.patient_notes"
                            rows="2"
                            placeholder="e.g. Please send Napa Extra 500mg, Sergel 20mg"
                            class="w-full border border-gray-200 rounded-xl p-2.5 sm:p-3 text-base sm:text-[13px] focus:outline-none focus:border-primary"
                        ></textarea>
                    </div>

                    <div>
                        <label class="block text-xs sm:text-[12.5px] font-semibold text-gray-700 mb-1">Delivery Address</label>
                        <input
                            v-model="rxUploadForm.delivery_address"
                            type="text"
                            :placeholder="addressesList[0]?.formatted || 'Your delivery address'"
                            class="w-full border border-gray-200 rounded-xl px-3 py-2 text-base sm:text-[13px] focus:outline-none focus:border-primary"
                        />
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-gray-100 shrink-0">
                    <button
                        type="button"
                        @click="showUploadRxModal = false"
                        class="text-xs sm:text-[12.5px] font-semibold text-gray-500 hover:text-gray-700 px-3 py-2"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        :disabled="rxUploading"
                        @click="submitPrescription"
                        class="bg-primary hover:bg-primary2 text-white font-bold text-xs sm:text-[13px] px-4 py-2 rounded-xl transition-colors cursor-pointer shadow-xs flex items-center gap-1.5"
                    >
                        <svg v-if="rxUploading" class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                        <span>{{ rxUploading ? 'Uploading...' : 'Submit to Pharmacist' }}</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- ════════════════════════════════════════
             MODAL: ADD NEW ADDRESS
        ════════════════════════════════════════ -->
        <div
            v-if="showAddAddressModal"
            class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-black/60 backdrop-blur-xs overscroll-contain"
            @click.self="showAddAddressModal = false"
        >
            <div class="bg-white rounded-2xl sm:rounded-3xl max-w-md w-full max-h-[88vh] sm:max-h-[92vh] flex flex-col p-4 sm:p-6 shadow-2xl relative">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100 shrink-0">
                    <h3 class="text-base sm:text-lg font-extrabold text-gray-900">Add Delivery Address</h3>
                    <button
                        type="button"
                        @click="showAddAddressModal = false"
                        class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center text-gray-500 cursor-pointer text-xs"
                    >
                        ✕
                    </button>
                </div>

                <div class="overflow-y-auto flex-1 space-y-3 py-3 text-xs sm:text-[13px]">
                    <div class="flex gap-2">
                        <button
                            v-for="tag in ['Home', 'Office', 'Other']"
                            :key="tag"
                            type="button"
                            @click="newAddressForm.title = tag"
                            class="flex-1 py-1.5 rounded-xl border font-bold text-xs transition-colors cursor-pointer"
                            :class="newAddressForm.title === tag ? 'bg-primary text-white border-primary' : 'bg-gray-50 text-gray-600 border-gray-200'"
                        >
                            {{ tag }}
                        </button>
                    </div>

                    <div class="grid grid-cols-2 gap-2.5">
                        <div>
                            <label class="block font-semibold text-gray-700 mb-1">First Name *</label>
                            <input
                                v-model="newAddressForm.first_name"
                                type="text"
                                class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:border-primary text-base sm:text-xs"
                            />
                        </div>
                        <div>
                            <label class="block font-semibold text-gray-700 mb-1">Last Name</label>
                            <input
                                v-model="newAddressForm.last_name"
                                type="text"
                                class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:border-primary text-base sm:text-xs"
                            />
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">Contact Phone *</label>
                        <input
                            v-model="newAddressForm.phone"
                            type="text"
                            placeholder="e.g. 01711223344"
                            class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:border-primary text-base sm:text-xs"
                        />
                    </div>

                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">Street Address *</label>
                        <input
                            v-model="newAddressForm.address_1"
                            type="text"
                            placeholder="House / Flat / Road"
                            class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:border-primary text-base sm:text-xs"
                        />
                    </div>

                    <div class="grid grid-cols-2 gap-2.5">
                        <div>
                            <label class="block font-semibold text-gray-700 mb-1">City / District</label>
                            <input
                                v-model="newAddressForm.state_district"
                                type="text"
                                placeholder="Dhaka"
                                class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:border-primary text-base sm:text-xs"
                            />
                        </div>
                        <div>
                            <label class="block font-semibold text-gray-700 mb-1">Zip Code</label>
                            <input
                                v-model="newAddressForm.zip"
                                type="text"
                                placeholder="1230"
                                class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:border-primary text-base sm:text-xs"
                            />
                        </div>
                    </div>

                    <label class="flex items-center gap-2 pt-1 cursor-pointer">
                        <input v-model="newAddressForm.is_default" type="checkbox" class="rounded text-primary focus:ring-primary"/>
                        <span class="text-xs text-gray-600 font-medium">Set as default address</span>
                    </label>
                </div>

                <div class="flex justify-end gap-2.5 pt-3 border-t border-gray-100 shrink-0">
                    <button
                        type="button"
                        @click="showAddAddressModal = false"
                        class="text-xs font-semibold text-gray-500 hover:text-gray-700 px-3 py-2"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        :disabled="addressLoading"
                        @click="saveNewAddress"
                        class="bg-primary hover:bg-primary2 text-white font-bold text-xs px-4 py-2 rounded-xl transition-colors cursor-pointer shadow-xs"
                    >
                        {{ addressLoading ? 'Saving...' : 'Save Address' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- ════════════════════════════════════════
             MODAL: PRESCRIPTION LIGHTBOX
        ════════════════════════════════════════ -->
        <div
            v-if="selectedRxImage"
            class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-black/80 backdrop-blur-xs"
            @click.self="closeRxLightbox"
        >
            <div class="max-w-3xl w-full max-h-[90vh] bg-white rounded-2xl sm:rounded-3xl p-3 sm:p-4 shadow-2xl relative flex flex-col items-center">
                <button
                    type="button"
                    @click="closeRxLightbox"
                    class="absolute top-3 right-3 w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center text-gray-600 font-bold z-10 cursor-pointer text-xs"
                >
                    ✕
                </button>
                <div class="w-full overflow-auto max-h-[82vh] flex items-center justify-center rounded-xl bg-gray-50 p-2">
                    <img :src="selectedRxImage" alt="Prescription Document" class="max-w-full max-h-[78vh] object-contain rounded-lg" />
                </div>
            </div>
        </div>

        <!-- ════════════════════════════════════════
             FLOATING TOAST NOTIFICATION (RESPONSIVE)
        ════════════════════════════════════════ -->
        <div
            v-if="toast"
            class="fixed bottom-4 inset-x-4 sm:inset-x-auto sm:right-6 sm:bottom-6 z-50 bg-gray-900 text-white text-xs sm:text-[13.5px] font-semibold px-4 py-3 rounded-xl sm:rounded-2xl shadow-xl flex items-center justify-center sm:justify-start gap-2 max-w-sm mx-auto sm:mx-0"
        >
            <span>{{ toast }}</span>
        </div>
    </div>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
