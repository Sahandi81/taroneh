<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Jenssegers\Mongodb\Auth\User as Authenticatable;

/**
 * @method static create(array $array)
 */
class Cart extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $connection = 'mongodb';
    protected $collection = 'carts';
    protected $primaryKey = '_id';

    protected $fillable = [
        'user_id',
        'products',
    ];

    protected $hidden = [
        'category_id',
        'active',
    ];

    /** @var mixed */
    private $user_id;
    /* @var array|mixed */
    private $products;

}
