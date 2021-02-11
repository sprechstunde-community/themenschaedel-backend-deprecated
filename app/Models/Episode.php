<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

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
 * @property Carbon date_published
 *
 * @property Collection|Topic[] $topics All topics discussed in this episode.
 * @property Collection|Subtopic[] $subtopics All subtopics attached to this episode.
 */
class Episode extends Model
{
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
        'date_published',
    ];

    public function getRouteKeyName()
    {
        return 'guid';
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
     * All sub-topics attached to this episode's topics.s
     *
     * @return HasManyThrough
     */
    public function subtopics(): HasManyThrough
    {
        return $this->hasManyThrough(Subtopic::class, Topic::class);
    }
}
