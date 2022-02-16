<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Traits\Paginator;
use App\Traits\ProductBuilder;
use DateTime;

class SearchController extends Controller
{
    use Paginator, ProductBuilder;


    public function searchProduct()
    {

        $fields = \request()->validate([
            'per_page'   => 'numeric|regex:/^^(?!(\d)\1{9})\d{1,3}$/',
            'page'      => 'numeric|regex:/^^(?!(\d)\1{9})\d{1,4}$/',
            'q'         => 'string|max:50|required',
        ]);


        $prepage    = $fields['per_page'] ?? 5;
        $page       = $fields['page'] ?? 1;

        $search = \request()->input('q');
        if (isset($search)) {
            $response = Product::where('title', 'LIKE', '%' .  $search . '%')->get()->toArray();
            if (empty($response)) return response(['success' => false], 404);
            $response = Product::where('title', 'LIKE', '%' .  $search . '%')->with(['offers','subCategory','specialSale','comments',])
               ->orderByDesc('created_at')
               ->paginate((int)$prepage);

            return response([
                'success' => true,
                'products' => $response
            ]);
        }
        return response(['success' => false], 503);

    }

}
