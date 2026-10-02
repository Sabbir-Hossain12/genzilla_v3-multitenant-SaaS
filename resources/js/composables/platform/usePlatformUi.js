import { ref, watch } from 'vue'

/**
 * UI state for the platform marketing site.
 *
 * Deliberately separate from the storefront's useStoreUi: the two surfaces have
 * different drawers and different branding, and sharing one flag would let a
 * storefront overlay leak onto the marketing site.
 */

export const navOpen = ref(false)

export const openMobileNav = () => {
    navOpen.value = true
}
export const closeMobileNav = () => {
    navOpen.value = false
}
export const toggleMobileNav = () => {
    navOpen.value = !navOpen.value
}

/**
 * Single place that locks body scroll while the drawer is open. Module-scoped,
 * mirroring the storefront pattern — see the note in useStoreUi.js about why no
 * component-scope teardown hook is used here.
 */
watch(navOpen, (open) => {
    document.body.style.overflow = open ? 'hidden' : ''
})
