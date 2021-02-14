<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * A host is a person talking on an podcast episode.
 *
 * @author Vincent Neubauer <v.neubauer@darlor.de>
 * @package App\Models
 *
 * @property string $name Name of the host
 * @property string|null $description A short description about the host
 * @property string|null $profile_picture Url to a profile picture
 * @property bool $main Indicator if the host is the (or one of) main host, that is present on (nearly) any
 *     {@see Episode}.
 *
 * @property Collection|Episode[] $episodes List of all {@see Episode}s, that this host was present on
 */
class Host extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'profile_picture',
        'main',
    ];

    /**
     *List of all {@see Episode}s, that this host was present on
     *
     * @return BelongsToMany
     */
    public function episodes(): BelongsToMany
    {
        return $this->belongsToMany(Episode::class);
    }
}
