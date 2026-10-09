<script setup>
import { computed } from 'vue'
import { Link, useForm, usePage } from '@inertiajs/vue3'
import PlatformLogo from '@/Components/platform/PlatformLogo.vue'
import { footerColumns, legalLinks, socials } from '@/data/platform'

const page = usePage()
const brand = computed(() => page.props.brand ?? {})

const form = useForm({ email: '' })

function subscribe() {
    form.post('/newsletter', {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    })
}

/**
 * Show only the socials that have a configured link; fall back to the full set
 * (unlinked) when the platform settings carry no social profiles yet.
 */
const socialsView = computed(() => {
    const links = brand.value.socials ?? {}
    const hasAny = Object.values(links).some(Boolean)

    return socials
        .map((social) => ({ ...social, href: links[social.key] ?? null }))
        .filter((social) => !hasAny || social.href)
})
</script>

<template>
    <footer class="bg-ink text-gray-400">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 pt-14 pb-8">
            <!-- Newsletter -->
            <div
                class="flex flex-col lg:flex-row items-center justify-between gap-5 pb-10 mb-10 border-b border-white/10"
            >
                <div class="text-center lg:text-left">
                    <h3 class="text-white text-[19px] font-bold">Stay in the loop</h3>
                    <p class="text-[13.5px] mt-1">
                        Product updates, ecommerce tips, and playbooks — no spam.
                    </p>
                </div>
                <div class="w-full max-w-md">
                    <form class="flex gap-2" @submit.prevent="subscribe">
                        <input
                            v-model="form.email"
                            type="email"
                            required
                            placeholder="Enter your email"
                            aria-label="Email address"
                            class="flex-1 min-w-0 bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-[14px] text-white placeholder-gray-500 focus:outline-none focus:border-primary transition-colors"
                        />
                        <button
                            type="submit"
                            class="bg-primary hover:bg-primarydark text-white font-semibold text-[14px] px-5 rounded-xl transition-colors shrink-0 disabled:opacity-60"
                            :disabled="form.processing"
                        >
                            Subscribe
                        </button>
                    </form>
                    <p
                        v-if="form.recentlySuccessful"
                        class="text-[13px] text-accent mt-2 lg:text-right"
                    >
                        Thanks — you're on the list.
                    </p>
                    <p
                        v-else-if="form.errors.email"
                        class="text-[13px] text-rose-400 mt-2 lg:text-right"
                    >
                        {{ form.errors.email }}
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-8">
                <div class="col-span-2 sm:col-span-1 lg:col-span-2">
                    <PlatformLogo variant="footer" class="mb-3" />
                    <p class="text-[13.5px] leading-relaxed max-w-xs">
                        {{
                            brand.short_desc ||
                            'The all-in-one commerce platform for selling physical and digital products online.'
                        }}
                    </p>
                    <div class="flex items-center gap-2 mt-4">
                        <template v-for="social in socialsView" :key="social.label">
                            <a
                                v-if="social.href"
                                :href="social.href"
                                target="_blank"
                                rel="noopener noreferrer"
                                :aria-label="social.label"
                                :title="social.label"
                                class="w-9 h-9 rounded-full bg-white/5 flex items-center justify-center hover:bg-white/10 transition-colors"
                            >
                                <svg
                                    v-if="social.solid"
                                    width="15"
                                    height="15"
                                    viewBox="0 0 24 24"
                                    fill="currentColor"
                                >
                                    <path :d="social.path" />
                                </svg>
                                <svg v-else width="15" height="15" viewBox="0 0 24 24" fill="none">
                                    <component
                                        :is="shape.tag"
                                        v-for="(shape, i) in social.shapes"
                                        :key="i"
                                        v-bind="shape.attrs"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                </svg>
                            </a>
                            <button
                                v-else
                                type="button"
                                :aria-label="social.label"
                                :title="social.label"
                                class="w-9 h-9 rounded-full bg-white/5 flex items-center justify-center hover:bg-white/10 transition-colors"
                            >
                                <svg
                                    v-if="social.solid"
                                    width="15"
                                    height="15"
                                    viewBox="0 0 24 24"
                                    fill="currentColor"
                                >
                                    <path :d="social.path" />
                                </svg>
                                <svg v-else width="15" height="15" viewBox="0 0 24 24" fill="none">
                                    <component
                                        :is="shape.tag"
                                        v-for="(shape, i) in social.shapes"
                                        :key="i"
                                        v-bind="shape.attrs"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                </svg>
                            </button>
                        </template>
                    </div>
                </div>

                <div v-for="column in footerColumns" :key="column.title">
                    <p class="text-white text-[13.5px] font-bold mb-3">{{ column.title }}</p>
                    <ul class="space-y-2.5 text-[13.5px]">
                        <li v-for="link in column.links" :key="link.label">
                            <Link
                                v-if="link.to"
                                :href="link.to"
                                class="hover:text-white transition-colors"
                            >
                                {{ link.label }}
                            </Link>
                            <span v-else class="cursor-default hover:text-white transition-colors">
                                {{ link.label }}
                            </span>
                        </li>
                    </ul>
                </div>
            </div>

            <div
                class="flex flex-col sm:flex-row items-center justify-between gap-3 mt-10 pt-6 border-t border-white/10 text-[12.5px]"
            >
                <p>{{ brand.copyright || '© 2026 Shopwave, Inc. All rights reserved.' }}</p>
                <div class="flex items-center gap-5">
                    <template v-for="link in legalLinks" :key="link.label">
                        <Link
                            v-if="link.to"
                            :href="link.to"
                            class="hover:text-white transition-colors"
                        >
                            {{ link.label }}
                        </Link>
                        <span v-else class="cursor-default hover:text-white transition-colors">
                            {{ link.label }}
                        </span>
                    </template>
                </div>
            </div>
        </div>
    </footer>
</template>
