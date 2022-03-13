<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PurchaseHistory;
use App\Traits\Paginator;
use App\Traits\ProductBuilder;

class ShoppingController extends Controller
{
	use Paginator, ProductBuilder;


	public function index()
	{
		$fields = \request()->validate([
			'per_page'  => 'numeric|regex:/^^(?!(\d)\1{9})\d{1,3}$/',
			'page'      => 'numeric|regex:/^^(?!(\d)\1{9})\d{1,4}$/',
			'q'      	=> 'string',
			'id'		=> 'string|regex:/^[a-f\d]{24}$/',
		]);

		# search order
		if (isset($fields['id'])){
			$order = PurchaseHistory::find($fields['id']);
			if ( ! $order)
				return response(['success' => false, 'msg' => 'ORDER_NOT_FOUND']);
			else
				return $order;
		}

		# return all orders
		$prepage    = $fields['per_page'] ?? 5;
		$page       = $fields['page'] ?? 1;

		if (isset($fields['q'])){
			$orders = PurchaseHistory::where('status', $fields['q'])->get()->toArray();
		}else{
			$orders = PurchaseHistory::all()->toArray();
		}

		$orders = array_reverse($orders);
		$orders = $this->paginator((array)$orders, $prepage, $page);
		return $orders;
    }

	public function update()
	{
		$fields = \request()->validate([
			'id'		=> 'required|string|regex:/^[a-f\d]{24}$/',
			'condition'	=> 'required|string',
			'address'	=> 'numeric'
		]);

		$order = PurchaseHistory::find($fields['id']);
		if (!$order)
			return response(['success' => false, 'msg' => 'ORDER_NOT_FOUND']);

		if ($order->status !== 'paid')
			return response(['success' => false, 'msg' => 'ORDER_NOT_PAID']);

		try {

			$order->update($fields);
			return response([
				'success' => true,
				'msg' => 'EDIT_SUCCESSFULLY',
				'user' => PurchaseHistory::find($fields['id'])
			]);

		} catch (\PDOException $e){

			return response(['success' => false, 'msg' => 'CALL_BACKEND_DEVELOPER' , [
				'line' => $e->getLine(),
				'file' => $e->getFile()
			]],422);

		}
	}
}
