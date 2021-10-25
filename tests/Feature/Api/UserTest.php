<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use InvalidArgumentException;
use Laravel\Sanctum\Contracts\HasApiTokens;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Tests\TestCase;
use Tests\Traits\AssertableJson\AssertsJsonForModel;

class UserTest extends TestCase
{
    use AssertsJsonForModel, RefreshDatabase;

    /** @var User|HasApiTokens */
    private $user;
    private string $token;

    public function test_anyone_can_get_user_by_username()
    {
        $response = self::getJson(route('api.users.show', $this->user->username));

        $response
            ->assertOk()
            ->assertJson(fn($json) => $json->has('data', fn($json) => self::assertJsonIsUser($json)));
    }

    public function test_guest_cannot_list_users()
    {
        $status = $this->statusCode(fn() => self::getJson(route('api.users.index')));

        $this->assertGreaterThanOrEqual(400, $status, "Failed asserting that guests cannot list users");
    }

    public function test_user_cannot_list_users()
    {
        $status = $this->statusCode(fn() => self::withToken($this->token)->getJson(route('api.users.index')));

        $this->assertGreaterThanOrEqual(400, $status, "Failed asserting that guests cannot list users");
    }


    public function test_guest_cannot_update_users()
    {
        $status = $this->statusCode(fn() => self::postJson(route('api.users.update', 1), [
            'description' => 'Lorem ipsum dolor sit amet...',
        ]));

        $this->assertGreaterThanOrEqual(400, $status, "Failed asserting that guests cannot list users");
    }

    public function test_user_cannot_update_users()
    {
        $status = $this->statusCode(fn() => self::withToken($this->token)->postJson(route('api.users.update', 1), [
                'description' => 'Lorem ipsum dolor sit amet...',
        ]));

        $this->assertGreaterThanOrEqual(400, $status, "Failed asserting that guests cannot list users");
    }

    public function test_guest_cannot_delete_users()
    {
        $status = $this->statusCode(fn() => self::postJson(route('api.users.delete', 1)));

        $this->assertGreaterThanOrEqual(400, $status, "Failed asserting that guests cannot list users");
    }

    public function test_user_cannot_delete_users()
    {
        $status = $this->statusCode(fn() => self::withToken($this->token)->postJson(route('api.users.delete', 1)));

        $this->assertGreaterThanOrEqual(400, $status, "Failed asserting that guests cannot list users");
    }

    protected function setUp(): void
    {
        parent::setUp();
        // Create dummy users and remember one as current user
        $this->user = User::factory()->count(5)->create()->get(3);
        $this->token = $this->user->createToken('testapp')->plainTextToken;
    }

    /**
     * Collect status code from a response and catches route not found exceptions.
     *
     * @param Response|callable $response
     *
     * @return int
     * @throws InvalidArgumentException
     */
    private function statusCode($response): int
    {
        if (is_callable($response)) {
            try {
                $response = $response();
            } catch (RouteNotFoundException $exception) {
                return 404;
            }
        }

        if ($response instanceof Response) {
            return $response->status();
        }

        throw new InvalidArgumentException(
            sprintf("Passed argument is not of type [%s] or callable returning one", Response::class));
    }
}
