<?php

namespace Tests\Traits\AssertableJson;

use App\Models\Episode;
use App\Models\Flag;
use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;

/**
 * Reusable Trait for ensuring that the JSON represents a specific model instance.
 *
 * @author Vincent Neubauer <v.neubauer@vonmaehlen.com>
 */
trait AssertsJsonForModel
{
    /**
     *  Ensure that the JSON represents a {@see Episode} instance.
     *
     * @param AssertableJson $json
     *
     * @return AssertableJson
     */
    public static function assertJsonIsEpisode(AssertableJson $json): AssertableJson
    {
        return $json
            ->whereAllType([
                'id' => 'integer',
                'uuid' => 'string',
                'episode_number' => 'integer',
                'title' => 'string',
                'subtitle' => 'nullable|string',
                'description' => 'nullable|string',
                'image' => 'nullable|string',
                'duration' => 'integer',
                'claimed' => 'boolean',
                'explicit' => 'boolean',
                'upvotes' => 'integer',
                'downvotes' => 'integer',
                'created_at' => 'string',
                'updated_at' => 'string',
                'published_at' => 'string',
            ])
            ->missingAll([
                'deleted_at',
            ])
            ->has('hosts', fn($json) => self::assertJsonIsHost($json))
            ->has('topics', fn($json) => self::assertJsonIsTopic($json))
            ->etc();
    }

    /**
     *  Ensure that the JSON represents a {@see Flag} instance.
     *
     * @param AssertableJson $json
     *
     * @return AssertableJson
     */
    public static function assertJsonIsFlag(AssertableJson $json): AssertableJson
    {
        return $json
            ->whereAllType([
                'id' => 'integer',
                'episode_id' => 'integer',
                'user_id' => 'integer',
                'reason' => 'string',
                'created_at' => 'string',
                'updated_at' => 'string',
            ])
            ->missingAll([
                'deleted_at',
            ])
            ->etc();
    }

    public static function assertJsonIsHost(AssertableJson $json): AssertableJson
    {
        return $json
            ->whereAllType([
                'id' => 'integer',
                'name' => 'string',
                'description' => 'string',
                'created_at' => 'string',
                'updated_at' => 'string',
            ])
            ->missing('deleted_at')
            ->etc();
    }

    public static function assertJsonIsSubtopic(AssertableJson $json): AssertableJson
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

    public static function assertJsonIsTopic(AssertableJson $json): AssertableJson
    {
        return $json
            ->whereAllType([
                'id' => 'integer',
                'episode_id' => 'integer',
                'user_id' => 'integer',
                'name' => 'string',
                'start' => 'integer',
                'end' => 'integer',
                'ad' => 'boolean',
                'community_contribution' => 'boolean',
                'created_at' => 'string',
                'updated_at' => 'string',
                'subtopics' => 'array'
            ])
            ->missing('deleted_at')
            ->etc();
    }

    /**
     *  Ensure that the JSON represents a {@see User} instance.
     *
     * @param AssertableJson $json
     *
     * @return AssertableJson
     */
    public static function assertJsonIsUser(AssertableJson $json): AssertableJson
    {
        return $json
            ->whereAllType([
                'id' => 'integer',
                'username' => 'string',
                'description' => 'string',
                'created_at' => 'string',
                'updated_at' => 'string',
            ])
            ->missingAll([
                'email',
                'email_verified_at',
                'deleted_at',
            ])
            ->etc();
    }
}
