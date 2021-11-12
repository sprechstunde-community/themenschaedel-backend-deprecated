<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Flag
 *
 * A flag on an episode means, that the episode is not correctly maintained and should be re-done.
 *
 * @OA\Schema(
 *     schema="Flag",
 *     required={"episode_id", "user_id", "reason"},
 *     @OA\Property(property="episode_id", type="integer", example=1, readOnly=true),
 *     @OA\Property(property="user_id", type="integer", example=1, readOnly=true),
 *     @OA\Property(property="reason", type="string", example="Lorem ipsum dolor sit amet...",
 *         description="Summary why this episode should be updated."),
 * )
 *
 * @OA\Schema(schema="FlagCollection", type="array", @OA\Items(ref="#/components/schemas/Flag"))
 *
 * @property Episode $episode The {@see Episode}, that got flagged.
 * @property User $user The {@see User}, that submitted the flag.
 *
 * @property string|null $reason The reason, why the {@see User} flagged the episode.
 *
 * @author Vincent Neubauer <v.neubauer@darlor.de>
 */
class Flag extends Model
{
    use HasFactory;

    protected $casts = [
        'episode_id' => 'integer',
        'user_id' => 'integer',
    ];

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
