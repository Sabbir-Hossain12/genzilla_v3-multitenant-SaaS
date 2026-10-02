<script setup>
/**
 * Renders a declarative list of SVG shapes as a stroked line icon.
 *
 * Data modules describe icons as `{ tag, attrs }` pairs so pages and data stay
 * free of markup; this keeps the shared stroke/fill defaults in one place.
 */
defineProps({
    shapes: { type: Array, required: true },
    size: { type: [Number, String], default: 20 },
    strokeWidth: { type: [Number, String], default: 2 },
})
</script>

<template>
    <svg
        :width="size"
        :height="size"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        :stroke-width="strokeWidth"
        stroke-linecap="round"
        stroke-linejoin="round"
    >
        <component
            :is="shape.tag"
            v-for="(shape, i) in shapes"
            :key="i"
            v-bind="shape.attrs"
        />
    </svg>
</template>