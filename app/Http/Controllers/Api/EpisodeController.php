<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\EpisodeResource;
use App\Models\Episode;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Throwable;

class EpisodeController extends AbstractApiController
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
        return EpisodeResource::collection(Episode::paginate($this->getPerPageParameter($request)));
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

        return new EpisodeResource($model);
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
        return new EpisodeResource($episode);
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
        return new EpisodeResource($episode->getAttributes());
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
}
