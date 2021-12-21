<?php

namespace Tests\Feature\Api;

use App\Models\Episode;
use App\Models\Host;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\AssertableJson\AssertsJsonForModel;

class EpisodeTest extends TestCase
{
    use AssertsJsonForModel;
    use RefreshDatabase;

    private User $user;
    private Episode $episode;
    private string $token;

    public function test_guest_can_list()
    {
        self::getJson(route('api.episodes.index'))
            ->assertOk()
            ->assertJson(fn($json) => $json->has('data.0', fn($json) => self::assertJsonIsEpisode($json))->etc());
    }

    public function test_guest_can_read()
    {
        self::getJson(route('api.episodes.show', [$this->episode->getKey()]))
            ->assertOk()
            ->assertJson(fn($json) => $json->has('data', fn($json) => self::assertJsonIsEpisode($json))->etc());
    }

    public function test_guest_cannot_create()
    {
        $countBefore = Episode::count();

        self::postJson(route('api.episodes.store'), Episode::factory()->makeOne()->getAttributes())
            ->assertUnauthorized();

        self::assertSame($countBefore, Episode::count(), 'Failed asserting that no episode was created.');
    }

    /** @depends test_guest_cannot_create */
    public function test_user_cannot_create()
    {
        $countBefore = Episode::count();

        self::withToken($this->token)
            ->postJson(route('api.episodes.store'), Episode::factory()->makeOne()->getAttributes())
            ->assertForbidden();

        self::assertSame($countBefore, Episode::count(), 'Failed asserting that no episode was created.');
    }

    //TODO add moderator/admin can create test case

    public function test_guest_cannot_update()
    {
        $dummy = Episode::factory()->make()->only($this->episode->getFillable());
        $reference = $this->episode->refresh()->getAttributes();

        self::patchJson(route('api.episodes.update', $this->episode), $dummy)
            ->assertUnauthorized();

        self::assertEquals($reference, $this->episode->refresh()->getAttributes(),
            'Failed asserting that episode was not updated');
    }

    /** @depends test_guest_cannot_update */
    public function test_user_cannot_update()
    {
        $dummy = Episode::factory()->make()->only($this->episode->getFillable());
        $reference = $this->episode->refresh()->getAttributes();

        self::withToken($this->token)->patchJson(route('api.episodes.update', $this->episode), $dummy)
            ->assertForbidden();

        self::assertEquals($reference, $this->episode->refresh()->getAttributes(),
            'Failed asserting that episode was not updated');
    }

    public function test_guest_cannot_publish()
    {
        // Ensure episode is not yet published
        $this->episode->published_at = null;
        $this->episode->save();

        self::patchJson(route('api.episodes.update', $this->episode), ['published_at' => now()])
            ->assertUnauthorized();

        self::assertNull($this->episode->refresh()->published_at, 'Failed asserting that episode was not published.');
    }

    /** @depends test_guest_cannot_publish */
    public function test_user_cannot_publish()
    {
        // Ensure episode is not yet published
        $this->episode->published_at = null;
        $this->episode->save();

        self::withToken($this->token)
            ->patchJson(route('api.episodes.update', $this->episode), ['published_at' => now()])
            ->assertForbidden();

        self::assertNull($this->episode->refresh()->published_at, 'Failed asserting that episode was not published.');
    }

    public function test_guest_cannot_delete()
    {
        $countBefore = Episode::count();

        self::deleteJson(route('api.episodes.destroy', $this->episode))
            ->assertUnauthorized();

        self::assertSame($countBefore, Episode::count(), 'Failed asserting that no episode was deleted.');
    }

    /** @depends test_guest_cannot_delete */
    public function test_user_cannot_delete()
    {
        $countBefore = Episode::count();

        self::withToken($this->token)
            ->deleteJson(route('api.episodes.destroy', $this->episode))
            ->assertForbidden();

        self::assertSame($countBefore, Episode::count(), 'Failed asserting that no episode was deleted.');
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('testsuite')->plainTextToken;

        $this->episode = Episode::factory()->create();
        $this->episode->hosts()->attach(Host::factory()->create()->getKey());
        Topic::factory()->create([], $this->episode);
    }
}
