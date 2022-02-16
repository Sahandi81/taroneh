<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SpecialSale;
use App\Models\Product;
use Carbon\Carbon;
use MongoDB\BSON\UTCDateTime;

class SpecialSaleController extends Controller
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

			SpecialSale::create([
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

}
