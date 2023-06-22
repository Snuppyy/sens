<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class EnforceProfileCompletion
{
    /**
     * Redirect to profile page unless requred data is filled.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!Auth::user()->complete) {
            return redirect('/register/success');
        }

        return $next($request);
    }
}
