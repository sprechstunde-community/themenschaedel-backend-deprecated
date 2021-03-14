<?php

namespace App\Services;

use App\Models\Episode;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ClaimManager
{
    use AuthorizesRequests;

    /**
     * Checks if an episode is already claimed by any user.
     *
     * @param Episode $episode
     *
     * @return bool
     */
    public function isClaimed(Episode $episode): bool
    {
        return $episode->claimed instanceof User;
    }

    /**
     * Checks if an episode is already claimed by any user.
     *
     * @param Episode $episode
     *
     * @return bool
     */
    public function isClaimedBy(Episode $episode, User $user): bool
    {
        return $this->isClaimed($episode) && $episode->claimed->getKey() === $user->getKey();
    }

    /**
     * Attempts to claim an episode. This will fail if user isn't authorized to do that, or if the episode is already
     * claimed.
     *
     * @param Episode $episode
     * @param User $user
     *
     * @return Episode
     * @throws AuthorizationException Either episode is already claimed or the claim policy prevents this action.
     */
    public function claim(Episode $episode, User $user): Episode
    {
        if ($this->isClaimed($episode) && !$this->isClaimedBy($episode, $user)) {
            throw new AuthorizationException('Episode is claimed by someone else');
        }

        if ($this->authorizeForUser($user, 'claim', $episode)) {
            throw new AuthorizationException('Episode cannot be claimed');
        }

        return $this->forceClaim($episode, $user);
    }

    /**
     * Claims an episode by the given user. This will overwrite any existing claims! This will also ignore any policies.
     *
     * @param Episode $episode
     * @param User $user
     *
     * @return Episode The claimed episode
     */
    public function forceClaim(Episode $episode, User $user): Episode
    {
        $episode->claimed = $user;
        $episode->save();

        return $episode;
    }

    /**
     * Attempt to remove the claim from the given episode. This will fail if the claim was done by another user or if
     * the user isn't authorized to do that.
     *
     * @param Episode $episode
     * @param User $user
     *
     * @return Episode
     * @throws AuthorizationException Either episode is claimed by someone else or a policy prevents this action.
     */
    public function drop(Episode $episode, User $user)
    {
        if ($this->isClaimed($episode) && !$this->isClaimedBy($episode, $user)) {
            throw new AuthorizationException('Episode is claimed by someone else');
        }

        if ($this->authorizeForUser($user, 'unclaim', $episode)) {
            throw new AuthorizationException('Claim cannot be dropped');
        }

        return $this->forceDrop($episode);
    }

    /**
     * Removes any claim from the episode. This will ignore any policies or who owns the claim and just remove it!
     *
     * @param Episode $episode
     *
     * @return Episode The claim-free episode
     */
    public function forceDrop(Episode $episode): Episode
    {
        $episode->claimed = null;
        $episode->save();

        return $episode;
    }
}
