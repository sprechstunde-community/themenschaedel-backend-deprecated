<?php

namespace App\Http\Controllers\Api;

use App\Models\Episode;
use App\Models\Host;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;
use Psr\Log\LoggerInterface;
use Throwable;

class HostController extends AbstractApiController
{
    use AuthorizesRequests;

    private ConnectionInterface $db;
    private LoggerInterface $logger;

    /**
     * HostController constructor
     *
     * @OA\Schema(schema="HostResponse", @OA\Property (property="data", ref="#/components/schemas/Host"))
     * @OA\Schema(schema="HostsResponse", @OA\Property (property="data", ref="#/components/schemas/HostCollection"))
     *
     * @param ConnectionInterface $connection
     * @param LoggerInterface $logger
     */
    public function __construct(ConnectionInterface $connection, LoggerInterface $logger)
    {
        $this->authorizeResource(Host::class, 'host');

        $this->db = $connection;
        $this->logger = $logger;
    }

    /**
     * List of hosts.
     *
     * @OA\Get(
     *     path="/hosts",
     *     tags={"hosts"},
     *     @OA\Parameter(name="page", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(ref="#/components/schemas/HostsResponse")
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
            Host::with([])
                ->paginate($this->getPerPageParameter($request))
        );
    }

    /**
     * List of hosts scoped by an episode
     *
     * @OA\Get(
     *     path="/episodes/{episode}/hosts",
     *     tags={"episodes", "hosts"},
     *     @OA\Parameter(name="page", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="episode", in="path", required=true, @OA\Schema(type="integer", example=13)),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(ref="#/components/schemas/HostsResponse")
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
        $this->authorize('viewAny', Host::class);

        return JsonResource::collection(
            $episode
                ->hosts()
                ->paginate($this->getPerPageParameter($request))
        );
    }

    /**
     *
     * Store new host resource
     *
     * @OA\Post(
     *     path="/hosts",
     *     tags={"hosts"},
     *     security={{ "bearerAuth":{} }},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/Host")
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(ref="#/components/schemas/HostResponse"),
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
        $host = new Host();
        $host->fill($request->all());
        $host->saveOrFail();

        return new JsonResource($host->refresh());
    }

    /**
     * Display the host resource
     *
     * @OA\Get(
     *     path="/hosts/{host}",
     *     tags={"hosts"},
     *     @OA\Parameter(name="host", in="path", @OA\Schema(type="integer", example=1)),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(ref="#/components/schemas/HostResponse"),
     *     ),
     * )
     *
     * @param Host $host
     *
     * @return JsonResource
     */
    public function show(Host $host): JsonResource
    {
        return new JsonResource($host);
    }

    /**
     * Update the host resource
     *
     * @OA\Put(
     *     path="/hosts/{host}",
     *     tags={"hosts"},
     *     security={{ "bearerAuth":{} }},
     *     @OA\Parameter(name="host", in="path", @OA\Schema(type="integer", example=1)),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(ref="#/components/schemas/Host")
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(ref="#/components/schemas/HostResponse"),
     *     ),
     *     @OA\Response(response="401", description="Unauthenticated"),
     *     @OA\Response(response="403", description="Forbidden"),
     * )
     *
     * @param Request $request
     * @param Host $host
     *
     * @return JsonResource
     * @throws Throwable
     */
    public function update(Request $request, Host $host): JsonResource
    {
        $host->fill($request->all());
        $host->saveOrFail();

        return new JsonResource($host->refresh());
    }

    /**
     * Remove the host resource
     *
     * @OA\Delete(
     *     path="/hosts/{host}",
     *     tags={"hosts"},
     *     security={{ "bearerAuth":{} }},
     *     @OA\Parameter(name="host", in="path", @OA\Schema(type="integer", example=1)),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="401", description="Unauthenticated"),
     *     @OA\Response(response="403", description="Forbidden"),
     * )
     *
     * @param Host $host
     *
     * @return JsonResponse
     * @throws Exception
     */
    public function destroy(Host $host): JsonResponse
    {
        // TODO fix deleting if model has relations
        return new JsonResponse(null, $host->delete() ? 200 : 500);
    }

    /**
     * Assign an episode onto the host
     *
     * @OA\Post(
     *     path="/hosts/{host}/episodes/{episode}",
     *     tags={"episodes", "hosts"},
     *     security={{ "bearerAuth":{} }},
     *     @OA\Parameter(name="episode", in="path", @OA\Schema(type="integer", example=1)),
     *     @OA\Parameter(name="host", in="path", @OA\Schema(type="integer", example=1)),
     *     @OA\Response(response="200", description="Already assigned"),
     *     @OA\Response(response="201", description="Created assignment"),
     *     @OA\Response(response="401", description="Unauthenticated"),
     *     @OA\Response(response="403", description="Forbidden"),
     * )
     *
     *
     * @param Host $host
     * @param Episode $episode
     *
     * @return Response|JsonResponse
     * @throws AuthorizationException
     */
    public function attachEpisode(Host $host, Episode $episode)
    {
        $this->authorize('update', $host);

        $entryExists = $this->db->table('episode_host')
            ->where('episode_id', $episode->getKey())
            ->where('host_id', $host->getKey())
            ->get()->first();

        if (!empty($entryExists)) {
            return new Response(null); // return status 200, because relation already exists
        }

        try {
            $host->episodes()->attach($episode->getKey());
            return new Response(null, 201); // return status 200 to indicate success
        } catch (Throwable $exception) {
            // Log as much useful information as possible
            $this->logger->error('Failed to populate host-episode-relationship', [
                'episode' => $episode->getKey(),
                'host' => $host->getKey(),
                'message' => $exception->getMessage(),
            ]);
        }

        // return status 500 to indicate failed request
        return new JsonResponse([
            'status' => 500,
            'reason' => 'INTERNAL_SERVER_ERROR',
            'message' => 'See server logs for additional information',
        ], 500);
    }

    /**
     * Detach an episode onto the host
     *
     * @OA\Delete(
     *     path="/hosts/{host}/episodes/{episode}",
     *     tags={"episodes", "hosts"},
     *     security={{ "bearerAuth":{} }},
     *     @OA\Parameter(name="episode", in="path", @OA\Schema(type="integer", example=1)),
     *     @OA\Parameter(name="host", in="path", @OA\Schema(type="integer", example=1)),
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="401", description="Unauthenticated"),
     *     @OA\Response(response="403", description="Forbidden"),
     * )
     *
     * @param Host $host
     * @param Episode $episode
     *
     * @return void
     * @throws AuthorizationException
     */
    public function detachEpisode(Host $host, Episode $episode): void
    {
        $this->authorize('update', $host);
        // will not throw an exception if the relation does not exist
        $host->episodes()->detach($episode->getKey());
    }
}
