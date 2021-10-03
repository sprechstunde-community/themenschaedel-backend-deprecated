<?php

namespace Tests\Feature\Api;

use App\Models\Episode;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VoteTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Episode $episode;

    public function test_must_authenticate_to_vote()
    {
        $response = self::postJson(route('api.episodes.vote', ['episode' => $this->episode]));

        $response->assertUnauthorized();
        self::assertEmpty(Vote::all(), 'Failed asserting that vote was not created');
    }

    public function test_can_vote_up()
    {
        $token = $this->user->createToken('testsuite')->plainTextToken;

        $response = self::withToken($token)->postJson(route('api.episodes.vote', ['episode' => $this->episode]), [
            'direction' => 1,
        ]);

        $response->assertCreated()->assertJson(['data' => ['positive' => true]]);
        self::assertTrue(Vote::first()->positive, 'Failed asserting that vote was created');
    }

    public function test_can_vote_down()
    {
        $token = $this->user->createToken('testsuite')->plainTextToken;

        $response = self::withToken($token)->postJson(route('api.episodes.vote', ['episode' => $this->episode]), [
            'direction' => -1,
        ]);

        $response->assertCreated()->assertJson(['data' => ['positive' => false]]);
        self::assertFalse(Vote::first()->positive, 'Failed asserting that vote was created');
    }

    public function test_can_update_vote()
    {
        $token = $this->user->createToken('testsuite')->plainTextToken;

        /** @var Vote $vote */
        $vote = $this->episode->votes()->newModelInstance(['positive' => true]);
        $vote->episode()->associate($this->episode);
        $vote->user()->associate($this->user);
        $vote->save();

        $response = self::withToken($token)->postJson(route('api.episodes.vote', ['episode' => $this->episode]), [
            'direction' => -1,
        ]);

        $response->assertStatus(200);
        self::assertFalse(Vote::first()->positive, 'Failed to assert vote was updated');
    }

    public function test_can_remove_vote()
    {
        $token = $this->user->createToken('testsuite')->plainTextToken;

        /** @var Vote $vote */
        $vote = $this->episode->votes()->newModelInstance(['positive' => true]);
        $vote->episode()->associate($this->episode);
        $vote->user()->associate($this->user);
        $vote->save();

        $response = self::withToken($token)->postJson(route('api.episodes.vote', ['episode' => $this->episode]), [
            'direction' => 0,
        ]);

        $response->assertStatus(200);
        self::assertEmpty(Vote::all(), 'Failed to assert vote was removed');
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->episode = Episode::factory()->create();
        $this->user = User::factory()->create();
    }
}
