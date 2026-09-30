<script setup>
import { ref, computed } from 'vue'
import ProductCard from './ProductCard.vue'
import { pills } from '@/data/store'

const props = defineProps({
    products: { type: Array, required: true },
    /** Show the filter pill row above the grid. */
    filterable: { type: Boolean, default: false },
    /** Show the "View All Products" button under the grid. */
    showFooter: { type: Boolean, default: false },
})

const emit = defineEmits(['add'])

const activePill = ref(pills[0])

const visible = computed(() => {
    if (!props.filterable || activePill.value === 'All') return props.products
    return props.products.filter((p) => p.category === activePill.value)
})
</script>

<template>
    <div>
        <!-- Filter pills -->
        <div v-if="filterable" class="flex gap-2 px-5 py-3 overflow-x-auto no-scrollbar border-b border-gray-50">
            <button
                v-for="pill in pills"
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

        <p v-if="!visible.length" class="px-5 pb-4 text-[14px] text-gray-400">
            No products in this category yet.
        </p>

        <!-- View all -->
        <div v-if="showFooter" class="px-4 pb-4">
            <a
                href="#"
                class="block w-full text-center border border-primary text-primary text-[15px] font-semibold py-2.5 rounded-xl hover:bg-primarylt transition-colors"
            >View All Products</a>
        </div>
    </div>
</template>
