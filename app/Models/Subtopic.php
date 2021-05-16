<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Scout\Searchable;

/**
 * A more granular defined sub-topic of the episode.
 *
 * @author Vincent Neubauer <v.neubauer@darlor.de>
 * @package App\Models
 *
 * @property string $name The name of the subtopic
 * @property Topic $topic The {@see Topic}, that this is a part of
 * @property User $user The {@see User}, that has submitted this subtopic
 */
class Subtopic extends Model
{
    use HasFactory, Searchable;

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
