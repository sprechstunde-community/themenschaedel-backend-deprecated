<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;

/**
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
 *
 * @OA\Schema(required={"username"}, {
 *     @OA\Property(property="username", type="string"),
 *     @OA\Property(property="name", type="string"),
 *     @OA\Property(property="description", type="string"),
 * })
 *
 * @author Vincent Neubauer <v.neubauer@darlor.de>
 */
class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;

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
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_moderator' => 'boolean',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'profile_photo_url',
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

    /** Check if user has moderator capabilities */
    public function isModerator(): bool
    {
        return (bool)$this->getAttribute('is_moderator');
    }
}
