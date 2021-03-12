<?php

namespace App\Http\Controllers\Api;

use App\Models\Claim;
use App\Models\Episode;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EpisodeClaimController extends AbstractApiController
{
    use AuthorizesRequests;

    /**
     * Claim a new episode.
     *
     * @param Episode $episode
     * @param Request $request
     *
     * @return JsonResponse|JsonResource
     * @throws AuthorizationException
     */
    public function store(Episode $episode, Request $request)
    {
        if ($episode->claimed) {
            return new JsonResponse([
                'code' => 409,
                'reason' => 'ALREADY_CLAIMED'
            ], 409);
        }

        $this->authorize('claim', $episode);

        $claim = new Claim();
        $claim->claimed_at = now();
        // TODO enforce setting user id by authenticated user
        $claim->user()->associate(User::all()->random());
        $episode->claimed()->save($claim);

        return new JsonResource($claim->refresh()->loadMissing('user', 'episode'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Episode $episode
     *
     * @return JsonResponse
     */
    public function destroy(Episode $episode): JsonResponse
    {
        $this->authorize('unclaim', $episode);

        if (!$episode->claimed instanceof Claim) {
                return new JsonResponse([
                    'code' => 409,
                    'reason' => 'NOT_YET_CLAIMED'
                ], 409);
        }

        return new JsonResponse(null, $episode->claimed()->delete() ? 200 : 500);
    }
}
