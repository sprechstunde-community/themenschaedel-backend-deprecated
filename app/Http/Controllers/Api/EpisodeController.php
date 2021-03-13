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
     * Display a listing of the resource.
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
     * Store a newly created resource in storage.
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
     * Display the specified resource.
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
     * Update the specified resource in storage.
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
     * Claim a new episode.
     *
     * @param Episode $episode
     * @param Request $request
     *
     * @return JsonResponse|JsonResource
     * @throws AuthorizationException
     */
    public function claim(Episode $episode, Request $request)
    {
        if ($episode->claimed) {
            return new JsonResponse([
                'code' => 409,
                'reason' => 'ALREADY_CLAIMED',
            ], 409);
        }

        $this->authorize('claim', $episode);

        $claim = new Claim();
        $claim->claimed_at = now();
        $claim->user()->associate($request->user());
        $episode->claimed()->save($claim);

        return new JsonResource($claim->refresh()->loadMissing('user', 'episode'));
    }

    /**
     * Remove the specified resource from storage.
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
     * Vote for an episode. Handles up votes, down votes and removal of votes.
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

        return new JsonResource(
            $vote
                ->refresh()
                ->loadMissing([
                    'episode',
                ])
        );

    }
}
