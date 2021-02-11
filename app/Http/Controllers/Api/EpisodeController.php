<?php

namespace App\Http\Controllers\Api;

use App\Models\Episode;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Throwable;

class EpisodeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        return new JsonResponse(Episode::all()->all());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws Throwable
     */
    public function store(Request $request): JsonResponse
    {
        $model = (new Episode())->fill($request->all());
        $model->saveOrFail();

        return new JsonResponse($model->getAttributes());
    }

    /**
     * Display the specified resource.
     *
     * @param Episode $podcastEpisode
     *
     * @return JsonResponse
     */
    public function show(Episode $podcastEpisode): JsonResponse
    {
        return new JsonResponse($podcastEpisode->getAttributes());
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Episode $podcastEpisode
     *
     * @return JsonResponse
     * @throws Throwable
     */
    public function update(Request $request, Episode $podcastEpisode): JsonResponse
    {
        $podcastEpisode->fill($request->all());
        $podcastEpisode->saveOrFail();
        return new JsonResponse($podcastEpisode->getAttributes());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Episode $podcastEpisode
     *
     * @return JsonResponse
     * @throws Exception
     */
    public function destroy(Episode $podcastEpisode): JsonResponse
    {
        return new JsonResponse($podcastEpisode->delete());
    }
}
