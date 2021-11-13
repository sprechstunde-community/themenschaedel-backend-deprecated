<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Scout\Searchable;

/**
 * A single topic discussed in an episode.
 *
 * @OA\Schema(
 *     schema="Topic",
 *     required={"name"},
 *     @OA\Property(property="id", readOnly=true, type="int", example=13),
 *     @OA\Property(property="name", type="string", example="Lorem Ipsum"),
 *     @OA\Property(property="start", type="int", example=0),
 *     @OA\Property(property="end", type="int", example=780),
 *     @OA\Property(property="ad", type="bool", example=false, description="Topic is an ad / sponsor segment"),
 *     @OA\Property(property="community_contribution", type="bool", example=true),
 * )
 *
 * @OA\Schema(schema="TopicCollection", type="array", @OA\Items(ref="#/components/schemas/Topic"))
 *
 * @property string $name Name of the topic
 * @property int $start Amount of seconds into the {@see Episode}, where this topic gets discussed
 * @property int $end Amount of seconds into the {@see Episode}, where this topic's discussion ends
 * @property bool $ad Whether this topic is an ad or not
 * @property bool $community_contribution Whether this topic was suggested by the community or if the hosts themselves
 *     came up with it.
 *
 * @property Episode $episode The {@see Episode}, in which this topic gets discussed
 * @property Collection|Subtopic[] $subtopics All {@see Subtopic}s that get discussed in this section too
 * @property User $user The {@see User}, that has submitted this topic
 *
 * @author Vincent Neubauer <v.neubauer@darlor.de>
 */
class Topic extends Model
{
    use HasFactory, Searchable;

    protected $casts = [
        'episode_id' => 'integer',
        'user_id' => 'integer',
        'start' => 'integer',
        'end' => 'integer',
        'ad' => 'boolean',
        'community_contribution' => 'boolean',
    ];

    protected $fillable = [
        'name',
        'start',
        'end',
        'ad',
        'community_contribution',
    ];

    /**
     * All of the relationships to be marked as updated too.
     *
     * @var array
     */
    protected $touches = [
        'episode',
        'user',
    ];

    /**
     * The {@see Episode}, in which this topic gets discussed
     *
     * @return BelongsTo
     */
    public function episode(): BelongsTo
    {
        return $this->belongsTo(Episode::class);
    }

    /**
     * All {@see Subtopic}s that too get discussed in this section
     *
     * @return HasMany
     */
    public function subtopics(): HasMany
    {
        return $this->hasMany(Subtopic::class);
    }

    /**
     * The {@see User}, that has submitted this topic
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function toSearchableArray()
    {
        return [
            'id' => $this->getKey(),
            'episode_id' => $this->episode_id,
            'name' => $this->name,
            'ad' => $this->ad ? 'advertisement' : null,
            'community_contribution' => $this->community_contribution ? 'community contribution' : null,
        ];
    }

}
