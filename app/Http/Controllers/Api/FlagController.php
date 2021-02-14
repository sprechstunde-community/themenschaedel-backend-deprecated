<?php

namespace App\Http\Controllers\Api;

use App\Models\Episode;
use App\Models\Flag;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Throwable;

class FlagController extends AbstractApiController
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
        return new JsonResource(
            Flag::paginated($this->getPerPageParameter($request))
        );
    }

    /**
     * Display a listing of the resource scoped by the parent resource.
     *
     * @param Episode $episode
     * @param Request $request
     *
     * @return JsonResource
     */
    public function indexScoped(Episode $episode, Request $request): JsonResource
    {
        return new JsonResource(
            Flag::where($episode->getKeyName(), $episode->getKey())
                ->paginated($this->getPerPageParameter($request))
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Episode $episode
     * @param Request $request
     *
     * @return JsonResource
     */
    public function store(Episode $episode, Request $request): JsonResource
    {
        /** @var Flag $flag */
        $flag = $episode->flags()->create();
        $flag->fill($request->all());
        $flag->push();

        return new JsonResource($flag->refresh());
    }

    /**
     * Display the specified resource.
     *
     * @param Flag $flag
     *
     * @return JsonResource
     */
    public function show(Flag $flag): JsonResource
    {
        return new JsonResource($flag);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Flag $flag
     *
     * @return JsonResource
     * @throws Throwable
     */
    public function update(Request $request, Flag $flag): JsonResource
    {
        // TODO add check, that user is allowed to modify flag

        $flag->fill($request->all());
        $flag->saveOrFail();

        return new JsonResource($flag);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Flag $flag
     *
     * @return JsonResponse
     * @throws Exception
     */
    public function destroy(Flag $flag): JsonResponse
    {
        // TODO add check, that user is allowed to delete the flag
        return new JsonResponse(null, $flag->delete() ? 200 : 500);
    }
}
