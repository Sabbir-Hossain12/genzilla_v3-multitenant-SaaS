<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Platform\Concerns\SharesPlatformBrand;
use App\Models\PlatformFaq;
use App\Models\PlatformFeature;
use App\Models\PlatformHero;
use App\Models\PlatformMatrix;
use App\Models\PlatformPlan;
use App\Models\PlatformStep;
use App\Models\PlatformTestimonial;
use App\Models\PlatformUseCase;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

class PlatformWebviewController extends Controller
{
    use SharesPlatformBrand;

    public function index(): Response
    {
        return Inertia::render('platform/index', [
            'hero' => $this->heroData(),
            'stats' => $this->statsData(),
            'features' => $this->featureList(),
            'steps' => $this->stepList(),
            'testimonials' => $this->testimonialList(),
            'plans' => $this->planTeaser(),
            'brand' => $this->brand(),
        ]);
    }

    public function features(): Response
    {
        return Inertia::render('platform/features', [
            'useCases' => $this->useCaseList(),
            'featureGrid' => $this->featureList(),
            'brand' => $this->brand(),
        ]);
    }

    public function pricing(): Response
    {
        return Inertia::render('platform/pricing', [
            'plans' => $this->pricingPlanList(),
            'comparison' => $this->comparisonRows(),
            'faqs' => $this->faqList(),
            'brand' => $this->brand(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function heroData(): array
    {
        $hero = PlatformHero::query()->first();

        if (! $hero) {
            return [];
        }

        return [
            'badge' => $hero->ai_product_desc,
            'title' => $hero->title,
            'subtitle' => $hero->short_desc,
            'primaryLabel' => $hero->btn_1_text,
            'primaryTo' => $hero->btn_1_link,
            'secondaryLabel' => $hero->btn_2_text,
            'secondaryTo' => $hero->btn_2_link,
        ];
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function statsData(): array
    {
        $matrix = PlatformMatrix::query()->first();

        if (! $matrix) {
            return [];
        }

        return [
            ['value' => $this->compactNumber($matrix->active_merchant).'+', 'label' => 'Active Merchants'],
            ['value' => $this->compactMoney($matrix->gmv_proccessed), 'label' => 'GMV Processed'],
            ['value' => (string) $matrix->countries_served, 'label' => 'Countries Served'],
            ['value' => $matrix->platform_uptime, 'label' => 'Platform Uptime'],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function featureList(): array
    {
        return PlatformFeature::query()
            ->where('status', 1)
            ->orderBy('sort_order')
            ->get()
            ->map(fn (PlatformFeature $feature) => [
                'title' => $feature->title,
                'body' => $feature->short_desc,
                'icon' => $feature->icon,
            ])
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function stepList(): array
    {
        return PlatformStep::query()
            ->where('status', 1)
            ->orderBy('sort_order')
            ->get()
            ->map(fn (PlatformStep $step) => [
                'title' => $step->title,
                'body' => $step->short_desc,
            ])
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function testimonialList(): array
    {
        return PlatformTestimonial::query()
            ->where('status', 1)
            ->orderBy('sort_order')
            ->get()
            ->map(fn (PlatformTestimonial $testimonial) => [
                'quote' => $testimonial->text,
                'name' => $testimonial->merchant_name,
                'role' => $testimonial->merchant_title,
                'initials' => $this->initials($testimonial->merchant_name),
            ])
            ->all();
    }

    /**
     * Condensed plan cards for the landing page pricing teaser.
     *
     * @return array<int, array<string, mixed>>
     */
    private function planTeaser(): array
    {
        $plans = $this->activePlans();

        return $plans->values()
            ->map(fn (PlatformPlan $plan, int $index) => [
                'name' => $plan->name,
                'price' => $this->money($plan->monthly_price),
                'suffix' => '/mo',
                'note' => 'Up to '.number_format($plan->max_products).' products',
                'highlighted' => $this->isFeatured($index, $plans->count()),
            ])
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function useCaseList(): array
    {
        return PlatformUseCase::query()
            ->where('status', 1)
            ->orderBy('sort_order')
            ->get()
            ->map(fn (PlatformUseCase $useCase) => [
                'title' => $useCase->title,
                'body' => $useCase->description,
                'badge' => $useCase->badge_label,
                'badgeType' => $useCase->badge_type,
                'bgColor' => $useCase->bg_color,
                'iconUrl' => $useCase->icon_url,
            ])
            ->all();
    }

    /**
     * Full pricing cards.
     *
     * @return array<int, array<string, mixed>>
     */
    private function pricingPlanList(): array
    {
        $plans = $this->activePlans();
        $taglines = [
            'starter' => 'For new merchants getting started',
            'growth' => 'For growing stores that need more',
            'enterprise' => 'For high-volume & multi-brand businesses',
        ];

        return $plans->values()
            ->map(fn (PlatformPlan $plan, int $index) => [
                'name' => $plan->name,
                'slug' => $plan->slug,
                'tagline' => $taglines[$plan->slug] ?? 'For every stage of growth',
                'monthly' => $this->money($plan->monthly_price),
                'yearly' => $this->money($plan->yearly_price),
                'fee' => '14-day free trial · Cancel anytime',
                'ctaLabel' => $plan->slug === 'enterprise' ? 'Contact Sales' : 'Start Free Trial',
                'ctaTo' => $plan->slug === 'enterprise' ? null : '/register',
                'featured' => $this->isFeatured($index, $plans->count()),
                'features' => $this->planFeatures($plan),
            ])
            ->all();
    }

    /**
     * Capability comparison table, one row per capability with values aligned to
     * the plan order.
     *
     * @return array<int, array<string, mixed>>
     */
    private function comparisonRows(): array
    {
        $plans = $this->activePlans();

        $column = fn (callable $callback) => $plans->map($callback)->all();

        return [
            ['feature' => 'Products', 'values' => $column(fn (PlatformPlan $p) => number_format($p->max_products)), 'emphasize' => true],
            ['feature' => 'Staff accounts', 'values' => $column(fn (PlatformPlan $p) => number_format($p->max_staff_accounts)), 'emphasize' => true, 'striped' => true],
            ['feature' => 'Custom domain', 'values' => $column(fn (PlatformPlan $p) => (bool) $p->allow_custom_domain)],
            ['feature' => 'Multi-currency', 'values' => $column(fn (PlatformPlan $p) => (bool) $p->has_multi_currency), 'striped' => true],
            ['feature' => 'Advanced reporting', 'values' => $column(fn (PlatformPlan $p) => (bool) $p->has_advanced_reporting)],
            ['feature' => 'Custom roles & permissions', 'values' => $column(fn (PlatformPlan $p) => (bool) $p->has_custom_rbac), 'striped' => true],
            ['feature' => 'Audit logs', 'values' => $column(fn (PlatformPlan $p) => (bool) $p->has_audit_logs)],
            ['feature' => 'Storage', 'values' => $column(fn (PlatformPlan $p) => number_format($p->max_storage_mb).' MB'), 'striped' => true],
        ];
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function faqList(): array
    {
        return PlatformFaq::query()
            ->where('status', 1)
            ->orderBy('sort_order')
            ->get()
            ->map(fn (PlatformFaq $faq) => [
                'question' => $faq->question,
                'answer' => $faq->answer,
            ])
            ->all();
    }

    /**
     * @return Collection<int, PlatformPlan>
     */
    private function activePlans(): Collection
    {
        return PlatformPlan::query()
            ->where('status', 1)
            ->orderBy('monthly_price')
            ->get();
    }

    /**
     * @return array<int, string>
     */
    private function planFeatures(PlatformPlan $plan): array
    {
        $features = [
            'Up to '.number_format($plan->max_products).' products',
            number_format($plan->max_staff_accounts).' staff '.($plan->max_staff_accounts === 1 ? 'account' : 'accounts'),
            number_format($plan->max_storage_mb).' MB storage',
            $plan->allow_custom_domain ? 'Custom domain & SSL' : 'Shared subdomain',
            $plan->has_multi_currency
                ? 'Multi-currency ('.$plan->max_currencies_supported.' currencies)'
                : 'Single currency',
        ];

        if ($plan->has_advanced_reporting) {
            $features[] = 'Advanced reporting';
        }

        if ($plan->has_custom_rbac) {
            $features[] = 'Custom roles & permissions';
        }

        if ($plan->has_audit_logs) {
            $features[] = 'Audit logs';
        }

        return $features;
    }

    /**
     * Highlight the middle plan when there are three or more, otherwise the last.
     */
    private function isFeatured(int $index, int $count): bool
    {
        if ($count >= 3) {
            return $index === 1;
        }

        return $index === $count - 1;
    }

    private function money(float|int|string $value): string
    {
        $value = (float) $value;
        $decimals = $value === floor($value) ? 0 : 2;

        return '$'.number_format($value, $decimals);
    }

    private function compactNumber(int|string $value): string
    {
        $value = (int) $value;

        if ($value >= 1_000_000) {
            return rtrim(rtrim(number_format($value / 1_000_000, 1), '0'), '.').'M';
        }

        if ($value >= 1_000) {
            return rtrim(rtrim(number_format($value / 1_000, 1), '0'), '.').'K';
        }

        return (string) $value;
    }

    private function compactMoney(float|int|string $value): string
    {
        $value = (float) $value;

        if ($value >= 1_000_000_000) {
            return '$'.round($value / 1_000_000_000, 1).'B';
        }

        if ($value >= 1_000_000) {
            return '$'.round($value / 1_000_000, 1).'M';
        }

        if ($value >= 1_000) {
            return '$'.round($value / 1_000, 1).'K';
        }

        return '$'.number_format($value);
    }
}
