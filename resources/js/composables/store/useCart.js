import { ref, computed } from 'vue'

export const FREE_DELIVERY_THRESHOLD = 500

/** Single currency formatter (replaces the duplicate formatTaka/formatTk pair). */
export function formatTaka(n) {
    return '৳' + Math.round(n).toLocaleString('en-US')
}

/**
 * Cart state. Seeded from the design prototype; `original` powers the
 * MRP/discount lines that previously always resolved to zero because the
 * hidden data-stub was shadowed by the drawer's own #cart-items element.
 */
export const cart = ref([
    {
        id: 1,
        name: 'Kirkland Minoxidil 5% Hair Regrowth 60ml',
        price: 799,
        original: 1490,
        qty: 1,
        image: 'https://www.arogga.com/_next/image?url=https%3A%2F%2Fcdn2.arogga.com%2FeyJidWNrZXQiOiJhcm9nZ2EiLCJrZXkiOiJQcm9kdWN0LXBfaW1hZ2VzLzg1MjUzLzg1MjUzLTgxb0ZLb2Z5YmZMLXpiNTFwcC5qcGVnIiwiZWRpdHMiOnsicmVzaXplIjp7IndpZHRoIjozMDAsImhlaWdodCI6MzAwLCJmaXQiOiJvdXRzaWRlIn19fQ%3D%3D&w=375&q=75',
    },
    {
        id: 2,
        name: 'The Ordinary Niacinamide 10%+Zinc 30ml',
        price: 1090,
        original: 1850,
        qty: 1,
        image: 'https://www.arogga.com/_next/image?url=https%3A%2F%2Fcdn2.arogga.com%2FeyJidWNrZXQiOiJhcm9nZ2EiLCJrZXkiOiJQcm9kdWN0LXBfaW1hZ2VzLzg0ODQzLzg0ODQzLU5pYWNpbmFtaWRlLUtvamljLUFjaWQtQXJidXRpbi1HbHVjb2JyaWdodC1QbHVzLVdoaXRlbmluZy1Tb2FwLTEzNWctRm9yLVNraW4tQnJpZ2h0ZW5pbmctTGlnaHRlbmluZy0zbjVxaXgud2VicCIsImVkaXRzIjp7InJlc2l6ZSI6eyJ3aWR0aCI6MzAwLCJoZWlnaHQiOjMwMCwiZml0Ijoib3V0c2lkZSJ9fX0%3D&w=375&q=75',
    },
    {
        id: 3,
        name: 'Simple Kind to Skin Moisturiser 125ml',
        price: 650,
        original: 1000,
        qty: 1,
        image: 'https://www.arogga.com/_next/image?url=https%3A%2F%2Fcdn2.arogga.com%2FeyJidWNrZXQiOiJhcm9nZ2EiLCJrZXkiOiJQcm9kdWN0VmFyaWFudC1wdl9pbWFnZXNcLzM2MTU4XC8zNjE1OC1mNzJmYjk3ODk1ZDgzZjA4ZTg2NmZhMmNhM2M1NjQzYy0xbjZyNXkud2VicCIsImVkaXRzIjp7InJlc2l6ZSI6eyJ3aWR0aCI6MjE2LCJoZWlnaHQiOjIxNiwiZml0Ijoib3V0c2lkZSJ9fX0%3D&w=256&q=75',
    },
])

export const cartCount = computed(() =>
    cart.value.reduce((sum, i) => sum + i.qty, 0),
)

export const cartSubtotal = computed(() =>
    cart.value.reduce((sum, i) => sum + i.price * i.qty, 0),
)

export const cartMrp = computed(() =>
    cart.value.reduce((sum, i) => sum + (i.original ?? i.price) * i.qty, 0),
)

export const cartDiscount = computed(() =>
    Math.max(0, cartMrp.value - cartSubtotal.value),
)

/** MediMart Cash: capped at ৳10, never exceeds the subtotal. */
export const cashApplied = computed(() =>
    cartSubtotal.value > 0 ? Math.min(10, cartSubtotal.value) : 0,
)

export const cartPayable = computed(() =>
    Math.max(0, cartSubtotal.value - cashApplied.value),
)

export const freeDeliveryRemaining = computed(() =>
    Math.max(0, FREE_DELIVERY_THRESHOLD - cartSubtotal.value),
)

export const freeDeliveryPct = computed(() =>
    Math.min(100, Math.round((cartSubtotal.value / FREE_DELIVERY_THRESHOLD) * 100)),
)

export const hasFreeDelivery = computed(() => freeDeliveryRemaining.value === 0)

export function addToCart(product) {
    const existing = cart.value.find((i) => i.id === product.id)
    if (existing) {
        existing.qty += 1
        return
    }
    cart.value.push({
        id: product.id,
        name: product.name,
        price: product.price,
        original: product.original,
        qty: 1,
        image: product.image,
    })
}

export function changeQty(id, delta) {
    const item = cart.value.find((i) => i.id === id)
    if (!item) return
    item.qty = Math.max(1, item.qty + delta)
}

export function removeItem(id) {
    cart.value = cart.value.filter((i) => i.id !== id)
}
