<?php

namespace Tests\Feature;

use App\Models\PlatformPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlanUpdateReproTest extends TestCase
{
    use RefreshDatabase;

    public function test_plan_name_updates_via_admin_endpoint(): void
    {
        $this->seed();

        $admin = User::where('email', 'admin@admin.com')->firstOrFail();

        $plan = PlatformPlan::first();
        $newName = 'Renamed '.uniqid();

        $response = $this->actingAs($admin)
            ->put($this->host().'/admin/plans/'.$plan->id, [
                'name' => $newName,
                'slug' => $plan->slug,
                'monthly_price' => '9.99',
                'yearly_price' => '99.00',
                'max_products' => 50,
                'max_staff_accounts' => 1,
                'max_storage_mb' => 100,
                'max_digital_file_size_mb' => 10,
                'max_currencies_supported' => 1,
            ]);

        $response->assertSessionHasNoErrors();

        dump('STATUS='.$response->status());
        dump('RECORD='.json_encode(PlatformPlan::find($plan->id)->toArray()));

        $this->assertDatabaseHas('platform_plans', [
            'id' => $plan->id,
            'name' => $newName,
        ]);
    }

    private function host(): string
    {
        return 'http://'.config('app.base_domain');
    }
}