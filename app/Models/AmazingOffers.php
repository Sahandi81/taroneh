<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Jenssegers\Mongodb\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;
use Jenssegers\Mongodb\Auth\User as Authenticatable;

class AmazingOffers extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens, SoftDeletes;

    protected $connection = 'mongodb';
    protected $collection = 'amazing_offers';
    protected $primaryKey = '_id';

    protected $fillable = [
        'expire',
        'percent',
    ];

    public function products()
    {
        return $this->hasMany(Product::class, 'amazing_offer');
    }

}

