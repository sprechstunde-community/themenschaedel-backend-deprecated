<?php

namespace App\Http\Controllers\Api;

use App\Models\Episode;
use App\Models\Host;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Throwable;

class HostController extends AbstractApiController
{
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
}
