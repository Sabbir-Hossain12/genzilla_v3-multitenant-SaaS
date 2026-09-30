<script setup>
import { categoryTiles } from '@/data/store'
</script>

<template>
    <div class="grid grid-cols-4 sm:grid-cols-6 gap-3">
        <a v-for="tile in categoryTiles" :key="tile.name" href="#" class="flex flex-col items-center gap-1.5 group">
            <div class="w-full h-full rounded-2xl overflow-hidden shrink-0 ring-1 ring-gray-100 group-hover:ring-2 group-hover:ring-primary/40 transition-all">
                <img :src="tile.image" :alt="tile.name" class="w-full h-full object-cover" />
            </div>
            <span class="text-[13px] text-gray-600 text-center group-hover:text-primary transition-colors font-medium">
                {{ tile.name }}
            </span>
        </a>
    </div>
</template>
