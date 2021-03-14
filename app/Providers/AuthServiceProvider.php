<?php

namespace App\Providers;

use App\Models\Host;
use App\Models\Subtopic;
use App\Models\Topic;
use App\Policies\BasicPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',

        // enable basic community policies on how to manage these models
        Host::class => BasicPolicy::class,
        Subtopic::class => BasicPolicy::class,
        Topic::class => BasicPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        //
    }
}
