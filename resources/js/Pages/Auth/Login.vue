<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import PlatformAuthLayout from '@/Layouts/PlatformAuthLayout.vue'
import AuthSocialButtons from '@/Components/auth/AuthSocialButtons.vue'
import AuthTextField from '@/Components/auth/AuthTextField.vue'
import AuthPasswordField from '@/Components/auth/AuthPasswordField.vue'
import { authPanels } from '@/data/platform'

defineProps({
    canResetPassword: { type: Boolean, default: true },
    status: { type: String, default: null },
})

const form = useForm({
    email: '',
    password: '',
    remember: false,
})

function submit() {
    form.post('/login', {
        onFinish: () => form.reset('password'),
    })
}
</script>

<template>
    <Head title="Log In" />

    <PlatformAuthLayout :heading="authPanels.login.heading" :body="authPanels.login.body">
        <h1 class="text-[26px] font-extrabold text-ink">Welcome back</h1>
        <p class="text-[14px] text-gray-500 mt-1.5 mb-7">Log in to manage your store.</p>

        <p v-if="status" class="text-[13px] text-accentdark bg-emerald-50 rounded-lg px-3.5 py-2.5 mb-5">
            {{ status }}
        </p>

        <AuthSocialButtons />

        <form novalidate class="space-y-4" @submit.prevent="submit">
            <AuthTextField
                v-model="form.email"
                id="li-email"
                label="Email Address"
                type="email"
                placeholder="you@example.com"
                autocomplete="username"
                :error="form.errors.email"
            />

            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label for="li-password" class="block text-[13px] font-semibold text-gray-700">
                        Password
                    </label>
                    <Link
                        v-if="canResetPassword"
                        :href="route('password.request')"
                        class="text-[12.5px] font-semibold text-primary hover:underline"
                    >
                        Forgot password?
                    </Link>
                </div>
                <AuthPasswordField
                    v-model="form.password"
                    id="li-password"
                    placeholder="Enter your password"
                    :has-error="Boolean(form.errors.password)"
                />
                <p v-if="form.errors.password" class="text-[12.5px] text-red-500 mt-1.5">
                    {{ form.errors.password }}
                </p>
            </div>

            <label class="flex items-center gap-2.5 cursor-pointer">
                <input v-model="form.remember" type="checkbox" class="w-4 h-4 rounded accent-primary" />
                <span class="text-[13px] text-gray-600">Remember me for 30 days</span>
            </label>

            <button
                type="submit"
                class="w-full bg-primary hover:bg-primarydark text-white font-bold text-[14.5px] py-3 rounded-xl transition-colors disabled:opacity-60 disabled:cursor-not-allowed"
                :disabled="form.processing"
            >
                {{ form.processing ? 'Logging in…' : 'Log In' }}
            </button>
        </form>

        <p class="text-center text-[13.5px] text-gray-500 mt-6">
            Don't have an account?
            <Link :href="route('register')" class="text-primary font-semibold hover:underline">
                Sign up free
            </Link>
        </p>
    </PlatformAuthLayout>
</template>