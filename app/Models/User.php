<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Jenssegers\Mongodb\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;
use Jenssegers\Mongodb\Auth\User as Authenticatable;

/**
 * @method static find($id)
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens, SoftDeletes;

    protected $connection = 'mongodb' ;
    protected $collection = 'users';
    protected $primaryKey = '_id';



    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'f_name',
        'l_name',
        'phone',
        'email',
        'phone',
        'address',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'created_at',
        'updated_at',
        'verification_code',
        'code_expire',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * @var mixed inputs
     */
    private $phone;
    private $f_name;
    private $l_name;


    public function profile()
    {
        return $this->hasOne(UserProfile::class);
    }
    // WishList !clean code :)
    public function customWishList()
    {
        return $this->hasOne(WishList::class);
    }

    public function wishList(string $outPut = 'Model')
    {
        $wishList = $this->hasOne(WishList::class);
        if ($wishList->first() === null) return false;
        $wishList = $wishList->first()->toArray();
        $products = [];
        foreach ($wishList['products'] as $key => $product){
            $details = ($outPut === 'array') ?
                Product::find($product)->toArray() :
                Product::find($product);
            if ($details === null){
                $products[] = (object)[
                    '_id' => $product,
                    'title'=>'DELETED_PRODUCT'
                ];
                continue;
            }
            $products[] = $details ;
        }
        return $products;
    }

    // Cart !clean code :)
    public function customCart()
    {
        return $this->hasOne(Cart::class);
    }

    public function cart(string $outPut = 'Model')
    {
        $wishList = $this->hasMany(Cart::class);
        if ($wishList === null) return false;
        $wishList = $wishList->get()->toArray();
        $products = [];
        foreach ($wishList as $item) {
            foreach ($item['products'] as $key => $product){
                $details = ($outPut === 'array') ?
                    Product::find($product)->toArray() :
                    Product::find($product);
                if ($details === null){
                    $products[] = (object)[
                        '_id' => $product,
                        'title'=>'DELETED_PRODUCT'
                    ];
                    continue;
                }
                $products[] = $details ;
            }
        }
        return $products;
    }

	public function history()
	{
		return $this->hasMany(PurchaseHistory::class);
    }
}
