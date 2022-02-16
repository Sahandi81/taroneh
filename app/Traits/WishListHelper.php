<?php


namespace App\Traits;


use App\Models\User;
use App\Models\WishList;

trait WishListHelper
{

    public function getWishList(string $outPut = 'Model')
    {
        $user = \request()->user();
        $wishList = User::find($user->id)->wishList($outPut);
        if ($wishList === false){
            $wishList = new WishList();
            $wishList->user_id = $user->id;
            $wishList->products = [];
            $wishList->save();
            $wishList = User::find($user->id)->wishList($outPut);
        }
        return $wishList;
    }

}
