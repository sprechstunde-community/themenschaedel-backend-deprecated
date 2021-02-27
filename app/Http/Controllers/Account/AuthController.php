<?php

namespace App\Http\Controllers\Account;

use App\Models\OAuthCredentials;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Config\Repository;
use Illuminate\Support\Facades\Auth;
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

        // ensure, that a user exists and is associated
        if (!$credentials->user()->exists()) {
            /** @var User $user */
            $user = new User();
            $user->forceFill([ // force it so we can set email_verified_at
                'username' => $oAuth->getNickname(),
                'email' => $oAuth->getEmail(),
                'name' => $oAuth->getName(),
                'email_verified_at' => Carbon::now(),
            ]);

            $user->save();
            $credentials->user()->associate($user);
        }

        // store credentials in database
        $credentials->save();

        // login user for this session
        Auth::login($credentials->user);

        //TODO return view to notify user, that login was successfull and this page can now be closed
    }
}
