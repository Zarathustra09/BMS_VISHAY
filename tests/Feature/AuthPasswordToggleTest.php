<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthPasswordToggleTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_login_password_field_has_a_show_hide_toggle(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('data-password-toggle="password"', false)
            ->assertSee('aria-label="Show password"', false);
    }

    public function test_the_reset_form_toggles_both_password_fields(): void
    {
        $response = $this->get(route('password.reset', ['token' => 'reset-token', 'email' => 'user@example.com']));

        $response->assertOk()
            ->assertSee('data-password-toggle="password"', false)
            ->assertSee('data-password-toggle="password-confirm"', false);
    }

    public function test_the_toggle_script_is_emitted_once_per_page(): void
    {
        $html = $this->get(route('password.reset', ['token' => 'reset-token', 'email' => 'user@example.com']))
            ->assertOk()
            ->getContent();

        $this->assertSame(1, substr_count($html, "document.addEventListener('click'"));
    }

    public function test_the_confirm_form_has_a_show_hide_toggle(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('password.confirm'))
            ->assertOk()
            ->assertSee('data-password-toggle="password"', false);
    }
}
