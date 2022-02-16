<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class ProfileCompleteChecker
{

    /**
     * @var string Change it in `errorController.php`
     */
    protected $errorMSG = 'USER_PROFILE_NOT_COMPLETED';

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {

        $user = \request()->user();
        if (!empty($user->f_name) && !empty($user->l_name && !empty($user->email))){
            return $next($request);
        }
        return Redirect::route('error.handler', ['msg' => $this->errorMSG]);
    }
}
