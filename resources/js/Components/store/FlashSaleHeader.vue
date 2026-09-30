<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'

const props = defineProps({
    /** Seconds remaining. Defaults to 4h 37m 22s, matching the prototype. */
    initialSeconds: { type: Number, default: 4 * 3600 + 37 * 60 + 22 },
})

const remaining = ref(props.initialSeconds)

let timer = null

onMounted(() => {
    timer = setInterval(() => {
        if (remaining.value > 0) remaining.value -= 1
        else clearInterval(timer)
    }, 1000)
})

onBeforeUnmount(() => clearInterval(timer))

const parts = computed(() => {
    const total = Math.max(0, remaining.value)
    return [
        String(Math.floor(total / 3600)).padStart(2, '0'),
        String(Math.floor((total % 3600) / 60)).padStart(2, '0'),
        String(total % 60).padStart(2, '0'),
    ]
})
</script>

<template>
    <div class="flex items-center gap-3">
        <div class="flex items-center gap-1.5">
            <span class="w-2 h-2 rounded-full bg-red-500 pulse-dot"></span>
            <span class="text-[17px] font-bold text-gray-900">⚡ Flash Sale</span>
        </div>

        <div class="flex items-center gap-1 text-red-500">
            <div class="bg-red-500 text-white text-[14px] font-bold px-2 py-0.5 rounded">{{ parts[0] }}</div>
            <span class="text-[13px] font-bold">:</span>
            <div class="bg-red-500 text-white text-[14px] font-bold px-2 py-0.5 rounded">{{ parts[1] }}</div>
            <span class="text-[13px] font-bold">:</span>
            <div class="bg-red-500 text-white text-[14px] font-bold px-2 py-0.5 rounded">{{ parts[2] }}</div>
        </div>
    </div>
</template>
