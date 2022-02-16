<?php


namespace App\Traits;



use App\Models\Cart;
use App\Models\User;

trait CartHelper
{

    public function getCart(string $outPut = 'Model')
    {
        $user = \request()->user();
        $wishList = User::find($user->id)->cart($outPut);
        if ($wishList === false){
            $wishList = new Cart();
            $wishList->user_id = $user->id;
            $wishList->products = [];
            $wishList->save();
            $wishList = User::find($user->id)->cart($outPut);
        }
        return $wishList;
    }

}
