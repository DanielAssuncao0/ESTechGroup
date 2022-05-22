<?php 

namespace App\Services;

use App\Repositories\Repository;

class Service 
{
    protected $repository;
    
    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    public function getPrices(array $productCodes = [], ?string $accountId = null) : array
    {
        if(empty($productCodes))
            return []; // Or throw exception with specific message

        //Get Price from API
        $result = $this->apiGetPrices($productCodes, $accountId);
        if(!empty($result)) 
            return $result;

        //Get price from database
        $result = $this->repository->getPrices($productCodes, $accountId);
        if(!empty($result))
            return $result;

        return [];
    }

    private function apiGetPrices(array $productCodes = [], ?string $accountId = null) : array
    {
        $content = file_get_contents(database_path() . "/resources/live_prices.json");
        if($content === false) return null;

        $data = json_decode($content, true);
        if(is_null($data) || empty($data)) return null;

        $products = [];
        foreach ($data as $value) 
        {
            $sku = $value['sku'];
            //Skip logic if code doesn't exist in current iteration
            if(!in_array($sku, $productCodes))
                continue;

            //Skip logic if account is defined but doesn't exist in current iteration
            if(isset($accountId) && (!isset($value['account']) || $value['account'] !== $accountId))
                continue;                

            if(!isset($products[$sku]) || $products[$sku]['price'] > $value['price'])
                $products[$sku] = [
                    'product' => $sku, 
                    'price' => $value['price']
                ];
        }

        //Make response shorter
        return array_values($products);
    }
}