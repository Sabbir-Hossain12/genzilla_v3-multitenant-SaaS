<script setup>
import { Link } from '@inertiajs/vue3'
import {
    cart,
    cartCount,
    cartSubtotal,
    cartMrp,
    cartDiscount,
    freeDeliveryRemaining,
    freeDeliveryPct,
    hasFreeDelivery,
    hasPrescriptionItems,
    isCartSyncing,
    changeQty,
    removeItem,
    clearCart,
    formatTaka,
} from '@/composables/store/useCart'
import { cartOpen, closeCartDrawer, openCheckoutModal } from '@/composables/store/useStoreUi'

function handleImgError(e) {
    e.target.src = 'https://via.placeholder.com/150?text=Medicine'
}
</script>

<template>
    <div id="cart-overlay" :class="{ open: cartOpen }" @click.self="closeCartDrawer">
        <aside id="cart-drawer" class="flex flex-col h-full bg-white">
            <!-- Header -->
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 shrink-0">
                <div class="flex items-center gap-2">
                    <h3 class="text-[17px] font-bold text-gray-900 flex items-center gap-2">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4zM3 6h18M16 10a4 4 0 01-8 0" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        Your Cart
                    </h3>
                    <span class="inline-flex items-center justify-center px-2 py-0.5 text-xs font-bold rounded-full bg-primarylt text-primary">
                        {{ cartCount }} item{{ cartCount === 1 ? '' : 's' }}
                    </span>
                    <span v-if="isCartSyncing" class="inline-flex items-center gap-1 text-[11px] text-gray-400 font-medium">
                        <svg class="animate-spin h-3 w-3 text-primary" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                        Syncing
                    </span>
                </div>

                <div class="flex items-center gap-1">
                    <button
                        v-if="cart.length"
                        type="button"
                        class="text-xs text-gray-400 hover:text-red-500 font-semibold px-2 py-1 rounded transition-colors cursor-pointer"
                        title="Clear entire cart"
                        @click="clearCart"
                    >
                        Clear
                    </button>
                    <button
                        type="button"
                        aria-label="Close cart"
                        class="w-9 h-9 rounded-full flex items-center justify-center text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-colors"
                        @click="closeCartDrawer"
                    >
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    </button>
                </div>
            </div>

            <template v-if="cart.length">
                <!-- Free delivery progress strip -->
                <div id="cart-delivery-note" class="px-5 py-3 bg-teal-50/70 border-b border-teal-100 shrink-0">
                    <p class="text-[13px] text-teal-800 font-semibold flex items-center justify-between">
                        <span v-if="hasFreeDelivery">You've unlocked FREE delivery! 🎉</span>
                        <span v-else>Add {{ formatTaka(freeDeliveryRemaining) }} more for FREE delivery 🚚</span>
                        <span class="text-xs font-bold text-teal-600">{{ freeDeliveryPct }}%</span>
                    </p>
                    <div class="w-full h-1.5 bg-teal-100 rounded-full mt-2 overflow-hidden">
                        <div
                            id="cart-delivery-bar"
                            class="h-full bg-primary rounded-full transition-all duration-300"
                            :style="{ width: freeDeliveryPct + '%' }"
                        ></div>
                    </div>
                </div>

                <!-- Prescription Notice if Rx items exist -->
                <div v-if="hasPrescriptionItems" class="px-5 py-2.5 bg-purple-50/80 border-b border-purple-100 shrink-0 flex items-center gap-2">
                    <span class="text-sm">📋</span>
                    <p class="text-xs text-purple-900 font-medium leading-tight">
                        Contains <span class="font-bold">Prescription (Rx)</span> items. Prescription will be verified.
                    </p>
                </div>

                <!-- Cart Items List -->
                <div id="cart-items" class="flex-1 overflow-y-auto px-5 py-4 space-y-4">
                    <div
                        v-for="item in cart"
                        :key="item.id"
                        class="cart-item flex gap-3 p-3 rounded-xl border border-gray-100 hover:border-gray-200 bg-white transition-all shadow-2xs"
                    >
                        <!-- Item Image with Link -->
                        <Link
                            :href="item.slug ? `/product/${item.slug}` : `/product/${item.id}`"
                            class="w-18 h-18 sm:w-20 sm:h-20 shrink-0 rounded-lg overflow-hidden bg-gray-50 border border-gray-100 block"
                            @click="closeCartDrawer"
                        >
                            <img
                                :src="item.image || 'https://via.placeholder.com/150'"
                                :alt="item.name"
                                class="w-full h-full object-cover"
                                @error="handleImgError"
                            />
                        </Link>

                        <!-- Item Info -->
                        <div class="flex-1 min-w-0 flex flex-col justify-between">
                            <div>
                                <div class="flex items-start justify-between gap-1">
                                    <Link
                                        :href="item.slug ? `/product/${item.slug}` : `/product/${item.id}`"
                                        class="text-[13.5px] sm:text-[14px] font-bold text-gray-800 leading-snug line-clamp-2 hover:text-primary transition-colors"
                                        @click="closeCartDrawer"
                                    >
                                        {{ item.name }}
                                    </Link>
                                    <button
                                        type="button"
                                        :aria-label="`Remove ${item.name}`"
                                        class="text-gray-300 hover:text-red-500 transition-colors p-0.5 shrink-0"
                                        @click="removeItem(item.id)"
                                    >
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M3 6h18M8 6V4a2 2 0 012-2h4a2 2 0 012 2v2m3 0l-1 14a2 2 0 01-2 2H7a2 2 0 01-2-2L4 6h16z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </button>
                                </div>

                                <div class="flex items-center gap-2 mt-1 flex-wrap">
                                    <span v-if="item.brand" class="text-[11px] font-semibold text-gray-400 uppercase tracking-wide">
                                        {{ item.brand }}
                                    </span>
                                    <span
                                        v-if="item.requires_prescription"
                                        class="bg-purple-100 text-purple-800 text-[9.5px] font-extrabold px-1.5 py-0.2 rounded border border-purple-200"
                                    >
                                        Rx
                                    </span>
                                    <span
                                        v-if="item.badge"
                                        class="bg-emerald-50 text-emerald-700 text-[9.5px] font-extrabold px-1.5 py-0.2 rounded border border-emerald-200"
                                    >
                                        {{ item.badge }}
                                    </span>
                                </div>

                                <div class="flex items-center gap-2 mt-1">
                                    <span class="text-[13px] font-bold text-gray-700">
                                        {{ formatTaka(item.price) }}
                                    </span>
                                    <span
                                        v-if="item.original && item.original > item.price"
                                        class="text-[11px] text-gray-400 line-through"
                                    >
                                        {{ formatTaka(item.original) }}
                                    </span>
                                </div>
                            </div>

                            <!-- Qty & Line Total Row -->
                            <div class="flex items-center justify-between mt-2 pt-2 border-t border-gray-50">
                                <div class="flex items-center gap-2 border border-gray-200 rounded-lg px-2 py-0.5 bg-gray-50/50">
                                    <button
                                        type="button"
                                        :aria-label="`Decrease quantity of ${item.name}`"
                                        class="qty-btn text-gray-500 hover:text-primary font-bold text-base px-1 leading-none cursor-pointer"
                                        @click="changeQty(item.id, -1)"
                                    >−</button>
                                    <span class="qty-val text-[13px] font-bold w-5 text-center text-gray-800">{{ item.qty }}</span>
                                    <button
                                        type="button"
                                        :aria-label="`Increase quantity of ${item.name}`"
                                        class="qty-btn text-gray-500 hover:text-primary font-bold text-base px-1 leading-none cursor-pointer"
                                        @click="changeQty(item.id, 1)"
                                    >+</button>
                                </div>

                                <span class="item-total text-[14px] font-extrabold text-primary">
                                    {{ formatTaka(item.price * item.qty) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Savings Banner -->
                <div v-if="cartDiscount > 0" class="px-5 py-2 bg-emerald-50 text-emerald-800 text-xs font-semibold flex items-center justify-between border-t border-emerald-100 shrink-0">
                    <span>🎉 Instant Savings</span>
                    <span class="font-bold">-{{ formatTaka(cartDiscount) }}</span>
                </div>

                <!-- Footer Summary & Checkout -->
                <div id="cart-footer" class="border-t border-gray-100 px-5 py-4 shrink-0 space-y-3 bg-gray-50/40">
                    <div class="space-y-1.5 text-[13.5px]">
                        <div v-if="cartMrp > cartSubtotal" class="flex items-center justify-between text-gray-400">
                            <span>Total MRP</span>
                            <span class="line-through">{{ formatTaka(cartMrp) }}</span>
                        </div>
                        <div class="flex items-center justify-between text-gray-600">
                            <span>Subtotal</span>
                            <span class="font-semibold text-gray-800">{{ formatTaka(cartSubtotal) }}</span>
                        </div>
                        <div class="flex items-center justify-between text-gray-600">
                            <span>Delivery Fee</span>
                            <span class="font-semibold text-emerald-600">FREE</span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-2 border-t border-gray-200">
                        <span class="font-bold text-gray-900 text-base">Total Payable</span>
                        <span class="font-extrabold text-primary text-xl">{{ formatTaka(cartSubtotal) }}</span>
                    </div>

                    <button
                        type="button"
                        class="w-full bg-primary hover:bg-primary2 text-white font-bold text-[15px] py-3.5 rounded-xl transition-all shadow-sm hover:shadow-md cursor-pointer mt-1"
                        @click="openCheckoutModal"
                    >
                        Proceed to Checkout &rarr;
                    </button>

                    <button
                        type="button"
                        class="w-full text-center text-[13px] text-gray-500 hover:text-primary font-medium transition-colors cursor-pointer"
                        @click="closeCartDrawer"
                    >
                        Continue Shopping
                    </button>
                </div>
            </template>

            <!-- Dynamic Empty State -->
            <div v-else id="cart-empty" class="flex-1 flex flex-col items-center justify-center px-8 py-12 text-center">
                <div class="w-20 h-20 rounded-2xl bg-teal-50 border border-teal-100 flex items-center justify-center text-4xl mb-4 text-primary shadow-2xs">
                    🛒
                </div>
                <h4 class="text-[18px] font-extrabold text-gray-800 mb-1">Your cart is empty</h4>
                <p class="text-[13.5px] text-gray-400 mb-6 max-w-xs leading-relaxed">
                    Looks like you haven't added anything yet. Browse genuine medicines, healthcare essentials & daily wellness.
                </p>
                <button
                    type="button"
                    class="bg-primary hover:bg-primary2 text-white font-bold text-[14px] px-7 py-3 rounded-xl transition-all shadow-sm hover:shadow-md cursor-pointer"
                    @click="closeCartDrawer"
                >
                    Start Shopping
                </button>
            </div>
        </aside>
    </div>
</template>
