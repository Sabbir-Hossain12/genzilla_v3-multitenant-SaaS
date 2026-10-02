<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import Header from '@/Components/store/Header.vue'
import Footer from '@/Components/store/Footer.vue'
import LoginModal from '@/Components/store/LoginModal.vue'
import CartDrawer from '@/Components/store/CartDrawer.vue'
import CheckoutModal from '@/Components/store/CheckoutModal.vue'
import MobileNavDrawer from '@/Components/store/MobileNavDrawer.vue'

const toast = ref('')
let toastTimer = null
let fadeTimer = null

function showToast(message) {
    toast.value = message
    clearTimeout(toastTimer)
    clearTimeout(fadeTimer)

    toastTimer = setTimeout(() => {
        fadeTimer = setTimeout(() => { toast.value = '' }, 300)
    }, 2500)
}

// Inertia does not re-render <body> on client-side navigation, and app.blade.php
// hard-codes these storefront colours there. Claiming them on mount keeps the
// storefront correct after navigating over from the platform marketing site.
const BODY_CLASS = 'bg-gray-50 text-gray-800 font-sans'

onMounted(() => {
    document.body.className = BODY_CLASS
    window.addEventListener('medimart:toast', onToast)
})
onBeforeUnmount(() => {
    document.body.style.overflow = ''
    window.removeEventListener('medimart:toast', onToast)
    clearTimeout(toastTimer)
    clearTimeout(fadeTimer)
})

function onToast(event) {
    showToast(event.detail)
}
</script>

<template>
    <Header />

    <!-- Page Content -->
    <main>
        <slot />
    </main>

    <Footer />

    <!-- Page-level overlays -->
    <LoginModal />
    <CartDrawer />
    <CheckoutModal />
    <MobileNavDrawer />

    <!-- Toast -->
    <Transition
        enter-active-class="transition-opacity duration-300 ease-out"
        enter-from-class="opacity-0"
        leave-active-class="transition-opacity duration-300 ease-in"
        leave-to-class="opacity-0"
    >
        <div
            v-if="toast"
            class="fixed bottom-24 left-1/2 -translate-x-1/2 bg-gray-900 text-white px-5 py-3 rounded-xl text-[14px] font-semibold z-[200] shadow-lg whitespace-nowrap"
        >{{ toast }}</div>
    </Transition>
</template>
