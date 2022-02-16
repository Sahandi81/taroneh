<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AmazingOffers;
use App\Models\Discount;
use App\Models\Product;
use Carbon\Carbon;
use MongoDB\BSON\UTCDateTime;

class DiscountController extends Controller
{
	public function store()
	{
		$fields = request()->validate([
			'id'		=> 'string|regex:/^[a-f\d]{24}$/',
			'code'		=> 'numeric',
			'percent'	=> 'required|numeric',
			'expire'	=> "required|numeric|min:".time(),
		]);
		if (!isset($fields['id']) && !isset($fields['code']))
			return response(['success' => false, 'msg' => 'CODE_OR_ID_REQUIRED'],422);

		if (isset($fields['code'])){
			$product = Product::where('code', (int)$fields['code'])->first();

			if (!$product)
				return response(['success' => false, 'msg' => 'PRODUCT_DOESNT_FOUND', __LINE__],422);

			$fields['id'] = $product->id;
		}
		if ( ! Product::find($fields['id']))
			return response(['success' => false, 'msg' => 'PRODUCT_DOESNT_FOUND', __LINE__],422);

		try {
			
			$expire = Carbon::createFromTimestamp($fields['expire']);
			$expire = new UTCDateTime($expire);

			Discount::create([
				'product_id' 	=> $fields['id'],
				'percent'		=> $fields['percent'],
				'expire'		=> $expire,
				'active'		=> true,
			]);
			return response(['success' => true, 'msg' => 'OFFER_ADD_SUCCESSFULLY']);
		} catch (\PDOException $e){
			return response(['success' => false, 'msg' => 'CALL_BACKEND_DEVELOPER' , [
				'line' => $e->getLine(),
				'file' => $e->getFile()
			]],422);
		}
    }

	public function update()
	{
		$fields = request()->validate([
			'id'		=> 'required|string|regex:/^[a-f\d]{24}$/',
			'percent'	=> 'required|numeric',
			'expire'	=> "numeric|min:".time(),
			'active'	=> "bool", # timestamp
		]);
		$offer = Discount::find($fields['id']);

		if ($offer->count() <= 0)
			return response(['success' => false, 'msg' => 'OFFER_DOESNT_FOUND'],422);

		try {
			if (isset($fields['expire'])){
				$expire = Carbon::createFromTimestamp($fields['expire']);
				$expire = new UTCDateTime($expire);
				$fields['expire'] = $expire;
			}

			$offer->update($fields);
			return response([
				'success' => true,
				'msg' => 'EDIT_SUCCESSFULLY',
				'offer' => Discount::find($fields['id'])
			]);

		} catch (\PDOException $e){

			return response(['success' => false, 'msg' => 'CALL_BACKEND_DEVELOPER' , [
				'line' => $e->getLine(),
				'file' => $e->getFile()
			]],422);
		}
	}

	public function destroy()
	{
		$fields = \request()->validate([
			'id' => 'required|string|regex:/^[a-f\d]{24}$/',
		]);

		$user = Discount::find($fields['id']);
		if (!$user) {
			if (Discount::withTrashed()->find($fields['id'])) {
				return response(['success' => false, 'msg' => 'DISCOUNT_ALREADY_DELETED'], 400);
			}
			return response(['success' => false, 'msg' => 'DISCOUNT_NOT_FOUND'], 400);
		}
		$user->delete();

		return response(['success' => true, 'msg' => 'SUCCESSFULLY']);
	}
}
