<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\WishList;
use App\Traits\WishListHelper;
use Illuminate\Http\Request;
use App\Traits\ProductBuilder;

class WishListController extends Controller
{

    use ProductBuilder, WishListHelper;

    /**
     * Display a listing of the resource.
     *
     * @return array
     */
    public function index()
    {
        $wishList = $this->getWishList();
        $response = $this->getProductDetails($wishList);
        return [
            'success' => true,
            'list' => $response,
        ];
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     */
    public function store(): array
    {
        $fields = \request()->validate([
           'product_id' => 'required|string|regex:/^[a-f\d]{24}$/'
        ]);
        $wishList = $this->getWishList('array');
        foreach ($wishList as $product) {
            if (in_array($fields['product_id'], (array)$product)) return ['success' => false, 'msg' => 'ALREADY_EXIST'];
        }

        $list = User::find(\request()->user()->id)->customWishList()->first();
        $products = $list->products;
        $products[] = $fields['product_id'];
        if ($list->update(['products' => $products])){
            return ['success' => true, 'msg' => 'PRODUCT_ADDED'];
        }else{
            return ['success' => false, 'msg' => 'unknown Error'];
        }

    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\WishList $wishList
     * @return \Illuminate\Http\Response
     */
    public function show(WishList $wishList)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\WishList $wishList
     * @return \Illuminate\Http\Response
     */
    public function edit(WishList $wishList)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\WishList $wishList
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, WishList $wishList)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\WishList $wishList
     * @return \Illuminate\Http\Response
     */
    public function destroy(WishList $wishList)
    {
        //
    }
}
