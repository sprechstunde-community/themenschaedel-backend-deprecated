<?php

namespace App\Http\Resources;

use App\Models\Claim;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * EpisodeResource
 *
 * @OA\Schema(schema="EpisodeResource", required={"guid", "episode_number", "title"}, {
 *     @OA\Property(property="guid", type="string", format="uuid"),
 *     @OA\Property(property="episode_number", type="integer", example=13),
 *     @OA\Property(property="title", type="string"),
 *     @OA\Property(property="subtitle", type="string", nullable=true),
 *     @OA\Property(property="description", type="string", nullable=true),
 *     @OA\Property(property="upvotes", type="integer", readOnly=true),
 *     @OA\Property(property="downvotes", type="integer", readOnly=true),
 * })
 *
 * @OA\Schema(schema="EpisodeResourceCollection", type="array", @OA\Items(ref="#/components/schemas/EpisodeResource"))
 *
 * @author Vincent Neubauer <v.neubauer@vonmaehlen.com>
 */
class EpisodeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     *
     * @return array
     */
    public function toArray($request): array
    {
        return array_merge(parent::toArray($request), [
            'claimed' => $this->claimed instanceof Claim,
            'upvotes' => count($this->votes()->where('positive', true)->get()),
            'downvotes' => count($this->votes()->where('positive', false)->get()),
            'flags' => count($this->flags),
        ]);
    }
}
