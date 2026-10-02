<script setup>
import { openCartDrawer, openLoginModal } from '@/composables/store/useStoreUi'
import { cartCount } from '@/composables/store/useCart'
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
                        <div class="w-8 h-8 rounded-lg bg-primary flex items-center justify-center">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                                <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" stroke="white"
                                      stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <span class="text-2xl font-extrabold text-primary">Medi<span
                            class="text-accent">Mart</span></span>
                    </div>
                    <p class="text-[15px] text-gray-500 leading-relaxed mb-4">Bangladesh's most trusted online pharmacy
                        for medicine, beauty & healthcare products. Serving all 64 districts.</p>
                    <div class="flex gap-2">
                        <a href="#"
                           class="w-10 h-10 bg-primarylt rounded-lg flex items-center justify-center text-primary hover:bg-primary hover:text-white transition-colors text-base">f</a>
                        <a href="#"
                           class="w-10 h-10 bg-primarylt rounded-lg flex items-center justify-center text-primary hover:bg-primary hover:text-white transition-colors text-base">in</a>
                        <a href="#"
                           class="w-10 h-10 bg-primarylt rounded-lg flex items-center justify-center text-primary hover:bg-primary hover:text-white transition-colors text-base">yt</a>
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
                        <li><a href="#" class="hover:text-primary transition-colors">Blog</a></li>
                    </ul>
                </div>

                <!-- Services -->
                <div>
                    <h4 class="text-[16px] font-bold text-gray-800 mb-4">Our Services</h4>
                    <ul class="space-y-2.5 text-[15px] text-gray-500">
                        <li><a href="#" class="hover:text-primary transition-colors">Online Doctor Consultation</a></li>
                        <li><a href="#" class="hover:text-primary transition-colors">Lab Test — Home Collection</a></li>
                        <li><a href="#" class="hover:text-primary transition-colors">Medicine Home Delivery</a></li>
                        <li><a href="#" class="hover:text-primary transition-colors">Healthcare Products</a></li>
                        <li><a href="#" class="hover:text-primary transition-colors">Register Your Pharmacy</a></li>
                    </ul>
                </div>

                <!-- Contact -->
                <div>
                    <h4 class="text-[16px] font-bold text-gray-800 mb-4">Contact</h4>
                    <ul class="space-y-2.5 text-[15px] text-gray-500">
                        <li class="flex items-center gap-2">📞 <a href="tel:+8809610016778" class="hover:text-primary">09610-016778</a>
                        </li>
                        <li class="flex items-center gap-2">💬 <a href="#" class="hover:text-primary">WhatsApp:
                            01810117100</a></li>
                        <li class="flex items-start gap-2">📍 <span>D/15-1, Road-36, Mirpur, Dhaka-1216</span></li>
                    </ul>

                    <!-- Payment -->
                    <h4 class="text-[16px] font-bold text-gray-800 mt-5 mb-3">Payment Partners</h4>
                    <div class="flex flex-wrap gap-1.5">
                        <span
                            class="bg-gray-100 text-gray-600 text-[13px] font-semibold px-2.5 py-1.5 rounded">bKash</span>
                        <span
                            class="bg-gray-100 text-gray-600 text-[13px] font-semibold px-2.5 py-1.5 rounded">Nagad</span>
                        <span
                            class="bg-gray-100 text-gray-600 text-[13px] font-semibold px-2.5 py-1.5 rounded">Rocket</span>
                        <span
                            class="bg-gray-100 text-gray-600 text-[13px] font-semibold px-2.5 py-1.5 rounded">VISA</span>
                        <span
                            class="bg-gray-100 text-gray-600 text-[13px] font-semibold px-2.5 py-1.5 rounded">Master</span>
                        <span
                            class="bg-gray-100 text-gray-600 text-[13px] font-semibold px-2.5 py-1.5 rounded">COD</span>
                    </div>
                </div>

            </div>

            <!-- Bottom bar -->
            <div
                class="border-t border-gray-100 pt-6 flex flex-col sm:flex-row items-center justify-between gap-2 text-[13px] text-gray-400">
                <p>© 2026 MediMart Ltd. All rights reserved. Trade License: TRAD/DNCC/2024</p>
                <p>Powered by <span class="text-primary font-semibold">MediMart SaaS</span> — Multi-tenant e-commerce
                    platform</p>
            </div>
        </div>
    </footer>

    <!-- ════════════════════════════════════════
         MOBILE BOTTOM NAV
    ════════════════════════════════════════ -->
    <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-100 z-40 flex sm:hidden">
        <a href="#" class="flex-1 flex flex-col items-center py-2.5 text-primary">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z" stroke="currentColor" stroke-width="2"/>
                <path d="M9 22V12h6v10" stroke="currentColor" stroke-width="2"/>
            </svg>
            <span class="text-[12px] font-medium mt-0.5">Home</span>
        </a>
        <a href="#" class="flex-1 flex flex-col items-center py-2.5 text-gray-400 hover:text-primary">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                <circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/>
                <path d="M21 21l-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
            <span class="text-[12px] font-medium mt-0.5">Search</span>
        </a>
        <button @click="openCartDrawer"
                class="flex-1 flex flex-col items-center py-2.5 text-gray-400 hover:text-primary relative">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4zM3 6h18M16 10a4 4 0 01-8 0" stroke="currentColor" stroke-width="2"
                      stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span id="cart-badge-mobile"
                  class="absolute top-1.5 right-4 w-4 h-4 bg-accent text-white text-[11px] font-bold rounded-full flex items-center justify-center">{{ cartCount }}</span>
            <span class="text-[12px] font-medium mt-0.5">Cart</span>
        </button>
        <button @click="openLoginModal"

                class="flex-1 flex flex-col items-center py-2.5 text-gray-400 hover:text-primary">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2M12 11a4 4 0 100-8 4 4 0 000 8z" stroke="currentColor"
                      stroke-width="2" stroke-linecap="round"/>
            </svg>
            <span class="text-[12px] font-medium mt-0.5">Account</span>
        </button>
    </div>
    <div class="h-16 sm:hidden"></div>
</template>

<style scoped>

</style>
