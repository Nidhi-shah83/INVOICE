<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Http\Request;

class LogoutController extends Controller
{
    public function __construct(protected AuthService $auth)
    {
        $this->middleware('auth:sanctum');
    }

    public function __invoke(Request $request)
    {
        $this->auth->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
