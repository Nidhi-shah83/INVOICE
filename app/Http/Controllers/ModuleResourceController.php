<?php

namespace App\Http\Controllers;

use App\Services\ModuleService;
use Illuminate\Http\Request;
use Illuminate\View\View;

abstract class ModuleResourceController extends Controller
{
    public function __construct(protected ModuleService $service)
    {
        $this->middleware('auth:sanctum');
    }

    protected function render(string $action, ?string $primaryAction = null): View
    {
        $payload = $this->service->actionPayload($action);
        $payload['primaryAction'] = $primaryAction;

        return view('modules.placeholder', $payload);
    }

    public function index(): View
    {
        return $this->render('index', $this->service->primaryActionLabel());
    }

    public function create(): View
    {
        return $this->render('create');
    }

    public function store(Request $request): View
    {
        return $this->render('store');
    }

    public function show(string $id): View
    {
        return $this->render('show');
    }

    public function edit(string $id): View
    {
        return $this->render('edit');
    }

    public function update(Request $request, string $id): View
    {
        return $this->render('update');
    }

    public function destroy(string $id): View
    {
        return $this->render('destroy');
    }
}
