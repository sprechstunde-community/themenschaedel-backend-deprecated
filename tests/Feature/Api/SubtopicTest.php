<?php

namespace Tests\Feature\Api;

use App\Models\Episode;
use App\Models\Subtopic;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class SubtopicTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Episode $episode;
    private Topic $topic;

    public function test_listing_resources()
    {
        Subtopic::factory()->create([], $this->topic);

        $response = self::getJson(route('api.subtopics.index'));

        $response
            ->assertStatus(200)
            ->assertJson(fn(AssertableJson $json) => $json
                ->has('data', 1, fn($json) => $this->assertJsonIsSubtopic($json))
                ->etc()
            );
    }

    public function test_showing_resource()
    {
        $resource = Subtopic::factory()->create([], $this->topic);

        $response = self::getJson(route('api.subtopics.show', ['subtopic' => $resource]));

        $response
            ->assertStatus(200)
            ->assertJson(fn(AssertableJson $json) => $json
                ->has('data', fn($json) => $this->assertJsonIsSubtopic($json)));
    }

    public function test_creating_resource_requires_authentication()
    {
        $resource = Subtopic::factory()->makeOne()->toArray();

        $response = self::postJson(route('api.topics.subtopics.store', ['topic' => $this->topic]), $resource);

        $response->assertUnauthorized();
        self::assertCount(0, Subtopic::all(), 'Failed asserting that resource was not created');
    }

    /** @depends test_creating_resource_requires_authentication */
    public function test_creating_resource()
    {
        $token = $this->user->createToken('testsuite')->plainTextToken;
        $resource = Subtopic::factory()->makeOne();

        self::assertEmpty(Subtopic::all(), 'Failed asserting that test environment has no topics generated.');

        $response = self::withToken($token)
            ->postJson(route('api.topics.subtopics.store', ['topic' => $this->topic]), $resource->toArray());

        $response->assertCreated();
        self::assertCount(1, Subtopic::all(), 'Failed asserting that resource was created');
    }

    public function test_updating_resource_requires_authentication()
    {
        $resource = Subtopic::factory()->create([], $this->topic);

        $response = self::putJson(route('api.subtopics.update', ['subtopic' => $resource]), [
            'name' => 'Updated name',
        ]);

        $response->assertUnauthorized();
        self::assertSame($resource, $resource->refresh(), 'Failed asserting that resource was not updated');
    }

    /** @depends test_updating_resource_requires_authentication */
    public function test_updating_resource()
    {
        $resource = Subtopic::factory()->create([], $this->topic);
        $token = $this->user->createToken('testsuite')->plainTextToken;

        $response = self::withToken($token)->putJson(route('api.subtopics.update', ['subtopic' => $resource]), [
            'name' => 'Updated name',
        ]);

        $response->assertStatus(200)->assertJson(['data' => ['name' => 'Updated name']]);
        self::assertEquals('Updated name', $resource->refresh()->name, 'Failed asserting that resource was updated');
    }

    /** @depends test_updating_resource_requires_authentication */
    public function test_updating_resource_fails_if_not_owner()
    {
        $resource = Subtopic::factory()->create([], $this->topic);
        $token = User::factory()->create()->createToken('testsuite')->plainTextToken;

        $response = self::withToken($token)->putJson(route('api.subtopics.update', ['subtopic' => $resource]), [
            'name' => 'Updated name',
        ]);

        $response->assertForbidden();
        self::assertSame($resource, $resource->refresh(), 'Failed asserting that resource was not updated');
    }

    public function test_deleting_resource_requires_authentication()
    {
        $resource = Subtopic::factory()->create([], $this->topic);

        $response = self::deleteJson(route('api.subtopics.destroy', ['subtopic' => $resource]));

        $response->assertUnauthorized();
        self::assertCount(1, Subtopic::all(), 'Failed asserting that resource was not deleted');
    }

    /** @depends test_deleting_resource_requires_authentication */
    public function test_deleting_resource()
    {
        $resource = Subtopic::factory()->create([], $this->topic);
        $token = $this->user->createToken('testsuite')->plainTextToken;

        $response = self::withToken($token)->deleteJson(route('api.subtopics.destroy', ['subtopic' => $resource]));

        $response->assertStatus(200);
        self::assertCount(0, Subtopic::all(), 'Failed asserting that resource was deleted');
    }

    /** @depends test_deleting_resource_requires_authentication */
    public function test_deleting_resource_fails_if_not_owner()
    {
        $resource = Subtopic::factory()->create([], $this->topic);
        $token = User::factory()->create()->createToken('testsuite')->plainTextToken;

        $response = self::withToken($token)->deleteJson(route('api.subtopics.destroy', ['subtopic' => $resource]));

        $response->assertForbidden();
        self::assertCount(1, Subtopic::all(), 'Failed asserting that resource was not deleted');
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->episode = Episode::factory()->create([], $this->user);
        $this->topic = Topic::factory()->create([], $this->episode);
    }

    private function assertJsonIsSubtopic(AssertableJson $json): AssertableJson
    {
        return $json
            ->whereAllType([
                'id' => 'integer',
                'user_id' => 'integer',
                'topic_id' => 'integer',
                'name' => 'string',
                'created_at' => 'string',
                'updated_at' => 'string',
            ])
            ->missing('deleted_at')
            ->etc();
    }
}
