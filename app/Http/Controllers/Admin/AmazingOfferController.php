<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AmazingOffers;
use Carbon\Carbon;
use MongoDB\BSON\UTCDateTime;

class AmazingOfferController extends Controller
{
	public function store()
	{
		$fields = request()->validate([
			'percent'	=> 'required|numeric',
			'expire'	=> 'required|numeric|min:'. time(),
		]);

		if (AmazingOffers::all()->count() != 0)
			return response(['success' => false, 'msg' => 'AMAZING_OFFERS_ALREADY_EXISTS'],201);
		$fields['active'] = true;
		AmazingOffers::create($fields);

		return response(['success' => true, 'msg' => 'SUCCESSFULLY']);
    }

	public function update()
	{
		$fields = request()->validate([
			'id'		=> 'required|string|regex:/^[a-f\d]{24}$/',
			'percent'	=> 'numeric',
			'expire'	=> 'numeric',
			'active'	=> 'bool', # timestamp
		]);

		if (isset($fields['expire'])){
			$expire = Carbon::createFromTimestamp($fields['expire']);
			$expire = new UTCDateTime($expire);
			$fields['expire'] = $expire;
		}

		$offer = AmazingOffers::find($fields['id']);

		if ( ! $offer)
			return response(['success' => false, 'msg' => 'AMAZING_OFFERS_NOT_FOUND'],404);

		$offer->update($fields);
		return response(['success' => true, 'msg' => 'EDIT_SUCCESSFULLY', 'data' => AmazingOffers::find($fields['id'])]);

    }

	public function destroy()
	{
		$fields = \request()->validate([
			'id' => 'required|string|regex:/^[a-f\d]{24}$/',
		]);

		$user = AmazingOffers::find($fields['id']);
		if (!$user) {
			if (AmazingOffers::withTrashed()->find($fields['id'])) {
				return response(['success' => false, 'msg' => 'AMAZING_OFFER_ALREADY_DELETED'], 400);
			}
			return response(['success' => false, 'msg' => 'AMAZING_OFFER_NOT_FOUND'], 400);
		}
		$user->delete();

		return response(['success' => true, 'msg' => 'SUCCESSFULLY']);
	}

}
