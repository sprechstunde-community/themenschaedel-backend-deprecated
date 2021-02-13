<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\SubtopicResource;
use App\Models\Subtopic;
use App\Models\Topic;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Routing\Controller;
use Throwable;

class SubtopicController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResource
     */
    public function index(): JsonResource
    {
        return SubtopicResource::collection(Subtopic::paginate());
    }

    /**
     * Display a listing of the resource scoped by the parent resource.
     *
     * @return JsonResource
     */
    public function indexScoped(Topic $topic): JsonResource
    {
        return SubtopicResource::collection(Subtopic::where('topic_id', $topic->id)->paginate());
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
        $model = (new Subtopic($request->all()));
        $model->saveOrFail();

        return new SubtopicResource($model);
    }

    /**
     * Display the specified resource.
     *
     * @param Subtopic $subtopic
     *
     * @return JsonResource
     */
    public function show(Subtopic $subtopic): JsonResource
    {
        return new SubtopicResource($subtopic);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Subtopic $subtopic
     *
     * @return JsonResource
     * @throws Throwable
     */
    public function update(Request $request, Subtopic $subtopic): JsonResource
    {
        $model = $subtopic->fill($request->all());
        $model->saveOrFail();

        return new SubtopicResource($model);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Subtopic $subtopic
     *
     * @return JsonResponse
     * @throws Exception
     */
    public function destroy(Subtopic $subtopic): JsonResponse
    {
        return new JsonResponse($subtopic->delete());
    }
}
