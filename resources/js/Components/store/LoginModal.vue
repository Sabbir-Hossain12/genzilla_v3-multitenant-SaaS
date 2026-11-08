<script setup>
import { ref, computed, watch, onBeforeUnmount, nextTick } from 'vue'
import { usePage, router } from '@inertiajs/vue3'
import axios from 'axios'
import { loginOpen, closeLoginModal } from '@/composables/store/useStoreUi'

const page = usePage()
const storeName = computed(() => page.props.store?.name || 'MediMart')

// Steps: 'phone' | 'password' (existing) | 'otp' (new) | 'set-password' (new complete)
const step = ref('phone')
const phone = ref('')
const phoneError = ref('')
const generalError = ref('')
const loading = ref(false)

// Existing customer data
const existingCustomer = ref(null)

// Password step
const password = ref('')
const showPassword = ref(false)

// OTP step
const otp = ref(['', '', '', ''])
const resendIn = ref(0)
const otpBoxes = ref([])
const demoOtp = ref('1234')
let resendTimer = null

const otpCode = computed(() => otp.value.join(''))
const otpComplete = computed(() => otp.value.every((d) => d !== ''))

// New customer registration form
const registerForm = ref({
    firstName: '',
    lastName: '',
    email: '',
    password: '',
    passwordConfirmation: '',
})
const showRegisterPassword = ref(false)

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
    phoneError.value = ''
    generalError.value = ''
    password.value = ''
    otp.value = ['', '', '', '']
    loading.value = false
    existingCustomer.value = null
}

function submitPhone() {
    const raw = phone.value.trim()
    const valid = /^01[0-9]{9}$/.test(raw) || /^(?:\+8801|8801)[0-9]{9}$/.test(raw)
    if (!valid) {
        phoneError.value = 'Please enter a valid 11-digit mobile number (e.g. 01711223344).'
        return
    }

    phoneError.value = ''
    generalError.value = ''
    loading.value = true

    axios.post('/customer/auth/check-phone', { phone: raw })
        .then((res) => {
            loading.value = false
            if (res.data.exists) {
                // Existing customer -> prompt for password
                existingCustomer.value = res.data.customer
                step.value = 'password'
                password.value = ''
            } else {
                // New customer -> prompt for OTP
                demoOtp.value = res.data.demo_otp || '1234'
                step.value = 'otp'
                startResendTimer()
                nextTick(() => otpBoxes.value[0]?.focus())
            }
        })
        .catch((err) => {
            loading.value = false
            phoneError.value = err.response?.data?.message || 'Unable to check mobile number. Please try again.'
        })
}

function submitPassword() {
    if (!password.value) {
        generalError.value = 'Please enter your password.'
        return
    }

    loading.value = true
    generalError.value = ''

    axios.post('/customer/auth/login', {
        phone: phone.value.trim(),
        password: password.value,
    })
        .then((res) => {
            loading.value = false
            closeLoginModal()
            const name = res.data.customer?.first_name || 'Customer'
            window.dispatchEvent(new CustomEvent('medimart:toast', { detail: `✨ Welcome back, ${name}!` }))
            router.visit('/dashboard')
        })
        .catch((err) => {
            loading.value = false
            generalError.value = err.response?.data?.message || 'Incorrect password. Please try again.'
        })
}

function loginWithOtp() {
    // Allows existing customer to verify with OTP
    generalError.value = ''
    step.value = 'otp'
    startResendTimer()
    nextTick(() => otpBoxes.value[0]?.focus())
}

function onOtpInput(index) {
    const el = otpBoxes.value[index]
    if (!el) return

    el.value = el.value.replace(/[^0-9]/g, '')
    otp.value[index] = el.value

    if (el.value && index < otp.value.length - 1) {
        otpBoxes.value[index + 1]?.focus()
    }

    if (otpComplete.value) submitOtp()
}

function onOtpKeydown(index, event) {
    if (event.key === 'Backspace' && !otp.value[index] && index > 0) {
        otpBoxes.value[index - 1]?.focus()
    }
}

function submitOtp() {
    if (!otpComplete.value) return

    loading.value = true
    generalError.value = ''

    axios.post('/customer/auth/verify-otp', {
        phone: phone.value.trim(),
        otp: otpCode.value,
    })
        .then((res) => {
            loading.value = false
            if (existingCustomer.value) {
                // If existing customer verified OTP (forgot password / instant login)
                closeLoginModal()
                window.dispatchEvent(new CustomEvent('medimart:toast', { detail: '✨ Phone verified and logged in!' }))
                router.visit('/dashboard')
            } else {
                // New customer -> proceed to set password screen
                step.value = 'set-password'
                registerForm.value.firstName = ''
                registerForm.value.lastName = ''
                registerForm.value.email = ''
                registerForm.value.password = ''
                registerForm.value.passwordConfirmation = ''
            }
        })
        .catch((err) => {
            loading.value = false
            generalError.value = err.response?.data?.message || 'Invalid code. Use 1234 for demo.'
        })
}

function submitRegister() {
    if (!registerForm.value.firstName.trim()) {
        generalError.value = 'Please enter your first name.'
        return
    }
    if (registerForm.value.password.length < 6) {
        generalError.value = 'Password must be at least 6 characters.'
        return
    }
    if (registerForm.value.password !== registerForm.value.passwordConfirmation) {
        generalError.value = 'Passwords do not match.'
        return
    }

    loading.value = true
    generalError.value = ''

    axios.post('/customer/auth/register', {
        phone: phone.value.trim(),
        first_name: registerForm.value.firstName.trim(),
        last_name: registerForm.value.lastName.trim(),
        email: registerForm.value.email.trim() || null,
        password: registerForm.value.password,
        password_confirmation: registerForm.value.passwordConfirmation,
    })
        .then((res) => {
            loading.value = false
            closeLoginModal()
            window.dispatchEvent(new CustomEvent('medimart:toast', { detail: '🎉 Account created successfully!' }))
            router.visit('/dashboard')
        })
        .catch((err) => {
            loading.value = false
            generalError.value = err.response?.data?.message || 'Registration failed. Please check your inputs.'
        })
}

function onBackdropClick(event) {
    if (event.target === event.currentTarget) closeLoginModal()
}

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
                class="absolute top-4 right-4 w-9 h-9 rounded-full flex items-center justify-center text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-colors z-10 cursor-pointer"
                @click="closeLoginModal"
            >
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            </button>

            <!-- Brand header -->
            <div class="px-7 pt-8 pb-2 text-center">
                <div class="w-14 h-14 rounded-2xl bg-primary mx-auto flex items-center justify-center mb-4 text-white shadow-xs">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none">
                        <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>

                <!-- Header texts according to step -->
                <template v-if="step === 'phone'">
                    <h2 class="text-2xl font-extrabold text-gray-900">Welcome to {{ storeName }}</h2>
                    <p class="text-[14.5px] text-gray-500 mt-1.5">Enter your phone to sign in or register</p>
                </template>
                <template v-else-if="step === 'password'">
                    <h2 class="text-2xl font-extrabold text-gray-900">
                        Welcome back{{ existingCustomer?.first_name ? `, ${existingCustomer.first_name}` : '' }}!
                    </h2>
                    <p class="text-[14.5px] text-gray-500 mt-1.5">Please enter your password to continue</p>
                </template>
                <template v-else-if="step === 'otp'">
                    <h2 class="text-2xl font-extrabold text-gray-900">Verify Number</h2>
                    <p class="text-[14.5px] text-gray-500 mt-1.5">We sent a 4-digit code to verify your phone</p>
                </template>
                <template v-else-if="step === 'set-password'">
                    <h2 class="text-2xl font-extrabold text-gray-900">Create Account</h2>
                    <p class="text-[14.5px] text-gray-500 mt-1.5">Set up your name and password</p>
                </template>
            </div>

            <!-- ════════════════════════════════════════
                 STEP 1: PHONE NUMBER
            ════════════════════════════════════════ -->
            <div v-if="step === 'phone'" class="px-7 pt-5 pb-8">
                <label class="block text-[13px] font-semibold text-gray-700 mb-2">Mobile Number</label>
                <div
                    class="flex items-stretch border rounded-xl overflow-hidden focus-within:border-primary transition-colors"
                    :class="phoneError ? 'border-red-400 ring-1 ring-red-300' : 'border-gray-200'"
                >
                    <span class="flex items-center px-4 bg-gray-50 text-gray-600 text-[15px] font-semibold border-r border-gray-200 shrink-0">🇧🇩 +88</span>
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
                <p v-if="phoneError" class="text-[13px] text-red-500 mt-2">
                    {{ phoneError }}
                </p>

                <button
                    type="button"
                    :disabled="loading"
                    class="w-full mt-5 bg-primary hover:bg-primary2 text-white font-bold text-[15px] py-3.5 rounded-xl transition-colors cursor-pointer flex items-center justify-center gap-2 shadow-xs"
                    :class="loading ? 'opacity-70 cursor-not-allowed' : ''"
                    @click="submitPhone"
                >
                    <svg v-if="loading" class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    <span>{{ loading ? 'Checking...' : 'Continue' }}</span>
                </button>

                <p class="text-[12px] text-gray-400 text-center mt-4 leading-relaxed">
                    By continuing, you agree to {{ storeName }}'s
                    <a href="#" class="text-primary hover:underline">Terms of Service</a> &amp;
                    <a href="#" class="text-primary hover:underline">Privacy Policy</a>
                </p>

                <div class="flex items-center gap-3 my-5">
                    <div class="flex-1 h-px bg-gray-100"></div>
                    <span class="text-[11px] text-gray-400 font-medium uppercase tracking-wider">Demo Quick Access</span>
                    <div class="flex-1 h-px bg-gray-100"></div>
                </div>

                <div class="bg-gray-50 border border-gray-100 rounded-xl p-3 text-center">
                    <p class="text-[12px] text-gray-500">
                        Existing account demo: <span class="font-bold text-gray-700">01711223344</span> (password: <code class="text-primary font-semibold">password</code>)
                    </p>
                </div>
            </div>

            <!-- ════════════════════════════════════════
                 STEP 2: PASSWORD (EXISTING CUSTOMER)
            ════════════════════════════════════════ -->
            <div v-else-if="step === 'password'" class="px-7 pt-4 pb-8">
                <button
                    type="button"
                    class="flex items-center gap-1.5 text-[13px] font-semibold text-gray-500 hover:text-primary mb-4 transition-colors cursor-pointer"
                    @click="showPhoneStep"
                >
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    Change number
                </button>

                <div class="bg-primarylt/60 border border-primary/20 rounded-xl p-3.5 mb-4 flex items-center justify-between">
                    <div>
                        <p class="text-[12px] text-gray-500">Signing in as</p>
                        <p class="text-[14.5px] font-bold text-gray-900">+88 {{ phone }}</p>
                    </div>
                    <span class="text-[11.5px] font-semibold text-primary bg-white px-2.5 py-1 rounded-md shadow-xs">Existing Customer</span>
                </div>

                <div class="mb-2">
                    <label class="block text-[13px] font-semibold text-gray-700 mb-1.5">Password</label>
                    <div class="relative">
                        <input
                            v-model="password"
                            :type="showPassword ? 'text' : 'password'"
                            placeholder="Enter your password"
                            class="w-full border border-gray-200 rounded-xl px-4 py-3.5 text-[15px] pr-11 focus:outline-none focus:border-primary transition-colors"
                            :class="generalError ? 'border-red-400' : ''"
                            @keyup.enter="submitPassword"
                        />
                        <button
                            type="button"
                            class="absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors"
                            @click="showPassword = !showPassword"
                        >
                            <svg v-if="!showPassword" width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/></svg>
                            <svg v-else width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24M1 1l22 22" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                        </button>
                    </div>
                </div>

                <p v-if="generalError" class="text-[13px] text-red-500 mb-2">
                    {{ generalError }}
                </p>

                <div class="flex items-center justify-between mt-1 mb-4">
                    <button
                        type="button"
                        class="text-[13px] text-primary font-semibold hover:underline cursor-pointer"
                        @click="loginWithOtp"
                    >
                        Forgot password? Verify with OTP
                    </button>
                </div>

                <button
                    type="button"
                    :disabled="loading"
                    class="w-full bg-primary hover:bg-primary2 text-white font-bold text-[15px] py-3.5 rounded-xl transition-colors cursor-pointer flex items-center justify-center gap-2 shadow-xs"
                    :class="loading ? 'opacity-70 cursor-not-allowed' : ''"
                    @click="submitPassword"
                >
                    <svg v-if="loading" class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    <span>{{ loading ? 'Signing in...' : 'Sign In' }}</span>
                </button>
            </div>

            <!-- ════════════════════════════════════════
                 STEP 3: OTP (NEW CUSTOMER)
            ════════════════════════════════════════ -->
            <div v-else-if="step === 'otp'" class="px-7 pt-4 pb-8">
                <button
                    type="button"
                    class="flex items-center gap-1.5 text-[13px] font-semibold text-gray-500 hover:text-primary mb-4 transition-colors cursor-pointer"
                    @click="showPhoneStep"
                >
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    Change number
                </button>

                <p class="text-[15px] text-gray-600 mb-1">Enter the 4-digit code sent to</p>
                <p class="text-[15px] font-bold text-gray-900 mb-3">+88 {{ phone }}</p>

                <!-- Demo helper notice -->
                <div class="bg-amber-50 border border-amber-200 rounded-xl px-3.5 py-2 mb-4 flex items-center gap-2 text-[12.5px] text-amber-800">
                    <span>💡</span>
                    <span>Demo verification code: <strong class="font-bold underline text-amber-900">{{ demoOtp }}</strong></span>
                </div>

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

                <p v-if="generalError" class="text-[13px] text-red-500 mt-2 text-center">
                    {{ generalError }}
                </p>

                <button
                    type="button"
                    :disabled="loading"
                    class="w-full mt-6 bg-primary hover:bg-primary2 text-white font-bold text-[15px] py-3.5 rounded-xl transition-colors cursor-pointer flex items-center justify-center gap-2 shadow-xs"
                    :class="loading ? 'opacity-70 cursor-not-allowed' : ''"
                    @click="submitOtp"
                >
                    <svg v-if="loading" class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    <span>{{ loading ? 'Verifying...' : 'Verify Code' }}</span>
                </button>

                <p class="text-[13px] text-gray-500 text-center mt-4">
                    Didn't get the code?
                    <button
                        type="button"
                        class="text-primary font-semibold hover:underline cursor-pointer"
                        :disabled="resendIn > 0"
                        :class="resendIn > 0 ? 'opacity-60 cursor-not-allowed' : ''"
                        @click="startResendTimer"
                    >Resend<span id="resend-timer">{{ resendIn > 0 ? ` (${resendIn}s)` : '' }}</span></button>
                </p>
            </div>

            <!-- ════════════════════════════════════════
                 STEP 4: SET PASSWORD & NAME (NEW CUSTOMER)
            ════════════════════════════════════════ -->
            <div v-else-if="step === 'set-password'" class="px-7 pt-4 pb-8">
                <div class="bg-green-50 border border-green-200 rounded-xl px-3.5 py-2.5 mb-4 flex items-center gap-2 text-[13px] text-green-800">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" class="text-green-600 shrink-0"><path d="M20 6L9 17l-5-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <span>Number <strong>+88 {{ phone }}</strong> verified! Now set up your account.</span>
                </div>

                <div class="space-y-3">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[12.5px] font-semibold text-gray-700 mb-1">First Name *</label>
                            <input
                                v-model="registerForm.firstName"
                                type="text"
                                placeholder="e.g. Sabbir"
                                class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-[14px] focus:outline-none focus:border-primary transition-colors"
                            />
                        </div>
                        <div>
                            <label class="block text-[12.5px] font-semibold text-gray-700 mb-1">Last Name</label>
                            <input
                                v-model="registerForm.lastName"
                                type="text"
                                placeholder="e.g. Hossain"
                                class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-[14px] focus:outline-none focus:border-primary transition-colors"
                            />
                        </div>
                    </div>

                    <div>
                        <label class="block text-[12.5px] font-semibold text-gray-700 mb-1">Email Address (Optional)</label>
                        <input
                            v-model="registerForm.email"
                            type="email"
                            placeholder="you@example.com"
                            class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-[14px] focus:outline-none focus:border-primary transition-colors"
                        />
                    </div>

                    <div>
                        <label class="block text-[12.5px] font-semibold text-gray-700 mb-1">Create Password *</label>
                        <div class="relative">
                            <input
                                v-model="registerForm.password"
                                :type="showRegisterPassword ? 'text' : 'password'"
                                placeholder="At least 6 characters"
                                class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 pr-10 text-[14px] focus:outline-none focus:border-primary transition-colors"
                            />
                            <button
                                type="button"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                                @click="showRegisterPassword = !showRegisterPassword"
                            >
                                <svg v-if="!showRegisterPassword" width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/></svg>
                                <svg v-else width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24M1 1l22 22" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[12.5px] font-semibold text-gray-700 mb-1">Confirm Password *</label>
                        <input
                            v-model="registerForm.passwordConfirmation"
                            :type="showRegisterPassword ? 'text' : 'password'"
                            placeholder="Re-enter password"
                            class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-[14px] focus:outline-none focus:border-primary transition-colors"
                            @keyup.enter="submitRegister"
                        />
                    </div>
                </div>

                <p v-if="generalError" class="text-[13px] text-red-500 mt-2.5">
                    {{ generalError }}
                </p>

                <button
                    type="button"
                    :disabled="loading"
                    class="w-full mt-5 bg-primary hover:bg-primary2 text-white font-bold text-[15px] py-3.5 rounded-xl transition-colors cursor-pointer flex items-center justify-center gap-2 shadow-xs"
                    :class="loading ? 'opacity-70 cursor-not-allowed' : ''"
                    @click="submitRegister"
                >
                    <svg v-if="loading" class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    <span>{{ loading ? 'Creating Account...' : 'Complete & Continue' }}</span>
                </button>
            </div>
        </div>
    </div>
</template>
