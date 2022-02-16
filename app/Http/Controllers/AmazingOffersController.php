<?php

namespace App\Http\Controllers;

use App\Models\AmazingOffers;
use DateTime;

class AmazingOffersController extends Controller
{

    public function index()
    {
        $amazingOffers = AmazingOffers::where('expire', '>', new DateTime())
            ->where('active', true)
            ->first();
        if (!$amazingOffers) return response([
            'success'   => false,
            'msg'       => 'NO_OFFER_FOUND',
        ], 404);

        $products = $amazingOffers->products()
            ->get(['title','price','rank','unit_measurement'])
            ->all();
        return [
            'success' => true,
            'products' => $products,
            'expire' => $amazingOffers->expire,
            'percent' => $amazingOffers->percent,
        ];
    }

}
