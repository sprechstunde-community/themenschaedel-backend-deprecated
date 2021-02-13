<?php

namespace App\Http\Controllers\Api;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;

abstract class AbstractApiController extends Controller
{
    protected function getPerPageParameter(Request $request): int {
        $perPage = (int) $request->input('per_page');

        return ($perPage < 0) ? 0 : $perPage;
    }
}
