import { ref, computed } from 'vue'
import axios from 'axios'

export const FREE_DELIVERY_THRESHOLD = 500
const STORAGE_KEY = 'medimart_cart_v2'
const SESSION_TOKEN_KEY = 'medimart_cart_session_token'

/** Indicator showing if background database synchronization is currently active */
export const isCartSyncing = ref(false)

/**
 * Format currency with Bangladeshi Taka sign (৳) and standard thousand separators.
 */
export function formatTaka(n) {
    if (n === null || n === undefined || isNaN(n)) return '৳0'
    return '৳' + Math.round(Number(n)).toLocaleString('en-US')
}

/**
 * Clean up any legacy prototype dummy cart data from localStorage.
 */
function cleanupLegacyStorage() {
    if (typeof window === 'undefined') return
    try {
        localStorage.removeItem('medimart_cart')
        localStorage.removeItem('cart')
    } catch (_) {
        // Storage access might fail in restricted iframe environments
    }
}

/**
 * Retrieve or generate a persistent session token for guest cart synchronization.
 */
export function getCartSessionToken() {
    if (typeof window === 'undefined') return ''
    try {
        let token = localStorage.getItem(SESSION_TOKEN_KEY)
        if (!token) {
            token = 'guest_' + Math.random().toString(36).substring(2, 12) + Date.now().toString(36)
            localStorage.setItem(SESSION_TOKEN_KEY, token)
        }
        return token
    } catch (_) {
        return ''
    }
}

/**
 * Safely load persisted cart items from localStorage.
 * Guaranteed to return an empty array if storage is empty, corrupted, or unavailable.
 */
function loadCartFromStorage() {
    cleanupLegacyStorage()
    if (typeof window === 'undefined') return []
    try {
        const raw = localStorage.getItem(STORAGE_KEY)
        if (!raw) return []
        const parsed = JSON.parse(raw)
        return Array.isArray(parsed) ? parsed : []
    } catch (e) {
        console.warn('Failed to parse cart from localStorage:', e)
        return []
    }
}

/**
 * Persist cart items to localStorage and trigger debounced database synchronization.
 */
function saveCartToStorage() {
    if (typeof window === 'undefined') return
    try {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(cart.value))
    } catch (e) {
        console.warn('Failed to save cart to localStorage:', e)
    }

    // Schedule debounced background database sync
    syncCartToDatabase()
}

/**
 * Dynamic cart state.
 * Fully dynamic: starts empty or hydrated with user's saved items from localStorage.
 */
export const cart = ref(loadCartFromStorage())

// ══════════════════════════════════════════
// COMPUTED TOTALS & DERIVED METRICS
// ══════════════════════════════════════════

/** Total quantity of all items in cart */
export const cartCount = computed(() =>
    cart.value.reduce((sum, item) => sum + (Number(item.qty) || 0), 0),
)

/** Count of distinct product line items */
export const cartItemsCount = computed(() => cart.value.length)

/** Cart subtotal based on selling price */
export const cartSubtotal = computed(() =>
    cart.value.reduce((sum, item) => {
        const price = Number(item.price) || 0
        const qty = Number(item.qty) || 0
        return sum + price * qty
    }, 0),
)

/** Cart total MRP (original before discount) */
export const cartMrp = computed(() =>
    cart.value.reduce((sum, item) => {
        const orig = Number(item.original) || Number(item.price) || 0
        const qty = Number(item.qty) || 0
        return sum + orig * qty
    }, 0),
)

/** Total instant discount savings */
export const cartDiscount = computed(() =>
    Math.max(0, cartMrp.value - cartSubtotal.value),
)

/** MediMart Cash discount: capped at ৳10, never exceeds subtotal */
export const cashApplied = computed(() =>
    cartSubtotal.value > 0 ? Math.min(10, cartSubtotal.value) : 0,
)

/** Delivery charge: FREE if threshold met or cart empty, else standard ৳0 for MediMart promo */
export const deliveryFee = computed(() => 0)

/** Total payable amount */
export const cartPayable = computed(() =>
    Math.max(0, cartSubtotal.value - cashApplied.value + deliveryFee.value),
)

/** Remaining amount needed to unlock free delivery */
export const freeDeliveryRemaining = computed(() =>
    Math.max(0, FREE_DELIVERY_THRESHOLD - cartSubtotal.value),
)

/** Percentage progress towards free delivery threshold */
export const freeDeliveryPct = computed(() => {
    if (cartSubtotal.value <= 0) return 0
    return Math.min(100, Math.round((cartSubtotal.value / FREE_DELIVERY_THRESHOLD) * 100))
})

/** Whether free delivery has been unlocked */
export const hasFreeDelivery = computed(() =>
    cartSubtotal.value >= FREE_DELIVERY_THRESHOLD && cartSubtotal.value > 0,
)

/** Whether any item in cart requires a doctor prescription */
export const hasPrescriptionItems = computed(() =>
    cart.value.some((item) => Boolean(item.requires_prescription)),
)

/** List of items in cart that require prescription */
export const prescriptionItems = computed(() =>
    cart.value.filter((item) => Boolean(item.requires_prescription)),
)

// ══════════════════════════════════════════
// PRODUCT NORMALIZATION HELPER
// ══════════════════════════════════════════

/**
 * Normalize any storefront product data structure into a clean cart line item.
 * Supports products from ProductCard, ProductDetails, SearchListing, or raw Eloquent JSON.
 */
export function normalizeCartItem(product, quantity = 1) {
    if (!product) return null

    const productId = Number(product.id ?? product.product_id) || product.id
    const variantId = product.variant_id ?? product.variant?.id ?? product.default_variant?.id ?? null
    const variantTitle = product.variant_title ?? product.variant?.title ?? null

    // Deterministic item ID: includes variant ID if present so multiple variants of same product can coexist
    const itemId = variantId ? `${productId}-${variantId}` : String(productId)

    // Price resolution
    const price = Number(product.price ?? product.unit_price ?? product.selling_price ?? 0)
    const rawOriginal = Number(product.original ?? product.original_price ?? product.compare_at_price ?? product.mrp ?? price)
    const original = rawOriginal > price ? rawOriginal : price

    // Image resolution with safe fallback
    const image = product.image ?? product.thumbnail_url ?? product.cover_image_url ?? 'https://via.placeholder.com/300'

    // Name resolution
    let name = String(product.name || 'Product').trim()
    if (variantTitle && !name.toLowerCase().includes(variantTitle.toLowerCase())) {
        name = `${name} (${variantTitle})`
    }

    const qty = Math.max(1, Number(quantity) || Number(product.qty) || 1)

    // Brand and category strings
    const brand = typeof product.brand === 'object' && product.brand !== null
        ? product.brand.name
        : String(product.brand || '')

    const category = typeof product.category === 'object' && product.category !== null
        ? product.category.name
        : String(product.category || '')

    return {
        id: itemId,
        product_id: productId,
        variant_id: variantId,
        variant_title: variantTitle,
        name,
        slug: product.slug || String(productId),
        price,
        original,
        qty,
        image,
        brand,
        category,
        requires_prescription: Boolean(product.requires_prescription),
        badge: product.badge || null,
        unit: product.unit || '',
        is_in_stock: product.is_in_stock !== false,
    }
}

// ══════════════════════════════════════════
// DATABASE SYNCHRONIZATION WORKER
// ══════════════════════════════════════════

let syncTimer = null

/**
 * Debounced background database sync worker.
 * Syncs the current state of cart.value to /api/cart/sync without blocking UI interaction.
 */
export function syncCartToDatabase() {
    if (typeof window === 'undefined') return

    clearTimeout(syncTimer)
    syncTimer = setTimeout(async () => {
        isCartSyncing.value = true
        try {
            const sessionToken = getCartSessionToken()
            const payload = {
                session_token: sessionToken,
                items: cart.value.map((item) => ({
                    product_id: item.product_id,
                    variant_id: item.variant_id || null,
                    quantity: item.qty,
                    price: item.price,
                })),
            }

            const response = await axios.post('/api/cart/sync', payload, {
                headers: {
                    'X-Cart-Session': sessionToken,
                },
            })

            if (response.data?.session_token && response.data.session_token !== sessionToken) {
                try {
                    localStorage.setItem(SESSION_TOKEN_KEY, response.data.session_token)
                } catch (_) {}
            }
        } catch (e) {
            console.debug('Background cart sync deferred:', e?.message || e)
        } finally {
            isCartSyncing.value = false
        }
    }, 450)
}

/**
 * Hydrate cart from database on application boot (e.g. customer logged in from another device).
 */
export async function hydrateCartFromDatabase() {
    if (typeof window === 'undefined') return
    try {
        const sessionToken = getCartSessionToken()
        const response = await axios.get('/api/cart', {
            headers: { 'X-Cart-Session': sessionToken },
        })

        if (response.data?.success && Array.isArray(response.data.items)) {
            const dbItems = response.data.items

            // If local storage is empty but DB has items, adopt database items
            if (cart.value.length === 0 && dbItems.length > 0) {
                cart.value = dbItems.map((item) => normalizeCartItem(item, item.qty))
                try {
                    localStorage.setItem(STORAGE_KEY, JSON.stringify(cart.value))
                } catch (_) {}
            } else if (cart.value.length > 0) {
                // If local cart has items, sync to database to refresh backend state
                syncCartToDatabase()
            }
        }
    } catch (e) {
        console.debug('Cart hydration from database deferred:', e?.message || e)
    }
}

// Auto-run initial hydration in browser environment
if (typeof window !== 'undefined') {
    setTimeout(() => {
        hydrateCartFromDatabase()
    }, 350)
}

// ══════════════════════════════════════════
// CART MUTATION ACTIONS
// ══════════════════════════════════════════

/**
 * Add a product to the cart with dynamic quantity.
 * Automatically saves to localStorage, syncs to DB, and dispatches confirmation toast.
 *
 * @param {Object} product The product object from card/page
 * @param {number} quantity Quantity to add (default: 1)
 * @param {Object} options Options: { notify?: boolean }
 */
export function addToCart(product, quantity = 1, options = { notify: true }) {
    const item = normalizeCartItem(product, quantity)
    if (!item) return

    const existing = cart.value.find((i) => i.id === item.id)
    if (existing) {
        existing.qty += item.qty
    } else {
        cart.value.push(item)
    }

    saveCartToStorage()

    // Trigger toast notification if requested
    if (options?.notify !== false && typeof window !== 'undefined') {
        const addedQty = item.qty
        const msg = addedQty > 1
            ? `🛒 Added ${addedQty} × ${item.name} to cart!`
            : `🛒 Added ${item.name} to cart!`

        window.dispatchEvent(new CustomEvent('medimart:toast', { detail: msg }))
    }
}

/**
 * Adjust quantity by delta (+1 or -1).
 * If quantity drops below 1, removes the item cleanly.
 */
export function changeQty(id, delta) {
    const item = cart.value.find((i) => i.id === id)
    if (!item) return

    const newQty = item.qty + delta
    if (newQty <= 0) {
        removeItem(id)
        return
    }

    item.qty = Math.min(99, newQty)
    saveCartToStorage()
}

/**
 * Set an explicit quantity for a cart line item.
 */
export function updateQty(id, quantity) {
    const item = cart.value.find((i) => i.id === id)
    if (!item) return

    const parsed = parseInt(quantity, 10)
    if (isNaN(parsed) || parsed <= 0) {
        removeItem(id)
        return
    }

    item.qty = Math.min(99, parsed)
    saveCartToStorage()
}

/**
 * Remove an item completely from the cart.
 */
export function removeItem(id) {
    const removedItem = cart.value.find((i) => i.id === id)
    cart.value = cart.value.filter((i) => i.id !== id)
    saveCartToStorage()

    if (removedItem && typeof window !== 'undefined') {
        window.dispatchEvent(
            new CustomEvent('medimart:toast', {
                detail: `Removed ${removedItem.name} from cart`,
            }),
        )
    }
}

/**
 * Clear all items from the cart and sync deletion to database.
 */
export function clearCart() {
    cart.value = []
    saveCartToStorage()

    if (typeof window !== 'undefined') {
        const sessionToken = getCartSessionToken()
        axios
            .post(
                '/api/cart/clear',
                { session_token: sessionToken },
                { headers: { 'X-Cart-Session': sessionToken } },
            )
            .catch(() => {})
    }
}

/**
 * Check if a product is already in the cart.
 */
export function isInCart(productId, variantId = null) {
    const itemId = variantId ? `${productId}-${variantId}` : String(productId)
    return cart.value.some((i) => i.id === itemId || i.product_id === productId)
}

/**
 * Get current quantity of a product in the cart.
 */
export function getItemQty(productId, variantId = null) {
    const itemId = variantId ? `${productId}-${variantId}` : String(productId)
    const item = cart.value.find((i) => i.id === itemId || i.product_id === productId)
    return item ? item.qty : 0
}
