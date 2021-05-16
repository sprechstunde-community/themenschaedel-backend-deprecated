<?php

namespace Tests\Feature\Account;

use App\Providers\RouteServiceProvider;
use Tests\TestCase;

abstract class AccountTestCase extends TestCase
{
    protected function baseUrl(): string
    {
        return 'http://' . RouteServiceProvider::getAccountDomain();
    }
}
