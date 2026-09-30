<script setup>
import { ref, computed, watch, onBeforeUnmount, nextTick } from 'vue'
import { loginOpen, closeLoginModal } from '@/composables/useStoreUi'

const step = ref('phone')          // 'phone' | 'otp'
const phone = ref('')
const phoneError = ref(false)
const otp = ref(['', '', '', ''])
const resendIn = ref(0)

const otpBoxes = ref([])

let resendTimer = null

const otpCode = computed(() => otp.value.join(''))
const otpComplete = computed(() => otp.value.every((d) => d !== ''))

function startResendTimer() {
    resendIn.value = 30
    clearInterval(resendTimer)
    resendTimer = setInterval(() => {
        resendIn.value -= 1
        if (resendIn.value <= 0) clearInterval(resendTimer)
    }, 1000)
}

function showPhoneStep() {
    step.value = 'phone'
    phoneError.value = false
    otp.value = ['', '', '', '']
}

function submitPhone() {
    const valid = /^01[0-9]{9}$/.test(phone.value.trim())
    phoneError.value = !valid
    if (!valid) return

    step.value = 'otp'
    startResendTimer()
    nextTick(() => otpBoxes.value[0]?.focus())
}

function onOtpInput(index) {
    const el = otpBoxes.value[index]
    if (!el) return

    // Keep digits only.
    el.value = el.value.replace(/[^0-9]/g, '')
    otp.value[index] = el.value

    // Auto-advance.
    if (el.value && index < otp.value.length - 1) {
        otpBoxes.value[index + 1]?.focus()
    }

    // Auto-submit once the code is complete.
    if (otpComplete.value) submitOtp()
}

function onOtpKeydown(index, event) {
    if (event.key === 'Backspace' && !otp.value[index] && index > 0) {
        otpBoxes.value[index - 1]?.focus()
    }
}

function submitOtp() {
    if (!otpComplete.value) return
    // No backend yet — the prototype simply closes the modal.
    closeLoginModal()
}

function onBackdropClick(event) {
    if (event.target === event.currentTarget) closeLoginModal()
}

// Reopen always starts from a clean phone step.
watch(loginOpen, (isOpen) => {
    if (isOpen) {
        showPhoneStep()
        clearInterval(resendTimer)
    } else {
        clearInterval(resendTimer)
        resendIn.value = 0
    }
})

onBeforeUnmount(() => clearInterval(resendTimer))
</script>

<template>
    <div id="login-modal" :class="{ open: loginOpen }" @click="onBackdropClick">
        <div class="modal-card bg-white w-full max-w-md rounded-2xl shadow-2xl overflow-hidden relative">
            <button
                type="button"
                aria-label="Close"
                class="absolute top-4 right-4 w-9 h-9 rounded-full flex items-center justify-center text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-colors"
                @click="closeLoginModal"
            >
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            </button>

            <div class="px-7 pt-8 pb-2 text-center">
                <div class="w-14 h-14 rounded-2xl bg-primary mx-auto flex items-center justify-center mb-4">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none">
                        <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <h2 class="text-2xl font-extrabold text-gray-900">Welcome to MediMart</h2>
                <p class="text-[15px] text-gray-500 mt-1.5">Login or create an account to continue</p>
            </div>

            <!-- Step 1: phone -->
            <div v-if="step === 'phone'" id="login-step-phone" class="px-7 pt-6 pb-8">
                <label class="block text-[13px] font-semibold text-gray-700 mb-2">Mobile Number</label>
                <div
                    class="flex items-stretch border rounded-xl overflow-hidden focus-within:border-primary transition-colors"
                    :class="phoneError ? 'border-red-400' : 'border-gray-200'"
                >
                    <span class="flex items-center px-4 bg-gray-50 text-gray-600 text-[15px] font-semibold border-r border-gray-200">🇧🇩 +88</span>
                    <input
                        v-model="phone"
                        type="tel"
                        inputmode="numeric"
                        maxlength="11"
                        placeholder="01XXXXXXXXX"
                        class="flex-1 px-4 py-3.5 text-[15px] focus:outline-none"
                        @keyup.enter="submitPhone"
                    />
                </div>
                <p v-if="phoneError" id="login-phone-error" class="text-[13px] text-red-500 mt-2">
                    Please enter a valid 11-digit mobile number.
                </p>

                <button
                    type="button"
                    class="w-full mt-5 bg-primary hover:bg-primary2 text-white font-bold text-[15px] py-3.5 rounded-xl transition-colors"
                    @click="submitPhone"
                >Continue</button>

                <p class="text-[12px] text-gray-400 text-center mt-4 leading-relaxed">
                    By continuing, you agree to MediMart's
                    <a href="#" class="text-primary hover:underline">Terms of Service</a> &amp;
                    <a href="#" class="text-primary hover:underline">Privacy Policy</a>
                </p>

                <div class="flex items-center gap-3 my-6">
                    <div class="flex-1 h-px bg-gray-100"></div>
                    <span class="text-[12px] text-gray-400 font-medium">OR CONTINUE WITH</span>
                    <div class="flex-1 h-px bg-gray-100"></div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <button type="button" class="flex items-center justify-center gap-2 border border-gray-200 rounded-xl py-3 text-[14px] font-semibold text-gray-700 hover:bg-gray-50 transition-colors">
                        <svg width="18" height="18" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
                        Google
                    </button>
                    <button type="button" class="flex items-center justify-center gap-2 border border-gray-200 rounded-xl py-3 text-[14px] font-semibold text-gray-700 hover:bg-gray-50 transition-colors">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="#1877F2"><path d="M24 12.07C24 5.7 18.63.5 12 .5S0 5.7 0 12.07c0 5.75 4.39 10.52 10.13 11.43v-8.09H7.08v-3.34h3.05V9.41c0-3 1.8-4.67 4.55-4.67 1.32 0 2.7.24 2.7.24v2.94h-1.52c-1.5 0-1.97.92-1.97 1.87v2.24h3.35l-.54 3.34h-2.81v8.09C19.61 22.59 24 17.82 24 12.07z"/></svg>
                        Facebook
                    </button>
                </div>
            </div>

            <!-- Step 2: OTP -->
            <div v-else id="login-step-otp" class="px-7 pt-6 pb-8">
                <button
                    type="button"
                    class="flex items-center gap-1.5 text-[13px] font-semibold text-gray-500 hover:text-primary mb-4 transition-colors"
                    @click="showPhoneStep"
                >
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    Change number
                </button>

                <p class="text-[15px] text-gray-600 mb-1">Enter the 4-digit code sent to</p>
                <p class="text-[15px] font-bold text-gray-900 mb-5" id="login-phone-display">+88 {{ phone }}</p>

                <div class="flex gap-2.5 justify-between">
                    <input
                        v-for="(_, i) in otp"
                        :key="i"
                        :ref="(el) => { if (el) otpBoxes[i] = el }"
                        type="text"
                        maxlength="1"
                        inputmode="numeric"
                        autocomplete="one-time-code"
                        class="otp-box text-center text-xl font-bold border rounded-xl focus:outline-none focus:border-primary transition-colors"
                        :class="otp[i] ? 'border-primary' : 'border-gray-200'"
                        @input="onOtpInput(i)"
                        @keydown="onOtpKeydown(i, $event)"
                    />
                </div>

                <button
                    type="button"
                    class="w-full mt-6 bg-primary hover:bg-primary2 text-white font-bold text-[15px] py-3.5 rounded-xl transition-colors"
                    @click="submitOtp"
                >Verify &amp; Continue</button>

                <p class="text-[13px] text-gray-500 text-center mt-4">
                    Didn't get the code?
                    <button
                        type="button"
                        class="text-primary font-semibold hover:underline"
                        :disabled="resendIn > 0"
                        :class="resendIn > 0 ? 'opacity-60 cursor-not-allowed' : ''"
                        @click="startResendTimer"
                    >Resend<span id="resend-timer">{{ resendIn > 0 ? ` (${resendIn}s)` : '' }}</span></button>
                </p>
            </div>
        </div>
    </div>
</template>
