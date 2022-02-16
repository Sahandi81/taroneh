<?php

namespace App\Http\Controllers;

use function request;

class errorController extends Controller
{

    public function error()
    {
        return response([
            'success' => false,
            'msg' => request()->input('msg'),
        ], 403);
    }

}
