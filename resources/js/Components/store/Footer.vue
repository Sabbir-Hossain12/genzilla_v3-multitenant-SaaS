<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { openCartDrawer, openLoginModal } from '@/composables/store/useStoreUi'
import { cartCount } from '@/composables/store/useCart'

const page = usePage()
const store = computed(() => page.props.store || {})
const storeName = computed(() => store.value.name || 'MediMart')
const storeLogo = computed(() => store.value.logo_url)
const storeAddress = computed(() => store.value.address || "Serving all 64 districts across Bangladesh.")
const storePhone = computed(() => store.value.phone || '09610-016778')
const storeEmail = computed(() => store.value.email || 'support@medimart.com')
const currentYear = new Date().getFullYear()
</script>

<template>
    <!-- ════════════════════════════════════════
         FOOTER
    ════════════════════════════════════════ -->
    <footer class="bg-white border-t border-gray-100 mt-4">
        <div class="max-w-7xl mx-auto px-4 pt-10 pb-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-10 mb-10">

                <!-- Brand -->
                <div class="lg:col-span-2">
                    <div class="flex items-center gap-2 mb-3">
                        <img
                            v-if="storeLogo"
                            :src="storeLogo"
                            :alt="storeName"
                            class="h-8 w-auto max-w-[150px] object-contain rounded-lg"
                        />
                        <template v-else>
                            <div class="w-8 h-8 rounded-lg bg-primary flex items-center justify-center shrink-0">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                                    <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                            <span class="text-2xl font-extrabold text-primary">{{ storeName }}</span>
                        </template>
                    </div>
                    <p class="text-[15px] text-gray-500 leading-relaxed mb-4">
                        {{ storeAddress }}
                    </p>
                    <div class="flex gap-2">
                        <a href="#" class="w-10 h-10 bg-primarylt rounded-lg flex items-center justify-center text-primary hover:bg-primary hover:text-white transition-colors text-base font-bold">f</a>
                        <a href="#" class="w-10 h-10 bg-primarylt rounded-lg flex items-center justify-center text-primary hover:bg-primary hover:text-white transition-colors text-base font-bold">in</a>
                        <a href="#" class="w-10 h-10 bg-primarylt rounded-lg flex items-center justify-center text-primary hover:bg-primary hover:text-white transition-colors text-base font-bold">yt</a>
                    </div>
                </div>

                <!-- Quick links -->
                <div>
                    <h4 class="text-[16px] font-bold text-gray-800 mb-4">Quick Links</h4>
                    <ul class="space-y-2.5 text-[15px] text-gray-500">
                        <li><a href="#" class="hover:text-primary transition-colors">Careers</a></li>
                        <li><a href="#" class="hover:text-primary transition-colors">Privacy Policy</a></li>
                        <li><a href="#" class="hover:text-primary transition-colors">Terms & Conditions</a></li>
                        <li><a href="#" class="hover:text-primary transition-colors">Return & Refund</a></li>
                        <li><a href="#" class="hover:text-primary transition-colors">Frequently Asked Questions</a></li>
                    </ul>
                </div>

                <!-- Services -->
                <div>
                    <h4 class="text-[16px] font-bold text-gray-800 mb-4">Our Services</h4>
                    <ul class="space-y-2.5 text-[15px] text-gray-500">
                        <li><a href="#" class="hover:text-primary transition-colors">Online Doctor Consultation</a></li>
                        <li><a href="#" class="hover:text-primary transition-colors">Lab Test — Home Collection</a></li>
                        <li><a href="#" class="hover:text-primary transition-colors">Medicine Home Delivery</a></li>
                        <li><a href="#" class="hover:text-primary transition-colors">Healthcare & Wellness</a></li>
                        <li><a href="#" class="hover:text-primary transition-colors">Register Your Pharmacy</a></li>
                    </ul>
                </div>

                <!-- Contact & Payment -->
                <div>
                    <h4 class="text-[16px] font-bold text-gray-800 mb-4">Need Help?</h4>
                    <div class="space-y-2 mb-4 text-[14px] text-gray-600">
                        <p class="flex items-center gap-2">
                            <span>📞</span> <a :href="`tel:${storePhone}`" class="hover:text-primary font-semibold">{{ storePhone }}</a>
                        </p>
                        <p class="flex items-center gap-2">
                            <span>✉️</span> <a :href="`mailto:${storeEmail}`" class="hover:text-primary">{{ storeEmail }}</a>
                        </p>
                        <p class="text-xs text-gray-400">Available 24/7 for emergency prescription orders</p>
                    </div>

                    <h4 class="text-[14px] font-bold text-gray-800 mb-2">Payment Methods</h4>
                    <div class="flex flex-wrap gap-1.5">
                        <span class="bg-gray-100 text-gray-600 text-[12px] font-semibold px-2 py-1 rounded">bKash</span>
                        <span class="bg-gray-100 text-gray-600 text-[12px] font-semibold px-2 py-1 rounded">Nagad</span>
                        <span class="bg-gray-100 text-gray-600 text-[12px] font-semibold px-2 py-1 rounded">Rocket</span>
                        <span class="bg-gray-100 text-gray-600 text-[12px] font-semibold px-2 py-1 rounded">VISA</span>
                        <span class="bg-gray-100 text-gray-600 text-[12px] font-semibold px-2 py-1 rounded">Master</span>
                        <span class="bg-gray-100 text-gray-600 text-[12px] font-semibold px-2 py-1 rounded">COD</span>
                    </div>
                </div>

            </div>

            <!-- Bottom bar -->
            <div class="border-t border-gray-100 pt-6 flex flex-col sm:flex-row items-center justify-between gap-2 text-[13px] text-gray-400">
                <p>© {{ currentYear }} {{ storeName }}. All rights reserved.</p>
                <p>Powered by <span class="text-primary font-semibold">Genzilla SaaS</span></p>
            </div>
        </div>
    </footer>

    <!-- ════════════════════════════════════════
         MOBILE BOTTOM NAV
    ════════════════════════════════════════ -->
    <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-100 z-40 flex sm:hidden">
        <a href="/" class="flex-1 flex flex-col items-center py-2.5 text-primary">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z" stroke="currentColor" stroke-width="2"/>
                <path d="M9 22V12h6v10" stroke="currentColor" stroke-width="2"/>
            </svg>
            <span class="text-[12px] font-medium mt-0.5">Home</span>
        </a>
        <a href="#all-categories" class="flex-1 flex flex-col items-center py-2.5 text-gray-400 hover:text-primary">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                <circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/>
                <path d="M21 21l-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
            <span class="text-[12px] font-medium mt-0.5">Search</span>
        </a>
        <button
            type="button"
            class="flex-1 flex flex-col items-center py-2.5 text-gray-400 hover:text-primary relative"
            aria-label="Open cart drawer"
            @click="openCartDrawer"
        >
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4zM3 6h18M16 10a4 4 0 01-8 0" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span
                id="cart-badge-mobile"
                class="absolute top-1.5 right-4 w-4 h-4 bg-accent text-white text-[11px] font-bold rounded-full flex items-center justify-center"
            >{{ cartCount }}</span>
            <span class="text-[12px] font-medium mt-0.5">Cart</span>
        </button>
        <button
            type="button"
            class="flex-1 flex flex-col items-center py-2.5 text-gray-400 hover:text-primary"
            aria-label="Open account modal"
            @click="openLoginModal"
        >
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2M12 11a4 4 0 100-8 4 4 0 000 8z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
            <span class="text-[12px] font-medium mt-0.5">Account</span>
        </button>
    </div>
    <div class="h-16 sm:hidden"></div>
</template>
