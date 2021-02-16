<?php

namespace App\Http\Controllers\Api;

use App\Models\Subtopic;
use App\Models\Topic;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Throwable;

class SubtopicController extends AbstractApiController
{
    private array $relations = [
        'topic',
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
        return JsonResource::collection(
            Subtopic::with([$this->relations])->paginate($this->getPerPageParameter($request))
        );
    }

    /**
     * Display a listing of the resource scoped by the parent resource.
     *
     * @param Topic $topic
     * @param Request $request
     *
     * @return JsonResource
     */
    public function indexScoped(Topic $topic, Request $request): JsonResource
    {
        return JsonResource::collection(
            $topic->subtopics()
                ->with($this->relations)
                ->paginate($this->getPerPageParameter($request))
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Topic $topic
     * @param Request $request
     *
     * @return JsonResource
     */
    public function store(Topic $topic, Request $request): JsonResource
    {
        $subtopic = new Subtopic();
        $subtopic->fill($request->all());
        // TODO enforce setting user id by authenticated user
        $subtopic->user()->associate(User::all()->random());
        $topic->subtopics()->save($subtopic);

        return new JsonResource($subtopic->refresh()->loadMissing($this->relations));
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
        return new JsonResource($subtopic->loadMissing($this->relations));
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

        return new JsonResource($model->refresh()->loadMissing($this->relations));
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
