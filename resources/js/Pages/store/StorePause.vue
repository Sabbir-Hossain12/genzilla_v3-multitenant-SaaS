<script setup>
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    storeName: {
        type: String,
        default: 'This Store',
    },
    isOwner: {
        type: Boolean,
        default: false,
    },
    billingUrl: {
        type: String,
        default: '/merchant/billing',
    },
    supportEmail: {
        type: String,
        default: 'support@genzilla.com',
    },
});
</script>

<template>
    <Head :title="`${storeName} - Account Paused`" />

    <div class="min-h-screen bg-gray-50 flex flex-col justify-between font-sans antialiased">
        <!-- Top Navigation / Brand Header -->
        <header class="w-full max-w-7xl mx-auto px-6 py-6 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-emerald-600 flex items-center justify-center text-white font-bold text-lg">
                    G
                </div>
                <span class="text-xl font-extrabold text-gray-900 tracking-tight">
                    Gen<span class="text-emerald-600">zilla</span>
                </span>
            </div>

            <a
                :href="`mailto:${supportEmail}`"
                class="text-xs font-semibold text-gray-500 hover:text-gray-800 transition-colors"
            >
                Contact Platform Support
            </a>
        </header>

        <!-- Main Card Content -->
        <main class="flex-1 flex items-center justify-center px-4 py-12">
            <div class="max-w-md w-full bg-white rounded-2xl shadow-xl border border-gray-100 p-8 text-center relative overflow-hidden">
                <!-- Top Accent Bar -->
                <div class="absolute top-0 left-0 right-0 h-2 bg-amber-500"></div>

                <!-- Warning Icon -->
                <div class="w-16 h-16 bg-amber-50 rounded-full flex items-center justify-center mx-auto mb-6 text-amber-500 ring-8 ring-amber-50/50">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>

                <h1 class="text-2xl font-bold text-gray-900 tracking-tight">
                    Store Temporarily Paused
                </h1>

                <p class="mt-3 text-sm text-gray-600 leading-relaxed">
                    <span class="font-semibold text-gray-900">{{ storeName }}</span> is currently inactive. Access to the storefront and catalog has been suspended.
                </p>

                <!-- Conditional View for Store Owner vs Customer -->
                <div v-if="isOwner" class="mt-8 pt-6 border-t border-gray-100">
                    <div class="bg-amber-50 border border-amber-200/80 rounded-xl p-4 text-left mb-6">
                        <p class="text-xs font-bold text-amber-900 uppercase tracking-wider mb-1">
                            Action Required for Store Owner
                        </p>
                        <p class="text-xs text-amber-800 leading-normal">
                            Your trial period or subscription term has ended. Reactivate your subscription to restore storefront availability and resume managing orders.
                        </p>
                    </div>

                    <a
                        :href="billingUrl"
                        class="block w-full py-3 px-4 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-sm rounded-xl transition-colors shadow-md shadow-emerald-600/20"
                    >
                        Reactivate Subscription
                    </a>
                </div>

                <div v-else class="mt-8 pt-6 border-t border-gray-100">
                    <p class="text-xs text-gray-400">
                        If you are the owner of this store, please log in to your store administration portal to reactivate your subscription.
                    </p>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="w-full text-center py-6 text-xs text-gray-400">
            &copy; {{ new Date().getFullYear() }} Genzilla. All rights reserved.
        </footer>
    </div>
</template>
