<?php

namespace App\Events;

use App\Models\Episode;
use App\Models\User;
use Illuminate\Queue\SerializesModels;

/**
 * Event to notify the system, that an episode's claim was dropped.
 *
 * @property Episode $episode The episode, that was claimed until now
 * @property User|null $user The user (if available), who had claimed the episode until now
 *
 * @author Vincent Neubauer <v.neubauer@darlor.de>
 * @package App\Events
 */
class EpisodeClaimDropped
{
    use SerializesModels;

    public Episode $episode;
    public ?User $user;

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
