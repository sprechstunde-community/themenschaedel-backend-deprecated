<?php

namespace App\Http\Controllers\Api;

use App\Models\Episode;
use App\Models\Topic;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Throwable;

class TopicController extends AbstractApiController
{
    use AuthorizesRequests;

    private array $relations = [
        'subtopics',
        'user',
    ];

    public function __construct()
    {
        $this->authorizeResource(Topic::class, 'topic');
    }

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
            Topic::with($this->relations)
                ->paginate($this->getPerPageParameter($request))
        );
    }

    /**
     * Display a listing of the resource scoped by parent model.
     *
     * @param Episode $episode
     * @param Request $request
     *
     * @return JsonResource
     * @throws AuthorizationException
     */
    public function indexScoped(Episode $episode, Request $request): JsonResource
    {
        $this->authorize('viewAny', Topic::class);

        return JsonResource::collection(
            $episode
                ->topics()
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
     * @throws Throwable
     */
    public function store(Episode $episode, Request $request): JsonResource
    {
        $topic = new Topic();
        $topic->fill($request->all());
        $topic->user()->associate($request->user());
        $episode->topics()->save($topic);

        return new JsonResource($topic->refresh()->loadMissing($this->relations));
    }

    /**
     * Display the specified resource.
     *
     * @param Topic $topic
     *
     * @return JsonResource
     */
    public function show(Topic $topic): JsonResource
    {
        return new JsonResource($topic->loadMissing($this->relations));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Topic $topic
     *
     * @return JsonResource
     * @throws Throwable
     */
    public function update(Request $request, Topic $topic): JsonResource
    {
        $topic->fill($request->all());
        $topic->saveOrFail();

        return new JsonResource($topic->refresh()->loadMissing($this->relations));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Topic $topic
     *
     * @return JsonResponse
     * @throws Exception
     */
    public function destroy(Topic $topic): JsonResponse
    {
        return new JsonResponse($topic->delete());
    }
}
