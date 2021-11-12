<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\EpisodeResource;
use App\Models\Claim;
use App\Models\Episode;
use App\Models\Vote;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Throwable;

class EpisodeController extends AbstractApiController
{
    use AuthorizesRequests;

    private array $relations = [
        'claimed',
        'hosts',
        'topics',
        'topics.subtopics',
    ];

    /**
     * EpisodeController constructor.
     */
    public function __construct()
    {
        $this->authorizeResource(Episode::class, 'episode');
    }

    /**
     * Listing episodes
     *
     * @OA\Get(
     *     path="/episodes",
     *     tags={"episodes"},
     *     @OA\Parameter(name="page", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(ref="#/components/schemas/EpisodeResourceCollection"))
     *     )
     * )
     *
     * @param Request $request
     *
     * @return JsonResource
     */
    public function index(Request $request): JsonResource
    {
        return EpisodeResource::collection(
            Episode::query()
                ->with([
                    'hosts:name,main,profile_picture',
                    'topics:id,episode_id,name',
                ])
                ->orderBy('episode_number', 'desc')
                ->paginate($this->getPerPageParameter($request))
        );
    }

    /**
     * Store a newly created episode resource in storage.
     *
     * @OA\Post(
     *     path="/episodes",
     *     tags={"episodes"},
     *     security={{ "bearerAuth":{} }},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/EpisodeResource")
     *         )
     *     ),
     *     @OA\Response(response="401", description="Unauthenticated"),
     *     @OA\Response(response="403", description="Forbidden"),
     * )
     *
     * @param Request $request
     *
     * @return JsonResource
     * @throws Throwable
     */
    public function store(Request $request): JsonResource
    {
        $model = (new Episode())->fill($request->all());
        $model->saveOrFail();

        return new EpisodeResource($model->refresh()->loadMissing($this->relations));
    }

    /**
     * Display episode resource
     *
     * @OA\Get(
     *     path="/episodes/{episode}",
     *     tags={"episodes"},
     *     @OA\Parameter(
     *         name="episode",
     *         in="path",
     *         required=true,
     *         description="Internal episode ID",
     *         @OA\Schema(type="integer", example=13)
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(ref="#/components/schemas/EpisodeResource")
     *     ),
     * )
     *
     * @param Episode $episode
     *
     * @return JsonResource
     */
    public function show(Episode $episode): JsonResource
    {
        return new EpisodeResource($episode->loadMissing($this->relations));
    }

    /**
     * Update the episode resource in storage
     *
     * @OA\Put(
     *     path="/episodes/{episode}",
     *     tags={"episodes"},
     *     security={{ "bearerAuth":{} }},
     *     @OA\Parameter(
     *         name="episode",
     *         in="path",
     *         required=true,
     *         description="Internal episode ID",
     *         @OA\Schema(type="integer", example=13)
     *     ),
     *     @OA\Response(response="401", description="Unauthenticated"),
     *     @OA\Response(response="403", description="Forbidden"),
     * )
     *
     * @param Request $request
     * @param Episode $episode
     *
     * @return JsonResource
     * @throws Throwable
     */
    public function update(Request $request, Episode $episode): JsonResource
    {
        $episode->fill($request->all());
        $episode->saveOrFail();

        return new EpisodeResource($episode->refresh()->loadMissing($this->relations));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @OA\Delete(
     *     path="/episodes/{episode}",
     *     tags={"episodes"},
     *     security={{ "bearerAuth":{} }},
     *     @OA\Parameter(
     *         name="episode",
     *         in="path",
     *         required=true,
     *         description="Internal episode ID",
     *         @OA\Schema(type="integer", example=13)
     *     ),
     *     @OA\Response(response="401", description="Unauthenticated"),
     *     @OA\Response(response="403", description="Forbidden"),
     * )
     *
     * @param Episode $episode
     *
     * @return JsonResponse
     * @throws Exception
     */
    public function destroy(Episode $episode): JsonResponse
    {
        return new JsonResponse($episode->delete());
    }

    /**
     * Claim a new episode
     *
     * @OA\Post(
     *     path="/episodes/{episode}/claim",
     *     tags={"claims", "episodes"},
     *     security={{ "bearerAuth":{} }},
     *     @OA\Parameter(
     *         name="episode",
     *         in="path",
     *         required=true,
     *         description="Internal episode ID",
     *         @OA\Schema(type="integer", example=13)
     *     ),
     *     @OA\Response(response="201", description="Success"),
     *     @OA\Response(response="401", description="Unauthenticated"),
     *     @OA\Response(response="403", description="Already Claimed"),
     * )
     *
     * @param Episode $episode
     * @param Request $request
     *
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function claim(Episode $episode, Request $request)
    {
        $this->authorize('claim', $episode);

        $claim = new Claim();
        $claim->claimed_at = now();
        $claim->user()->associate($request->user());
        $episode->claimed()->save($claim);

        return new JsonResponse(null, 201);
    }

    /**
     * Remove the claim from episode resource
     *
     * @OA\Delete(
     *     path="/episodes/{episode}/claim",
     *     tags={"claims", "episodes"},
     *     security={{ "bearerAuth":{} }},
     *     @OA\Parameter(
     *         name="episode",
     *         in="path",
     *         required=true,
     *         description="Internal episode ID",
     *         @OA\Schema(type="integer", example=13)
     *     ),
     *     @OA\Response(response="201", description="Success"),
     *     @OA\Response(response="401", description="Unauthenticated"),
     *     @OA\Response(response="403", description="Claimed by someone else"),
     * )
     *
     * @param Episode $episode
     *
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function unclaim(Episode $episode): JsonResponse
    {
        $this->authorize('unclaim', $episode);

        if (!$episode->claimed instanceof Claim) {
            return new JsonResponse([
                'code' => 409,
                'reason' => 'NOT_YET_CLAIMED',
            ], 409);
        }

        return new JsonResponse(null, $episode->claimed()->delete() ? 200 : 500);
    }

    /**
     * Vote for an episode
     *
     * Handles up votes, down votes and removal of votes.
     *
     * @OA\Post(
     *     path="/episodes/{episode}/vote",
     *     tags={"vote", "episodes"},
     *     security={{ "bearerAuth":{} }},
     *     @OA\Parameter(
     *         name="episode",
     *         in="path",
     *         required=true,
     *         description="Internal episode ID",
     *         @OA\Schema(type="integer", example=13)
     *     ),
     *     @OA\Parameter(
     *         name="direction",
     *         in="query",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             enum={-1, 0, 1},
     *             example=1,
     *         ),
     *     ),
     *     @OA\Response(response="201", description="Success"),
     *     @OA\Response(response="400", description="Bad Request"),
     *     @OA\Response(response="401", description="Unauthenticated"),
     * )
     *
     * @param Episode $episode
     * @param Request $request
     *
     * @return JsonResponse|JsonResource
     * @throws Exception
     */
    public function vote(Episode $episode, Request $request)
    {
        $this->authorize('vote', $episode);

        if (!$request->has('direction')) {
            return new JsonResponse([
                'code' => 400,
                'reason' => 'DIRECTION_PARAMETER_MISSING',
            ], 400);
        }

        $direction = (int) $request->input('direction');
        $vote = $episode->votes()->where('user_id', $request->user()->getKey())->first();

        if ($direction === 0) {
            // Delete vote and return status code
            if ($vote instanceof Vote) {
                $success = $vote->delete();
            } else {
                $success = true;
            }

            return new JsonResponse(null, $success ? 200 : 500);
        }

        if (!$vote instanceof Vote) {
            $vote = new Vote();
        }

        $vote->positive = $direction > 0;
        $vote->episode()->associate($episode);
        $vote->user()->associate($request->user());
        $vote->save();

        return new JsonResource(
            $vote
                ->refresh()
                ->loadMissing([
                    'episode',
                ])
        );
    }
}
