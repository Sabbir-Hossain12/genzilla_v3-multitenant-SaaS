<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import PlatformAuthLayout from '@/Layouts/PlatformAuthLayout.vue'
import AuthTextField from '@/Components/auth/AuthTextField.vue'
import AuthPasswordField from '@/Components/auth/AuthPasswordField.vue'
import { authPanels } from '@/data/platform'

const props = defineProps({
    email: { type: String, default: '' },
    token: { type: String, required: true },
})

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
})

function submit() {
    form.post('/reset-password', {
        onFinish: () => form.reset('password', 'password_confirmation'),
    })
}
</script>

<template>
    <Head title="Reset Password" />

    <PlatformAuthLayout
        :heading="authPanels['reset-password'].heading"
        :body="authPanels['reset-password'].body"
    >
        <h1 class="text-[26px] font-extrabold text-ink">Set a new password</h1>
        <p class="text-[14px] text-gray-500 mt-1.5 mb-7">
            Choose a strong password you have not used before.
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

            <div>
                <label for="rp-password" class="block text-[13px] font-semibold text-gray-700 mb-1.5">
                    New Password
                </label>
                <AuthPasswordField
                    v-model="form.password"
                    id="rp-password"
                    placeholder="At least 8 characters"
                    autocomplete="new-password"
                    :has-error="Boolean(form.errors.password)"
                />
                <p v-if="form.errors.password" class="text-[12.5px] text-red-500 mt-1.5">
                    {{ form.errors.password }}
                </p>
            </div>

            <div>
                <label
                    for="rp-password-confirmation"
                    class="block text-[13px] font-semibold text-gray-700 mb-1.5"
                >
                    Confirm Password
                </label>
                <AuthPasswordField
                    v-model="form.password_confirmation"
                    id="rp-password-confirmation"
                    placeholder="Re-enter your new password"
                    autocomplete="new-password"
                    :has-error="Boolean(form.errors.password_confirmation)"
                />
                <p v-if="form.errors.password_confirmation" class="text-[12.5px] text-red-500 mt-1.5">
                    {{ form.errors.password_confirmation }}
                </p>
            </div>

            <button
                type="submit"
                class="w-full bg-primary hover:bg-primarydark text-white font-bold text-[14.5px] py-3 rounded-xl transition-colors disabled:opacity-60 disabled:cursor-not-allowed"
                :disabled="form.processing"
            >
                {{ form.processing ? 'Saving…' : 'Reset Password' }}
            </button>
        </form>

        <p class="text-center text-[13.5px] text-gray-500 mt-6">
            Remembered your password?
            <Link :href="route('login')" class="text-primary font-semibold hover:underline">
                Log in
            </Link>
        </p>
    </PlatformAuthLayout>
</template>