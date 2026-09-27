<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_can_update_their_name_and_email(): void
    {
        $user = User::factory()->create(['name' => 'Original Name', 'email' => 'original@example.com']);

        $response = $this->actingAs($user)->put(route('profile.update'), [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);

        $response->assertRedirect(route('profile.index'));
        $this->assertSame('Updated Name', $user->fresh()->name);
        $this->assertSame('updated@example.com', $user->fresh()->email);
    }

    public function test_updating_the_email_to_one_already_taken_is_rejected(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);
        $user = User::factory()->create(['email' => 'mine@example.com']);

        $response = $this->actingAs($user)->put(route('profile.update'), [
            'name' => $user->name,
            'email' => 'taken@example.com',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertSame('mine@example.com', $user->fresh()->email);
    }

    public function test_a_user_can_keep_their_own_email_when_resaving_the_profile(): void
    {
        $user = User::factory()->create(['email' => 'mine@example.com']);

        $response = $this->actingAs($user)->put(route('profile.update'), [
            'name' => 'A New Name',
            'email' => 'mine@example.com',
        ]);

        $response->assertRedirect(route('profile.index'));
        $response->assertSessionHasNoErrors();
        $this->assertSame('A New Name', $user->fresh()->name);
    }

    public function test_changing_password_requires_the_correct_current_password(): void
    {
        $user = User::factory()->create(['password' => bcrypt('the-real-password')]);

        $response = $this->actingAs($user)->put(route('profile.update-password'), [
            'current_password' => 'a-wrong-password',
            'password' => 'a-new-password',
            'password_confirmation' => 'a-new-password',
        ]);

        $response->assertSessionHasErrors('current_password');
        $this->assertTrue(Hash::check('the-real-password', $user->fresh()->password));
    }

    public function test_a_user_can_change_their_password_with_the_correct_current_password(): void
    {
        $user = User::factory()->create(['password' => bcrypt('the-real-password')]);

        $response = $this->actingAs($user)->put(route('profile.update-password'), [
            'current_password' => 'the-real-password',
            'password' => 'a-brand-new-password',
            'password_confirmation' => 'a-brand-new-password',
        ]);

        $response->assertRedirect(route('profile.index'));
        $this->assertTrue(Hash::check('a-brand-new-password', $user->fresh()->password));
    }
}
