<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AmazingOffers;
use App\Models\Product;
use App\Models\SubCategory;

class ProductController extends Controller
{

	public function store()
	{

		$fields = request()->validate([
			'title' 				=> 'required|string|max:255',
			'code' 					=> 'required|numeric|unique:products',
			'quality' 				=> 'required|numeric',
			'unit_measurement' 		=> 'required|string|max:50',
			'amount' 				=> 'numeric',
			'types'					=> 'required|array|min:1',
			'types.*.*.name'		=> 'required|string|max:255',
			'types.*.*.package'		=> 'required|array|min:2',
			'amazing_offer'			=> 'required|bool',
			'types.*.*.package.inventory' => 'required|numeric',
			'short_explanation' 	=> 'required|string|max:1500',
			'sub_category_id' 		=> 'required|string|regex:/^[a-f\d]{24}$/',
			'Description' 			=> 'required|string|max:4500',
			'attributes'			=> 'array|min:1|max:20',
			'attributes.*'			=> 'string|max:255',
			'photos'				=> 'array|min:1|max:20',
			'photos.*'				=> 'string|max:255',
		]);

		if (SubCategory::where('_id', $fields['sub_category_id'])->exists()){
			if (SubCategory::find($fields['sub_category_id'])->category()->exists()){

				# update amazing offer value to _id
				if ($fields['amazing_offer']) {
					$amazingOffer = AmazingOffers::all()->first();
					if (!$amazingOffer) return response(['success' => false, 'AMAZING_OFFER_DOESNT_FOUND'],422);
					$fields['amazing_offer'] = $amazingOffer->id;
				}

				$fields['photos'] = PhotoController::movePhotos($fields['photos'], $fields['code']);

				try {
					$result = Product::create($fields);
					return response(['success' => true, 'msg' => 'PRODUCT_ADD_SUCCESSFULLY', 'details' => $result]);
				} catch (\PDOException $e){
					return response(['success' => false, 'msg' => 'CALL_BACKEND_DEVELOPER' , [
						'line' => $e->getLine(),
						'file' => $e->getFile()
					]],422);
				}
			}
		}
		return response(['success' => false, 'msg' => 'WRONG_SUBCATEGORY_ID'],422);
	}

	public function update()
	{
		$fields = request()->validate([
			'id'					=> 'required|string|regex:/^[a-f\d]{24}$/',
			'title' 				=> 'string|max:255',
			'quality' 				=> 'numeric',
			'amount' 				=> 'numeric',
			'types'					=> 'array|min:1',
			'types.*.*.name'		=> 'string|max:255',
			'types.*.*.package'		=> 'array|min:2',
			'amazing_offer'			=> 'bool',
			'types.*.*.package.inventory' => 'numeric',
			'short_explanation' 	=> 'string|max:1500',
			'sub_category_id' 		=> 'string|regex:/^[a-f\d]{24}$/',
			'Description' 			=> 'string|max:4500',
			'attributes'			=> 'array|min:1|max:20',
			'attributes.*'			=> 'string|max:255',
			'photos'				=> 'array|min:1|max:20',
			'photos.*'				=> 'string|max:255',
		]);

		$product = Product::find($fields['id']);
		if (!$product->exists())
			return response(['success' => false, 'msg' => 'PRODUCT_NOT_FOUND'], 404);

		if (isset($fields['sub_category_id'])) {
			if ( ! SubCategory::where('_id', $fields['sub_category_id'])->exists()) {
				return response(['success' => false, 'msg' => 'WRONG_SUBCATEGORY_ID'], 422);
			}
		}

		# update amazing offer value to _id
		if (isset($fields['amazing_offer'])) {
			if ($fields['amazing_offer']) {
				$amazingOffer = AmazingOffers::all()->first();
				if (!$amazingOffer) return response(['success' => false, 'AMAZING_OFFER_DOESNT_FOUND'], 422);
				$fields['amazing_offer'] = $amazingOffer->id;
			}
		}

        if (isset($fields['photos']))
            $fields['photos'] = PhotoController::movePhotos($fields['photos'], $product->code, $product->photos);


        try {
			$product->update($fields);
			return response([
				'success' => true,
				'msg' => 'EDIT_SUCCESSFULLY',
				'product' => Product::find($fields['id']),
			]);

		} catch (\PDOException $e) {
			return response(['success' => false, 'msg' => 'CALL_BACKEND_DEVELOPER', [
				'line' => $e->getLine(),
				'file' => $e->getFile()
			]], 422);
		}
	}

	public function destroy()
	{
		{
			$fields = \request()->validate([
				'id' 		=> 'required|string|regex:/^[a-f\d]{24}$/',
			]);

			$user = Product::find($fields['id']);
			if ( ! $user){
				if (Product::withTrashed()->find($fields['id'])){
					return response(['success' => false, 'msg' => 'PRODUCT_ALREADY_DELETED'], 400);
				}
				return response(['success' => false, 'msg' => 'PRODUCT_NOT_FOUND'], 400);
			}
			$user->delete();

			return response(['success' => true, 'msg' => 'SUCCESSFULLY']);
		}
	}
}
