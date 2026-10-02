<script setup>
defineProps({
    eyebrow: { type: String, default: null },
    title: { type: String, required: true },
    subtitle: { type: String, default: null },
})
</script>

<template>
    <div class="text-center max-w-2xl mx-auto mb-12">
        <p
            v-if="eyebrow"
            class="text-[13px] font-bold text-primary uppercase tracking-wider mb-2"
        >
            {{ eyebrow }}
        </p>
        <h2 class="text-2xl sm:text-[34px] font-extrabold tracking-tight text-ink">
            {{ title }}
        </h2>
        <p v-if="subtitle" class="text-gray-500 text-[15px] sm:text-lg mt-4">
            {{ subtitle }}
        </p>
    </div>
</template>