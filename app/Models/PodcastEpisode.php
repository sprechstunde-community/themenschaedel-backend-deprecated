<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class PodcastEpisode
 *
 * @author Vincent Neubauer <v.neubauer@vonmaehlen.com>
 * @package App\Models
 *
 * @property string uuid
 * @property int episode_number
 * @property string title
 * @property string|null subtitle
 * @property string|null description
 * @property string|null duration
 * @property string|null type
 * @property string|null image
 * @property boolean explicit
 * @property Carbon date_published
 */
class PodcastEpisode extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'episode_number',
        'title',
        'subtitle',
        'description',
        'duration',
        'type',
        'image',
        'explicit',
        'date_published',
    ];

    public function getRouteKeyName()
    {
        return 'uuid';
    }
}
