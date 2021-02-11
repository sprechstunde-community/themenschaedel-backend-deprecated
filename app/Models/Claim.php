<?php

namespace App\Models;

use DateTime;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A claim provides write access to an {@see Episode}. Used to manage editing locks to specific {@see User}s to prevent
 * duplicate editing at the same time by different {@see User}s.
 *
 * @author Vincent Neubauer <v.neubauer@darlor.de>
 * @package App\Models
 *
 * @property DateTime $claimed_at The date and time at which this {@see Episode} was claimed
 * @property Episode $episode The claimed {@see Episode}
 * @property User $user The claiming {@see User}
 */
class Claim extends Model
{
    use HasFactory;

    protected $fillable = [
        'claimed_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'claimed_at' => 'datetime',
    ];

    /**
     * The claimed {@see Episode}
     *
     * @return BelongsTo
     */
    public function episode(): BelongsTo
    {
        return $this->belongsTo(Episode::class);
    }

    /**
     * The claiming {@see User}
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
