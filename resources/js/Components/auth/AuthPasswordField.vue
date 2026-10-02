<script setup>
import { ref } from 'vue'

/**
 * Password input with a reveal toggle, bound through v-model.
 *
 * Inertia needs the plain string for submission, so the visible type flips
 * between 'password' and 'text' while the model keeps the actual value.
 */
defineProps({
    modelValue: { type: String, default: '' },
    id: { type: String, required: true },
    placeholder: { type: String, default: '' },
    autocomplete: { type: String, default: 'current-password' },
    hasError: { type: Boolean, default: false },
})

const emit = defineEmits(['update:modelValue'])

const revealed = ref(false)

function toggle() {
    revealed.value = !revealed.value
}
</script>

<template>
    <div class="relative">
        <input
            :id="id"
            :type="revealed ? 'text' : 'password'"
            :value="modelValue"
            :placeholder="placeholder"
            :autocomplete="autocomplete"
            class="w-full border rounded-lg px-3.5 py-2.5 pr-10 text-[14px] focus:outline-none transition-colors"
            :class="hasError ? 'border-red-300 focus:border-red-400' : 'border-gray-200 focus:border-primary'"
            @input="emit('update:modelValue', $event.target.value)"
        />
        <button
            type="button"
            class="absolute right-3 top-1/2 -translate-y-1/2 hover:text-gray-600"
            :class="revealed ? 'text-primary' : 'text-gray-400'"
            :aria-label="revealed ? 'Hide password' : 'Show password'"
            :aria-pressed="revealed"
            @click="toggle"
        >
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path
                    d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"
                    stroke="currentColor"
                    stroke-width="2"
                />
                <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2" />
            </svg>
        </button>
    </div>
</template>