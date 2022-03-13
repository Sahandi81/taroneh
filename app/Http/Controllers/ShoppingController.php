<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\PurchaseHistory;
use App\Models\User;
use App\Traits\ProductBuilder;
use Illuminate\Support\Facades\Response;

class ShoppingController extends Controller
{
	use  ProductBuilder;

    public function index()
    {
		$fields = \request()->validate([
			'products' 					=> 'required|array|min:1',
			'products.*' 				=> 'required|array|min:1',
			'products.*.id'				=> 'required|string|regex:/^[a-f\d]{24}$/',
			'products.*.type.package'	=> 'required|numeric',
			'products.*.type.number'	=> 'required|numeric',
			'details' 					=> 'required|array|min:2',
			'details.*' 				=> 'required|distinct|min:1',
			'details.address'			=> 'required|numeric|min:1',

		]);
		$user = request()->user();
		$products = [];
		$payment = 0;
		$allOffers = 0;
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
					$payment += ($packagePrice * $product['type']['number']);
					if ($products[$key]['offer']){
						$offer = ($payment / 100) * $products[$key]['offer']['percent'];
						$allOffers += $offer;
					}
					break;
				}
				return response(['success' => false, 'msg' => 'PRODUCT_TYPE_NOT_FOUND'], 404);
			}
		}

		$address = User::find($user->id)->where('address.id', '=', $fields['details']['address'])->first(['address']);
		if (!$address) return response(['success' => false, 'msg' => 'BAD_DETAILS'], 401);
		foreach ($address->toArray() as $items){
			if (!is_array($items)) continue;
			foreach ($items as $item) {
				if ( $item['id'] === $fields['details']['address']){
					$address = $item['address'];
				}
			}
		}

		$trackingCode = rand(999999, 99999999);
		try {

			$result = PurchaseHistory::create([
				'code' 			=> $trackingCode,
				'real'			=> $payment,
				'user_id' 		=> $user->id,
				'products'		=> $products,
				'offer'			=> $allOffers,
				'final_price'	=> $payment - $allOffers,
				'address'		=> $address,
				'delivery_time'	=> $fields['details']['delivery_time'],
				'status'		=> 'unpaid',
				'condition'		=> 'not_verified',
			]);
			if ($result)
				return [
					'success' 		=> true,
					'address' 		=> $address,
					'delivery_time' => $fields['details']['delivery_time'],
					'real' 			=> $payment,
					'offer' 		=> $allOffers,
					'final_price' 	=> $payment - $allOffers,
					'products' 		=> $products,
					'details'		=> $result,
				];
			else
				return response(['success'=>false, 'msg' => 'UNKNOWNS_ERROR'], 500);

		} catch (\Exception $exception){
			return response(['success'=>false, 'msg' => 'TRY_AGAIN'], 503);
		}
    }

	public function show($id)
	{
		$order = PurchaseHistory::with('users')->find($id);
		if (!$order){
			return Response::json(['success' => false, 'msg' => 'NOT_FOUND']);
		}
		return Response::json(['success' => true, 'details' => $order]);
	}

    public function shoppingHistory()
	{
		$user = request()->user();
		$history = User::find($user->id)->history()->get();
		return $history;
	}

	public function listOrders()
	{
		$fields = \request()->validate([
			'per_page'  => 'numeric|regex:/^^(?!(\d)\1{9})\d{1,3}$/',
			'page'      => 'numeric|regex:/^^(?!(\d)\1{9})\d{1,4}$/',
		]);
		$per_page = (int)($fields['per_page'] ?? 15);
		$list = PurchaseHistory::with('users')->where('deleted_at', null)
			->orderByDesc('created_at')
			->paginate($per_page)
		;

		return ['success' => true, 'details' => $list];
	}

}
