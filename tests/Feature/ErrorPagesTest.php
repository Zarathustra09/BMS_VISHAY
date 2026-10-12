<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class ErrorPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_opening_a_forbidden_page_directly_shows_the_branded_403_screen(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $user = User::factory()->create();
        $user->assignRole(Role::create(['name' => 'machines-only'])->givePermissionTo('machines.view'));

        $this->actingAs($user)->get(route('users.index'))
            ->assertForbidden()
            ->assertSee('Access denied')
            ->assertSee('Go to dashboard')
            // not Laravel's stock screen, and never the internal exception text
            ->assertDontSee('FORBIDDEN')
            ->assertDontSee('User does not have the right permissions');
    }

    public function test_an_unknown_url_shows_the_branded_404_screen(): void
    {
        $this->get('/this-page-does-not-exist')
            ->assertNotFound()
            ->assertSee('Page not found')
            ->assertSee('Go to sign in');
    }

    public function test_json_requests_still_get_json_errors(): void
    {
        $this->getJson('/this-page-does-not-exist')->assertNotFound()->assertJsonStructure(['message']);
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function errorViews(): array
    {
        return [
            '401' => ['401', 'Please sign in'],
            '403' => ['403', 'Access denied'],
            '404' => ['404', 'Page not found'],
            '419' => ['419', 'Session expired'],
            '429' => ['429', 'Too many requests'],
            '500' => ['500', 'Something went wrong'],
            '503' => ['503', 'Under maintenance'],
        ];
    }

    /**
     * @dataProvider errorViews
     */
    public function test_each_error_view_renders_standalone(string $code, string $title): void
    {
        $html = view("errors.{$code}", ['exception' => new HttpException((int) $code)])->render();

        $this->assertStringContainsString($title, $html);
        $this->assertStringContainsString(">{$code}</p>", $html);
        $this->assertStringContainsString('Go back', $html);
    }

    public function test_the_generic_fallbacks_use_the_exceptions_status_code(): void
    {
        $this->assertStringContainsString('>418</p>', view('errors.4xx', ['exception' => new HttpException(418)])->render());
        $this->assertStringContainsString('>502</p>', view('errors.5xx', ['exception' => new HttpException(502)])->render());
    }
}
