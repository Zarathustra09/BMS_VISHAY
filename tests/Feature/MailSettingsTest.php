<?php

namespace Tests\Feature;

use App\Interfaces\SettingInterface;
use App\Models\User;
use App\Services\MailSettings;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class MailSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    private function admin(): User
    {
        return tap(User::factory()->create())->assignRole('admin');
    }

    /** @return array<string, mixed> */
    private function settingsPayload(array $overrides = []): array
    {
        return array_merge([
            'company_name' => 'LogXense Enterprise',
            'alert_emails' => '',
            'offline_timeout_minutes' => 10,
            'preventive_lead_days' => 7,
            'log_retention_days' => 365,
            'mail_host' => 'smtp.example.com',
            'mail_port' => 587,
            'mail_username' => 'alerts@example.com',
            'mail_password' => 's3cret',
            'mail_encryption' => 'tls',
            'mail_from_address' => 'alerts@example.com',
            'mail_from_name' => 'Plant Alerts',
            'app_url' => 'http://192.168.1.50/',
        ], $overrides);
    }

    public function test_saving_stores_the_password_encrypted_and_never_shows_it_again(): void
    {
        $this->actingAs($this->admin())
            ->put(route('settings.update'), $this->settingsPayload())
            ->assertRedirect(route('settings.index'));

        $stored = app(SettingInterface::class)->get('mail_password');

        $this->assertNotSame('s3cret', $stored);
        $this->assertSame('s3cret', Crypt::decryptString($stored));
        $this->assertSame('http://192.168.1.50', app(SettingInterface::class)->get('app_url'));

        $this->actingAs($this->admin())->get(route('settings.index'))
            ->assertOk()
            ->assertSee('smtp.example.com')
            ->assertDontSee('s3cret')
            ->assertDontSee($stored);
    }

    public function test_a_blank_password_keeps_the_saved_one(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->put(route('settings.update'), $this->settingsPayload());

        $this->actingAs($admin)->put(route('settings.update'), $this->settingsPayload(['mail_password' => '', 'mail_port' => 465]));

        $this->assertSame('s3cret', Crypt::decryptString(app(SettingInterface::class)->get('mail_password')));
        $this->assertSame(465, (int) app(SettingInterface::class)->get('mail_port'));
    }

    public function test_apply_puts_the_saved_smtp_details_into_the_mail_config(): void
    {
        $this->actingAs($this->admin())->put(route('settings.update'), $this->settingsPayload(['mail_encryption' => 'ssl', 'mail_port' => 465]));

        app(MailSettings::class)->apply();

        $this->assertSame('smtp', config('mail.default'));
        $this->assertSame('smtp.example.com', config('mail.mailers.smtp.host'));
        $this->assertSame(465, config('mail.mailers.smtp.port'));
        $this->assertSame('alerts@example.com', config('mail.mailers.smtp.username'));
        $this->assertSame('s3cret', config('mail.mailers.smtp.password'));
        $this->assertSame('smtps', config('mail.mailers.smtp.scheme'));
        $this->assertSame('alerts@example.com', config('mail.from.address'));
        $this->assertSame('Plant Alerts', config('mail.from.name'));
    }

    public function test_with_no_host_saved_the_environment_mailer_is_left_alone(): void
    {
        config(['mail.default' => 'log']);

        app(MailSettings::class)->apply();

        $this->assertSame('log', config('mail.default'));
    }

    public function test_the_test_email_goes_to_the_signed_in_user(): void
    {
        config(['mail.default' => 'array']);
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('settings.test-email'))
            ->assertRedirect(route('settings.index'))
            ->assertSessionHas('success');

        $messages = app('mail.manager')->mailer('array')->getSymfonyTransport()->messages();

        $this->assertCount(1, $messages);
        $this->assertSame($admin->email, $messages->first()->getEnvelope()->getRecipients()[0]->getAddress());
    }

    public function test_a_failing_test_email_shows_the_servers_error(): void
    {
        Mail::shouldReceive('purge')->andReturnNull();
        Mail::shouldReceive('raw')->andThrow(new \RuntimeException('Connection refused'));

        $this->actingAs($this->admin())
            ->post(route('settings.test-email'))
            ->assertSessionHas('error', fn ($message) => str_contains($message, 'Connection refused'));
    }

    public function test_only_someone_with_settings_update_can_send_a_test_email(): void
    {
        $viewer = User::factory()->create();
        $viewer->assignRole('viewer');

        $this->actingAs($viewer)->post(route('settings.test-email'))->assertForbidden();
    }
}
