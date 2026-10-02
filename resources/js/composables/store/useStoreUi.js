import { ref, watch } from 'vue'

/**
 * Shared UI state for the store overlays.
 *
 * Module-scoped refs act as a lightweight singleton store so that Header.vue,
 * Footer.vue and the page-level overlays can all read and mutate the same
 * flags without threading props through StoreLayout.
 */

export const cartOpen = ref(false)
export const loginOpen = ref(false)
export const navOpen = ref(false)
export const checkoutOpen = ref(false)

export const openCartDrawer = () => { cartOpen.value = true }
export const closeCartDrawer = () => { cartOpen.value = false }
export const openLoginModal = () => { loginOpen.value = true }
export const closeLoginModal = () => { loginOpen.value = false }
export const openMobileNav = () => { navOpen.value = true }
export const closeMobileNav = () => { navOpen.value = false }
export const openCheckoutModal = () => {
    // The cart drawer would otherwise stay stacked on top of the checkout modal.
    cartOpen.value = false
    checkoutOpen.value = true
}
export const closeCheckoutModal = () => { checkoutOpen.value = false }

/** Close everything — used when one overlay opens while another is open. */
export function closeAllOverlays() {
    cartOpen.value = false
    loginOpen.value = false
    navOpen.value = false
    checkoutOpen.value = false
}

/**
 * Single place that locks body scroll, replacing the scattered manual writes.
 * Module-scoped on purpose: the refs are app-lifetime singletons, so no
 * component-scope teardown hook is needed (and onUnmounted() would warn here,
 * since this file is not evaluated inside setup()).
 */
watch(
    [cartOpen, loginOpen, navOpen, checkoutOpen],
    (flags) => {
        document.body.style.overflow = flags.some(Boolean) ? 'hidden' : ''
    },
)
