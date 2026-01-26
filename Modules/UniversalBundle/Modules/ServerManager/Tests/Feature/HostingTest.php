<?php

namespace Modules\ServerManager\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\ServerManager\Entities\ServerHosting;
use App\Models\User;

class HostingTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function it_can_list_hostings()
    {
        $response = $this->actingAs($this->user)
            ->get(route('hosting.index'));

        $response->assertStatus(200);
    }

    /** @test */
    public function it_can_create_hosting()
    {
        $hostingData = [
            'name' => 'Test Hosting',
            'domain_name' => 'test.com',
            'hosting_provider' => 'Test Provider',
            'server_type' => 'shared',
            'purchase_date' => '2024-01-01',
            'renewal_date' => '2025-01-01',
            'status' => 'active',
        ];

        $response = $this->actingAs($this->user)
            ->post(route('hosting.store'), $hostingData);

        $response->assertStatus(200);
        $this->assertDatabaseHas('server_hostings', [
            'name' => 'Test Hosting',
            'domain_name' => 'test.com',
        ]);
    }

    /** @test */
    public function it_can_update_hosting()
    {
        $hosting = ServerHosting::factory()->create([
            'company_id' => $this->user->company_id,
        ]);

        $updateData = [
            'name' => 'Updated Hosting',
            'domain_name' => 'updated.com',
        ];

        $response = $this->actingAs($this->user)
            ->put(route('hosting.update', $hosting->id), $updateData);

        $response->assertStatus(200);
        $this->assertDatabaseHas('server_hostings', [
            'id' => $hosting->id,
            'name' => 'Updated Hosting',
        ]);
    }

    /** @test */
    public function it_can_delete_hosting()
    {
        $hosting = ServerHosting::factory()->create([
            'company_id' => $this->user->company_id,
        ]);

        $response = $this->actingAs($this->user)
            ->delete(route('hosting.destroy', $hosting->id));

        $response->assertStatus(200);
        $this->assertDatabaseMissing('server_hostings', [
            'id' => $hosting->id,
        ]);
    }
}
