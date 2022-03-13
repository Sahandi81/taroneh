<?php

namespace App\Http\Controllers;

use App\Models\SpecialSale;
use App\Traits\Paginator;
use App\Traits\ProductBuilder;

class SpecialSaleController extends Controller
{

    use ProductBuilder, Paginator;


    public function index()
    {
        $prepage    = $fields['per_page'] ?? 5;
        $page       = $fields['page'] ?? 1;

        $list 		= SpecialSale::all()->sortDesc();
		$response   = $this->getProductDetails($list, 'product_id', true, false);
        $response   = $this->paginator((array)$response, $prepage, $page);
        return [
            'success' => true,
            'list' => $response,
        ];
    }

}
