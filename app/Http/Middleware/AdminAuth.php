<?php

namespace App\Http\Middleware;

use App\Models\Admin\Admin;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class AdminAuth
{
    /**
     * @var string Change it in `errorController.php`
     */
    protected $errorMSG = 'Access denied';

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = \request()->user();
        if (!Admin::find($user->id))
            return Redirect::route('error.handler', ['msg' => $this->errorMSG]);
        return $next($request);
    }
}
