<?php

namespace App\Models;

use DateTime;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Class User
 *
 * @author Vincent Neubauer <v.neubauer@darlor.de>
 * @package App\Models
 *
 * @property string $username
 * @property string $email
 * @property string|null $name
 * @property string|null $description
 * @property string $password Hashed password
 * @property string|null $remember_token
 * @property DateTime $email_verified_at
 *
 * @property Collection|Topic[] $topics All {@see Topic}s, that this user has submitted
 * @property Collection|Subtopic[] $subtopics All {@see Subtopic}s, that this user has submitted
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username',
        'email',
        'name',
        'description',
        'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getRouteKeyName()
    {
        return 'username';
    }

    public function claim(): HasOne
    {
        return $this->hasOne(Claim::class);
    }

    /**
     * All {@see Topic}s, that this user has submitted
     *
     * @return HasMany
     */
    public function topics(): HasMany
    {
        return $this->hasMany(Topic::class);
    }

    /**
     * All {@see Subtopic}s, that this user has submitted
     *
     * @return HasMany
     */
    public function subtopics(): HasMany
    {
        return $this->hasMany(Subtopic::class);
    }
}
