<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;

class TenantProductReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tenants = Tenant::all();

        foreach ($tenants as $tenant) {
            $products = Product::where('tenant_id', $tenant->id)->get();
            if ($products->isEmpty()) {
                continue;
            }

            $customers = Customer::where('tenant_id', $tenant->id)->get();
            $adminUser = $tenant->user ?? User::first();
            $orders = Order::where('tenant_id', $tenant->id)->get();

            $reviewsData = [
                [
                    'rating' => 5,
                    'title' => 'Genuine medicine & super fast delivery',
                    'comment' => 'Ordered during high fever at 10 PM and received the medicine within 45 minutes in Dhanmondi. Authentic blister pack with fresh batch expiry date (2027). Highly recommended!',
                    'status' => 'approved',
                    'is_verified_purchase' => true,
                    'reviewer_name' => 'Dr. Tanvir Ahmed',
                    'reviewer_email' => 'tanvir.ahmed@example.com',
                    'admin_reply' => 'Thank you Dr. Tanvir! We maintain temperature-controlled storage and prioritized rapid dispatch for acute fever medicines. Wish you good health!',
                    'replied' => true,
                    'images' => [
                        'https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?w=400&auto=format&fit=crop&q=60',
                    ],
                ],
                [
                    'rating' => 5,
                    'title' => 'Authentic gastric medication, well packed',
                    'comment' => 'Regular user for chronic acid reflux. The strip packaging was intact, moisture-free, and sealed. Pharmacist called to confirm before dispatch.',
                    'status' => 'approved',
                    'is_verified_purchase' => true,
                    'reviewer_name' => 'Sarah Jenkins',
                    'reviewer_email' => 'sarah.jenkins@example.com',
                    'admin_reply' => 'Thank you Sarah! Please ensure taking it 30 minutes before breakfast as prescribed by your physician for optimal efficacy.',
                    'replied' => true,
                    'images' => null,
                ],
                [
                    'rating' => 4,
                    'title' => 'Prompt verification and good service',
                    'comment' => 'My doctor wrote a handwritten prescription which was slightly blurred, but their pharmacist called me immediately, verified the dosage, and processed my order.',
                    'status' => 'approved',
                    'is_verified_purchase' => true,
                    'reviewer_name' => 'Rahim Chowdhury',
                    'reviewer_email' => 'rahim.c@example.com',
                    'admin_reply' => 'Thank you Rahim! Ensuring correct medicine dosage from handwritten prescriptions is our core safety protocol.',
                    'replied' => true,
                    'images' => null,
                ],
                [
                    'rating' => 5,
                    'title' => 'Prescription verification was smooth',
                    'comment' => 'Uploaded my hospital discharge summary, they confirmed all antibiotic items and provided exact brand substitutes as authorized. Very impressed with the standard of care.',
                    'status' => 'pending',
                    'is_verified_purchase' => true,
                    'reviewer_name' => 'Farhana Kabir',
                    'reviewer_email' => 'farhana.k@example.com',
                    'admin_reply' => null,
                    'replied' => false,
                    'images' => [
                        'https://images.unsplash.com/photo-1471864190281-a93a3070b6de?w=400&auto=format&fit=crop&q=60',
                    ],
                ],
                [
                    'rating' => 3,
                    'title' => 'Good product but delivery rider arrived late',
                    'comment' => 'The medicine itself is genuine and sealed, but the rider took 3 hours instead of the promised 90 minutes. Please improve delivery speed in Mirpur area.',
                    'status' => 'pending',
                    'is_verified_purchase' => true,
                    'reviewer_name' => 'Kamrul Hasan',
                    'reviewer_email' => 'kamrul.hasan@example.com',
                    'admin_reply' => null,
                    'replied' => false,
                    'images' => null,
                ],
                [
                    'rating' => 1,
                    'title' => 'Spam advertisement attempt',
                    'comment' => 'Visit my telegram crypto channel for cheap bitcoin signals 100% daily profit guaranteed click link now!',
                    'status' => 'rejected',
                    'is_verified_purchase' => false,
                    'reviewer_name' => 'Crypto Bot',
                    'reviewer_email' => 'bot449@tempmail.xyz',
                    'admin_reply' => null,
                    'replied' => false,
                    'images' => null,
                ],
                [
                    'rating' => 5,
                    'title' => 'Essential supplement for family',
                    'comment' => 'Great pricing compared to local pharmacy chains. 100% genuine seal on the box with QR security code.',
                    'status' => 'approved',
                    'is_verified_purchase' => true,
                    'reviewer_name' => 'Nusrat Jahan',
                    'reviewer_email' => 'nusrat.jahan@example.com',
                    'admin_reply' => 'Thank you Nusrat! We source directly from verified pharmaceutical distributors.',
                    'replied' => true,
                    'images' => null,
                ],
                [
                    'rating' => 4,
                    'title' => 'Convenient medicine re-order',
                    'comment' => 'Very easy to order my monthly hypertension prescription refills. Packing is always discreet and moisture-protected.',
                    'status' => 'pending',
                    'is_verified_purchase' => false,
                    'reviewer_name' => 'Mohammad Ali',
                    'reviewer_email' => 'm.ali98@example.com',
                    'admin_reply' => null,
                    'replied' => false,
                    'images' => null,
                ],
            ];

            foreach ($reviewsData as $index => $data) {
                $product = $products->get($index % $products->count());
                $customer = $customers->isNotEmpty() ? $customers->get($index % $customers->count()) : null;
                $order = $orders->isNotEmpty() && $data['is_verified_purchase'] ? $orders->get($index % $orders->count()) : null;

                ProductReview::updateOrCreate(
                    [
                        'tenant_id' => $tenant->id,
                        'product_id' => $product->id,
                        'reviewer_email' => $data['reviewer_email'],
                    ],
                    [
                        'customer_id' => $customer?->id,
                        'order_id' => $order?->id,
                        'reviewer_name' => $customer ? trim($customer->first_name . ' ' . ($customer->last_name ?? '')) : $data['reviewer_name'],
                        'rating' => $data['rating'],
                        'title' => $data['title'],
                        'comment' => $data['comment'],
                        'status' => $data['status'],
                        'is_verified_purchase' => $data['is_verified_purchase'],
                        'admin_reply' => $data['admin_reply'],
                        'replied_at' => $data['replied'] ? now()->subHours(rand(1, 24)) : null,
                        'replied_by' => $data['replied'] ? $adminUser?->id : null,
                        'images' => $data['images'],
                        'helpful_count' => rand(1, 15),
                        'unhelpful_count' => rand(0, 2),
                        'created_at' => now()->subDays(rand(0, 14))->subHours(rand(1, 20)),
                    ]
                );
            }
        }
    }
}
