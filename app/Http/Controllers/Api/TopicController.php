<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\TopicResource;
use App\Models\Episode;
use App\Models\Topic;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Throwable;

class TopicController extends AbstractApiController
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
        return TopicResource::collection(
            Topic::paginate($this->getPerPageParameter($request))
        );
    }

    /**
     * Display a listing of the resource scoped by parent model.
     *
     * @param Episode $episode
     * @param Request $request
     *
     * @return JsonResource
     */
    public function indexScoped(Episode $episode, Request $request): JsonResource
    {
        return TopicResource::collection(
            Topic::where('episode_id', $episode->id)
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
        // TODO enforce setting user id by authenticated user
        $topic->user()->associate(User::all()->random());
        $episode->topics()->save($topic);

        return new TopicResource($topic->refresh());
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
        return new TopicResource($topic);
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

        return new TopicResource($topic);
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
