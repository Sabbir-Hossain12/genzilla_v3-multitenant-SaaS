<script setup>
import { categories } from '@/data/store'
</script>

<template>
    <aside class="hidden lg:block w-60 shrink-0 bg-white rounded-xl border border-gray-100 self-start relative z-30">
        <div class="bg-primary px-5 py-3.5 rounded-t-xl">
            <p class="text-white text-[16px] font-semibold">Browse Categories</p>
        </div>

        <nav class="rounded-b-xl overflow-visible">
            <div v-for="cat in categories" :key="cat.name" class="relative group">
                <a
                    href="#"
                    class="flex items-center gap-3 px-5 py-3.5 text-[15px] text-gray-700 group-hover:bg-primarylt group-hover:text-primary border-b border-gray-50 transition-colors"
                >
                    <span class="text-lg">{{ cat.icon }}</span>
                    <span class="flex-1">{{ cat.name }}</span>
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" class="text-gray-300 group-hover:text-primary"><path d="M9 18l6-6-6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                </a>

                <!-- Flyout -->
                <div class="hidden group-hover:block absolute left-full top-0 -ml-px w-72 bg-white rounded-xl border border-gray-100 shadow-2xl p-5 z-40">
                    <p class="text-[12px] font-bold text-gray-400 uppercase tracking-wider mb-3">{{ cat.name }}</p>
                    <div class="space-y-0.5">
                        <a
                            v-for="child in cat.children"
                            :key="child"
                            href="#"
                            class="block px-2.5 py-2 rounded-lg text-[14px] text-gray-700 hover:bg-primarylt hover:text-primary transition-colors"
                        >{{ child }}</a>
                    </div>
                    <a href="#" class="block mt-3 pt-3 border-t border-gray-100 text-[13px] font-semibold text-primary hover:underline">
                        Shop all {{ cat.name }} →
                    </a>
                </div>
            </div>
        </nav>
    </aside>
</template>
