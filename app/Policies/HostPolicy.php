<?php

namespace App\Policies;

use App\Models\Host;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class HostPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any hosts.
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
     * Determine whether the user can view the host.
     *
     * @param User|null $user
     * @param Host $host
     *
     * @return mixed
     */
    public function view(?User $user, Host $host)
    {
        return true;
    }

    /**
     * Determine whether the user can create hosts.
     *
     * @param User $user
     *
     * @return mixed
     */
    public function create(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can update the host.
     *
     * @param User $user
     * @param Host $host
     *
     * @return mixed
     */
    public function update(User $user, Host $host)
    {
        // TODO restrict to users with moderator role
        return true;
    }

    /**
     * Determine whether the user can delete the host.
     *
     * @param User $user
     * @param Host $host
     *
     * @return mixed
     */
    public function delete(User $user, Host $host)
    {
        // TODO restrict to users with moderator role
        return false;
    }

    /**
     * Determine whether the user can restore the host.
     *
     * @param User $user
     * @param Host $host
     *
     * @return mixed
     */
    public function restore(User $user, Host $host)
    {
        return Response::deny('Only the system is able to perform this action');
    }

    /**
     * Determine whether the user can permanently delete the host.
     *
     * @param User $user
     * @param Host $host
     *
     * @return mixed
     */
    public function forceDelete(User $user, Host $host)
    {
        return Response::deny('Only the system is able to perform this action');
    }
}
