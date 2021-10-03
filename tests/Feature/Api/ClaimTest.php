<?php

namespace Tests\Feature\Api;

use App\Models\Claim;
use App\Models\Episode;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClaimTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Episode $episode;
    private string $token;

    public function test_must_authenticate_to_claim()
    {
        $response = self::postJson(route('api.episodes.claim.store', ['episode' => $this->episode]));

        $response->assertUnauthorized();
        self::assertEmpty(Claim::all(), 'Failed asserting that claim was not created');


        $response = self::postJson(route('api.episodes.claim.destroy', ['episode' => $this->episode]));

        $response->assertUnauthorized();
        self::assertEmpty(Claim::all(), 'Failed asserting that claim was not created');
    }

    public function test_can_claim()
    {
        $response = self::withToken($this->token)
            ->postJson(route('api.episodes.claim.store', ['episode' => $this->episode]));

        $response->assertCreated();
        self::assertCount(1, Claim::all(), 'Failed asserting that claim was created');
    }

    /** @depends test_can_claim */
    public function test_cannot_claim_multiple_episodes()
    {
        $episode = Episode::factory()->create();
        // set claim to another episode
        self::withToken($this->token)->postJson(route('api.episodes.claim.store', ['episode' => $episode]));

        $response = self::withToken($this->token)
            ->postJson(route('api.episodes.claim.store', ['episode' => $this->episode]));

        $response->assertForbidden();
        self::assertCount(1, Claim::all(), 'Failed asserting that claim was not created');
    }

    /** @depends test_can_claim */
    public function test_cannot_claim_episode_multiple_times()
    {
        $token = User::factory()->create()->createToken('testsuite')->plainTextToken;
        // set claim from another user
        self::withToken($token)->postJson(route('api.episodes.claim.store', ['episode' => $this->episode]));

        $response = self::withToken($this->token)
            ->postJson(route('api.episodes.claim.store', ['episode' => $this->episode]));

        $response->assertForbidden();
        self::assertCount(1, Claim::all(), 'Failed asserting that claim was not created');
    }

    public function test_can_remove_claim()
    {
        /** @var Vote $claim */
        $claim = $this->episode->claimed()->newModelInstance();
        $claim->forceFill([
            'episode_id' => $this->episode->getKey(),
            'user_id' => $this->user->getKey(),
            'claimed_at' => now(),
        ])->save();

        $response = self::withToken($this->token)
            ->deleteJson(route('api.episodes.claim.destroy', ['episode' => $this->episode]));

        $response->assertStatus(200);
        self::assertEmpty(Claim::all(), 'Failed to assert claim was removed');
    }

    public function test_cannot_remove_other_users_claim()
    {
        /** @var Vote $claim */
        $claim = $this->episode->claimed()->newModelInstance();
        $claim->forceFill([
            'episode_id' => $this->episode->getKey(),
            'user_id' => User::factory()->create()->getKey(),
            'claimed_at' => now(),
        ])->save();

        $response = self::withToken($this->token)
            ->deleteJson(route('api.episodes.claim.destroy', ['episode' => $this->episode]));

        $response->assertForbidden();
        self::assertCount(1, Claim::all(), 'Failed to assert claim was not removed');
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->episode = Episode::factory()->create();
        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('testsuite')->plainTextToken;
    }
}
