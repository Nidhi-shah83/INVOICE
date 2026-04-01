<?php

namespace App\Http\Controllers;

use App\Services\AiAssistantService;

class AiAssistantController extends ModuleResourceController
{
    public function __construct(AiAssistantService $service)
    {
        parent::__construct($service);
    }
}
