<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BrandingTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_login_page_carries_the_product_name_and_no_old_branding(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('LogXense')
            ->assertDontSee('NextBuild')
            ->assertDontSee('NexBuild')
            ->assertDontSee('Building Management System');
    }

    public function test_the_app_shows_the_product_name_and_never_mentions_an_edition(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $admin = tap(User::factory()->create())->assignRole('admin');

        $html = $this->actingAs($admin)->get(route('home'))
            ->assertOk()
            ->assertSee('LogXense Enterprise')
            ->assertDontSee('NextBuild')
            ->assertDontSee('NexBuild')
            ->getContent();

        // "lite" is an internal packaging detail - it must never reach the client.
        $this->assertDoesNotMatchRegularExpression('/\blite\b/i', strip_tags($html));
    }

    public function test_error_pages_use_the_product_name(): void
    {
        $this->get('/no-such-page')
            ->assertNotFound()
            ->assertSee('LogXense')
            ->assertDontSee('NextBuild');
    }

    public function test_the_default_company_name_is_the_product_name(): void
    {
        $this->assertSame('LogXense Enterprise', app(\App\Interfaces\SettingInterface::class)->get('company_name'));
    }
}
