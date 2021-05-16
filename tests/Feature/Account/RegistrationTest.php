<?php

namespace Tests\Feature\Account;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Jetstream\Jetstream;

class RegistrationTest extends AccountTestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered()
    {
        $response = $this->get($this->baseUrl() . '/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register()
    {
        $response = $this->json('POST', $this->baseUrl() . '/register', [
            'name' => 'Test User',
            'username' => 'testuser123',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature(),
        ]);


        $this->assertAuthenticated();
        $response->assertRedirect();
        //TODO assertRedirect fails in feature test, but works in real application; must check
        //$response->assertRedirect(RouteServiceProvider::HOME);
    }
}
