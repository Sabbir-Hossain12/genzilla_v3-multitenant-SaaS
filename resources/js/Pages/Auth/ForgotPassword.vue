<script setup>
import { onBeforeUnmount, ref } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import PlatformAuthLayout from '@/Layouts/PlatformAuthLayout.vue'
import AuthTextField from '@/Components/auth/AuthTextField.vue'
import { authPanels } from '@/data/platform'

defineProps({
    status: { type: String, default: null },
})

const form = useForm({ email: '' })

/** Swaps the form for the "check your email" state once the link is sent. */
const sent = ref(false)
const sentTo = ref('')
const resendIn = ref(0)

let timer = null

function startResendTimer() {
    resendIn.value = 30
    clearInterval(timer)
    timer = setInterval(() => {
        resendIn.value -= 1
        if (resendIn.value <= 0) clearInterval(timer)
    }, 1000)
}

function submit() {
    form.post('/forgot-password', {
        preserveScroll: true,
        onSuccess: () => {
            sentTo.value = form.email
            sent.value = true
            startResendTimer()
        },
    })
}

function resend() {
    startResendTimer()
    submit()
}

onBeforeUnmount(() => clearInterval(timer))
</script>

<template>
    <Head title="Forgot Password" />

    <PlatformAuthLayout
        :heading="authPanels['forgot-password'].heading"
        :body="authPanels['forgot-password'].body"
    >
        <!-- Request state -->
        <div v-if="!sent">
            <h1 class="text-[26px] font-extrabold text-ink">Reset your password</h1>
            <p class="text-[14px] text-gray-500 mt-1.5 mb-7">
                Enter the email associated with your account and we'll send a link to reset your
                password.
            </p>

            <p
                v-if="status"
                class="text-[13px] text-accentdark bg-emerald-50 rounded-lg px-3.5 py-2.5 mb-5"
            >
                {{ status }}
            </p>

            <form novalidate class="space-y-4" @submit.prevent="submit">
                <AuthTextField
                    v-model="form.email"
                    id="rp-email"
                    label="Email Address"
                    type="email"
                    placeholder="you@example.com"
                    autocomplete="username"
                    :error="form.errors.email"
                />
                <button
                    type="submit"
                    class="w-full bg-primary hover:bg-primarydark text-white font-bold text-[14.5px] py-3 rounded-xl transition-colors disabled:opacity-60 disabled:cursor-not-allowed"
                    :disabled="form.processing"
                >
                    {{ form.processing ? 'Sending…' : 'Send Reset Link' }}
                </button>
            </form>

            <p class="text-center text-[13.5px] text-gray-500 mt-6">
                Remembered your password?
                <Link :href="route('login')" class="text-primary font-semibold hover:underline">
                    Log in
                </Link>
            </p>
        </div>

        <!-- Success state -->
        <div v-else class="flex flex-col items-center text-center">
            <div class="w-16 h-16 rounded-full bg-emerald-50 flex items-center justify-center mb-5">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" class="text-accent">
                    <rect x="2" y="4" width="20" height="16" rx="2" stroke="currentColor" stroke-width="2" />
                    <path
                        d="M22 6l-10 7L2 6"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                </svg>
            </div>
            <h1 class="text-[22px] font-extrabold text-ink">Check your email</h1>
            <p class="text-[14px] text-gray-500 mt-2 leading-relaxed">
                We've sent a password reset link to
                <span class="font-semibold text-ink">{{ sentTo }}</span>. The link expires in 30
                minutes.
            </p>
            <button
                type="button"
                class="mt-6 text-[13.5px] font-semibold text-primary hover:underline disabled:text-gray-400 disabled:cursor-not-allowed disabled:no-underline"
                :disabled="resendIn > 0"
                @click="resend"
            >
                Didn't get it? Resend link<span v-if="resendIn > 0"> ({{ resendIn }}s)</span>
            </button>
            <Link
                :href="route('login')"
                class="block w-full text-center border border-gray-200 text-gray-700 font-semibold text-[14px] py-3 rounded-xl hover:bg-gray-50 transition-colors mt-6"
            >
                Back to Log In
            </Link>
        </div>
    </PlatformAuthLayout>
</template>