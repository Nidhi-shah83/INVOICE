<?php

namespace App\Http\Controllers;

use App\Services\ReportService;

class ReportController extends ModuleResourceController
{
    public function __construct(ReportService $service)
    {
        parent::__construct($service);
    }
}
