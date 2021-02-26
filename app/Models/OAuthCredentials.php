<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OAuthCredentials extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider',
        'provider_id',
        'token',
        'refresh_token',
        'expires_at',
        'user_information',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
