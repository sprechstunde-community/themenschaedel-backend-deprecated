<?php

namespace App\Events;

use App\Models\Episode;
use App\Models\User;
use Illuminate\Queue\SerializesModels;

/**
 * Event to notify the system, that an episode was claimed.
 *
 * @property Episode $episode The episode, that got claimed
 * @property User|null $user The user, who claimed the episode
 *
 * @author Vincent Neubauer <v.neubauer@darlor.de>
 * @package App\Events
 */
class EpisodeClaimed
{
    use SerializesModels;

    public Episode $episode;
    public User $user;

    /**
     * EpisodeClaimDropped constructor.
     *
     * @param Episode $episode
     * @param User $user
     */
    public function __construct(Episode $episode, User $user)
    {
        $this->episode = $episode;
        $this->user = $user;
    }

}
