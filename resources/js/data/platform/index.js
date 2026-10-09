/**
 * Content for the Shopwave platform marketing site.
 *
 * Mirrors the storefront's data/store module: templates stay declarative and
 * every list that the prototypes repeat (nav, footer columns, feature cards,
 * steps, testimonials, plan teaser) lives here instead of being hard-coded.
 */

export const navLinks = [
    { label: 'Features', to: '/features' },
    { label: 'Use Cases', to: '/features#use-cases' },
    { label: 'Pricing', to: '/pricing' },
    { label: 'Blog', to: '/blog' },
]

export const socials = [
    {
        key: 'facebook',
        label: 'Facebook',
        path: 'M24 12.07C24 5.7 18.63.5 12 .5S0 5.7 0 12.07c0 5.75 4.39 10.52 10.13 11.43v-8.09H7.08v-3.34h3.05V9.41c0-3 1.8-4.67 4.55-4.67 1.32 0 2.7.24 2.7.24v2.94h-1.52c-1.5 0-1.97.92-1.97 1.87v2.24h3.35l-.54 3.34h-2.81v8.09C19.61 22.59 24 17.82 24 12.07z',
        solid: true,
    },
    {
        key: 'x',
        label: 'X (Twitter)',
        path: 'M23 3a10.9 10.9 0 01-3.14 1.53 4.48 4.48 0 00-7.86 3v1A10.66 10.66 0 013 4s-4 9 5 13a11.64 11.64 0 01-7 2c9 5 20 0 20-11.5a4.5 4.5 0 00-.08-.83A7.72 7.72 0 0023 3z',
    },
    {
        key: 'instagram',
        label: 'Instagram',
        shapes: [
            { tag: 'rect', attrs: { x: 2, y: 2, width: 20, height: 20, rx: 5 } },
            { tag: 'circle', attrs: { cx: 12, cy: 12, r: 4 } },
            { tag: 'circle', attrs: { cx: 17.5, cy: 6.5, r: 1, fill: 'currentColor', stroke: 'none' } },
        ],
    },
    {
        key: 'youtube',
        label: 'YouTube',
        shapes: [
            { tag: 'rect', attrs: { x: 2, y: 5, width: 20, height: 14, rx: 4 } },
            { tag: 'path', attrs: { d: 'M10 9l5 3-5 3z', fill: 'currentColor', stroke: 'none' } },
        ],
    },
    {
        key: 'linkedin',
        label: 'LinkedIn',
        shapes: [
            { tag: 'path', attrs: { d: 'M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-4 0v7h-4v-7a6 6 0 016-6z' } },
            { tag: 'rect', attrs: { x: 2, y: 9, width: 4, height: 12 } },
            { tag: 'circle', attrs: { cx: 4, cy: 4, r: 2 } },
        ],
    },
]

export const footerColumns = [
    {
        title: 'Product',
        links: [
            { label: 'Features', to: '/features' },
            { label: 'Pricing', to: '/pricing' },
            { label: 'Integrations', to: null },
            { label: 'Changelog', to: null },
        ],
    },
    {
        title: 'Solutions',
        links: [
            { label: 'Sell Physical Goods', to: '/features#use-cases' },
            { label: 'Sell Digital Goods', to: '/features#use-cases' },
            { label: 'Subscriptions', to: '/features#use-cases' },
            { label: 'Enterprise', to: null },
        ],
    },
    {
        title: 'Company',
        links: [
            { label: 'About Us', to: null },
            { label: 'Careers', to: null },
            { label: 'Blog', to: '/blog' },
            { label: 'Contact Sales', to: null },
        ],
    },
    {
        title: 'Resources',
        links: [
            { label: 'Help Center', to: null },
            { label: 'API Docs', to: null },
            { label: 'Community', to: null },
            { label: 'Status', to: null },
        ],
    },
]

export const legalLinks = [
    { label: 'Terms of Service', to: null },
    { label: 'Privacy Policy', to: null },
    { label: 'Cookie Settings', to: null },
]

export const hero = {
    badge: 'Shopwave 3.0 — AI product descriptions are here',
    titleLead: 'Launch your online store',
    titleHighlight: 'in minutes',
    titleTail: ', not months.',
    subtitle:
        'Shopwave gives merchants everything they need to sell physical and digital products — storefront, checkout, payments, and marketing — in one platform.',
    note: 'No credit card required · Cancel anytime',
    url: 'yourstore.shopwave.com/dashboard',
}

export const trustLogos = [
    'Northline',
    'Verdant Co.',
    'Aurelia',
    'Pixel & Pine',
    'Marlow Goods',
    'Kindred Studio',
]

export const stats = [
    { value: '10K+', label: 'Active Merchants' },
    { value: '$480M', label: 'GMV Processed' },
    { value: '64', label: 'Countries Served' },
    { value: '99.98%', label: 'Platform Uptime' },
]

/** Icons are plain shape descriptors so they can be rendered with <component :is>. */
export const features = [
    {
        icon: 'builder',
        iconClass: 'bg-primarylt text-primary',
        title: 'Drag-and-Drop Store Builder',
        body: 'Design a beautiful storefront with pre-built themes — no code required, fully customizable when you need more control.',
        shapes: [
            { tag: 'path', attrs: { d: 'M4 4h16v4H4zM4 12h7v8H4zM13 12h7v4h-7zM13 18h7v2h-7z' } },
        ],
    },
    {
        icon: 'checkout',
        iconClass: 'bg-emerald-50 text-accent',
        title: 'Secure Checkout & Payments',
        body: 'Accept cards, wallets, and local payment methods with PCI-compliant checkout, built to convert.',
        shapes: [
            { tag: 'rect', attrs: { x: 2, y: 5, width: 20, height: 14, rx: 2 } },
            { tag: 'path', attrs: { d: 'M2 10h20' } },
        ],
    },
    {
        icon: 'products',
        iconClass: 'bg-amber-50 text-amber-500',
        title: 'Physical + Digital Products',
        body: 'Ship physical inventory or deliver downloads, licenses, and memberships instantly — all from one catalog.',
        shapes: [
            { tag: 'path', attrs: { d: 'M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M17 8l-5-5-5 5M12 3v12' } },
        ],
    },
    {
        icon: 'marketing',
        iconClass: 'bg-rose-50 text-rose-500',
        title: 'Built-in Marketing Tools',
        body: 'Discount codes, abandoned cart recovery, and email campaigns — turn browsers into buyers automatically.',
        shapes: [
            { tag: 'path', attrs: { d: 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5M18 2l4 4-11 11H7v-4z' } },
        ],
    },
    {
        icon: 'analytics',
        iconClass: 'bg-blue-50 text-blue-500',
        title: 'Real-Time Analytics',
        body: 'Track sales, traffic, and customer behavior with dashboards that update live — no spreadsheets needed.',
        shapes: [
            { tag: 'path', attrs: { d: 'M3 3v18h18M7 15l4-4 3 3 5-6' } },
        ],
    },
    {
        icon: 'support',
        iconClass: 'bg-violet-50 text-violet-500',
        title: '24/7 Merchant Support',
        body: 'Live chat, email, and phone support around the clock — plus a dedicated manager on Enterprise plans.',
        shapes: [
            { tag: 'path', attrs: { d: 'M18 10a6 6 0 00-12 0v4l-2 3h16l-2-3v-4zM9 21a3 3 0 006 0' } },
        ],
    },
]

export const steps = [
    {
        title: 'Sign up free',
        body: 'Create your account in under a minute — no credit card required.',
    },
    {
        title: 'Customize your store',
        body: 'Pick a theme, add products, and set up shipping or digital delivery.',
    },
    {
        title: 'Start selling',
        body: 'Publish your storefront and accept your first order the same day.',
    },
]

export const testimonials = [
    {
        quote:
            'We migrated from a patchwork of plugins to Shopwave in a weekend. Checkout conversion went up 18% in the first month alone.',
        name: 'Rima Akter',
        role: 'Founder, Verdant Co.',
        initials: 'RA',
        avatarClass: 'bg-primary/10 text-primary',
    },
    {
        quote:
            'Selling digital courses used to mean stitching together three different tools. Shopwave handles delivery, licensing, and payments natively.',
        name: 'David Kim',
        role: 'Creator, Kindred Studio',
        initials: 'DK',
        avatarClass: 'bg-emerald-500/10 text-accent',
    },
    {
        quote:
            'The abandoned cart recovery alone paid for our subscription in the first week. Support is fast and actually helpful.',
        name: 'Maria Santos',
        role: 'Owner, Marlow Goods',
        initials: 'MS',
        avatarClass: 'bg-amber-500/10 text-amber-500',
    },
]

export const plans = [
    { name: 'Basic', price: '$19', suffix: '/mo', note: '2% transaction fee' },
    { name: 'Pro', price: '$49', suffix: '/mo', note: '1% transaction fee', highlighted: true },
    { name: 'Enterprise', price: 'Custom', suffix: '', note: 'Negotiated fees' },
]

/* ──────────────────────────────────────────────────────────────
   FEATURES PAGE
   ────────────────────────────────────────────────────────────── */

export const useCases = [
    {
        iconClass: 'bg-amber-50 text-amber-500',
        shapes: [
            {
                tag: 'path',
                attrs: { d: 'M21 8l-9-5-9 5 9 5 9-5zM3 8v8l9 5 9-5V8M12 13v8'},
            },
        ],
        title: 'Sell Physical Goods',
        body: 'From handmade jewelry to bulk wholesale — manage stock, shipping, and fulfillment without leaving your dashboard.',
        points: [
            { strong: 'Inventory management', text: 'track stock across variants, locations, and low-stock alerts.' },
            { strong: 'Live shipping rates', text: 'calculate accurate rates at checkout from major couriers.' },
            { strong: 'Order fulfillment tracking', text: 'print labels and share tracking numbers automatically.' },
            { strong: 'Multi-warehouse support', text: 'route orders to the nearest fulfillment location.' },
            { strong: 'Returns management', text: 'self-serve return requests with configurable policies.' },
        ],
    },
    {
        iconClass: 'bg-violet-50 text-violet-500',
        shapes: [
            {
                tag: 'path',
                attrs: { d: 'M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M17 8l-5-5-5 5M12 3v12'},
            },
        ],
        title: 'Sell Digital Goods',
        body: 'Courses, ebooks, software licenses, or memberships — deliver instantly with zero manual work.',
        points: [
            { strong: 'Instant download delivery', text: 'secure, expiring links sent the moment payment clears.' },
            { strong: 'License key generation', text: 'auto-generate and track unique keys per sale.' },
            { strong: 'Protected streaming', text: 'host video/audio content with access controls, not raw file links.' },
            { strong: 'Subscriptions & memberships', text: 'recurring billing with automatic access renewal.' },
            { strong: 'Automatic email delivery', text: 'receipts and files sent together, fully customizable.' },
        ],
    },
]

export const featureGrid = [
    {
        iconClass: 'bg-primarylt text-primary',
        shapes: [
            { tag: 'rect', attrs: { x: 3, y: 3, width: 18, height: 18, rx: 2}, },
            { tag: 'path', attrs: { d: 'M3 9h18M9 21V9'}, },
        ],
        title: 'Store Builder & Themes',
        body: 'Dozens of responsive themes, fully customizable with a visual editor.',
    },
    {
        iconClass: 'bg-emerald-50 text-accent',
        shapes: [
            { tag: 'path', attrs: { d: 'M12 1v22M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6'}, },
        ],
        title: 'Multiple Payment Gateways',
        body: 'Cards, wallets, and local payment methods across 60+ countries.',
    },
    {
        iconClass: 'bg-blue-50 text-blue-500',
        shapes: [
            { tag: 'circle', attrs: { cx: 11, cy: 11, r: 8}, },
            { tag: 'path', attrs: { d: 'M21 21l-4.35-4.35'}, },
        ],
        title: 'Built-in SEO Tools',
        body: 'Editable meta tags, sitemaps, and fast pages that rank out of the box.',
    },
    {
        iconClass: 'bg-rose-50 text-rose-500',
        shapes: [
            {
                tag: 'path',
                attrs: { d: 'M20.8 4.6a5.5 5.5 0 00-7.8 0L12 5.6l-1-1a5.5 5.5 0 00-7.8 7.8l1 1L12 21l7.8-7.6 1-1a5.5 5.5 0 000-7.8z'},
            },
        ],
        title: 'Marketing & Discounts',
        body: 'Coupon codes, bundles, and automated email flows to drive repeat sales.',
    },
    {
        iconClass: 'bg-amber-50 text-amber-500',
        shapes: [
            { tag: 'path', attrs: { d: 'M3 3v18h18M7 15l4-4 3 3 5-6'}, },
        ],
        title: 'Analytics & Reporting',
        body: 'Real-time dashboards for sales, traffic, and customer lifetime value.',
    },
    {
        iconClass: 'bg-violet-50 text-violet-500',
        shapes: [
            { tag: 'rect', attrs: { x: 2, y: 7, width: 20, height: 14, rx: 2}, },
            { tag: 'path', attrs: { d: 'M16 7V5a4 4 0 00-8 0v2'}, },
        ],
        title: 'Multi-Channel Selling',
        body: 'Sync your catalog to social and marketplace channels from one place.',
    },
    {
        iconClass: 'bg-teal-50 text-teal-600',
        shapes: [
            { tag: 'rect', attrs: { x: 7, y: 2, width: 10, height: 20, rx: 2}, },
            { tag: 'path', attrs: { d: 'M11 18h2'}, },
        ],
        title: 'Mobile App Management',
        body: 'Run your store from your pocket — orders, chat, and inventory on the go.',
    },
    {
        iconClass: 'bg-sky-50 text-sky-500',
        shapes: [
            { tag: 'path', attrs: { d: 'M18 10a6 6 0 00-12 0v4l-2 3h16l-2-3v-4zM9 21a3 3 0 006 0'}, },
        ],
        title: 'Customer Support Suite',
        body: 'A built-in help center, contact forms, and order tracking for your buyers.',
    },
    {
        iconClass: 'bg-indigo-50 text-primary',
        shapes: [
            { tag: 'circle', attrs: { cx: 12, cy: 12, r: 10}, },
            { tag: 'path', attrs: { d: 'M2 12h20M12 2a15.3 15.3 0 010 20 15.3 15.3 0 010-20z'}, },
        ],
        title: 'Multilingual & Multi-Currency',
        body: 'Sell globally with localized storefronts and automatic currency conversion.',
    },
]

export const businessTypes = [
    {
        emoji: '👗',
        coverClass: 'bg-gradient-to-br from-amber-100 to-orange-100',
        badgeClass: 'text-amber-600 bg-amber-50',
        badge: 'Physical Goods',
        title: 'Fashion Boutique',
        body: 'Manages 400+ SKUs across sizes and colors, with live shipping rates and automated restock alerts.',
    },
    {
        emoji: '🎓',
        coverClass: 'bg-gradient-to-br from-violet-100 to-indigo-100',
        badgeClass: 'text-violet-600 bg-violet-50',
        badge: 'Digital Goods',
        title: 'Online Course Creator',
        body: 'Sells video courses with protected streaming and drip-fed lessons unlocked automatically after purchase.',
    },
    {
        emoji: '📦',
        coverClass: 'bg-gradient-to-br from-emerald-100 to-teal-100',
        badgeClass: 'text-emerald-600 bg-emerald-50',
        badge: 'Physical · Subscription',
        title: 'Subscription Box Service',
        body: 'Runs recurring monthly billing alongside physical fulfillment, with churn and retention analytics built in.',
    },
]

export const integrationLogos = [
    { name: 'PayFlow' },
    { name: 'ShipEasy' },
    { name: 'MailBridge' },
    { name: 'QuickBooks' },
    { name: 'Zapier' },
    { name: 'Klaviyo', hideOnMobile: true },
]

/* ──────────────────────────────────────────────────────────────
   PRICING PAGE
   ────────────────────────────────────────────────────────────── */

export const feeCalloutShapes = [
    { tag: 'path', attrs: { d: 'M12 2v20M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6' } },
]

export const pricingPlans = [
    {
        name: 'Basic',
        tagline: 'For new merchants getting started',
        monthly: '$19',
        yearly: '$15',
        fee: '2.0% transaction fee per order',
        ctaLabel: 'Start Free Trial',
        ctaTo: '/register',
        featured: false,
        features: [
            'Up to 100 products',
            'Standard checkout',
            '1 staff account',
            'Email support',
        ],
    },
    {
        name: 'Pro',
        tagline: 'For growing stores that need more',
        monthly: '$49',
        yearly: '$39',
        fee: '1.0% transaction fee per order',
        ctaLabel: 'Start Free Trial',
        ctaTo: '/register',
        featured: true,
        features: [
            'Unlimited products',
            'Abandoned cart recovery',
            'Custom domain',
            '5 staff accounts',
            'Priority support',
        ],
    },
    {
        name: 'Enterprise',
        tagline: 'For high-volume & multi-brand businesses',
        monthly: 'Custom',
        yearly: 'Custom',
        fee: 'Negotiated transaction fees',
        ctaLabel: 'Contact Sales',
        // No contact page exists yet.
        ctaTo: null,
        featured: false,
        features: [
            'Unlimited staff accounts',
            'Dedicated account manager',
            'Full API access',
            '99.9% uptime SLA',
        ],
    },
]

/**
 * `true` renders a check, `false` renders a dash, and strings render as-is.
 */
export const pricingComparison = [
    { feature: 'Products', basic: '100', pro: 'Unlimited', enterprise: 'Unlimited', emphasize: true },
    { feature: 'Staff accounts', basic: '1', pro: '5', enterprise: 'Unlimited', emphasize: true, striped: true },
    { feature: 'Transaction fee', basic: '2.0%', pro: '1.0%', enterprise: 'Negotiated', emphasize: true },
    { feature: 'Custom domain', basic: false, pro: true, enterprise: true, striped: true },
    { feature: 'Abandoned cart recovery', basic: false, pro: true, enterprise: true },
    { feature: 'Advanced reporting', basic: false, pro: true, enterprise: true, striped: true },
    { feature: 'API access', basic: false, pro: false, enterprise: true },
    { feature: 'Uptime SLA', basic: false, pro: false, enterprise: true, striped: true },
]

export const pricingFaqs = [
    {
        question: 'Can I change plans at any time?',
        answer:
            'Yes. Upgrade or downgrade anytime from your dashboard — changes take effect immediately and billing is prorated for the current cycle.',
    },
    {
        question: 'Is there a free trial?',
        answer:
            'Every plan includes a 14-day free trial with full access to Pro features — no credit card required to start.',
    },
    {
        question: "What happens if I exceed my plan's product limit?",
        answer:
            "We'll notify you as you approach the limit. Your store keeps running as normal — you'll just need to upgrade to add more products beyond your plan's cap.",
    },
    {
        question: 'Do digital products have different fees?',
        answer:
            'No — digital and physical products share the same transaction fee structure based on your plan. There are no extra fees for hosting downloads or generating license keys.',
    },
    {
        question: 'Do you offer discounts for annual billing?',
        answer:
            'Yes — switch to yearly billing with the toggle above and save 20% compared to paying monthly, on any plan.',
    },
]

/* ──────────────────────────────────────────────────────────────
   AUTH PAGES
   ────────────────────────────────────────────────────────────── */

/** Left-hand panel copy differs per auth screen, so it is keyed by screen. */
export const authPanels = {
    login: {
        heading: 'Run your store from anywhere.',
        body: 'Log in to manage orders, products, and payments — all from one clean dashboard.',
    },
    register: {
        heading: 'Start selling in minutes, not months.',
        body: 'Join 10,000+ merchants running their store on Shopwave. Your first 14 days are free — no credit card required.',
    },
    'forgot-password': {
        heading: 'Forgot your password? It happens.',
        body: "We'll get you back into your dashboard in under a minute — no support ticket required.",
    },
    'reset-password': {
        heading: 'Choose a new password.',
        body: 'Pick something you have not used before. You will be signed in as soon as it is saved.',
    },
    'verify-email': {
        heading: 'One last step.',
        body: 'Confirm your email address to activate your merchant account and start selling.',
    },
    'confirm-password': {
        heading: "Confirm it's you",
        body: 'This is a protected area. Enter your password again to continue.',
    },
}

export const authTestimonial = {
    quote:
        'We migrated from a patchwork of plugins to Shopwave in a weekend. Checkout conversion went up 18% in the first month alone.',
    name: 'Rima Akter',
    role: 'Founder, Verdant Co.',
    initials: 'RA',
}

/* ── blog ─────────────────────────────────────────────────────
   `coverClass` values are soft tinted gradients, matching how
   businessTypes fakes artwork without shipping images.

   `body` is an ordered list of blocks rather than raw HTML so the
   show page stays declarative and never uses v-html. Supported
   types: p, h2, ul, quote. */

export const blogCategories = [
    { slug: 'all', label: 'All posts' },
    { slug: 'guides', label: 'Guides' },
    { slug: 'growth', label: 'Growth' },
    { slug: 'product', label: 'Product updates' },
    { slug: 'payments', label: 'Payments' },
]

export const blogFeatured = {
    slug: 'launch-your-first-store-in-a-weekend',
    category: 'Guides',
    title: 'How to launch your first store on Shopwave in a single weekend',
    excerpt:
        'A step-by-step walkthrough of everything that has to happen before you can take your first order — from picking products to wiring up payments. No code required.',
    date: 'March 4, 2026',
    readTime: '11 min read',
    author: 'Nadia Rahman',
    role: 'Head of Merchant Success',
    initials: 'NR',
    coverClass: 'bg-gradient-to-br from-indigo-50 to-indigo-100',
    emoji: '🚀',
    body: [
        {
            type: 'p',
            text: 'Most merchants do not need three months of planning. They need one focused Saturday and one Sunday. This is the exact sequence we walk new Shopwave merchants through, in the order that avoids rework.',
        },
        {
            type: 'h2',
            text: 'Before you start: what you need ready',
        },
        {
            type: 'ul',
            items: [
                'Twelve to thirty products, photographed against a plain background',
                'Cost price and intended retail price for each one',
                'Your bank or merchant account details for settlement',
                'One delivery partner account, if you are not collecting cash on delivery',
            ],
        },
        {
            type: 'h2',
            text: 'Saturday morning: catalogue and pricing',
        },
        {
            type: 'p',
            text: 'Create your account, then add products in bulk using the CSV importer. Get the name, description, price, and one clear photo in for every item. Resist the urge to perfect the copy here — you can refine descriptions after your first real customers tell you what confused them.',
        },
        {
            type: 'h2',
            text: 'Saturday afternoon: payments and delivery',
        },
        {
            type: 'p',
            text: 'Connect your gateway and run a test payment of one taka. Then set up delivery zones and rates. Merchants who skip this step and add it later almost always end up with a rate card that does not match the areas they actually ship to.',
        },
        {
            type: 'quote',
            text: 'The stores that take orders in week one are not the ones with the best photography. They are the ones that finished payments and delivery before they launched.',
            cite: 'Nadia Rahman, Head of Merchant Success',
        },
        {
            type: 'h2',
            text: 'Sunday: place a real test order',
        },
        {
            type: 'p',
            text: 'Buy something from your own store end to end — from checkout to the order appearing in your dashboard. Then cancel it. That single test catches more problems than an hour of clicking around the dashboard will, because it exercises the same path your customer will take.',
        },
    ],
}

export const blogPosts = [
    {
        slug: 'product-photography-that-sells',
        category: 'Growth',
        title: 'Product photography that actually sells',
        excerpt:
            'You do not need a studio. You need consistent light, a plain background, and six shots per product. Here is the setup we recommend to every new merchant.',
        date: 'February 27, 2026',
        readTime: '7 min read',
        author: 'Tanvir Ahmed',
        role: 'Growth Lead',
        initials: 'TA',
        coverClass: 'bg-gradient-to-br from-emerald-50 to-emerald-100',
        emoji: '📸',
        body: [
            {
                type: 'p',
                text: 'Shoppers decide in about two seconds, and most of that decision is made on the image before they read a single word of your description. Here is how to shoot product photos that hold up in a grid, without renting a studio.',
            },
            { type: 'h2', text: 'The one-room setup' },
            {
                type: 'ul',
                items: [
                    'A plain sheet of white or light grey paper as your background',
                    'A window for soft, indirect daylight — never direct sun',
                    'Your phone camera, with the lens wiped clean',
                    'Something to prop the product on so it does not sit flat on the paper',
                ],
            },
            { type: 'h2', text: 'Six shots is the sweet spot' },
            {
                type: 'p',
                text: 'Front, back, side, angled, in-hand for scale, and one detail close-up. That set covers every question a shopper asks — what is it, how big is it, what does the material look like, and does it work with what I already own.',
            },
            {
                type: 'quote',
                text: 'Merchants who shoot six consistent images per product see measurably fewer "is this genuine?" messages than merchants shooting two.',
                cite: 'Tanvir Ahmed, Growth Lead',
            },
            { type: 'h2', text: 'Consistency beats quality' },
            {
                type: 'p',
                text: 'A slightly soft photo that matches the rest of your catalogue looks more professional than one beautiful shot surrounded by inconsistent ones. Fix your lighting and background once, and reuse them for every product.',
            },
        ],
    },
    {
        slug: 'choosing-a-payment-gateway',
        category: 'Payments',
        title: 'Choosing a payment gateway in Bangladesh',
        excerpt:
            'Mobile wallets, cards, and cash on delivery each behave differently at checkout. A practical comparison of fees, settlement times, and failure rates.',
        date: 'February 19, 2026',
        readTime: '9 min read',
        author: 'Sadia Islam',
        role: 'Payments Specialist',
        initials: 'SI',
        coverClass: 'bg-gradient-to-br from-sky-50 to-sky-100',
        emoji: '💳',
        body: [
            {
                type: 'p',
                text: 'There is no single best gateway here — the right answer depends on what you are selling and how your customers prefer to pay. What follows is how the three main options actually behave in production.',
            },
            { type: 'h2', text: 'Mobile wallets' },
            {
                type: 'p',
                text: 'bKash, Nagad, and Rocket dominate for a reason: customers trust them and the money moves instantly. They carry a slightly higher percentage fee than cards, and failure rates spike when a customer has never used that wallet before.',
            },
            { type: 'h2', text: 'Cards' },
            {
                type: 'p',
                text: 'Lower percentage fees, but expect a decline rate in the single digits and settlement in days rather than minutes. Cards are usually worth enabling for higher-ticket orders where the fee difference is worth the delay.',
            },
            { type: 'h2', text: 'Cash on delivery' },
            {
                type: 'p',
                text: 'Still the default preference for a large share of shoppers, and it converts better than any digital method for first-time buyers. The tradeoff is that you absorb failed-delivery costs, so build your refusal policy before you need it.',
            },
            {
                type: 'quote',
                text: 'Enabling all three does not add meaningful complexity. Turning one off mid-checkout is what costs you sales.',
                cite: 'Sadia Islam, Payments Specialist',
            },
            { type: 'h2', text: 'The practical answer' },
            {
                type: 'p',
                text: 'Turn on everything, and watch your own failure rate per method for thirty days. Your customers will tell you which one to prioritise far more reliably than any benchmark we could quote.',
            },
        ],
    },
    {
        slug: 'what-is-new-in-march',
        category: 'Product updates',
        title: 'What is new in Shopwave this March',
        excerpt:
            'Bulk product editing, saved shipping rates, and a rebuilt order timeline. Here is everything that shipped, plus what we are building next.',
        date: 'March 2, 2026',
        readTime: '4 min read',
        author: 'Farhan Kabir',
        role: 'Product Manager',
        initials: 'FK',
        coverClass: 'bg-gradient-to-br from-violet-50 to-violet-100',
        emoji: '✨',
        body: [
            {
                type: 'p',
                text: 'A short release round this month, weighted towards the two things merchants told us slowed them down most: changing prices in bulk, and reworking delivery rates.',
            },
            { type: 'h2', text: 'Shipped' },
            {
                type: 'ul',
                items: [
                    'Bulk product editing — change price, stock, or status across a filtered selection',
                    'Saved shipping rates — reuse a rate you configured before instead of retyping it',
                    'Rebuilt order timeline — every fulfilment event on one scrollable column',
                    'Download links now expire automatically for digital products',
                ],
            },
            { type: 'h2', text: 'Still building' },
            {
                type: 'p',
                text: 'Multi-location stock is the big one. It is the most requested feature we have and the hardest to get right, so we are shipping it in stages rather than rushing it. Expect a beta for early testers next month.',
            },
            {
                type: 'quote',
                text: 'We would rather ship the rebuilt order timeline twice than ship a half-working multi-location system once.',
                cite: 'Farhan Kabir, Product Manager',
            },
        ],
    },
    {
        slug: 'reducing-abandoned-carts',
        category: 'Growth',
        title: 'Nine ways to reduce abandoned carts',
        excerpt:
            'Most carts are lost for one of nine reasons. We ranked them by how much revenue they cost, and paired each with the fix that worked for our merchants.',
        date: 'February 11, 2026',
        readTime: '8 min read',
        author: 'Nadia Rahman',
        role: 'Head of Merchant Success',
        initials: 'NR',
        coverClass: 'bg-gradient-to-br from-rose-50 to-rose-100',
        emoji: '🛒',
        body: [
            {
                type: 'p',
                text: 'We looked at where carts stall across several thousand stores. Nine causes accounted for nearly all of it, and they are listed here in roughly descending order of revenue lost.',
            },
            { type: 'h2', text: 'The big ones' },
            {
                type: 'ul',
                items: [
                    'Surprise delivery charges revealed only at the final step',
                    'No guest checkout — forced account creation before payment',
                    'A long or unclear payment form, especially on mobile',
                    'Only one payment method available',
                    'No way to edit the cart without starting over',
                ],
            },
            { type: 'h2', text: 'The cheap wins' },
            {
                type: 'p',
                text: 'Show delivery cost earlier, keep the cart reachable from the order summary, and offer guest checkout. Together these three account for most of the improvement we see from merchants who make only one change.',
            },
            {
                type: 'quote',
                text: 'A surprising number of abandoned carts are not price objections. They are friction objections. The customer liked the product and simply gave up.',
                cite: 'Nadia Rahman, Head of Merchant Success',
            },
            { type: 'h2', text: 'Measure it properly' },
            {
                type: 'p',
                text: 'Watch the funnel step by step rather than looking at a single abandonment rate. Knowing that forty percent stall on the delivery step tells you what to fix; knowing that forty percent abandon tells you nothing.',
            },
        ],
    },
    {
        slug: 'shipping-rates-explained',
        category: 'Guides',
        title: 'Shipping rates, explained without the jargon',
        excerpt:
            'Flat rate, weight-based, free over a threshold, or zone-based. What each model costs you, and when switching to it actually pays off.',
        date: 'February 4, 2026',
        readTime: '6 min read',
        author: 'Tanvir Ahmed',
        role: 'Growth Lead',
        initials: 'TA',
        coverClass: 'bg-gradient-to-br from-amber-50 to-amber-100',
        emoji: '🚚',
        body: [
            {
                type: 'p',
                text: 'Shipping is where margin quietly disappears. The rate model you pick changes what you can sell, what you earn per order, and how often a customer abandons at the last step.',
            },
            { type: 'h2', text: 'Flat rate' },
            {
                type: 'p',
                text: 'One price for every order, regardless of size or distance. Simplest to set up and easiest to explain. It works when your catalogue is uniform — same size, same weight class, roughly the same order value.',
            },
            { type: 'h2', text: 'Weight-based' },
            {
                type: 'p',
                text: 'Price scales with actual or volumetric weight. The model you want if you sell anything bulky or irregular. Mind volumetric weight: a light but large box can weigh more on the scale than it does on your scales.',
            },
            { type: 'h2', text: 'Free over a threshold' },
            {
                type: 'p',
                text: 'Not a rate model on its own but a modifier on one. It reliably raises average order value. Set the threshold just above your current average, not round — ৳1,500 converts better than ৳1,500 is a coincidence, but ৳2,000 might simply be unreachable.',
            },
            {
                type: 'quote',
                text: 'Whatever model you choose, quote the customer the same number you intend to charge. A changed delivery fee at checkout is the fastest way to lose a sale.',
                cite: 'Tanvir Ahmed, Growth Lead',
            },
        ],
    },
    {
        slug: 'setting-up-digital-downloads',
        category: 'Product updates',
        title: 'Selling digital downloads, now with expiring links',
        excerpt:
            'Upload your file, set how many times it can be downloaded, and let us expire the link automatically. Available on every plan from today.',
        date: 'January 28, 2026',
        readTime: '5 min read',
        author: 'Farhan Kabir',
        role: 'Product Manager',
        initials: 'FK',
        coverClass: 'bg-gradient-to-br from-teal-50 to-teal-100',
        emoji: '💾',
        body: [
            {
                type: 'p',
                text: 'Digital products on Shopwave now support expiring download links. Configure how many times a customer can download a file and when the link stops working, and we handle the rest of the delivery automatically.',
            },
            { type: 'h2', text: 'What you can set' },
            {
                type: 'ul',
                items: [
                    'Maximum download count per purchase',
                    'An expiry window in days from the moment of purchase',
                    'Per-product overrides where an item needs different rules',
                ],
            },
            { type: 'h2', text: 'Why expiry matters' },
            {
                type: 'p',
                text: 'An unlimited link that lands in an inbox is a link that gets shared. Expiry does not prevent that, but a link that stops working after seven days is far less valuable when it does.',
            },
            {
                type: 'quote',
                text: 'Every plan gets this. Expiring links are not a paid upsell — they should have shipped on day one.',
                cite: 'Farhan Kabir, Product Manager',
            },
            { type: 'h2', text: 'Getting started' },
            {
                type: 'p',
                text: 'Open any product, switch its type to digital, and upload the file. It appears at checkout immediately, and the customer receives their link the moment payment clears.',
            },
        ],
    },
]

/**
 * Every post in one list, featured first. The show page uses this for
 * lookups and for pulling related reads.
 */
export const allBlogPosts = [blogFeatured, ...blogPosts]

export function findBlogPost(slug) {
    return allBlogPosts.find((post) => post.slug === slug) ?? null
}

export function relatedBlogPosts(post, limit = 3) {
    if (!post) return []

    return allBlogPosts
        .filter((candidate) => candidate.slug !== post.slug && candidate.category === post.category)
        .slice(0, limit)
}