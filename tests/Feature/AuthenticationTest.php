<?php

namespace Tests\Feature;

use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use Laravel\Fortify\Features;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_can_authenticate()
    {
        $user = User::factory()->create();

        $response = $this->json('POST', 'https://' . RouteServiceProvider::getAccountDomain() . '/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated('web');
        $response->assertOk();
        $response->assertJson(['two_factor' => false]);
    }

    public function test_users_with_2fa_can_authenticate()
    {
        if (!Features::canManageTwoFactorAuthentication()) {
            $this->markTestSkipped('2FA support is not enabled.');
        }

        $user = User::factory()->create();
        $this->app->make(EnableTwoFactorAuthentication::class)($user);
        $recoveryKey = json_decode(decrypt($user->two_factor_recovery_codes))[0];
        $user->save();

        $response = $this->json('POST', 'https://' . RouteServiceProvider::getAccountDomain() . '/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertOk();
        $response->assertJson(['two_factor' => true]);

        $response = $this->json('POST', 'https://' . RouteServiceProvider::getAccountDomain()
            . '/two-factor-challenge', [
            'recovery_code' => $recoveryKey,
        ]);

        $response->assertSuccessful();
        $this->assertAuthenticated('web');
    }

    public function test_users_can_not_authenticate_with()
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }
}
