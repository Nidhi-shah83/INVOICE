<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(protected DashboardService $service)
    {
        $this->middleware('auth');
    }

    public function __invoke(): View
    {
        return view('dashboard', $this->service->overview());
    }
}
