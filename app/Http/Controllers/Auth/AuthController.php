<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Config\Repository;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse;

class AuthController
{

    private Repository $config;

    /**
     * AuthController constructor.
     *
     * @param Repository $config
     */
    public function __construct(Repository $config)
    {
        $this->config = $config;
    }

    public function auth(string $provider): RedirectResponse
    {
        return Socialite::driver($provider)->redirect();
    }

    public function callback(Request $request, string $provider)
    {
        dd((array) Socialite::driver($provider)->stateless()->user());
    }
}
