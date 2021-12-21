<?php

namespace Tests\Feature\Api;

use App\Models\Episode;
use App\Models\Host;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;
use Tests\Traits\AssertableJson\AssertsJsonForModel;

class HostTest extends TestCase
{
    use AssertsJsonForModel;
    use RefreshDatabase;

    private User $user;
    private Episode $episode;
    private Host $host;
    private string $token;

    public function test_listing_resources()
    {
        Host::factory()->create([], $this->user);

        $response = self::getJson(route('api.hosts.index'));

        $response
            ->assertStatus(200)
            ->assertJson(fn(AssertableJson $json) => $json
                ->has('data', 1, fn($json) => $this->assertJsonIsHost($json))
                ->etc()
            );
    }

    public function test_showing_resource()
    {
        $resource = Host::factory()->create([], $this->user);

        $response = self::getJson(route('api.hosts.show', ['host' => $resource]));

        $response
            ->assertStatus(200)
            ->assertJson(fn(AssertableJson $json) => $json
                ->has('data', fn($json) => $this->assertJsonIsHost($json)));
    }

    public function test_creating_resource_requires_authentication()
    {
        $resource = Host::factory()->makeOne()->toArray();

        $response = self::postJson(route('api.hosts.store', ['episode' => $this->episode]), $resource);

        $response->assertUnauthorized();
        self::assertCount(0, Host::all(), 'Failed asserting that resource was not created');
    }

    /** @depends test_creating_resource_requires_authentication */
    public function test_creating_resource()
    {
        $token = $this->token;
        $resource = Host::factory()->makeOne();

        self::assertEmpty(Host::all(), 'Failed asserting that test environment has no topics generated.');

        $response = self::withToken($token)
            ->postJson(route('api.hosts.store', ['episode' => $this->episode]), $resource->toArray());

        $response->assertCreated();
        self::assertCount(1, Host::all(), 'Failed asserting that resource was created');
    }

    public function test_updating_resource_requires_authentication()
    {
        $resource = Host::factory()->create([], $this->user);

        $response = self::putJson(route('api.hosts.update', ['host' => $resource]), [
            'name' => 'Updated name',
        ]);

        $response->assertUnauthorized();
        self::assertSame($resource, $resource->refresh(), 'Failed asserting that resource was not updated');
    }

    /** @depends test_updating_resource_requires_authentication */
    public function test_updating_resource_is_forbidden()
    {
        $resource = Host::factory()->create([], $this->user);
        $token = $this->token;

        $response = self::withToken($token)->putJson(route('api.hosts.update', ['host' => $resource]), [
            'name' => 'Updated name',
        ]);

        $response->assertForbidden();
        self::assertSame($resource, $resource->refresh(), 'Failed asserting that resource was not updated');
    }

    public function test_updating_resource_by_moderator()
    {
        [$user, $token] = $this->createModerator();
        $resource = Host::factory()->create([], $user);

        $response = self::withToken($token)->putJson(route('api.hosts.update', ['host' => $resource]), [
            'name' => 'Updated name',
        ]);

        $response->assertOk();
        self::assertEquals('Updated name', $resource->refresh()->getAttribute('name'),
            'Failed asserting that host was updated');
    }

    public function test_deleting_resource_requires_authentication()
    {
        $resource = Host::factory()->create([], $this->user);

        $response = self::deleteJson(route('api.hosts.destroy', ['host' => $resource]));

        $response->assertUnauthorized();
        self::assertCount(1, Host::all(), 'Failed asserting that resource was not deleted');
    }

    /** @depends test_deleting_resource_requires_authentication */
    public function test_deleting_resource_is_forbidden()
    {
        $resource = Host::factory()->create([], $this->user);
        $token = $this->token;

        $response = self::withToken($token)->deleteJson(route('api.hosts.destroy', ['host' => $resource]));

        $response->assertForbidden();
        self::assertCount(1, Host::all(), 'Failed asserting that resource was not deleted');
    }

    public function test_deleting_resource_by_moderator()
    {
        [$user, $token] = $this->createModerator();
        $resource = Host::factory()->create([], $user);

        $response = self::withToken($token)->deleteJson(route('api.hosts.destroy', ['host' => $resource]));

        $response->assertOk();
        self::assertEmpty(Host::all(), 'Failed asserting that host was deleted');
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->episode = Episode::factory()->create();
        [$this->user, $this->token] = $this->createContributor();
    }

    protected function createContributor(): array {
        $user = User::factory()->contributor()->create();
        $token = $user->createToken('testsuite')->plainTextToken;

        return [$user, $token];
    }

    protected function createModerator(): array {
        $user = User::factory()->moderator()->create();
        $token = $user->createToken('testsuite')->plainTextToken;

        return [$user, $token];
    }
}
