<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { categoryTiles as fallbackTiles } from '@/data/store'

const props = defineProps({
    tiles: {
        type: Array,
        default: () => [],
    },
})

const displayTiles = computed(() => {
    return props.tiles && props.tiles.length > 0
        ? props.tiles
        : fallbackTiles
})
</script>

<template>
    <div class="grid grid-cols-4 sm:grid-cols-6 gap-3">
        <Link
            v-for="tile in displayTiles"
            :key="tile.slug || tile.name"
            :href="tile.slug ? `/category/${tile.slug}` : '#'"
            class="flex flex-col items-center gap-1.5 group"
        >
            <div class="w-full aspect-square rounded-2xl overflow-hidden shrink-0 ring-1 ring-gray-100 group-hover:ring-2 group-hover:ring-primary/40 transition-all bg-gray-50 flex items-center justify-center">
                <img
                    :src="tile.image"
                    :alt="tile.name"
                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                    loading="lazy"
                />
            </div>
            <span class="text-[13px] text-gray-600 text-center group-hover:text-primary transition-colors font-medium line-clamp-1">
                {{ tile.name }}
            </span>
        </Link>
    </div>
</template>
