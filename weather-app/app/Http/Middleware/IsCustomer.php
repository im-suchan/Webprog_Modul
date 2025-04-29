<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsCustomer
{
  /**
   * Handle an incoming request.
   *
   * Allow only users with role 2 (Customer).
   */
  public function handle(Request $request, Closure $next): Response
  {
    if (auth()->check() && auth()->user()->role == 0) {
      return $next($request);
    }

    return redirect('/auth/redirect')->with(
      'msgError',
      'You don’t have permission to access this page. Please login as a customer.'
    );
  }
}
