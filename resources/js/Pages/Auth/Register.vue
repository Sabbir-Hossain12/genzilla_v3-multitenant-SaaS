<script setup>
import { ref } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import PlatformAuthLayout from '@/Layouts/PlatformAuthLayout.vue'
import AuthSocialButtons from '@/Components/auth/AuthSocialButtons.vue'
import AuthTextField from '@/Components/auth/AuthTextField.vue'
import AuthPasswordField from '@/Components/auth/AuthPasswordField.vue'
import { authPanels } from '@/data/platform'

const form = useForm({
    name: '',
    store_name: '',
    email: '',
    password: '',
    terms: false,
})

/**
 * The prototype has a single password field, but Laravel's `confirmed` rule
 * needs both values — so we echo the password as its own confirmation.
 *
 * `store_name` and `terms` are collected per the prototype but not yet
 * persisted: RegisteredUserController only stores name, email and password.
 */
function submit() {
    form.transform((data) => ({
        ...data,
        password_confirmation: data.password,
    }))
    form.post('/register', {
        onFinish: () => form.reset('password'),
    })
}

const termsError = ref(null)

function onSubmit() {
    termsError.value = form.terms ? null : 'You must accept the terms to continue.'
    if (!termsError.value) submit()
}
</script>

<template>
    <Head title="Sign Up" />

    <PlatformAuthLayout :heading="authPanels.register.heading" :body="authPanels.register.body">
        <h1 class="text-[26px] font-extrabold text-ink">Create your merchant account</h1>
        <p class="text-[14px] text-gray-500 mt-1.5 mb-7">
            Start your 14-day free trial — no credit card required.
        </p>

        <AuthSocialButtons />

        <form novalidate class="space-y-4" @submit.prevent="onSubmit">
            <AuthTextField
                v-model="form.name"
                id="su-name"
                label="Full Name"
                placeholder="Sabbir Hossain"
                autocomplete="name"
                :error="form.errors.name"
            />
            <AuthTextField
                v-model="form.store_name"
                id="su-store"
                label="Store / Business Name"
                placeholder="MediMart"
                :error="form.errors.store_name"
            />
            <AuthTextField
                v-model="form.email"
                id="su-email"
                label="Email Address"
                type="email"
                placeholder="you@example.com"
                autocomplete="username"
                :error="form.errors.email"
            />

            <div>
                <label for="su-password" class="block text-[13px] font-semibold text-gray-700 mb-1.5">
                    Password
                </label>
                <AuthPasswordField
                    v-model="form.password"
                    id="su-password"
                    placeholder="At least 8 characters"
                    autocomplete="new-password"
                    :has-error="Boolean(form.errors.password)"
                />
                <p v-if="form.errors.password" class="text-[12.5px] text-red-500 mt-1.5">
                    {{ form.errors.password }}
                </p>
            </div>

            <label class="flex items-start gap-2.5 cursor-pointer pt-1">
                <input v-model="form.terms" type="checkbox" class="w-4 h-4 mt-0.5 rounded accent-primary" />
                <span class="text-[12.5px] text-gray-500 leading-relaxed">
                    I agree to Shopwave's
                    <span class="text-primary" title="Terms of Service — coming soon">Terms of Service</span>
                    and
                    <span class="text-primary" title="Privacy Policy — coming soon">Privacy Policy</span>.
                </span>
            </label>
            <p v-if="termsError || form.errors.terms" class="text-[12.5px] text-red-500">
                {{ termsError || form.errors.terms }}
            </p>

            <button
                type="submit"
                class="w-full bg-primary hover:bg-primarydark text-white font-bold text-[14.5px] py-3 rounded-xl transition-colors disabled:opacity-60 disabled:cursor-not-allowed"
                :disabled="form.processing"
            >
                {{ form.processing ? 'Creating account…' : 'Create Free Account' }}
            </button>
        </form>

        <p class="text-center text-[13.5px] text-gray-500 mt-6">
            Already have an account?
            <Link :href="route('login')" class="text-primary font-semibold hover:underline">
                Log in
            </Link>
        </p>
    </PlatformAuthLayout>
</template>