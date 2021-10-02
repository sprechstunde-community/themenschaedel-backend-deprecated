<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    private const EMAIL = 'j.doe@example.com';
    private const USERNAME = 'j.doe';
    private const PASSWORD = 'password';

    public function test_user_can_register()
    {
        User::first()->delete(); // Remove test user, so it will not collide

        $response = $this->postJson(route('api.user.register'), [
            'application_name' => 'testsuite',
            'username' => self::USERNAME,
            'email' => self::EMAIL,
            'password' => static::PASSWORD,
        ], ['accept' => 'application/json']);

        $response
            ->assertOk()
            ->assertJson(fn(AssertableJson $json) => $json
                ->whereType('access_token', 'string')
                ->where('token_type', 'Bearer')
                ->missing('password')
                ->etc()
            );
    }

    public function test_user_can_authenticate()
    {
        $response = $this->postJson(route('api.user.login'), [
            'application_name' => 'testsuite',
            'username' => static::USERNAME,
            'password' => static::PASSWORD,
        ], ['accept' => 'application/json']);

        $response
            ->assertOk()
            ->assertJson(fn(AssertableJson $json) => $json
                ->where('token_type', 'Bearer')
                ->whereType('access_token', 'string')
                ->missing('password')
                ->etc()
            );
    }

    public function test_user_can_logout()
    {
        /** @var User $user */
        $user = User::first();
        $user->createToken('testsuite'); // should not be deleted
        $token = $user->createToken('testsuite2');

        self::assertCount(2, $user->tokens, 'Test setup is broken');

        $response = $this->withToken($token->plainTextToken)
            ->delete(route('api.user.logout'), ['accept' => 'application/json']);

        $response->assertOk();

        self::assertCount(1, $user->refresh()->tokens, 'Failed to delete only one token');
    }

    /** @depends test_user_can_logout */
    public function test_user_can_logout_everywhere()
    {
        /** @var User $user */
        $user = User::first();

        $user->createToken('testsuite1');
        $token = $user->createToken('testsuite2');
        $user->createToken('testsuite3');

        self::assertCount(3, $user->tokens, 'Test setup is broken');

        $response = $this->withToken($token->plainTextToken)
            ->delete(route('api.user.logout.everywhere'), ['accept' => 'application/json']);

        $response->assertOk();

        self::assertCount(0, $user->refresh()->tokens, 'Failed to delete all tokens');
    }

    protected function setUp(): void
    {
        parent::setUp();

        User::create([
            'username' => self::USERNAME,
            'email' => self::EMAIL,
            'password' => Hash::make(self::PASSWORD),
        ]);
    }
}
