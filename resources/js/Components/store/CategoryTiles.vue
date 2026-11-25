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

// Known subcategories list to prevent any accidental subcategory rendering in fallback or custom data
const SUBCATEGORY_NAMES = new Set([
    'Skincare', 'Haircare', 'Makeup', 'Fragrance', "Men's Grooming",
    'Prescription Medicine', 'OTC Medicine', 'Diabetic Care', 'Cardiac Care', 'Dermatological', 'Insulin',
    'Medical Devices', 'Devices', 'Diagnostic Kits', 'Mobility Aids', 'Personal Protection', 'Surgical Items',
    'Vital Organs', 'Life Style Packages', 'Checkup for Women', 'Checkup for Men', 'Full Body Checkup',
    'Baby Food', 'Diapers & Wipes', 'Maternal Care', 'Baby Skincare', 'Feeding Essentials',
    'Ayurvedic', 'Unani', 'Herbal Supplements', 'Herbal Skincare',
    'Dog Care', 'Cat Care', 'Pet Food', 'Pet Grooming', 'Veterinary',
    'Multivitamins', 'Protein & Fitness', 'Weight Management', 'Immunity Boosters',
    'Disinfectants', 'Sanitizers', 'Household Wellness', 'Air Fresheners',
])

const displayTiles = computed(() => {
    const raw = props.tiles && props.tiles.length > 0 ? props.tiles : fallbackTiles

    // Strictly render only parent / root categories
    return raw.filter((tile) => {
        // Exclude if parent_id is set and not root
        if (tile.parent_id !== undefined && tile.parent_id !== null && tile.parent_id !== 0 && tile.parent_id !== '') {
            return false
        }
        // Exclude if explicitly marked as subcategory
        if (tile.is_subcategory) {
            return false
        }
        // Exclude if matching known subcategory name
        if (SUBCATEGORY_NAMES.has(tile.name)) {
            return false
        }
        return true
    })
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
