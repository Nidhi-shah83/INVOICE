<?php

namespace App\Http\Controllers;

use App\Services\SettingService;

class SettingController extends ModuleResourceController
{
    public function __construct(SettingService $service)
    {
        parent::__construct($service);
    }
}
