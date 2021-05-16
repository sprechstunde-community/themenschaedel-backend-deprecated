<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function getDomain(): string {
        $domain = parse_url(config('app.url'), PHP_URL_HOST) ?? 'localhost';
        return 'api.' . $domain;
    }
}
