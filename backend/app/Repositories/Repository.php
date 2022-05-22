<?php 

namespace App\Repositories;

use App\Models\Prices;
use Illuminate\Support\Facades\DB;

class Repository 
{
    protected $model;
    
    public function __construct(Prices $model)
    {
        $this->model = $model;
    }

    public function getPrices(array $productCodes = [], ?string $accountId = null) : array
    {
        $query = DB::table('prices')->select([
            'products.sku as product',
            DB::raw('min(prices.value) as price')
        ])
        ->join('products', 'products.id', '=', 'prices.product_id')
        ->leftJoin('accounts', 'accounts.id', '=', 'prices.account_id')
        ->whereIn('products.sku', $productCodes)
        ->groupBy('products.sku')
        ->orderBy('products.sku');

        if(is_null($accountId))
            $query->whereNull('accounts.external_reference');
        else $query->where('accounts.external_reference', '=', $accountId);

        return $query->get()->toArray();
    }
}