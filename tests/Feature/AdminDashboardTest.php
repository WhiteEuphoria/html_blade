<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['integration.theme_routes' => true]);
    }

    public function test_admin_can_switch_between_clients(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
            'currency' => 'EUR',
            'main_balance' => 0,
            'verification_status' => 'approved',
        ]);

        $firstClient = User::factory()->create([
            'name' => 'Alice Client',
            'is_admin' => false,
            'currency' => 'USD',
            'main_balance' => 1500,
            'verification_status' => 'approved',
        ]);

        $secondClient = User::factory()->create([
            'name' => 'Bob Client',
            'is_admin' => false,
            'currency' => 'GBP',
            'main_balance' => 2750,
            'verification_status' => 'active',
        ]);

        Account::create([
            'user_id' => $secondClient->id,
            'number' => 'ACC-2001',
            'type' => 'Classic',
            'organization' => 'Acme Corp',
            'client_initials' => 'B. C.',
            'broker_initials' => 'Agent',
            'term' => now()->addYear(),
            'status' => 'Active',
            'balance' => 9999.99,
            'currency' => 'GBP',
            'is_default' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard', ['user' => $secondClient->id]));

        $response->assertOk();
        $response->assertViewHas('selectedUserId', $secondClient->id);
        $response->assertSee('Bob Client', false);
        $response->assertSee('ACC-2001', false);
        $response->assertSee('value="' . $secondClient->id . '" selected', false);
    }

    public function test_dashboard_defaults_to_first_client_when_selected_user_missing(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
            'currency' => 'EUR',
            'main_balance' => 0,
            'verification_status' => 'approved',
        ]);

        $firstClient = User::factory()->create([
            'name' => 'First Client',
            'is_admin' => false,
            'currency' => 'EUR',
            'main_balance' => 1000,
            'verification_status' => 'approved',
        ]);

        $secondClient = User::factory()->create([
            'name' => 'Second Client',
            'is_admin' => false,
            'currency' => 'USD',
            'main_balance' => 2000,
            'verification_status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard', ['user' => 99999]));

        $response->assertOk();
        $response->assertViewHas('selectedUserId', function ($value) use ($firstClient, $secondClient) {
            return in_array($value, [$firstClient->id, $secondClient->id], true);
        });
        $response->assertSee('First Client', false);
        $response->assertSee('Second Client', false);
    }
}
