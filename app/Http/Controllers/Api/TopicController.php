<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\TopicResource;
use App\Models\Episode;
use App\Models\Topic;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Routing\Controller;
use Throwable;

class TopicController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResource
     */
    public function index(): JsonResource
    {
        return TopicResource::collection(Topic::paginate());
    }

    /**
     * Display a listing of the resource scoped by parent model.
     *
     * @param Episode $episode
     *
     * @return JsonResource
     */
    public function indexScoped(Episode $episode): JsonResource
    {
        return TopicResource::collection(Topic::where('episode_id', $episode->id)->paginate());
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
        $model = (new Topic($request->all()));
        $model->saveOrFail();

        return new TopicResource($model);
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
