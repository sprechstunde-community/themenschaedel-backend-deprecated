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
     * Search for specific
     * @param Request $request
     * @param Episode $repository
     *
     * @return LengthAwarePaginator
     */
    public function episodes(Request $request, Episode $repository)
    {
        return $this->search($request, $repository);
    }

    public function topics(Request $request, Topic $repository)
    {
        return $this->search($request, $repository);
    }

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
