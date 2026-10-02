<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import PlatformAuthLayout from '@/Layouts/PlatformAuthLayout.vue'
import AuthPasswordField from '@/Components/auth/AuthPasswordField.vue'
import { authPanels } from '@/data/platform'

const form = useForm({ password: '' })

function submit() {
    form.post('/confirm-password', {
        onFinish: () => form.reset('password'),
    })
}
</script>

<template>
    <Head title="Confirm Password" />

    <PlatformAuthLayout
        :heading="authPanels['confirm-password'].heading"
        :body="authPanels['confirm-password'].body"
    >
        <h1 class="text-[26px] font-extrabold text-ink">Confirm your password</h1>
        <p class="text-[14px] text-gray-500 mt-1.5 mb-7">
            This is a secure area. Please confirm your password before continuing.
        </p>

        <form novalidate class="space-y-4" @submit.prevent="submit">
            <div>
                <label for="cp-password" class="block text-[13px] font-semibold text-gray-700 mb-1.5">
                    Password
                </label>
                <AuthPasswordField
                    v-model="form.password"
                    id="cp-password"
                    placeholder="Enter your password"
                    autocomplete="current-password"
                    :has-error="Boolean(form.errors.password)"
                />
                <p v-if="form.errors.password" class="text-[12.5px] text-red-500 mt-1.5">
                    {{ form.errors.password }}
                </p>
            </div>

            <button
                type="submit"
                class="w-full bg-primary hover:bg-primarydark text-white font-bold text-[14.5px] py-3 rounded-xl transition-colors disabled:opacity-60 disabled:cursor-not-allowed"
                :disabled="form.processing"
            >
                {{ form.processing ? 'Confirming…' : 'Confirm' }}
            </button>
        </form>
    </PlatformAuthLayout>
</template>