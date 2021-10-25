<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\UserResource;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Throwable;

class UserController extends AbstractApiController
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     *
     * @return JsonResource
     */
    public function index(Request $request): JsonResource
    {
        return JsonResource::collection(
            User::with([])
                ->paginate($this->getPerPageParameter($request))
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     *
     * @return JsonResource
     * @throws Throwable
     */
    public function store(Request $request): JsonResource
    {
        $model = (new User())->fill($request->all());
        $model->saveOrFail();

        return new JsonResource($model->refresh());
    }

    /**
     * Display the specified resource.
     *
     * @param User $user
     *
     *
     * @return JsonResource
     */
    public function show(User $user): JsonResource
    {
        return new UserResource($user->loadMissing(['claim', 'topics', 'subtopics']));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param User $user
     *
     *
     * @return JsonResource
     * @throws Throwable
     */
    public function update(Request $request, User $user): JsonResource
    {
        $user->fill($request->all());
        $user->saveOrFail();
        return new JsonResource($user->refresh()->loadMissing(['claim', 'topics', 'subtopics']));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param User $user
     *
     *
     * @return JsonResponse
     * @throws Exception
     */
    public function destroy(User $user): JsonResponse
    {
        return new JsonResponse($user->delete());
    }
}
