<script setup>
import { ref, computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import ProductCard from './ProductCard.vue'

const props = defineProps({
    products: { type: Array, required: true, default: () => [] },
    /** Show the filter pill row above the grid. */
    filterable: { type: Boolean, default: false },
    /** Show the "View All Products" button under the grid. */
    showFooter: { type: Boolean, default: false },
    footerHref: { type: String, default: '/products' },
    footerLabel: { type: String, default: 'View All Products' },
})

const emit = defineEmits(['add'])

const availablePills = computed(() => {
    const categoriesSet = new Set()
    for (const p of props.products || []) {
        if (p.category) categoriesSet.add(p.category)
        if (p.parent_category) categoriesSet.add(p.parent_category)
    }
    const list = Array.from(categoriesSet)
    return list.length > 0 ? ['All', ...list] : ['All']
})

const activePill = ref('All')

const visible = computed(() => {
    if (!props.filterable || activePill.value === 'All') return props.products || []
    return (props.products || []).filter(
        (p) => p.category === activePill.value || p.parent_category === activePill.value
    )
})
</script>

<template>
    <div>
        <!-- Filter pills -->
        <div v-if="filterable && availablePills.length > 1" class="flex gap-2 px-5 py-3 overflow-x-auto no-scrollbar border-b border-gray-50">
            <button
                v-for="pill in availablePills"
                :key="pill"
                type="button"
                class="cat-pill text-[14px] font-semibold px-3 py-1.5 rounded-full border border-gray-200 whitespace-nowrap transition-colors"
                :class="activePill === pill
                    ? 'active'
                    : 'text-gray-600 hover:border-primary hover:text-primary'"
                @click="activePill = pill"
            >{{ pill }}</button>
        </div>

        <!-- Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3 p-4">
            <ProductCard
                v-for="product in visible"
                :key="product.id"
                :product="product"
                @add="emit('add', $event)"
            />
        </div>

        <p v-if="!visible.length" class="px-5 py-6 text-center text-[14px] text-gray-400">
            No products in this category yet.
        </p>

        <!-- View all -->
        <div v-if="showFooter" class="px-4 pb-4">
            <Link
                :href="footerHref"
                class="block w-full text-center border border-primary text-primary text-[15px] font-semibold py-2.5 rounded-xl hover:bg-primarylt transition-colors"
            >{{ footerLabel }}</Link>
        </div>
    </div>
</template>
