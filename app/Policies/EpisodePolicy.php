<?php

namespace App\Policies;

use App\Models\Claim;
use App\Models\Episode;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class EpisodePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param User|null $user
     *
     * @return mixed
     */
    public function viewAny(?User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User|null $user
     * @param Episode $episode
     *
     * @return mixed
     */
    public function view(?User $user, Episode $episode)
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param User $user
     *
     * @return mixed
     */
    public function create(User $user)
    {
        return Response::deny('Only the system is able to perform this action');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param Episode $episode
     *
     * @return mixed
     */
    public function update(User $user, Episode $episode)
    {
        if (!$episode->claimed instanceof Claim) {
            return Response::deny('Must claim episode first');
        }

        return ($episode->claimed instanceof Claim && $episode->claimed->user->getKey() === $user->getKey())
            ? Response::allow()
            : Response::deny('Already claimed by other person');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param Episode $episode
     *
     * @return mixed
     */
    public function delete(User $user, Episode $episode)
    {
        return Response::deny('Only the system is able to perform this action');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param Episode $episode
     *
     * @return mixed
     */
    public function restore(User $user, Episode $episode)
    {
        return Response::deny('Only the system is able to perform this action');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param Episode $episode
     *
     * @return mixed
     */
    public function forceDelete(User $user, Episode $episode)
    {
        return Response::deny('Only the system is able to perform this action');
    }

    /**
     * Determine whether the user can claim the model.
     *
     * @param User $user
     * @param Episode $episode
     *
     * @return mixed
     */
    public function claim(User $user, Episode $episode)
    {
        // can only be claimed, if no one else has claimed it yet and the user has no other claims
        return is_null($episode->claimed) && is_null($user->claim()->first());
    }

    /**
     * Determine whether the user can claim the model.
     *
     * @param User $user
     * @param Episode $episode
     *
     * @return mixed
     */
    public function unclaim(User $user, Episode $episode)
    {
        // only the user claiming the episode is able to release the claim
        return is_null($episode->claimed) || $episode->claimed->user->getKey() === $user->getKey();
    }

    /**
     * Determine whether the user can vote for the episode
     *
     * @param User $user
     * @param Episode $episode
     *
     * @return bool
     */
    public function vote(User $user, Episode $episode): bool
    {
        return true; // all logged in users are able to vote
    }
}
