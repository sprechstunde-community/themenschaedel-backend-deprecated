<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * UserResource
 *
 * @OA\Schema(allOf={
 *     @OA\Schema(ref="#/components/schemas/User"),
 *     @OA\Schema(
 *         @OA\Property(property="profile_picture", type="string", format="url", readOnly=true),
 *     ),
 * })
 *
 * @author Vincent Neubauer <v.neubauer@vonmaehlen.com> */
class UserResource extends JsonResource
{
    private const PP_SIZE = 256;

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     *
     * @return array
     */
    public function toArray($request): array
    {
        return array_merge($this->only(['username', 'description', 'created_at']), [
            'profile_picture' => sprintf("https://www.gravatar.com/avatar/%s?s=%d",
                md5(strtolower(trim($this->email))), self::PP_SIZE),
        ]);
    }
}
