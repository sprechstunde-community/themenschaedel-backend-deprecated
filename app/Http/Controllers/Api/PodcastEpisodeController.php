<?php

namespace App\Http\Controllers\Api;

use App\Models\PodcastEpisode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Throwable;

class PodcastEpisodeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        return new JsonResponse(PodcastEpisode::all()->all());
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
        $model = (new PodcastEpisode())->fill($request->all());
        $model->saveOrFail();

        return new JsonResponse($model->getAttributes());
    }

    /**
     * Display the specified resource.
     *
     * @param PodcastEpisode $podcastEpisode
     *
     * @return JsonResponse
     */
    public function show(PodcastEpisode $podcastEpisode): JsonResponse
    {
        return new JsonResponse($podcastEpisode->getAttributes());
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param PodcastEpisode $podcastEpisode
     *
     * @return JsonResponse
     * @throws Throwable
     */
    public function update(Request $request, PodcastEpisode $podcastEpisode): JsonResponse
    {
        $podcastEpisode->fill($request->all());
        $podcastEpisode->saveOrFail();
        return new JsonResponse($podcastEpisode->getAttributes());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param PodcastEpisode $podcastEpisode
     *
     * @return JsonResponse
     */
    public function destroy(PodcastEpisode $podcastEpisode): JsonResponse
    {
        return new JsonResponse($podcastEpisode->delete());
    }
}
