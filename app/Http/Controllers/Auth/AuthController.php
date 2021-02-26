<?php

namespace App\Http\Controllers\Auth;

use App\Models\OAuthCredentials;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Config\Repository;
use Laravel\Socialite\Contracts\User as OAuthUser;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse;

class AuthController
{

    private Repository $config;
    private OAuthCredentials $credentialsRepository;
    private User $userRepository;

    /**
     * AuthController constructor.
     *
     * @param Repository $config
     * @param OAuthCredentials $credentialsRepository
     * @param User $userRepository
     */
    public function __construct(Repository $config,  OAuthCredentials $credentialsRepository, User $userRepository)
    {
        $this->config = $config;
        $this->credentialsRepository = $credentialsRepository;
        $this->userRepository = $userRepository;
    }

    public function auth(string $provider): RedirectResponse
    {
        return Socialite::driver($provider)->redirect();
    }

    public function callback(string $provider)
    {
        // request information from auth provider
        /** @var OAuthUser $oAuth */
        $oAuth = Socialite::driver($provider)->stateless()->user();

        // load existing credentials from database
        /** @var OAuthCredentials $credentials */
        $credentials = $this->credentialsRepository->firstOrNew([
            'provider' => $provider,
            'provider_id' => $oAuth->getId(),
        ]);

        // update (or insert) oauth credentials und user information
        $credentials->forceFill([
            'token' => $oAuth->token,
            'refresh_token' => $oAuth->refreshToken ?? null,
            'expires_at' => is_int($oAuth->expiresIn) ? Carbon::now()->addSeconds($oAuth->expiresIn) : null,
            'user_information' => json_encode((array) $oAuth->user ?? null),
        ]);

        // for temporary testing TODO assign actual user model
        $credentials->user()->associate(User::all()->random());

        $credentials->save();
    }
}
