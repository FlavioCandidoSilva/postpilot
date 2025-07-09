<?php

namespace App\Http\Middleware;

use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use App\Providers\RouteServiceProvider;
use Closure;


class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response|RedirectResponse
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                if ($request->hasHeader('X-Inertia')) {
                    return Inertia::location(route('dashboard'));
                }

                return redirect(RouteServiceProvider::HOME);
            }
        }

        return $next($request);
    }
}

