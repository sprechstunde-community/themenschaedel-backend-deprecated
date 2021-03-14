<?php

namespace App\Policies;

use App\Models\Flag;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class FlagPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return Response::deny('Only the system is able to perform this action');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Flag  $flag
     * @return mixed
     */
    public function view(User $user, Flag $flag)
    {
        return $flag->user->getKey() === $user->getKey();
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Flag  $flag
     * @return mixed
     */
    public function update(User $user, Flag $flag)
    {
        return $flag->user->getKey() === $user->getKey();
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Flag  $flag
     * @return mixed
     */
    public function delete(User $user, Flag $flag)
    {
        return $flag->user->getKey() === $user->getKey();
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Flag  $flag
     * @return mixed
     */
    public function restore(User $user, Flag $flag)
    {
        return Response::deny('Only the system is able to perform this action');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Flag  $flag
     * @return mixed
     */
    public function forceDelete(User $user, Flag $flag)
    {
        return Response::deny('Only the system is able to perform this action');
    }
}
