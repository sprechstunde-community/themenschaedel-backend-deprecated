<?php

namespace App\Models;

use DateTime;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * An episode of the podcast.
 *
 * @author Vincent Neubauer <v.neubauer@darlor.de>
 * @package App\Models
 *
 * @property string guid
 * @property int episode_number
 * @property string title
 * @property string|null subtitle
 * @property string|null description
 * @property string|null image
 * @property string|null media_file
 * @property string|null duration
 * @property string|null type
 * @property boolean explicit
 * @property DateTime published_at
 *
 * @property Claim|null $claimed The active {@see Claim} resource indicating, that this episode is currently claimed.
 * @property Collection|Flag[] $flags All flags, that this episode has.
 * @property Collection|Topic[] $topics All topics discussed in this episode.
 * @property Collection|Subtopic[] $subtopics All subtopics attached to this episode.
 * @property Collection|Vote[] $votes All votes attached to this episode.
 */
class Episode extends Model
{
    use HasFactory;

    protected $perPage = 50;

    protected $fillable = [
        'guid',
        'episode_number',
        'title',
        'subtitle',
        'description',
        'image',
        'media_file',
        'duration',
        'type',
        'explicit',
        'published_at',
    ];

    public function getRouteKeyName()
    {
        return 'guid';
    }

    /**
     * List of all {@see Claim}s, that got issued for this episode.
     *
     * @return HasOne
     */
    public function claimed(): HasOne
    {
        return $this->hasOne(Claim::class);
    }

    /**
     * List of all {@see Flag}s, that this episode got.
     *
     * @return HasMany
     */
    public function flags(): HasMany
    {
        return $this->hasMany(Flag::class);
    }

    /**
     * All topics discussed in this episode.
     *
     * @return HasMany
     */
    public function topics(): HasMany
    {
        return $this->hasMany(Topic::class);
    }

    /**
     * All sub-topics attached to this episode's topics
     *
     * @return HasManyThrough
     */
    public function subtopics(): HasManyThrough
    {
        return $this->hasManyThrough(Subtopic::class, Topic::class);
    }

    /**
     * List of all up and down {@see Vote}s, that this episode got
     *
     * @return HasMany
     */
    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }
}
