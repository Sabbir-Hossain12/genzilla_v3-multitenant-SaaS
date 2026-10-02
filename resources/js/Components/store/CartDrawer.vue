<script setup>
import {
    cart,
    cartCount,
    cartSubtotal,
    freeDeliveryRemaining,
    freeDeliveryPct,
    hasFreeDelivery,
    changeQty,
    removeItem,
    formatTaka,
} from '@/composables/store/useCart'
import { cartOpen, closeCartDrawer, openCheckoutModal } from '@/composables/store/useStoreUi'
</script>

<template>
    <div id="cart-overlay" :class="{ open: cartOpen }" @click.self="closeCartDrawer">
        <aside id="cart-drawer">
            <!-- Header -->
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 shrink-0">
                <h3 class="text-[17px] font-bold text-gray-900 flex items-center gap-2">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4zM3 6h18M16 10a4 4 0 01-8 0" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    Your Cart <span class="text-gray-400 font-medium">({{ cartCount }})</span>
                </h3>
                <button
                    type="button"
                    aria-label="Close cart"
                    class="w-9 h-9 rounded-full flex items-center justify-center text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-colors"
                    @click="closeCartDrawer"
                >
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                </button>
            </div>

            <template v-if="cart.length">
                <!-- Free delivery progress -->
                <div id="cart-delivery-note" class="px-5 py-3 bg-primarylt border-b border-primary/10 shrink-0">
                    <p class="text-[13px] text-primary font-medium">
                        <template v-if="hasFreeDelivery">You've unlocked FREE delivery! 🎉</template>
                        <template v-else>Add {{ formatTaka(freeDeliveryRemaining) }} more for FREE delivery 🚚</template>
                    </p>
                    <div class="w-full h-1.5 bg-white rounded-full mt-2 overflow-hidden">
                        <div
                            id="cart-delivery-bar"
                            class="h-full bg-primary rounded-full transition-all duration-300"
                            :style="{ width: freeDeliveryPct + '%' }"
                        ></div>
                    </div>
                </div>

                <!-- Items -->
                <div id="cart-items" class="flex-1 overflow-y-auto px-5 py-4 space-y-4">
                    <div v-for="item in cart" :key="item.id" class="cart-item flex gap-3">
                        <div class="w-20 h-20 shrink-0 rounded-xl overflow-hidden">
                            <img :src="item.image" :alt="item.name" class="w-full h-full object-cover" />
                        </div>

                        <div class="flex-1 min-w-0">
                            <p class="text-[14px] font-medium text-gray-800 leading-snug line-clamp-2">{{ item.name }}</p>
                            <p class="text-[13px] text-gray-400 mt-0.5">Unit price: {{ formatTaka(item.price) }}</p>

                            <div class="flex items-center justify-between mt-2">
                                <div class="flex items-center gap-2.5 border border-gray-200 rounded-lg px-1.5 py-1">
                                    <button
                                        type="button"
                                        :aria-label="`Decrease quantity of ${item.name}`"
                                        class="qty-btn flex items-center justify-center text-gray-500 hover:text-primary font-bold"
                                        @click="changeQty(item.id, -1)"
                                    >−</button>
                                    <span class="qty-val text-[14px] font-semibold w-4 text-center">{{ item.qty }}</span>
                                    <button
                                        type="button"
                                        :aria-label="`Increase quantity of ${item.name}`"
                                        class="qty-btn flex items-center justify-center text-gray-500 hover:text-primary font-bold"
                                        @click="changeQty(item.id, 1)"
                                    >+</button>
                                </div>

                                <div class="flex items-center gap-2">
                                    <span class="item-total text-[15px] font-bold text-primary">{{ formatTaka(item.price * item.qty) }}</span>
                                    <button
                                        type="button"
                                        :aria-label="`Remove ${item.name}`"
                                        class="text-gray-300 hover:text-red-400 transition-colors"
                                        @click="removeItem(item.id)"
                                    >
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M3 6h18M8 6V4a2 2 0 012-2h4a2 2 0 012 2v2m3 0l-1 14a2 2 0 01-2 2H7a2 2 0 01-2-2L4 6h16z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div id="cart-footer" class="border-t border-gray-100 px-5 py-4 shrink-0 space-y-3">
                    <div class="flex items-center justify-between text-[14px] text-gray-500">
                        <span>Subtotal</span>
                        <span class="font-semibold text-gray-800">{{ formatTaka(cartSubtotal) }}</span>
                    </div>
                    <div class="flex items-center justify-between text-[14px] text-gray-500">
                        <span>Delivery</span>
                        <span class="font-semibold text-green-600">FREE</span>
                    </div>
                    <div class="flex items-center justify-between text-[16px] pt-2 border-t border-gray-100">
                        <span class="font-bold text-gray-900">Total</span>
                        <span class="font-extrabold text-primary text-[19px]">{{ formatTaka(cartSubtotal) }}</span>
                    </div>

                    <button
                        type="button"
                        class="w-full bg-primary hover:bg-primary2 text-white font-bold text-[15px] py-3.5 rounded-xl transition-colors mt-1"
                        @click="openCheckoutModal"
                    >Proceed to Checkout</button>

                    <button
                        type="button"
                        class="w-full text-center text-[13px] text-gray-500 hover:text-primary font-medium transition-colors"
                        @click="closeCartDrawer"
                    >Continue Shopping</button>
                </div>
            </template>

            <!-- Empty state -->
            <div v-else id="cart-empty" class="flex-1 flex-col items-center justify-center px-8 text-center">
                <div class="text-6xl mb-4">🛒</div>
                <p class="text-[16px] font-bold text-gray-700 mb-1">Your cart is empty</p>
                <p class="text-[14px] text-gray-400 mb-5">Looks like you haven't added anything yet.</p>
                <button
                    type="button"
                    class="bg-primary hover:bg-primary2 text-white font-semibold text-[14px] px-6 py-2.5 rounded-xl transition-colors"
                    @click="closeCartDrawer"
                >Start Shopping</button>
            </div>
        </aside>
    </div>
</template>
