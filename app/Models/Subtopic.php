<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
    use HasFactory;

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
}
