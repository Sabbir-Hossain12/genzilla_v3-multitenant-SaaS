<script setup>
/** Monthly/yearly switch on the pricing page. Styles live in platform.css. */
defineProps({
    modelValue: { type: Boolean, default: false },
})

defineEmits(['update:modelValue'])
</script>

<template>
    <button
        type="button"
        role="switch"
        :aria-checked="modelValue"
        aria-label="Toggle yearly billing"
        class="pl-toggle-track"
        :class="{ on: modelValue }"
        @click="$emit('update:modelValue', !modelValue)"
    >
        <span class="pl-toggle-thumb" />
    </button>
</template>