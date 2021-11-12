<?php

namespace App\Http\Controllers\Api;

use App\Models\Episode;
use App\Models\Flag;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Throwable;

class FlagController extends AbstractApiController
{
    use AuthorizesRequests;

    private array $relations = [
        'episode',
        'user',
    ];

    /**
     * @OA\Schema(schema="FlagResponse", @OA\Property(property="data", ref="#/components/schemas/Flag"))
     * @OA\Schema(schema="FlagsResponse", @OA\Property(property="data", ref="#/components/schemas/FlagCollection"))
     */
    public function __construct()
    {
        $this->authorizeResource(Flag::class, 'flag');
    }

    /**
     * List of flags
     *
     * @OA\Get(
     *     path="/flags",
     *     tags={"flags"},
     *     security={{ "bearerAuth":{} }},
     *     @OA\Parameter(name="page", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer")),
     *     @OA\Response(response="401", description="Unauthorized"),
     *     @OA\Response(response="403", description="Forbidden"),
     * )
     *
     * @param Request $request
     *
     * @return JsonResource
     */
    public function index(Request $request): JsonResource
    {
        return new JsonResource(
            Flag::with($this->relations)
                ->paginate($this->getPerPageParameter($request))
        );
    }

    /**
     * List of flags scoped by parent resource
     *
     * @OA\Get(
     *     path="/episodes/{episode}/flags",
     *     tags={"episodes", "flags"},
     *     security={{ "bearerAuth":{} }},
     *     @OA\Parameter(name="page", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="episode", in="path", required=true, @OA\Schema(type="integer", example=13)),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(ref="#/components/schemas/FlagsResponse")
     *     ),
     *     @OA\Response(response="401", description="Unauthorized"),
     *     @OA\Response(response="403", description="Forbidden"),
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
        $this->authorize('viewAny', Flag::class);

        return new JsonResource(
            $episode->flags()
                ->with($this->relations)
                ->paginate($this->getPerPageParameter($request))
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @OA\Post(
     *     path="/episodes/{episode}/flags",
     *     tags={"flags"},
     *     security={{ "bearerAuth":{} }},
     *     @OA\Parameter(name="episode", in="path", required=true, @OA\Schema(type="integer", example=13)),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/Flag"),
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(ref="#/components/schemas/FlagResponse")
     *     ),
     *     @OA\Response(response="401", description="Unauthorized"),
     * )
     *
     * @param Episode $episode
     * @param Request $request
     *
     * @return JsonResource
     */
    public function store(Episode $episode, Request $request): JsonResource
    {
        /** @var Flag $flag */
        $flag = $episode->flags()->newModelInstance();
        $flag->fill($request->all());
        $flag->episode()->associate($episode);
        $flag->user()->associate($request->user());
        $flag->save();

        return new JsonResource($flag->refresh()->loadMissing($this->relations));
    }

    /**
     * Display the specified resource.
     *
     * @OA\Get(
     *     path="/flags/{flag}",
     *     tags={"flags"},
     *     security={{ "bearerAuth":{} }},
     *     @OA\Parameter(name="flag", in="path", required=true, @OA\Schema(type="integer", example=13)),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(ref="#/components/schemas/FlagResponse")
     *     ),
     *     @OA\Response(response="401", description="Unauthorized"),
     *     @OA\Response(response="403", description="Forbidden"),
     * )
     *
     * @param Flag $flag
     *
     * @return JsonResource
     */
    public function show(Flag $flag): JsonResource
    {
        // TODO fix 403 if current user matches creator of the flag
        return new JsonResource($flag->loadMissing($this->relations));
    }

    /**
     * Update the specified resource in storage.
     *
     * @OA\Put(
     *     path="/flags/{flag}",
     *     tags={"flags"},
     *     security={{ "bearerAuth":{} }},
     *     @OA\Parameter(name="flag", in="path", required=true, @OA\Schema(type="integer", example=13)),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/Flag"),
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(ref="#/components/schemas/FlagResponse")
     *     ),
     *     @OA\Response(response="401", description="Unauthorized"),
     *     @OA\Response(response="403", description="Forbidden"),
     * )
     *
     * @param Request $request
     * @param Flag $flag
     *
     * @return JsonResource
     * @throws Throwable
     */
    public function update(Request $request, Flag $flag): JsonResource
    {
        $flag->fill($request->all());
        $flag->saveOrFail();

        return new JsonResource($flag->refresh()->loadMissing($this->relations));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @OA\Delete(
     *     path="/flags/{flag}",
     *     tags={"flags"},
     *     security={{ "bearerAuth":{} }},
     *     @OA\Parameter(name="flag", in="path", required=true, @OA\Schema(type="integer", example=13)),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *     ),
     *     @OA\Response(response="401", description="Unauthorized"),
     *     @OA\Response(response="403", description="Forbidden"),
     *     @OA\Response(response="404", description="Not Found"),
     * )
     *
     * @param Flag $flag
     *
     * @return JsonResponse
     * @throws Exception
     */
    public function destroy(Flag $flag): JsonResponse
    {
        // TODO fix 404 if model missing
        return new JsonResponse(null, $flag->delete() ? 200 : 500);
    }
}
