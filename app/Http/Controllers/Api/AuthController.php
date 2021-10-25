<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends AbstractApiController
{
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

    public function reset()
    {
        /** @var User $user */
        $user = auth()->user();

        $user->tokens()->delete();

        return [
            'message' => 'You have successfully logged out and all tokens were successfully deleted',
        ];
    }

    public function me()
    {
        return new UserResource(auth()->user());
    }
}
