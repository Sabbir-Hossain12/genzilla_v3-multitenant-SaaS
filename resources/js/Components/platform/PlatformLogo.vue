<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
    variant: { type: String, default: 'header' },
})

const markClass = computed(() =>
    props.variant === 'header' ? 'w-8 h-8 sm:w-9 sm:h-9' : 'w-8 h-8',
)

const glyphSize = computed(() => (props.variant === 'header' ? 18 : 16))

const wordClass = computed(() => {
    if (props.variant === 'footer') return 'text-white text-lg font-extrabold'
    if (props.variant === 'drawer') return 'text-lg font-extrabold'
    return 'text-lg sm:text-xl font-extrabold tracking-tight'
})
</script>

<template>
    <Link href="/" class="flex items-center gap-2 shrink-0">
        <div
            class="rounded-lg bg-primary flex items-center justify-center"
            :class="markClass"
        >
            <svg :width="glyphSize" :height="glyphSize" viewBox="0 0 24 24" fill="none">
                <path
                    d="M3 12c3-4 6 4 9 0s6-4 9 0M3 18c3-4 6 4 9 0s6-4 9 0"
                    stroke="white"
                    stroke-width="2"
                    stroke-linecap="round"
                />
            </svg>
        </div>
        <span :class="wordClass">Shop<span class="text-accent">wave</span></span>
    </Link>
</template>
