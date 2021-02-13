<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A flag on an episode means, that the episode is not correctly maintained and should be redone.
 *
 * @author Vincent Neubauer <v.neubauer@darlor.de>
 * @package App\Models
 *
 * @property Episode $episode The {@see Episode}, that got flagged.
 * @property User $user The {@see User}, that submitted the flag.
 *
 * @property string|null $reason The reason, why the {@see User} flagged the episode.
 */
class Flag extends Model
{
    use HasFactory;

    protected $fillable = [
        'reason',
    ];

    /**
     * All of the relationships to be marked as updated too.
     *
     * @var array
     */
    protected $touches = [
        'user',
    ];

    protected $with = [
        'user',
    ];

    /**
     * The {@see Episode}, that got flagged for renewal.
     *
     * @return BelongsTo
     */
    public function episode(): BelongsTo
    {
        return $this->belongsTo(Episode::class);
    }

    /**
     * The {@see User}, that has submitted the flag.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
