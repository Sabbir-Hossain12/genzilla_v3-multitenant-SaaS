<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import Header from '@/Components/Header.vue'
import Footer from '@/Components/Footer.vue'
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

onMounted(() => window.addEventListener('medimart:toast', onToast))
onBeforeUnmount(() => {
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
