<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplyUserMailSettings
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()) {
            apply_user_mail_config((int) $request->user()->id);
        }

        return $next($request);
    }
}
