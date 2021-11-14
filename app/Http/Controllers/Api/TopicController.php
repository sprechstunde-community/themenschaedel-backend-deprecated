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
use Illuminate\Http\Response;
use Throwable;

class TopicController extends AbstractApiController
{
    use AuthorizesRequests;

    private array $relations = [
        'subtopics',
        'user',
    ];

    /**
     * @OA\Schema(schema="TopicResponse", @OA\Property (property="data", ref="#/components/schemas/Topic"))
     * @OA\Schema(schema="TopicsResponse", @OA\Property (property="data", ref="#/components/schemas/TopicCollection"))
     */
    public function __construct()
    {
        $this->authorizeResource(Topic::class, 'topic');
    }

    /**
     * List of topics
     *
     * @OA\Get(
     *     path="/topics",
     *     tags={"topics"},
     *     @OA\Parameter(name="page", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(ref="#/components/schemas/TopicsResponse")
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
            Topic::with($this->relations)
                ->paginate($this->getPerPageParameter($request))
        );
    }

    /**
     * List of topics scoped by episode
     *
     * @OA\Get(
     *     path="/episodes/{episode}/topics",
     *     tags={"episodes", "topics"},
     *     @OA\Parameter(name="episode", in="path", @OA\Schema(type="integer", example=13)),
     *     @OA\Parameter(name="page", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(ref="#/components/schemas/TopicsResponse")
     *     ),
     * )
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
     * Store a new topic
     *
     * @OA\Post(
     *     path="/episodes/{episode}/topics",
     *     tags={"topics"},
     *     security={{ "bearerAuth":{} }},
     *     @OA\Parameter(name="episode", in="path", @OA\Schema(type="integer", example=13)),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/Topic")
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(ref="#/components/schemas/TopicResponse")
     *     ),
     *     @OA\Response(response="401", description="Unauthenticated"),
     *     @OA\Response(response="403", description="Forbidden"),
     * )
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
     * Display the topic
     *
     * @OA\Get(
     *     path="/topics/{topic}",
     *     tags={"topics"},
     *     @OA\Parameter(name="topic", in="path", @OA\Schema(type="integer", example=13)),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(ref="#/components/schemas/TopicResponse")
     *     ),
     * )
     *
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
     * Update the topic
     *
     * @OA\Put(
     *     path="/topics/{topic}",
     *     tags={"topics"},
     *     security={{ "bearerAuth":{} }},
     *     @OA\Parameter(name="topic", in="path", @OA\Schema(type="integer", example=13)),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/Topic")
     *         )
     *     ),
     *     @OA\Response(response="200", description="Success", @OA\Schema(ref="#/components/schemas/Topic")),
     *     @OA\Response(response="401", description="Unauthenticated"),
     *     @OA\Response(response="403", description="Forbidden"),
     * )
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
     * Remove the topic
     *
     * @OA\Delete(
     *     path="/topics/{topic}",
     *     tags={"topics"},
     *     security={{ "bearerAuth":{} }},
     *     @OA\Parameter(name="topic", in="path", @OA\Schema(type="integer", example=13)),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="401", description="Unauthenticated"),
     *     @OA\Response(response="403", description="Forbidden"),
     *     @OA\Response(response="404", description="Not Found"),
     * )
     *
     * @param Topic $topic
     *
     * @return Response
     * @throws Exception
     */
    public function destroy(Topic $topic): Response
    {
        return new Response(null, $topic->delete() ? 200 : 500);

    }
}
