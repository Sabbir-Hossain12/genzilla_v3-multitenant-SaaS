<?php

namespace Database\Seeders;

use App\Models\PlatformBlogCategory;
use App\Models\PlatformBlogPost;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class PlatformBlogSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Guides', 'slug' => 'guides', 'sort_order' => 1],
            ['name' => 'Growth', 'slug' => 'growth', 'sort_order' => 2],
            ['name' => 'Product updates', 'slug' => 'product', 'sort_order' => 3],
            ['name' => 'Payments', 'slug' => 'payments', 'sort_order' => 4],
        ];

        $categoryIds = [];

        foreach ($categories as $category) {
            $model = PlatformBlogCategory::updateOrCreate(
                ['slug' => $category['slug']],
                [
                    'name' => $category['name'],
                    'sort_order' => $category['sort_order'],
                    'status' => 1,
                ]
            );

            $categoryIds[$category['slug']] = $model->id;
        }

        foreach ($this->posts() as $index => $post) {
            PlatformBlogPost::updateOrCreate(
                ['slug' => $post['slug']],
                [
                    'blog_category_id' => $categoryIds[$post['category']] ?? null,
                    'title' => $post['title'],
                    'excerpt' => $post['excerpt'],
                    'long_desc' => $this->renderBody($post['body']),
                    'cover_class' => $post['coverClass'],
                    'emoji' => $post['emoji'],
                    'author_name' => $post['author'],
                    'author_role' => $post['role'],
                    'author_initials' => $post['initials'],
                    'read_time' => (int) $post['readTime'],
                    'published_at' => Carbon::parse($post['date'])->format('Y-m-d'),
                    'is_featured' => $post['is_featured'] ?? false,
                    'status' => 1,
                    'meta_title' => $post['title'],
                    'meta_description' => $post['excerpt'],
                    'sort_order' => $index,
                ]
            );
        }
    }

    /**
     * Convert the block list into HTML markup for the long_desc column.
     *
     * @param  array<int, array<string, mixed>>  $blocks
     */
    private function renderBody(array $blocks): string
    {
        $html = '';

        foreach ($blocks as $block) {
            $type = $block['type'] ?? '';

            if ($type === 'ul') {
                $items = '';
                foreach ($block['items'] ?? [] as $item) {
                    $items .= '<li>'.e($item).'</li>';
                }
                $html .= '<ul>'.$items.'</ul>';
            } elseif ($type === 'quote') {
                $html .= '<blockquote><p>'.e($block['text'] ?? '').'</p><cite>'.e($block['cite'] ?? '').'</cite></blockquote>';
            } elseif ($type === 'h2') {
                $html .= '<h2>'.e($block['text'] ?? '').'</h2>';
            } elseif ($type === 'p') {
                $html .= '<p>'.e($block['text'] ?? '').'</p>';
            }
        }

        return $html;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function posts(): array
    {
        return [
            [
                'slug' => 'launch-your-first-store-in-a-weekend',
                'category' => 'guides',
                'title' => 'How to launch your first store on Shopwave in a single weekend',
                'excerpt' => 'A step-by-step walkthrough of everything that has to happen before you can take your first order — from picking products to wiring up payments. No code required.',
                'date' => 'March 4, 2026',
                'readTime' => '11',
                'author' => 'Nadia Rahman',
                'role' => 'Head of Merchant Success',
                'initials' => 'NR',
                'coverClass' => 'bg-gradient-to-br from-indigo-50 to-indigo-100',
                'emoji' => '🚀',
                'is_featured' => true,
                'body' => [
                    ['type' => 'p', 'text' => 'Most merchants do not need three months of planning. They need one focused Saturday and one Sunday. This is the exact sequence we walk new Shopwave merchants through, in the order that avoids rework.'],
                    ['type' => 'h2', 'text' => 'Before you start: what you need ready'],
                    ['type' => 'ul', 'items' => [
                        'Twelve to thirty products, photographed against a plain background',
                        'Cost price and intended retail price for each one',
                        'Your bank or merchant account details for settlement',
                        'One delivery partner account, if you are not collecting cash on delivery',
                    ]],
                    ['type' => 'h2', 'text' => 'Saturday morning: catalogue and pricing'],
                    ['type' => 'p', 'text' => 'Create your account, then add products in bulk using the CSV importer. Get the name, description, price, and one clear photo in for every item. Resist the urge to perfect the copy here — you can refine descriptions after your first real customers tell you what confused them.'],
                    ['type' => 'h2', 'text' => 'Saturday afternoon: payments and delivery'],
                    ['type' => 'p', 'text' => 'Connect your gateway and run a test payment of one taka. Then set up delivery zones and rates. Merchants who skip this step and add it later almost always end up with a rate card that does not match the areas they actually ship to.'],
                    ['type' => 'quote', 'text' => 'The stores that take orders in week one are not the ones with the best photography. They are the ones that finished payments and delivery before they launched.', 'cite' => 'Nadia Rahman, Head of Merchant Success'],
                    ['type' => 'h2', 'text' => 'Sunday: place a real test order'],
                    ['type' => 'p', 'text' => 'Buy something from your own store end to end — from checkout to the order appearing in your dashboard. Then cancel it. That single test catches more problems than an hour of clicking around the dashboard will, because it exercises the same path your customer will take.'],
                ],
            ],
            [
                'slug' => 'product-photography-that-sells',
                'category' => 'growth',
                'title' => 'Product photography that actually sells',
                'excerpt' => 'You do not need a studio. You need consistent light, a plain background, and six shots per product. Here is the setup we recommend to every new merchant.',
                'date' => 'February 27, 2026',
                'readTime' => '7',
                'author' => 'Tanvir Ahmed',
                'role' => 'Growth Lead',
                'initials' => 'TA',
                'coverClass' => 'bg-gradient-to-br from-emerald-50 to-emerald-100',
                'emoji' => '📸',
                'body' => [
                    ['type' => 'p', 'text' => 'Shoppers decide in about two seconds, and most of that decision is made on the image before they read a single word of your description. Here is how to shoot product photos that hold up in a grid, without renting a studio.'],
                    ['type' => 'h2', 'text' => 'The one-room setup'],
                    ['type' => 'ul', 'items' => [
                        'A plain sheet of white or light grey paper as your background',
                        'A window for soft, indirect daylight — never direct sun',
                        'Your phone camera, with the lens wiped clean',
                        'Something to prop the product on so it does not sit flat on the paper',
                    ]],
                    ['type' => 'h2', 'text' => 'Six shots is the sweet spot'],
                    ['type' => 'p', 'text' => 'Front, back, side, angled, in-hand for scale, and one detail close-up. That set covers every question a shopper asks — what is it, how big is it, what does the material look like, and does it work with what I already own.'],
                    ['type' => 'quote', 'text' => 'Merchants who shoot six consistent images per product see measurably fewer "is this genuine?" messages than merchants shooting two.', 'cite' => 'Tanvir Ahmed, Growth Lead'],
                    ['type' => 'h2', 'text' => 'Consistency beats quality'],
                    ['type' => 'p', 'text' => 'A slightly soft photo that matches the rest of your catalogue looks more professional than one beautiful shot surrounded by inconsistent ones. Fix your lighting and background once, and reuse them for every product.'],
                ],
            ],
            [
                'slug' => 'choosing-a-payment-gateway',
                'category' => 'payments',
                'title' => 'Choosing a payment gateway in Bangladesh',
                'excerpt' => 'Mobile wallets, cards, and cash on delivery each behave differently at checkout. A practical comparison of fees, settlement times, and failure rates.',
                'date' => 'February 19, 2026',
                'readTime' => '9',
                'author' => 'Sadia Islam',
                'role' => 'Payments Specialist',
                'initials' => 'SI',
                'coverClass' => 'bg-gradient-to-br from-sky-50 to-sky-100',
                'emoji' => '💳',
                'body' => [
                    ['type' => 'p', 'text' => 'There is no single best gateway here — the right answer depends on what you are selling and how your customers prefer to pay. What follows is how the three main options actually behave in production.'],
                    ['type' => 'h2', 'text' => 'Mobile wallets'],
                    ['type' => 'p', 'text' => 'bKash, Nagad, and Rocket dominate for a reason: customers trust them and the money moves instantly. They carry a slightly higher percentage fee than cards, and failure rates spike when a customer has never used that wallet before.'],
                    ['type' => 'h2', 'text' => 'Cards'],
                    ['type' => 'p', 'text' => 'Lower percentage fees, but expect a decline rate in the single digits and settlement in days rather than minutes. Cards are usually worth enabling for higher-ticket orders where the fee difference is worth the delay.'],
                    ['type' => 'h2', 'text' => 'Cash on delivery'],
                    ['type' => 'p', 'text' => 'Still the default preference for a large share of shoppers, and it converts better than any digital method for first-time buyers. The tradeoff is that you absorb failed-delivery costs, so build your refusal policy before you need it.'],
                    ['type' => 'quote', 'text' => 'Enabling all three does not add meaningful complexity. Turning one off mid-checkout is what costs you sales.', 'cite' => 'Sadia Islam, Payments Specialist'],
                    ['type' => 'h2', 'text' => 'The practical answer'],
                    ['type' => 'p', 'text' => 'Turn on everything, and watch your own failure rate per method for thirty days. Your customers will tell you which one to prioritise far more reliably than any benchmark we could quote.'],
                ],
            ],
            [
                'slug' => 'what-is-new-in-march',
                'category' => 'product',
                'title' => 'What is new in Shopwave this March',
                'excerpt' => 'Bulk product editing, saved shipping rates, and a rebuilt order timeline. Here is everything that shipped, plus what we are building next.',
                'date' => 'March 2, 2026',
                'readTime' => '4',
                'author' => 'Farhan Kabir',
                'role' => 'Product Manager',
                'initials' => 'FK',
                'coverClass' => 'bg-gradient-to-br from-violet-50 to-violet-100',
                'emoji' => '✨',
                'body' => [
                    ['type' => 'p', 'text' => 'A short release round this month, weighted towards the two things merchants told us slowed them down most: changing prices in bulk, and reworking delivery rates.'],
                    ['type' => 'h2', 'text' => 'Shipped'],
                    ['type' => 'ul', 'items' => [
                        'Bulk product editing — change price, stock, or status across a filtered selection',
                        'Saved shipping rates — reuse a rate you configured before instead of retyping it',
                        'Rebuilt order timeline — every fulfilment event on one scrollable column',
                        'Download links now expire automatically for digital products',
                    ]],
                    ['type' => 'h2', 'text' => 'Still building'],
                    ['type' => 'p', 'text' => 'Multi-location stock is the big one. It is the most requested feature we have and the hardest to get right, so we are shipping it in stages rather than rushing it. Expect a beta for early testers next month.'],
                    ['type' => 'quote', 'text' => 'We would rather ship the rebuilt order timeline twice than ship a half-working multi-location system once.', 'cite' => 'Farhan Kabir, Product Manager'],
                ],
            ],
            [
                'slug' => 'reducing-abandoned-carts',
                'category' => 'growth',
                'title' => 'Nine ways to reduce abandoned carts',
                'excerpt' => 'Most carts are lost for one of nine reasons. We ranked them by how much revenue they cost, and paired each with the fix that worked for our merchants.',
                'date' => 'February 11, 2026',
                'readTime' => '8',
                'author' => 'Nadia Rahman',
                'role' => 'Head of Merchant Success',
                'initials' => 'NR',
                'coverClass' => 'bg-gradient-to-br from-rose-50 to-rose-100',
                'emoji' => '🛒',
                'body' => [
                    ['type' => 'p', 'text' => 'We looked at where carts stall across several thousand stores. Nine causes accounted for nearly all of it, and they are listed here in roughly descending order of revenue lost.'],
                    ['type' => 'h2', 'text' => 'The big ones'],
                    ['type' => 'ul', 'items' => [
                        'Surprise delivery charges revealed only at the final step',
                        'No guest checkout — forced account creation before payment',
                        'A long or unclear payment form, especially on mobile',
                        'Only one payment method available',
                        'No way to edit the cart without starting over',
                    ]],
                    ['type' => 'h2', 'text' => 'The cheap wins'],
                    ['type' => 'p', 'text' => 'Show delivery cost earlier, keep the cart reachable from the order summary, and offer guest checkout. Together these three account for most of the improvement we see from merchants who make only one change.'],
                    ['type' => 'quote', 'text' => 'A surprising number of abandoned carts are not price objections. They are friction objections. The customer liked the product and simply gave up.', 'cite' => 'Nadia Rahman, Head of Merchant Success'],
                    ['type' => 'h2', 'text' => 'Measure it properly'],
                    ['type' => 'p', 'text' => 'Watch the funnel step by step rather than looking at a single abandonment rate. Knowing that forty percent stall on the delivery step tells you what to fix; knowing that forty percent abandon tells you nothing.'],
                ],
            ],
            [
                'slug' => 'shipping-rates-explained',
                'category' => 'guides',
                'title' => 'Shipping rates, explained without the jargon',
                'excerpt' => 'Flat rate, weight-based, free over a threshold, or zone-based. What each model costs you, and when switching to it actually pays off.',
                'date' => 'February 4, 2026',
                'readTime' => '6',
                'author' => 'Tanvir Ahmed',
                'role' => 'Growth Lead',
                'initials' => 'TA',
                'coverClass' => 'bg-gradient-to-br from-amber-50 to-amber-100',
                'emoji' => '🚚',
                'body' => [
                    ['type' => 'p', 'text' => 'Shipping is where margin quietly disappears. The rate model you pick changes what you can sell, what you earn per order, and how often a customer abandons at the last step.'],
                    ['type' => 'h2', 'text' => 'Flat rate'],
                    ['type' => 'p', 'text' => 'One price for every order, regardless of size or distance. Simplest to set up and easiest to explain. It works when your catalogue is uniform — same size, same weight class, roughly the same order value.'],
                    ['type' => 'h2', 'text' => 'Weight-based'],
                    ['type' => 'p', 'text' => 'Price scales with actual or volumetric weight. The model you want if you sell anything bulky or irregular. Mind volumetric weight: a light but large box can weigh more on the scale than it does on your scales.'],
                    ['type' => 'h2', 'text' => 'Free over a threshold'],
                    ['type' => 'p', 'text' => 'Not a rate model on its own but a modifier on one. It reliably raises average order value. Set the threshold just above your current average, not round — ৳1,500 converts better than ৳1,500 is a coincidence, but ৳2,000 might simply be unreachable.'],
                    ['type' => 'quote', 'text' => 'Whatever model you choose, quote the customer the same number you intend to charge. A changed delivery fee at checkout is the fastest way to lose a sale.', 'cite' => 'Tanvir Ahmed, Growth Lead'],
                ],
            ],
            [
                'slug' => 'setting-up-digital-downloads',
                'category' => 'product',
                'title' => 'Selling digital downloads, now with expiring links',
                'excerpt' => 'Upload your file, set how many times it can be downloaded, and let us expire the link automatically. Available on every plan from today.',
                'date' => 'January 28, 2026',
                'readTime' => '5',
                'author' => 'Farhan Kabir',
                'role' => 'Product Manager',
                'initials' => 'FK',
                'coverClass' => 'bg-gradient-to-br from-teal-50 to-teal-100',
                'emoji' => '💾',
                'body' => [
                    ['type' => 'p', 'text' => 'Digital products on Shopwave now support expiring download links. Configure how many times a customer can download a file and when the link stops working, and we handle the rest of the delivery automatically.'],
                    ['type' => 'h2', 'text' => 'What you can set'],
                    ['type' => 'ul', 'items' => [
                        'Maximum download count per purchase',
                        'An expiry window in days from the moment of purchase',
                        'Per-product overrides where an item needs different rules',
                    ]],
                    ['type' => 'h2', 'text' => 'Why expiry matters'],
                    ['type' => 'p', 'text' => 'An unlimited link that lands in an inbox is a link that gets shared. Expiry does not prevent that, but a link that stops working after seven days is far less valuable when it does.'],
                    ['type' => 'quote', 'text' => 'Every plan gets this. Expiring links are not a paid upsell — they should have shipped on day one.', 'cite' => 'Farhan Kabir, Product Manager'],
                    ['type' => 'h2', 'text' => 'Getting started'],
                    ['type' => 'p', 'text' => 'Open any product, switch its type to digital, and upload the file. It appears at checkout immediately, and the customer receives their link the moment payment clears.'],
                ],
            ],
        ];
    }
}
