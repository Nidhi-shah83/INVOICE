<?php

namespace App\Http\Controllers;

use App\Services\OrderService;

class OrderController extends ModuleResourceController
{
    public function __construct(OrderService $service)
    {
        parent::__construct($service);
    }
}
