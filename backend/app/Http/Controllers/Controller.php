<?php

namespace App\Http\Controllers;

use App\Services\Service;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $service;

    public function __construct(Service $service)
    {
        $this->service = $service;
    }

    public function price(Request $request)
    {
        $productCodes = $request->input('productCodes', []);
        $accountId = $request->input('accountId', null);
        $price = $this->service->getPrices($productCodes, $accountId);

        return ['data' => $price];
    }
}
