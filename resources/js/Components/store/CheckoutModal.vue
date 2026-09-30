<script setup>
import { ref, computed, watch } from 'vue'
import {
    cart,
    cartCount,
    cartMrp,
    cartSubtotal,
    cartDiscount,
    cashApplied,
    cartPayable,
    formatTaka,
} from '@/composables/useCart'
import { checkoutOpen, closeCheckoutModal } from '@/composables/useStoreUi'

// Address
const savedAddress = ref(null)
const addressMode = ref('view')   // 'view' | 'empty' | 'form'
const pendingLabel = ref('Home')
const addrName = ref('')
const addrPhone = ref('')
const addrLine = ref('')
const addrError = ref('')
const addrNote = ref('')

const labels = [
    { key: 'Home',   icon: '🏠' },
    { key: 'Office', icon: '🏢' },
    { key: 'Other',  icon: '📍' },
]

// Payment
const payMethod = ref('cod')
const autoReorder = ref(false)
const agreementChecked = ref(true)

const agreementShake = ref(false)
const addressShake = ref(false)

const addrFormEl = ref(null)
const agreementEl = ref(null)

const paymentMethods = [
    { key: 'cod',   label: 'Cash on Delivery', badge: '💵' },
    { key: 'bkash', label: 'bKash',            badge: 'bKash', badgeClass: 'bg-[#e2136e]' },
    { key: 'card',  label: 'Cards & Others',   badge: null },
]

function openAddressForm() {
    addressMode.value = 'form'
    addrError.value = ''

    if (savedAddress.value) {
        addrName.value = savedAddress.value.name
        addrPhone.value = savedAddress.value.phone
        addrLine.value = savedAddress.value.address
        pendingLabel.value = savedAddress.value.label
    } else {
        addrName.value = ''
        addrPhone.value = ''
        addrLine.value = ''
        pendingLabel.value = 'Home'
    }
}

function saveAddress() {
    if (!addrName.value.trim() || !addrPhone.value.trim() || !addrLine.value.trim()) {
        addrError.value = 'Please fill in name, phone & address.'
        return
    }

    addrError.value = ''
    savedAddress.value = {
        label: pendingLabel.value,
        name: addrName.value.trim(),
        phone: addrPhone.value.trim(),
        address: addrLine.value.trim(),
    }
    addressMode.value = 'view'
}

function cancelAddressForm() {
    addressMode.value = 'view'
}

// Place order
function placeOrder() {
    if (!savedAddress.value) {
        openAddressForm()
        addressShake.value = true
        setTimeout(() => { addressShake.value = false }, 1500)
        return
    }

    if (!agreementChecked.value) {
        agreementShake.value = true
        setTimeout(() => { agreementShake.value = false }, 1200)
        return
    }

    closeCheckoutModal()
    window.dispatchEvent(new CustomEvent('medimart:toast', { detail: '🎉 Order placed successfully!' }))
}

function onBackdropClick(event) {
    if (event.target === event.currentTarget) closeCheckoutModal()
}

// Keep the view in sync with what the cart drawer shows.
watch(checkoutOpen, (isOpen) => {
    if (isOpen) addressMode.value = savedAddress.value ? 'view' : 'empty'
})
</script>

<template>
    <div id="checkout-modal" :class="{ open: checkoutOpen }" @click="onBackdropClick">
        <div class="modal-card bg-white w-full max-w-lg rounded-2xl shadow-2xl overflow-hidden relative flex flex-col" style="max-height:92vh;">

            <!-- Header -->
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 shrink-0">
                <h2 class="text-[19px] font-bold text-gray-900">Checkout</h2>
                <button
                    type="button"
                    aria-label="Close checkout"
                    class="w-9 h-9 rounded-full flex items-center justify-center text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-colors"
                    @click="closeCheckoutModal"
                >
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                </button>
            </div>

            <!-- Scrollable body -->
            <div class="flex-1 overflow-y-auto px-5 py-4 space-y-4">

                <!-- Delivery address -->
                <div class="border border-gray-100 rounded-xl p-4">
                    <h3 class="text-[15px] font-bold text-gray-900 mb-1">Selected delivery address</h3>

                    <!-- Filled state -->
                    <div v-if="addressMode === 'view' && savedAddress" id="address-filled">
                        <div class="flex items-center justify-between mt-2">
                            <span id="address-label-pill" class="inline-block bg-primarylt text-primary text-[12px] font-semibold px-2.5 rounded-md">
                                {{ savedAddress.label }}
                            </span>
                            <button type="button" class="text-[13px] font-semibold text-primary hover:underline" @click="openAddressForm">
                                Change
                            </button>
                        </div>
                        <p id="address-name" class="text-[14.5px] font-bold text-gray-900 mt-2.5">{{ savedAddress.name }}</p>
                        <p id="address-phone" class="text-[14px] text-gray-600 mt-0.5">{{ savedAddress.phone }}</p>
                        <p id="address-line" class="text-[14px] text-gray-500 mt-0.5 leading-snug">{{ savedAddress.address }}</p>

                        <textarea
                            v-model="addrNote"
                            maxlength="120"
                            rows="2"
                            placeholder="Write additional information"
                            class="w-full mt-3 border border-gray-200 rounded-lg px-3 py-2.5 text-[13.5px] text-gray-700 resize-none focus:outline-none focus:border-primary transition-colors"
                        ></textarea>
                        <p class="text-[12px] text-gray-400 text-right mt-1">
                            <span id="addr-note-count">{{ addrNote.length }}</span>/120
                        </p>
                    </div>

                    <!-- Empty state -->
                    <div v-else-if="addressMode === 'empty'" id="address-empty" class="flex flex-col items-center text-center py-4">
                        <div class="w-12 h-12 rounded-full bg-gray-50 flex items-center justify-center mb-2.5">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" class="text-gray-400"><path d="M21 10c0 6-9 12-9 12S3 16 3 10a9 9 0 0118 0z" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="10" r="3" stroke="currentColor" stroke-width="2"/></svg>
                        </div>
                        <p class="text-[14px] text-gray-500 mb-3">No saved address found</p>
                        <button
                            type="button"
                            class="flex items-center gap-1.5 bg-primary hover:bg-primary2 text-white text-[13.5px] font-semibold px-4 py-2 rounded-lg transition-colors"
                            @click="openAddressForm"
                        >
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                            Add New Address
                        </button>
                    </div>

                    <!-- Add / edit form -->
                    <div
                        v-else
                        id="address-form"
                        ref="addrFormEl"
                        class="pt-1"
                        :class="addressShake ? 'ring-2 ring-red-300 rounded-lg' : ''"
                    >
                        <div class="flex gap-2 mb-3">
                            <button
                                v-for="l in labels"
                                :key="l.key"
                                type="button"
                                class="addr-label-btn flex-1 text-[13px] font-semibold py-1.5 rounded-lg border transition-colors"
                                :class="pendingLabel === l.key ? 'active' : ''"
                                @click="pendingLabel = l.key"
                            >{{ l.icon }} {{ l.key }}</button>
                        </div>

                        <input
                            v-model="addrName"
                            type="text"
                            placeholder="Full name"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-[14px] mb-2.5 focus:outline-none focus:border-primary transition-colors"
                        />
                        <input
                            v-model="addrPhone"
                            type="tel"
                            placeholder="Phone number"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-[14px] mb-2.5 focus:outline-none focus:border-primary transition-colors"
                        />
                        <textarea
                            v-model="addrLine"
                            rows="2"
                            placeholder="Full address (house, road, area, city)"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-[14px] mb-1 resize-none focus:outline-none focus:border-primary transition-colors"
                        ></textarea>

                        <p v-if="addrError" id="address-form-error" class="text-[12.5px] text-red-500 mb-2">{{ addrError }}</p>

                        <div class="flex gap-2 mt-2">
                            <button
                                type="button"
                                class="flex-1 border border-gray-200 text-gray-600 text-[13.5px] font-semibold py-2.5 rounded-lg hover:bg-gray-50 transition-colors"
                                @click="cancelAddressForm"
                            >Cancel</button>
                            <button
                                type="button"
                                class="flex-1 bg-primary hover:bg-primary2 text-white text-[13.5px] font-semibold py-2.5 rounded-lg transition-colors"
                                @click="saveAddress"
                            >Save Address</button>
                        </div>
                    </div>
                </div>

                <!-- Delivery method -->
                <div class="border border-gray-100 rounded-xl p-4">
                    <h3 class="text-[15px] font-bold text-gray-900 mb-3">Delivery Method</h3>

                    <div class="flex items-center justify-between py-1.5">
                        <span class="flex items-center gap-2.5">
                            <span class="w-[18px] h-[18px] rounded-full border-2 border-primary flex items-center justify-center shrink-0">
                                <span class="w-2 h-2 rounded-full bg-primary"></span>
                            </span>
                            <span class="text-[14.5px] text-gray-800">
                                Regular Delivery
                                <span class="block text-[12.5px] text-gray-400">Estimated 12-48 Hours (In Dhaka City)</span>
                            </span>
                        </span>
                        <span class="text-[13.5px] font-semibold text-green-600 shrink-0">Free</span>
                    </div>

                    <div class="flex items-center justify-between py-1.5 opacity-50 cursor-not-allowed">
                        <span class="flex items-center gap-2.5">
                            <span class="w-[18px] h-[18px] rounded-full border-2 border-gray-300 shrink-0"></span>
                            <span class="text-[14.5px] text-gray-400">
                                Express Delivery
                                <span class="block text-[12.5px] text-primary/70">Not available for your location</span>
                            </span>
                        </span>
                    </div>
                </div>

                <!-- Coupon -->
                <details class="border border-gray-100 rounded-xl group">
                    <summary class="flex items-center justify-between p-4 cursor-pointer">
                        <div>
                            <h3 class="text-[15px] font-bold text-gray-900">Coupon code</h3>
                            <p class="text-[13px] text-gray-400 mt-0.5">Use coupon and save your money!</p>
                        </div>
                        <svg class="co-chevron text-gray-400 shrink-0" width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M9 18l6-6-6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </summary>
                    <div class="px-4 pb-4 flex gap-2">
                        <input type="text" placeholder="Enter coupon code" class="flex-1 min-w-0 border border-gray-200 rounded-lg px-3 py-2.5 text-[14px] focus:outline-none focus:border-primary transition-colors" />
                        <button type="button" class="bg-gray-900 hover:bg-gray-800 text-white text-[13.5px] font-semibold px-4 rounded-lg transition-colors shrink-0">Apply</button>
                    </div>
                </details>

                <!-- Payment method -->
                <div class="border border-gray-100 rounded-xl p-4">
                    <h3 class="text-[15px] font-bold text-gray-900 mb-3">Payment Method</h3>

                    <label
                        v-for="(method, i) in paymentMethods"
                        :key="method.key"
                        class="flex items-center justify-between py-2.5 cursor-pointer"
                        :class="i < paymentMethods.length - 1 ? 'border-b border-gray-50' : ''"
                    >
                        <span class="flex items-center gap-2.5">
                            <span
                                class="pay-radio w-[18px] h-[18px] rounded-full border-2 flex items-center justify-center shrink-0"
                                :class="payMethod === method.key ? 'border-primary pay-radio-selected' : 'border-gray-300'"
                            >
                                <span v-if="payMethod === method.key" class="w-2 h-2 rounded-full bg-primary"></span>
                            </span>
                            <span class="text-[14.5px] text-gray-800">{{ method.label }}</span>
                        </span>

                        <span v-if="method.key === 'card'" class="flex items-center gap-1">
                            <span class="bg-blue-600 text-white text-[9px] font-extrabold px-1.5 py-1 rounded">VISA</span>
                            <span class="bg-orange-500 text-white text-[9px] font-extrabold px-1.5 py-1 rounded">MC</span>
                            <span class="bg-gray-700 text-white text-[9px] font-extrabold px-1.5 py-1 rounded">AMEX</span>
                        </span>
                        <span
                            v-else-if="method.badge"
                            class="text-lg"
                            :class="method.badgeClass"
                        >{{ method.badge }}</span>

                        <input v-model="payMethod" type="radio" name="paymethod" :value="method.key" class="hidden" />
                    </label>
                </div>

                <!-- Payment summary -->
                <div class="border border-gray-100 rounded-xl p-4">
                    <h3 class="text-[15px] font-bold text-gray-900 mb-1">Payment Summary</h3>
                    <p id="co-savings-line" class="text-[13px] text-gray-500 mb-3">
                        You are saving {{ formatTaka(cartDiscount) }} in this order.
                    </p>
                    <div class="space-y-2 text-[14px]">
                        <div class="flex items-center justify-between text-gray-600">
                            <span>MRP</span>
                            <span id="co-mrp" class="font-medium text-gray-800">{{ formatTaka(cartMrp) }}</span>
                        </div>
                        <div class="flex items-center justify-between text-gray-600">
                            <span>Delivery charge (In Dhaka City)</span>
                            <span class="font-medium text-green-600">Free</span>
                        </div>
                        <div class="flex items-center justify-between text-gray-600">
                            <span>Discount applied</span>
                            <span id="co-discount" class="font-medium text-red-500">-{{ formatTaka(cartDiscount) }}</span>
                        </div>
                        <div class="flex items-center justify-between text-gray-600">
                            <span>MediMart Cash Applied</span>
                            <span id="co-cash" class="font-medium text-gray-800">{{ formatTaka(cashApplied) }}</span>
                        </div>
                        <div class="flex items-center justify-between pt-2 border-t border-gray-100">
                            <span class="font-bold text-gray-900">Payable</span>
                            <span id="co-payable" class="font-extrabold text-primary text-[16px]">{{ formatTaka(cartPayable) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Selected items -->
                <details class="border border-gray-100 rounded-xl group">
                    <summary class="flex items-center justify-between p-4 cursor-pointer">
                        <h3 class="text-[15px] font-bold text-gray-900">Selected product Item(s)</h3>
                        <svg class="co-chevron text-gray-400 shrink-0" width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M9 18l6-6-6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </summary>
                    <p id="co-item-count" class="px-4 -mt-2 pb-3 text-[13.5px] text-gray-400">Item Count: {{ cartCount }}</p>
                    <div id="co-item-list" class="px-4 pb-4 space-y-3 border-t border-gray-50 pt-3">
                        <div v-for="item in cart" :key="item.id" class="flex items-center justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-[13.5px] text-gray-700 leading-snug line-clamp-2">{{ item.name }}</p>
                                <p class="text-[12px] text-gray-400 mt-0.5">Qty: {{ item.qty }}</p>
                            </div>
                            <span class="text-[13.5px] font-semibold text-gray-800 shrink-0">{{ formatTaka(item.price * item.qty) }}</span>
                        </div>
                    </div>
                </details>

                <!-- Auto reorder -->
                <div class="border border-gray-100 rounded-xl p-4 flex items-center justify-between gap-4">
                    <div>
                        <h3 class="text-[14.5px] font-bold text-gray-900">Monthly auto reorder</h3>
                        <p class="text-[13px] text-gray-400 mt-0.5">Save time with recurring orders. We'll confirm with you before each delivery.</p>
                    </div>
                    <button
                        type="button"
                        role="switch"
                        :aria-checked="autoReorder"
                        aria-label="Toggle monthly auto reorder"
                        class="toggle-track shrink-0"
                        :class="autoReorder ? 'on' : ''"
                        @click="autoReorder = !autoReorder"
                    >
                        <span class="toggle-thumb"></span>
                    </button>
                </div>

                <!-- Agreement -->
                <div class="border border-gray-100 rounded-xl p-4">
                    <h3 class="text-[14.5px] font-bold text-gray-900 mb-2.5">Agreement</h3>
                    <div class="flex items-start gap-2.5">
                        <button
                            id="agreement-check"
                            ref="agreementEl"
                            type="button"
                            role="checkbox"
                            :aria-checked="agreementChecked"
                            aria-label="Agree to terms"
                            class="w-5 h-5 rounded-md flex items-center justify-center shrink-0 mt-0.5 transition-colors"
                            :class="[
                                agreementChecked
                                    ? 'bg-primary'
                                    : 'bg-white border border-gray-300',
                                agreementShake ? 'ring-2 ring-red-400' : '',
                            ]"
                            @click="agreementChecked = !agreementChecked"
                        >
                            <svg v-if="agreementChecked" width="12" height="12" viewBox="0 0 24 24" fill="none"><path d="M20 6L9 17l-5-5" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </button>
                        <span class="text-[13.5px] text-gray-500 leading-relaxed">
                            I agree to the <a href="#" class="text-primary hover:underline">Terms and Conditions</a>,
                            <a href="#" class="text-primary hover:underline">Privacy Policy</a>,
                            <a href="#" class="text-primary hover:underline">Return and Refund Policy.</a>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Sticky footer -->
            <div class="border-t border-gray-100 px-5 py-4 shrink-0 flex items-center justify-between gap-4">
                <div>
                    <p class="text-[15px] text-gray-800">Payable: <span id="co-payable-footer" class="font-bold">{{ formatTaka(cartPayable) }}</span></p>
                    <p class="text-[12.5px] text-gray-400">Saved: <span id="co-saved-footer">{{ formatTaka(cartDiscount) }}</span></p>
                </div>
                <button
                    id="place-order-btn"
                    type="button"
                    class="bg-primary hover:bg-primary2 text-white font-bold text-[15px] px-7 py-3 rounded-xl transition-colors shrink-0"
                    @click="placeOrder"
                >Place Order</button>
            </div>
        </div>
    </div>
</template>
