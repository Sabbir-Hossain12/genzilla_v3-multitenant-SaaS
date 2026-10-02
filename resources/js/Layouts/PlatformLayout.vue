<script setup>
import { onMounted, onBeforeUnmount } from 'vue'
import PlatformHeader from '@/Components/platform/PlatformHeader.vue'
import PlatformMobileNav from '@/Components/platform/PlatformMobileNav.vue'
import PlatformFooter from '@/Components/platform/PlatformFooter.vue'
import { navOpen, closeMobileNav } from '@/composables/platform/usePlatformUi'

/**
 * Inertia does not re-render <body> on client-side navigation, and app.blade.php
 * hard-codes the storefront's colours there. Each layout therefore claims the
 * body classes on mount so moving between / and /store repaints correctly.
 */
const BODY_CLASS = 'bg-white font-sans antialiased'

function onKeydown(event) {
    if (event.key === 'Escape' && navOpen.value) closeMobileNav()
}

onMounted(() => {
    document.body.className = BODY_CLASS
    window.addEventListener('keydown', onKeydown)
})

onBeforeUnmount(() => {
    document.body.style.overflow = ''
    window.removeEventListener('keydown', onKeydown)
})
</script>

<template>
    <div class="platform-scope bg-white text-ink min-h-screen flex flex-col">
        <PlatformHeader />
        <PlatformMobileNav />

        <main class="flex-1">
            <slot />
        </main>

        <PlatformFooter />
    </div>
</template>
