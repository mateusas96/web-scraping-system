<?php

namespace App\Http\Middleware;

use Closure;

class CheckIfAdmin
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
        if ($request->user() !== null) {
            if (!!auth()->user()->is_admin === false) {
                return redirect()->to('/403');
            }
        } else {
            \Auth::logout();

            $message = 'Please login';
            return redirect()->route('login')->withMessage($message);
        }
        return $next($request);
    }
}
