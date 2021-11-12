<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends AbstractApiController
{
    /**
     * User Registration
     *
     * Create new user resource, that can authenticate on this platform.
     *
     * @Since("1.0.0")
     * @OA\Post (
     *     path="/auth/register",
     *     tags={"auth"},
     *     @OA\RequestBody(required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(required={
     *                  "application_name", "username", "email", "password"
     *             }, {
     *                 @OA\Property(property="application_name", type="string", example="API-Docs"),
     *                 @OA\Property(property="username", type="string", example="john.doe"),
     *                 @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
     *                 @OA\Property(property="password", type="string", example="pass1234"),
     *                 @OA\Property(property="name", type="string", example="John"),
     *                 @OA\Property(property="description", type="string", example="Lorem ipsum dolor sit amet..."),
     *             })
     *         )
     *     ),
     *     @OA\Response(response="200", description="Success", @OA\JsonContent(example={
     *          "access_token": "token",
     *          "token_type": "Bearer",
     *     })),
     *     @OA\Response(response="400", description="Invalid Request"),
     * )
     *
     * @return JsonResponse
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'application_name' => 'required|string|max:255',
            'username' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken($request['application_name'])->plainTextToken;

        return response()
            ->json(['access_token' => $token, 'token_type' => 'Bearer', 'data' => $user]);
    }

    /**
     * Login
     *
     * Authenticate against the API and get a token to access restricted endpoints.
     *
     * @Since("1.0.0")
     * @OA\Post (
     *     path="/auth/login",
     *     tags={"auth"},
     *     @OA\RequestBody(required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(required={"application_name", "username", "password"}, {
     *                 @OA\Property(property="application_name", type="string", example="API-Docs"),
     *                 @OA\Property(property="username", type="string", example="john.doe"),
     *                 @OA\Property(property="password", type="string", example="pass1234"),
     *             })
     *         )
     *     ),
     *     @OA\Response(response="200", description="Success", @OA\JsonContent(example={
     *          "access_token": "token",
     *          "token_type": "Bearer",
     *     })),
     *     @OA\Response(response="401", description="Invalid credentials"),
     *     @OA\Response(response="404", description="User not found"),
     * )
     *
     *
     *
     *
     * @return JsonResponse
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'application_name' => 'required|string|max:255',
            'username' => 'required|string|max:255',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        if (!Auth::attempt($request->only('username', 'password'))) {
            return response()
                ->json(['message' => 'Unauthorized'], 401);
        }

        $user = User::where('username', $request['username'])->firstOrFail();

        $token = $user->createToken($request['application_name'])->plainTextToken;

        return response()
            ->json([
                'access_token' => $token,
                'token_type' => 'Bearer',
            ]);
    }

    /**
     * Logout from current devices
     *
     * Invalidate token, that is used by the request to authenticate the current user.
     *
     * @Since("1.0.0")
     * @OA\Delete(
     *     path="/auth/logout",
     *     tags={"auth"},
     *     security={{ "bearerAuth":{} }},
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="401", description="Unauthenticated"),
     * )
     *
     * @return string[]
     */
    public function logout()
    {
        /** @var User $user */
        $user = auth()->user();

        /** @var PersonalAccessToken $token */
        $token = $user->currentAccessToken();

        $token->delete();

        return [
            'message' => 'You have successfully logged out and the token was successfully deleted',
        ];
    }

    /**
     * Logout from all devices
     *
     * This invalidates all tokens, that are assigned to the currently authenticated user.
     *
     * @Since("1.0.0")
     * @OA\Delete(
     *     path="/auth/logout/all",
     *     tags={"auth"},
     *     security={{ "bearerAuth":{} }},
     *     @OA\Response(response="200", description="Success"),
     *     @OA\Response(response="401", description="Unauthenticated"),
     * )
     *
     * @return string[]
     */
    public function reset()
    {
        /** @var User $user */
        $user = auth()->user();

        $user->tokens()->delete();

        return [
            'message' => 'You have successfully logged out and all tokens were successfully deleted',
        ];
    }

    /**
     * Currently authenticated user resource
     *
     * @Since("1.0.0")
     * @OA\Get(
     *     path="/me",
     *     tags={"auth", "users"},
     *     security={{ "bearerAuth":{} }},
     *     @OA\Response(
     *          response="200",
     *          description="Success",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/UserResource"),
     *          )
     *     ),
     *     @OA\Response(response="401", description="Unauthenticated"),
     * )
     *
     * @return UserResource
     */
    public function me()
    {
        return new UserResource(auth()->user());
    }
}
