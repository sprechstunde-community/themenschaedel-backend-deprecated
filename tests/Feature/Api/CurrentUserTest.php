<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Contracts\HasApiTokens;
use Tests\TestCase;
use Tests\Traits\AssertableJson\AssertsJsonForModel;

class CurrentUserTest extends TestCase
{
    use AssertsJsonForModel, RefreshDatabase;

    public function test_me_endpoint_requires_authentication()
    {
        $response = self::getJson(route('api.user.me'));

        $response->assertUnauthorized();
    }

    public function test_me_endpoint_contains_current_user_resource()
    {
        $users = User::factory()->count(10)->create();
        /** @var User|HasApiTokens $user */
        $user = $users->get(3);

        $response = self::withToken($user->createToken('testapp')->plainTextToken)->getJson(route('api.user.me'));

        $response->assertOk();
        $response->assertJson(fn($json) => $json->has('data', fn($json) => self::assertJsonIsUser($json)));
        self::assertEquals($user->username, $response->json('data.username'),
            "Failed asserting that resource is the currently authenticated user");
    }

    public function setUp(): void
    {
        parent::setUp();
    }
}
