<?php

namespace App\Http\Controllers\Api;

use App\Models\Episode;
use App\Models\Host;
use Exception;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;
use Psr\Log\LoggerInterface;
use Throwable;

class HostController extends AbstractApiController
{

    private ConnectionInterface $db;
    private LoggerInterface $logger;

    /**
     * HostController constructor.
     *
     * @param ConnectionInterface $connection
     * @param LoggerInterface $logger
     */
    public function __construct(ConnectionInterface $connection, LoggerInterface $logger)
    {
        $this->db = $connection;
        $this->logger = $logger;
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
        return JsonResource::collection(
            Host::with([])
                ->paginate($this->getPerPageParameter($request))
        );
    }

    /**
     * Display a listing of the resource.
     *
     * @param Episode $episode
     * @param Request $request
     *
     * @return JsonResource
     */
    public function indexScoped(Episode $episode, Request $request): JsonResource
    {
        return JsonResource::collection(
            $episode
                ->hosts()
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
        $host = new Host();
        $host->fill($request->all());
        $host->saveOrFail();

        return new JsonResource($host->refresh());
    }

    /**
     * Display the specified resource.
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
     * Update the specified resource in storage.
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
     * Remove the specified resource from storage.
     *
     * @param Host $host
     *
     * @return JsonResponse
     * @throws Exception
     */
    public function destroy(Host $host): JsonResponse
    {
        return new JsonResponse(null, $host->delete() ? 200 : 500);
    }

    /**
     * Assign an episode onto the host
     *
     * @param Host $host
     * @param Episode $episode
     *
     * @return Response|JsonResponse
     */
    public function attachEpisode(Host $host, Episode $episode)
    {
        $entryExists = $this->db->table('episode_host')
            ->where('episode_id', $episode->getKey())
            ->where('host_id', $host->getKey())
            ->get()->first();

        if (!empty($entryExists)) {
            return new Response(null); // return status 200, because relation already exists
        }

        try {
            $host->episodes()->attach($episode->getKey());
            return new Response(null); // return status 200 to indicate success
        } catch (Throwable $exception) {
            // Log as much useful information as possible
            $this->logger->error('Failed to populate host-episode-relationship', [
                'episode' => $episode->getKey(),
                'host' => $host->getKey(),
                'message' => $exception->getMessage(),
            ]);
        } finally {
            // return status 500 to indicate failed request
            return new JsonResponse([
                'status' => 500,
                'reason' => 'INTERNAL_SERVER_ERROR',
                'message' => 'See server logs for additional information',
            ], 500);
        }
    }

    /**
     * Detach an episode onto the host
     *
     * @param Host $host
     * @param Episode $episode
     *
     * @return void
     */
    public function detachEpisode(Host $host, Episode $episode): void
    {
        // will not throw an exception if the relation does not exist
        $host->episodes()->detach($episode->getKey());
    }
}
