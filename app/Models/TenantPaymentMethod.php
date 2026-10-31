<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantPaymentMethod extends Model
{
    use HasFactory;

    protected $table = 'tenant_payment_methods';

    protected $guarded = ['id'];

    protected $casts = [
        'credentials' => 'encrypted:array',
        'is_sandbox' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function isCash(): bool
    {
        return $this->type === 'cash';
    }

    public function isManual(): bool
    {
        return $this->type === 'manual';
    }

    public function isAutomated(): bool
    {
        return $this->type === 'automated';
    }

    /**
     * Return masked credentials for safe display in Store Admin UI.
     */
    public function getMaskedCredentialsAttribute(): array
    {
        $creds = $this->credentials ?? [];
        if (!is_array($creds)) {
            return [];
        }

        $masked = [];
        $sensitiveKeys = ['store_passwd', 'store_password', 'secret_key', 'webhook_secret', 'app_secret', 'password', 'api_key'];

        foreach ($creds as $key => $val) {
            if (empty($val)) {
                $masked[$key] = '';
                continue;
            }

            $isSensitive = in_array(strtolower($key), $sensitiveKeys) || str_contains(strtolower($key), 'secret') || str_contains(strtolower($key), 'passwd') || str_contains(strtolower($key), 'password');

            if ($isSensitive && is_string($val)) {
                $len = strlen($val);
                if ($len > 8) {
                    $masked[$key] = substr($val, 0, 3) . str_repeat('•', max(4, $len - 7)) . substr($val, -4);
                } else {
                    $masked[$key] = '••••••••';
                }
            } else {
                $masked[$key] = $val;
            }
        }

        return $masked;
    }

    public function getTypeBadgeClassAttribute(): string
    {
        return match ($this->type) {
            'automated' => 'bg-emerald-50 text-emerald-700 border border-emerald-200/60 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800',
            'manual' => 'bg-indigo-50 text-indigo-700 border border-indigo-200/60 dark:bg-indigo-950/40 dark:text-indigo-300 dark:border-indigo-800',
            default => 'bg-amber-50 text-amber-700 border border-amber-200/60 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800',
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return $this->is_active
            ? 'bg-emerald-50 text-emerald-700 border-emerald-200/60 dark:bg-emerald-950/40 dark:text-emerald-400 dark:border-emerald-800'
            : 'bg-slate-100 text-slate-600 border-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700';
    }

    /**
     * Standard pre-defined catalog of available payment gateways and MFS providers.
     */
    public static function supportedGateways(): array
    {
        return [
            // Tier 1: Cash on Delivery
            'cod' => [
                'provider' => 'cod',
                'type' => 'cash',
                'title' => 'Cash on Delivery (COD)',
                'category' => 'Cash on Delivery',
                'description' => 'Collect cash payments upon delivery. Customers pay directly to the delivery rider.',
                'fields' => [
                    ['name' => 'max_order_amount', 'label' => 'Maximum Order Amount Limit', 'type' => 'number', 'placeholder' => 'Optional limit (e.g. 5000)'],
                    ['name' => 'note', 'label' => 'Customer Note / Delivery Rules', 'type' => 'text', 'placeholder' => 'Please have exact cash ready at delivery time.'],
                ],
            ],

            // Tier 2: Manual / Offline MFS
            'bkash_manual' => [
                'provider' => 'bkash_manual',
                'type' => 'manual',
                'title' => 'bKash (Send Money)',
                'category' => 'Mobile Financial Services (MFS)',
                'description' => 'Customers send money to your personal, agent, or merchant bKash number and provide their TrxID.',
                'fields' => [
                    ['name' => 'wallet_number', 'label' => 'bKash Wallet Number', 'type' => 'text', 'required' => true, 'placeholder' => '017XXXXXXXX'],
                    ['name' => 'account_type', 'label' => 'Account Type', 'type' => 'select', 'options' => ['personal' => 'Personal Account', 'merchant' => 'Merchant Account', 'agent' => 'Agent Account']],
                ],
            ],
            'nagad_manual' => [
                'provider' => 'nagad_manual',
                'type' => 'manual',
                'title' => 'Nagad (Send Money)',
                'category' => 'Mobile Financial Services (MFS)',
                'description' => 'Accept payments directly to your Nagad wallet account with TrxID confirmation.',
                'fields' => [
                    ['name' => 'wallet_number', 'label' => 'Nagad Wallet Number', 'type' => 'text', 'required' => true, 'placeholder' => '01XXXXXXXXX'],
                    ['name' => 'account_type', 'label' => 'Account Type', 'type' => 'select', 'options' => ['personal' => 'Personal Account', 'merchant' => 'Merchant Account']],
                ],
            ],
            'rocket_manual' => [
                'provider' => 'rocket_manual',
                'type' => 'manual',
                'title' => 'Rocket (DBBL)',
                'category' => 'Mobile Financial Services (MFS)',
                'description' => 'Accept direct Dutch-Bangla Rocket mobile banking payments.',
                'fields' => [
                    ['name' => 'wallet_number', 'label' => 'Rocket Account (12 digits)', 'type' => 'text', 'required' => true, 'placeholder' => '01XXXXXXXXX-X'],
                    ['name' => 'account_type', 'label' => 'Account Type', 'type' => 'select', 'options' => ['personal' => 'Personal Account', 'merchant' => 'Merchant Account']],
                ],
            ],
            'bank_transfer' => [
                'provider' => 'bank_transfer',
                'type' => 'manual',
                'title' => 'Direct Bank Transfer / Wire',
                'category' => 'Direct Bank Wire',
                'description' => 'Customers transfer funds directly to your corporate or pharmacy bank account.',
                'fields' => [
                    ['name' => 'bank_name', 'label' => 'Bank Name', 'type' => 'text', 'required' => true, 'placeholder' => 'City Bank PLC, DBBL, etc.'],
                    ['name' => 'account_name', 'label' => 'Account Holder Name', 'type' => 'text', 'required' => true, 'placeholder' => 'Pharmacy Ltd.'],
                    ['name' => 'account_number', 'label' => 'Account Number', 'type' => 'text', 'required' => true, 'placeholder' => '1102938475001'],
                    ['name' => 'branch_name', 'label' => 'Branch Name & Routing No.', 'type' => 'text', 'placeholder' => 'Gulshan Branch | 225272635'],
                ],
            ],

            // Tier 3: Automated Payment Gateways
            'sslcommerz' => [
                'provider' => 'sslcommerz',
                'type' => 'automated',
                'title' => 'SSLCommerz (Cards, MFS & Net Banking)',
                'category' => 'Automated Gateway',
                'description' => 'Accept Visa, MasterCard, bKash, Nagad, Amex, and Internet Banking seamlessly with real-time webhooks.',
                'fields' => [
                    ['name' => 'store_id', 'label' => 'Store ID', 'type' => 'text', 'required' => true, 'placeholder' => 'e.g. pharmacy_store_live'],
                    ['name' => 'store_passwd', 'label' => 'Store Password', 'type' => 'password', 'required' => true, 'placeholder' => '••••••••'],
                ],
            ],
            'bkash_api' => [
                'provider' => 'bkash_api',
                'type' => 'automated',
                'title' => 'bKash Tokenized Checkout (Direct API)',
                'category' => 'Automated Gateway',
                'description' => 'Official bKash PG checkout popup with 1-click tokenized payments.',
                'fields' => [
                    ['name' => 'app_key', 'label' => 'App Key', 'type' => 'text', 'required' => true, 'placeholder' => 'bKash App Key'],
                    ['name' => 'app_secret', 'label' => 'App Secret', 'type' => 'password', 'required' => true, 'placeholder' => 'bKash App Secret'],
                    ['name' => 'username', 'label' => 'Merchant Username', 'type' => 'text', 'required' => true, 'placeholder' => 'bKash API Username'],
                    ['name' => 'password', 'label' => 'Merchant Password', 'type' => 'password', 'required' => true, 'placeholder' => '••••••••'],
                ],
            ],
            'stripe' => [
                'provider' => 'stripe',
                'type' => 'automated',
                'title' => 'Stripe (Credit / Debit Cards)',
                'category' => 'Automated Gateway',
                'description' => 'International credit/debit card processing supporting Visa, Mastercard, Apple Pay, and Google Pay.',
                'fields' => [
                    ['name' => 'publishable_key', 'label' => 'Publishable Key', 'type' => 'text', 'required' => true, 'placeholder' => 'pk_live_... or pk_test_...'],
                    ['name' => 'secret_key', 'label' => 'Secret Key', 'type' => 'password', 'required' => true, 'placeholder' => 'sk_live_... or sk_test_...'],
                    ['name' => 'webhook_secret', 'label' => 'Webhook Secret Signing Key', 'type' => 'password', 'placeholder' => 'whsec_...'],
                ],
            ],
        ];
    }
}
