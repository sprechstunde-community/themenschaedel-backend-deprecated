<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Vote
 *
 * @author Vincent Neubauer <v.neubauer@darlor.de>
 * @package App\Models
 *
 * @property bool $positive Indicator if vote is an upvote or an down vote
 *
 * @property Episode $episode The voted {@see Episode}
 * @property User $user The voting {@see User}
 */
class Vote extends Model
{
    use HasFactory;

    protected $casts = [
        'positive' => 'boolean',
    ];

    protected $fillable = [
        'positive',
    ];

    /**
     * The voted {@see Episode}
     *
     * @return BelongsTo
     */
    public function episode(): BelongsTo
    {
        return $this->belongsTo(Episode::class);
    }

    /**
     * The voting {@see User}
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
