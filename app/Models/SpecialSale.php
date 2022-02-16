<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Jenssegers\Mongodb\Auth\User as Authenticatable;

class SpecialSale extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;


    protected $connection = 'mongodb';
    protected $collection = 'special_sales';
    protected $primaryKey = '_id';

    protected $fillable = [
        'product_id',
		'percent',
		'expire'
    ];

    protected $hidden = [
    	'product_id'
	];


    public function offers()
    {
        $product = new Product();
        return $product->offers();
    }

}
