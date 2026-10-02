<script setup>
/** Labelled text input with inline validation message, bound via v-model. */
defineProps({
    modelValue: { type: String, default: '' },
    id: { type: String, required: true },
    label: { type: String, required: true },
    type: { type: String, default: 'text' },
    placeholder: { type: String, default: '' },
    autocomplete: { type: String, default: 'off' },
    error: { type: String, default: null },
})

const emit = defineEmits(['update:modelValue'])
</script>

<template>
    <div>
        <label :for="id" class="block text-[13px] font-semibold text-gray-700 mb-1.5">
            {{ label }}
        </label>
        <input
            :id="id"
            :type="type"
            :value="modelValue"
            :placeholder="placeholder"
            :autocomplete="autocomplete"
            class="w-full border rounded-lg px-3.5 py-2.5 text-[14px] focus:outline-none transition-colors"
            :class="error ? 'border-red-300 focus:border-red-400' : 'border-gray-200 focus:border-primary'"
            @input="emit('update:modelValue', $event.target.value)"
        />
        <p v-if="error" class="text-[12.5px] text-red-500 mt-1.5">{{ error }}</p>
    </div>
</template>