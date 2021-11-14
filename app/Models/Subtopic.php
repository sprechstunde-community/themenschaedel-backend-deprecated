<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Scout\Searchable;

/**
 * A more granular defined sub-topic of the episode.
 *
 * @OA\Schema(
 *     schema="Subtopic",
 *     required={"name"},
 *     @OA\Property(property="id", type="integer", readOnly=true),
 *     @OA\Property(property="user_id", type="integer", readOnly=true),
 *     @OA\Property(property="topic_id", type="integer", description="Assigned topic"),
 *     @OA\Property(property="name", type="string", description="Caption of the subtopic"),
 * )
 *
 * @OA\Schema(schema="SubtopicCollection", type="array", @OA\Items(ref="#/components/schemas/Subtopic"))
 *
 * @property string $name The name of the subtopic
 * @property Topic $topic The {@see Topic}, that this is a part of
 * @property User $user The {@see User}, that has submitted this subtopic
 *
 * @author Vincent Neubauer <v.neubauer@darlor.de>
 */
class Subtopic extends Model
{
    use HasFactory, Searchable;

    protected $casts = [
        'user_id' => 'integer',
        'topic_id' => 'integer',
    ];

    protected $fillable = [
        'name',
    ];

    /**
     * All of the relationships to be marked as updated too.
     *
     * @var array
     */
    protected $touches = [
        'topic',
        'user',
    ];

    /**
     * The topic, that this is a part of.
     *
     * @return BelongsTo
     */
    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }

    /**
     * The {@see User}, that has submitted this subtopic
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
            'episode_id' => $this->topic->episode->getKey(),
            'topic_id' => $this->topic->getKey(),
            'name' => $this->name,
        ];
    }

    /**
     * Modify the query used to retrieve models when making all of the models searchable.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function makeAllSearchableUsing($query)
    {
        return $query->with('topic.episode');
    }

}
