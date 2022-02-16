<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Jenssegers\Mongodb\Auth\User as Authenticatable;

class WishList extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $connection = 'mongodb';
    protected $collection = 'wish_lists';
    protected $primaryKey = '_id';

    protected $casts = [
    # really i dont understand why when add this Attribute the app give me error json_decode :\
//        'products' => 'array'
    ];

    protected $fillable = [
        'user_id',
        'products',
    ];

    protected $hidden = [
        'user_id',
    ];


    /* @var array|mixed */
    private $products;

    /* @var string */
    private $user_id;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
