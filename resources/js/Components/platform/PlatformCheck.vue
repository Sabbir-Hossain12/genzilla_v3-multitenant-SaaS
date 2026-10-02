<script setup>
/** Green tick used by plan cards, comparison tables and feature lists. */
defineProps({
    colorClass: { type: String, default: 'text-accent' },
})
</script>

<template>
    <svg
        width="16"
        height="16"
        viewBox="0 0 24 24"
        fill="none"
        class="shrink-0 mt-0.5"
        :class="colorClass"
        aria-hidden="true"
    >
        <path
            d="M20 6L9 17l-5-5"
            stroke="currentColor"
            stroke-width="2.5"
            stroke-linecap="round"
            stroke-linejoin="round"
        />
    </svg>
</template>