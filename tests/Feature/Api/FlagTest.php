<?php

namespace Tests\Feature\Api;

use App\Models\Episode;
use App\Models\Flag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\AssertableJson\AssertsJsonForModel;

class FlagTest extends TestCase
{
    use AssertsJsonForModel;
    use RefreshDatabase;

    private User $user;
    private Episode $episode;
    private string $token;

    public function test_must_authenticate_to_flag()
    {
        $response = self::postJson(route('api.episodes.flags.store', ['episode' => $this->episode]));

        $response->assertUnauthorized();
        self::assertEmpty(Flag::all(), 'Failed asserting that claim was not created');
    }

    /** @depends test_must_authenticate_to_flag */
    public function test_can_flag()
    {
        $response = self::withToken($this->token)
            ->postJson(route('api.episodes.flags.store', ['episode' => $this->episode]), [
                'reason' => 'Lorem ipsum dolor sit amed...',
            ]);

        // check response status code
        $response->assertCreated()
            // check that response includes actual entity
            ->assertJson(fn($json) => $json->has('data', fn($json) => self::assertJsonIsFlag($json))->etc());

        // check that resource was persisted
        self::assertCount(1, Flag::all(), 'Failed asserting that claim was created');
    }

    /** @depends test_must_authenticate_to_flag */
    public function test_can_remove_flag()
    {
        $flag = $this->episode->flags()->newModelInstance()
            ->forceFill([
                'episode_id' => $this->episode->getKey(),
                'user_id' => $this->user->getKey(),
                'reason' => 'Lorem ipsum dolor sit amet...',
            ])->save();

        $response = self::withToken($this->token)
            ->deleteJson(route('api.flags.destroy', ['flag' => $flag]));

        $response->assertStatus(200);
        self::assertEmpty(Flag::all(), 'Failed to assert resource was removed');
    }

    public function test_must_authenticate_to_remove_flag()
    {
        $flag = $this->episode->flags()->newModelInstance()
            ->forceFill([
                'episode_id' => $this->episode->getKey(),
                'user_id' => $this->user->getKey(),
                'reason' => 'Lorem ipsum dolor sit amet...',
            ])->save();

        $response = self::deleteJson(route('api.flags.destroy', ['flag' => $flag]));

        $response->assertUnauthorized();
        self::assertCount(1, Flag::all(), 'Failed to assert resource was not removed');
    }

    /** @depends test_must_authenticate_to_remove_flag */
    public function test_cannot_remove_other_users_flags()
    {
        $flag = $this->episode->flags()->newModelInstance()
            ->forceFill([
                'episode_id' => $this->episode->getKey(),
                'user_id' => User::factory()->create()->getKey(),
                'reason' => 'Lorem ipsum dolor sit amet...',
            ])->save();

        $response = self::withToken($this->token)->deleteJson(route('api.flags.destroy', ['flag' => $flag]));

        $response->assertForbidden();
        self::assertCount(1, Flag::all(), 'Failed to assert resource was not removed');
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->episode = Episode::factory()->create();
        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('testsuite')->plainTextToken;
    }
}
