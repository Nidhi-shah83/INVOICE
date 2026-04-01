<?php

namespace App\Http\Controllers;

use App\Services\ClientService;

class ClientController extends ModuleResourceController
{
    public function __construct(ClientService $service)
    {
        parent::__construct($service);
    }
}
