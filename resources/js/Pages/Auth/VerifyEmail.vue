<script setup>
import { computed } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import PlatformAuthLayout from '@/Layouts/PlatformAuthLayout.vue'
import { authPanels } from '@/data/platform'

const props = defineProps({
    status: { type: String, default: null },
})

const form = useForm({})

const verificationLinkSent = computed(() => props.status === 'verification-link-sent')

function submit() {
    form.post(route('verification.send'))
}
</script>

<template>
    <Head title="Email Verification" />

    <PlatformAuthLayout
        :heading="authPanels['verify-email'].heading"
        :body="authPanels['verify-email'].body"
    >
        <h1 class="text-[26px] font-extrabold text-ink">Verify your email address</h1>
        <p class="text-[14px] text-gray-500 mt-1.5 mb-7">
            We sent a verification link to your inbox. Click it to finish setting up your account.
        </p>

        <p
            v-if="status === 'verification-link-sent'"
            class="text-[13px] text-accentdark bg-emerald-50 rounded-lg px-3.5 py-2.5 mb-5"
        >
            A new verification link has been sent to your email address.
        </p>

        <form novalidate class="space-y-4" @submit.prevent="submit">
            <button
                type="submit"
                class="w-full bg-primary hover:bg-primarydark text-white font-bold text-[14.5px] py-3 rounded-xl transition-colors disabled:opacity-60 disabled:cursor-not-allowed"
                :disabled="form.processing"
            >
                {{ form.processing ? 'Sending…' : 'Resend Verification Email' }}
            </button>
        </form>

        <p class="text-center text-[13.5px] text-gray-500 mt-6">
            <Link :href="route('dashboard')" class="text-primary font-semibold hover:underline">
                Skip for now
            </Link>
        </p>
    </PlatformAuthLayout>
</template>