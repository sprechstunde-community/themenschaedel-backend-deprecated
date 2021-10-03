<?php

namespace Tests\Feature\Api;

use App\Models\Episode;
use App\Models\Topic;
use App\Models\User;
use Faker\Generator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;
use Tests\Traits\AssertableJson\AssertsJsonForModel;

class TopicTest extends TestCase
{
    use AssertsJsonForModel;
    use RefreshDatabase;

    private User $user;
    private Episode $episode;

    public function test_it_lists_resources()
    {
        Topic::factory()->create([], $this->episode);

        $response = self::getJson(route('api.topics.index'));

        $response
            ->assertStatus(200)
            ->assertJson(fn(AssertableJson $json) => $json
                ->has('data', 1, fn($json) => $this->assertJsonIsTopic($json))
                ->etc()
            );
    }

    public function test_it_shows_resource()
    {
        $topic = Topic::factory()->create([], $this->episode);

        $response = self::getJson(route('api.topics.show', ['topic' => $topic]));

        $response
            ->assertStatus(200)
            ->assertJson(fn(AssertableJson $json) => $json
                ->has('data', fn($json) => $this->assertJsonIsTopic($json)));
    }

    public function test_creating_resource_requires_authentication()
    {
        $topic = Topic::factory()->newModel()->toArray();

        $response = self::postJson(route('api.episodes.topics.store', ['episode' => $this->episode]), $topic);

        $response->assertUnauthorized();
        self::assertCount(0, Topic::all(), 'Failed asserting that resource was not created');
    }

    /** @depends test_creating_resource_requires_authentication */
    public function test_creating_resource()
    {
        $token = $this->user->createToken('testsuite')->plainTextToken;
        $topic = Topic::factory()->makeOne();

        self::assertEmpty(Topic::all(), 'Failed asserting that test environment has no topics generated.');

        $response = self::withToken($token)
            ->postJson(route('api.episodes.topics.store', ['episode' => $this->episode]), $topic->toArray());

        $response->assertCreated();
        self::assertCount(1, Topic::all(), 'Failed asserting that resource was created');
    }

    public function test_updating_resource_requires_authentication()
    {
        $topic = Topic::factory()->create([], $this->episode);

        $response = self::putJson(route('api.topics.update', ['topic' => $topic]), [
            'name' => 'Updated topic name',
        ]);

        $response->assertUnauthorized();
        self::assertSame($topic, $topic->refresh(), 'Failed asserting that resource was not updated');
    }

    /** @depends test_updating_resource_requires_authentication */
    public function test_updating_resource()
    {
        $topic = Topic::factory()->create([], $this->episode);
        $token = $this->user->createToken('testsuite')->plainTextToken;

        $response = self::withToken($token)->putJson(route('api.topics.update', ['topic' => $topic]), [
            'name' => 'Updated topic name',
        ]);

        $response->assertStatus(200)->assertJson(['data' => ['name' => 'Updated topic name']]);
        self::assertEquals('Updated topic name', $topic->refresh()->name, 'Failed asserting that resource was updated');
    }

    /** @depends test_updating_resource_requires_authentication */
    public function test_updating_resource_fails_if_not_owner()
    {
        $topic = Topic::factory()->create([], $this->episode);
        $token = User::factory()->create()->createToken('testsuite')->plainTextToken;

        $response = self::withToken($token)->putJson(route('api.topics.update', ['topic' => $topic]), [
            'name' => 'Updated topic name',
        ]);

        $response->assertForbidden();
        self::assertSame($topic, $topic->refresh(), 'Failed asserting that resource was not updated');
    }

    public function test_deleting_resource_requires_authentication()
    {
        $topic = Topic::factory()->create([], $this->episode);

        $response = self::deleteJson(route('api.topics.destroy', ['topic' => $topic]));

        $response->assertUnauthorized();
        self::assertCount(1, Topic::all(), 'Failed asserting that resource was not deleted');
    }

    /** @depends test_deleting_resource_requires_authentication */
    public function test_deleting_resource()
    {
        $topic = Topic::factory()->create([], $this->episode);
        $token = $this->user->createToken('testsuite')->plainTextToken;

        $response = self::withToken($token)->deleteJson(route('api.topics.destroy', ['topic' => $topic]));

        $response->assertStatus(200);
        self::assertCount(0, Topic::all(), 'Failed asserting that resource was deleted');
    }

    /** @depends test_deleting_resource_requires_authentication */
    public function test_deleting_resource_fails_if_not_owner()
    {
        $topic = Topic::factory()->create([], $this->episode);
        $token = User::factory()->create()->createToken('testsuite')->plainTextToken;

        $response = self::withToken($token)->deleteJson(route('api.topics.destroy', ['topic' => $topic]));

        $response->assertStatus(403);
        self::assertCount(1, Topic::all(), 'Failed asserting that resource was not deleted');
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->faker = new Generator();

        $this->user = User::factory()->create();
        $this->episode = Episode::factory()->create([], $this->user);
    }
}
