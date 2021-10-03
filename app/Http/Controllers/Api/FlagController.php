<?php

namespace App\Http\Controllers\Api;

use App\Models\Episode;
use App\Models\Flag;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Throwable;

class FlagController extends AbstractApiController
{
    use AuthorizesRequests;

    public function __construct()
    {
        $this->authorizeResource(Flag::class, 'flag');
    }

    private array $relations = [
        'episode',
        'user',
    ];

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
            Flag::with($this->relations)
                ->paginate($this->getPerPageParameter($request))
        );
    }

    /**
     * Display a listing of the resource scoped by the parent resource.
     *
     * @param Episode $episode
     * @param Request $request
     *
     * @return JsonResource
     * @throws AuthorizationException
     */
    public function indexScoped(Episode $episode, Request $request): JsonResource
    {
        $this->authorize('viewAny', Flag::class);

        return new JsonResource(
            $episode->flags()
                ->with($this->relations)
                ->paginate($this->getPerPageParameter($request))
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
        $flag = $episode->flags()->newModelInstance();
        $flag->fill($request->all());
        $flag->episode()->associate($episode);
        $flag->user()->associate($request->user());
        $flag->save();

        return new JsonResource($flag->refresh()->loadMissing($this->relations));
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
        return new JsonResource($flag->loadMissing($this->relations));
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
        $flag->fill($request->all());
        $flag->saveOrFail();

        return new JsonResource($flag->refresh()->loadMissing($this->relations));
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
        return new JsonResponse(null, $flag->delete() ? 200 : 500);
    }
}
