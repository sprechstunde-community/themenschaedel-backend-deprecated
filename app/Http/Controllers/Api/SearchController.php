<?php

namespace App\Http\Controllers\Api;

use App\Models\Episode;
use App\Models\Subtopic;
use App\Models\Topic;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use RuntimeException;

class SearchController extends AbstractApiController
{
    /**
     * Search for episodes
     *
     * @OA\Get(
     *     path="/search/episodes",
     *     tags={"episodes", "search"},
     *     @OA\Parameter(name="q", in="query", description="Search string", required=true,
     *         @OA\Schema(type="string",example="Lorem Ipsum")),
     *     @OA\Parameter(name="page", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/EpisodeResourceCollection"),
     *             @OA\Property(property="current_page", type="integer", example=1),
     *             @OA\Property(property="last_page", type="integer", example=13),
     *             @OA\Property(property="per_page", type="integer", example=25),
     *             @OA\Property(property="total", type="integer", example=325),
     *             @OA\Property(property="last_page_url", type="string"),
     *             @OA\Property(property="next_page_url", type="string", nullable=true),
     *             @OA\Property(property="prev_page_url", type="string", nullable=true),
     *         )
     *     )
     * )
     *
     * @param Request $request
     * @param Episode $repository
     *
     * @return LengthAwarePaginator
     */
    public function episodes(Request $request, Episode $repository)
    {
        return $this->search($request, $repository);
    }

    /**
     * Search for topics
     *
     * @OA\Get(
     *     path="/search/topics",
     *     tags={"topics", "search"},
     *     @OA\Parameter(name="q", in="query", description="Search string", required=true,
     *         @OA\Schema(type="string",example="Lorem Ipsum")),
     *     @OA\Parameter(name="page", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/TopicCollection"),
     *             @OA\Property(property="current_page", type="integer", example=1),
     *             @OA\Property(property="last_page", type="integer", example=13),
     *             @OA\Property(property="per_page", type="integer", example=25),
     *             @OA\Property(property="total", type="integer", example=325),
     *             @OA\Property(property="last_page_url", type="string"),
     *             @OA\Property(property="next_page_url", type="string", nullable=true),
     *             @OA\Property(property="prev_page_url", type="string", nullable=true),
     *         )
     *     )
     * )
     * @param Request $request
     * @param Topic $repository
     *
     * @return LengthAwarePaginator
     */
    public function topics(Request $request, Topic $repository)
    {
        return $this->search($request, $repository);
    }

    /**
     * Search for subtopics
     *
     * @OA\Get(
     *     path="/search/subtopics",
     *     tags={"subtopics", "search"},
     *     @OA\Parameter(name="q", in="query", description="Search string", required=true,
     *         @OA\Schema(type="string",example="Lorem Ipsum")),
     *     @OA\Parameter(name="page", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response="200",
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/SubtopicCollection"),
     *             @OA\Property(property="current_page", type="integer", example=1),
     *             @OA\Property(property="last_page", type="integer", example=13),
     *             @OA\Property(property="per_page", type="integer", example=25),
     *             @OA\Property(property="total", type="integer", example=325),
     *             @OA\Property(property="last_page_url", type="string"),
     *             @OA\Property(property="next_page_url", type="string", nullable=true),
     *             @OA\Property(property="prev_page_url", type="string", nullable=true),
     *         )
     *     )
     * )
     * @param Request $request
     * @param Subtopic $repository
     *
     * @return LengthAwarePaginator
     */
    public function subtopics(Request $request, Subtopic $repository)
    {
        return $this->search($request, $repository);
    }

    /**
     * @param Request $request
     * @param Model $searchable
     *
     * @return LengthAwarePaginator
     * @throws RuntimeException If the {@see $searchable} is not searchable. Look at
     * {@see https://github.com/laravel/scout} on how to make them searchable.
     */
    protected function search(Request $request, Model $searchable): LengthAwarePaginator
    {
        $query = $request->input('q');

        if (!method_exists($searchable, 'search')) {
            throw new RuntimeException(sprintf('Model `%s` given, but is not searchable', $searchable));
        }

        return $searchable::search($query)->paginate($this->getPerPageParameter($request));
    }
}
