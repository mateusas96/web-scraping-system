<?php

namespace App\Http\Middleware;

use Closure;
use Auth;

class CheckIfUserIsDisabled
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user = Auth::user();

        if($user && $user->is_disabled == 1){
            Auth::logout();

            $message = 'Your account has been suspended. Please contact administration for more info.';
            return redirect()->route('login')->withMessage($message);
        }

        return $next($request);
    }
}
