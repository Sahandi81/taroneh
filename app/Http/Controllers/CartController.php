<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
use App\Traits\ProductBuilder;
use Illuminate\Http\Request;
use App\Traits\CartHelper;

class CartController extends Controller
{
    use ProductBuilder, CartHelper;

	/**
	 * Display a listing of the resource.
	 *
	 */
    public function index()
    {
		$response = $this->getCart();
//        $response = $this->getProductDetails($wishList);
        return [
            'success' => true,
            'list' => $response[0],
        ];
    }

    public function store()
    {
		$fields = \request()->validate([
			'products' 					=> 'required|array|min:1',
			'products.*' 				=> 'required|array|min:1',
			'products.*.id'				=> 'required|string|regex:/^[a-f\d]{24}$/',
			'products.*.type.package'	=> 'required|numeric',
			'products.*.type.number'	=> 'required|numeric',

		]);
		$user = request()->user();
		$products = [];
		foreach ($fields['products'] as $key => $product){
			$ModelProduct = Product::find($product['id']);
			if (!$ModelProduct) {
				$products[$key] = ['UNKNOWN_PRODUCT'];
				continue;
			}
			$products[$key] = $product;
			$products[$key]['offer'] = $this->getOffer($ModelProduct);
			$type = $product['type']['name'];
			foreach ($ModelProduct->types[0] as $item) {
				if (in_array($type, $item)){
					$package 		= $product['type']['package'];
					$packagePrice 	= $item['package'][$package];
					$products[$key]['type']['price'] = $packagePrice;
					break;
				}
				return response(['success' => false, 'msg' => 'PRODUCT_TYPE_NOT_FOUND'], 404);
			}
		}

		try {

			$result = Cart::create([
				'user_id' 		=> $user->id,
				'products'		=> $products,
			]);
			if ($result)
				return [
					'success' 		=> true,
					'products' 		=> $products
				];
			else
				return response(['success'=>false, 'msg' => 'UNKNOWNS_ERROR'], 500);

		} catch (\Exception $exception){
			return response(['success'=>false, 'msg' => 'TRY_AGAIN'], 503);
		}
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Cart  $cart
     * @return \Illuminate\Http\Response
     */
    public function show(Cart $cart)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Cart  $cart
     * @return \Illuminate\Http\Response
     */
    public function edit(Cart $cart)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Cart  $cart
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Cart $cart)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Cart  $cart
     * @return \Illuminate\Http\Response
     */
    public function destroy(Cart $cart)
    {
        //
    }
}
