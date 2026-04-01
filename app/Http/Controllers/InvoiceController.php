<?php

namespace App\Http\Controllers;

use App\Services\InvoiceService;

class InvoiceController extends ModuleResourceController
{
    public function __construct(InvoiceService $service)
    {
        parent::__construct($service);
    }
}
