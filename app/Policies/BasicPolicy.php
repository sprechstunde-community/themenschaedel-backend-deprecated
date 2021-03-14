<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;
use Illuminate\Database\Eloquent\Model;

class BasicPolicy
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
     * @param Model $model
     *
     * @return mixed
     */
    public function view(?User $user, Model $model)
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
        return true;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param Model $model
     *
     * @return mixed
     */
    public function update(User $user, Model $model)
    {
        return true;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param Model $model
     *
     * @return mixed
     */
    public function delete(User $user, Model $model)
    {
        return (property_exists($model, 'user') && $model->user instanceof User
            && $model->user->getKey() === $user->getKey()) ?: Response::deny('You do not own the resource');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param Model $model
     *
     * @return mixed
     */
    public function restore(User $user, Model $model)
    {
        return Response::deny('Only the system is able to perform this action');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param Model $model
     *
     * @return mixed
     */
    public function forceDelete(User $user, Model $model)
    {
        return Response::deny('Only the system is able to perform this action');
    }
}
