<?php

namespace App\Http\Controllers\Api;

use App\Models\Subtopic;
use App\Models\Topic;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;
use Throwable;

class SubtopicController extends AbstractApiController
{
    use AuthorizesRequests;

    private array $relations = [
        'topic',
        'user',
    ];

    /**
     * @OA\Schema(schema="SubtopicResponse", @OA\Property(property="data", ref="#/components/schemas/Subtopic"))
     * @OA\Schema(schema="SubtopicsResponse",
     *     @OA\Property(property="data", ref="#/components/schemas/SubtopicCollection"))
     */
    public function __construct()
    {
        $this->authorizeResource(Subtopic::class, 'subtopic');
    }

    /**
     * List of subtopics
     *
     * @OA\Get(
     *     path="/subtopics",
     *     tags={"subtopics"},
     *     @OA\Parameter(name="page", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer")),
     *     @OA\Response(response="200", description="Success",
     *          @OA\JsonContent(ref="#/components/schemas/SubtopicsResponse")
     *     ),
     * )
     *
     * @param Request $request
     *
     * @return JsonResource
     */
    public function index(Request $request): JsonResource
    {
        return JsonResource::collection(
            Subtopic::with($this->relations)->paginate($this->getPerPageParameter($request))
        );
    }

    /**
     * List of subtopics scoped by topic
     *
     * @OA\Get(
     *     path="/topics/{topic}/subtopics",
     *     tags={"topics", "subtopics"},
     *     @OA\Parameter(name="topic", in="path", @OA\Schema(type="integer", example=13)),
     *     @OA\Parameter(name="page", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer")),
     *     @OA\Response(response="200", description="Success",
     *          @OA\JsonContent(ref="#/components/schemas/SubtopicsResponse")
     *     ),
     * )
     *
     * @param Topic $topic
     * @param Request $request
     *
     * @return JsonResource
     * @throws AuthorizationException
     */
    public function indexScoped(Topic $topic, Request $request): JsonResource
    {
        $this->authorize('viewAny', Subtopic::class);

        return JsonResource::collection(
            $topic->subtopics()
                ->with($this->relations)
                ->paginate($this->getPerPageParameter($request))
        );
    }

    /**
     * Store a new subtopic
     *
     * @OA\Post(
     *     path="/topics/{topic}/subtopics",
     *     tags={"subtopics"},
     *     security={{ "bearerAuth":{} }},
     *     @OA\Parameter(name="topic", in="path", @OA\Schema(type="integer", example=13)),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/Subtopic")
     *         )
     *     ),
     *     @OA\Response(response="200", description="Success",
     *          @OA\JsonContent(ref="#/components/schemas/SubtopicResponse")
     *     ),
     *     @OA\Response(response="401", description="Unauthenticated"),
     *     @OA\Response(response="403", description="Forbidden"),
     * )
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
        $subtopic->user()->associate($request->user());
        $topic->subtopics()->save($subtopic);

        return new JsonResource($subtopic->refresh()->loadMissing($this->relations));
    }

    /**
     * Display the subtopic
     *
     * @OA\Get(
     *     path="/subtopics/{subtopic}",
     *     tags={"subtopics"},
     *     @OA\Parameter(name="subtopic", in="path", @OA\Schema(type="integer", example=13)),
     *     @OA\Response(response="200", description="Success",
     *          @OA\JsonContent(ref="#/components/schemas/SubtopicResponse")
     *     ),
     * )
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
     * Update the subtopic
     *
     * @OA\Put(
     *     path="/subtopics/{subtopic}",
     *     tags={"subtopics"},
     *     security={{ "bearerAuth":{} }},
     *     @OA\Parameter(name="subtopic", in="path", @OA\Schema(type="integer", example=13)),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/Subtopic")
     *         )
     *     ),
     *     @OA\Response(response="200", description="Success",
     *          @OA\JsonContent(ref="#/components/schemas/SubtopicResponse")
     *     ),
     *     @OA\Response(response="401", description="Unauthenticated"),
     *     @OA\Response(response="403", description="Forbidden"),
     * )
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
     * Remove the subtopic
     *
     * @OA\Delete(
     *     path="/subtopics/{subtopic}",
     *     tags={"subtopics"},
     *     security={{ "bearerAuth":{} }},
     *     @OA\Parameter(name="subtopic", in="path", @OA\Schema(type="integer", example=13)),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="401", description="Unauthenticated"),
     *     @OA\Response(response="403", description="Forbidden"),
     *     @OA\Response(response="404", description="Not Found"),
     * )
     *
     * @param Subtopic $subtopic
     *
     * @return Response
     * @throws Exception
     */
    public function destroy(Subtopic $subtopic): Response
    {
        return new Response(null, $subtopic->delete() ? 200 : 500);
    }
}
