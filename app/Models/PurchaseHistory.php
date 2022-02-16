<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Jenssegers\Mongodb\Auth\User as Authenticatable;

class PurchaseHistory extends Authenticatable
{
	use HasFactory, Notifiable, HasApiTokens;

	protected $connection = 'mongodb';
	protected $collection = 'purchase_histories';
	protected $primaryKey = '_id';


	protected $fillable = [
		'code',
		'real',
		'user_id',
		'products',
		'offer',
		'final_price',
		'address',
		'delivery_time',
		'status',
		'condition',
	];
}
