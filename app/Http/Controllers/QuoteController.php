<?php

namespace App\Http\Controllers;

use App\Services\QuoteService;

class QuoteController extends ModuleResourceController
{
    public function __construct(QuoteService $service)
    {
        parent::__construct($service);
    }
}
