<?php

namespace Modules\ServerManager\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\ServerManager\Entities\ServerHosting;
use App\Models\User;
use App\Models\Company;
use Carbon\Carbon;

class HostingExpiryNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Set up company context
        $company = Company::factory()->create();
        // Note: In a real test environment, you would set the company context appropriately
    }

    /** @test */
    public function it_can_determine_when_notification_should_be_sent()
    {
        // Create a hosting that expires in 30 days
        $hosting = ServerHosting::factory()->create([
            'expiry_notification' => true,
            'notification_days_before' => 30,
            'notification_time_unit' => 'days',
            'renewal_date' => now()->addDays(30),
            'status' => 'active',
        ]);

        // Should not send notification yet (notification date is today)
        $this->assertTrue($hosting->shouldSendNotification());

        // Create a hosting that expires in 60 days with 30 days notification
        $hosting2 = ServerHosting::factory()->create([
            'expiry_notification' => true,
            'notification_days_before' => 30,
            'notification_time_unit' => 'days',
            'renewal_date' => now()->addDays(60),
            'status' => 'active',
        ]);

        // Should not send notification yet (notification date is in 30 days)
        $this->assertFalse($hosting2->shouldSendNotification());
    }

    /** @test */
    public function it_can_calculate_notification_date_correctly()
    {
        $hosting = ServerHosting::factory()->create([
            'renewal_date' => now()->addDays(30),
            'notification_days_before' => 7,
            'notification_time_unit' => 'days',
        ]);

        $notificationDate = $hosting->getNotificationDate();
        $expectedDate = now()->addDays(30)->subDays(7)->startOfDay();

        $this->assertEquals($expectedDate->toDateString(), $notificationDate->toDateString());
    }

    /** @test */
    public function it_handles_different_time_units_correctly()
    {
        $hosting = ServerHosting::factory()->create([
            'renewal_date' => now()->addDays(30),
            'notification_days_before' => 2,
            'notification_time_unit' => 'weeks',
        ]);

        $notificationDate = $hosting->getNotificationDate();
        $expectedDate = now()->addDays(30)->subDays(14)->startOfDay(); // 2 weeks = 14 days

        $this->assertEquals($expectedDate->toDateString(), $notificationDate->toDateString());
    }

    /** @test */
    public function it_marks_notification_as_sent()
    {
        $hosting = ServerHosting::factory()->create([
            'last_notification_sent' => null,
        ]);

        $hosting->markNotificationSent();

        $this->assertNotNull($hosting->fresh()->last_notification_sent);
    }

    /** @test */
    public function it_includes_admin_users_in_notification_recipients()
    {
        // Create a hosting that should trigger notification
        $hosting = ServerHosting::factory()->create([
            'expiry_notification' => true,
            'notification_days_before' => 30,
            'notification_time_unit' => 'days',
            'renewal_date' => now()->addDays(30),
            'status' => 'active',
        ]);

        // Create an admin user
        $adminUser = User::factory()->create(['role' => 'admin']);

        // Mock the job to test the getUsersToNotify method
        $job = new \Modules\ServerManager\Jobs\CheckExpiringHostingsJob();
        $reflection = new \ReflectionClass($job);
        $method = $reflection->getMethod('getUsersToNotify');
        $method->setAccessible(true);

        $usersToNotify = $method->invoke($job, $hosting);

        // Should include admin users
        $this->assertNotEmpty($usersToNotify);
    }
}
