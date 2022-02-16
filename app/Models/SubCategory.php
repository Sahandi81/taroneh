<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Jenssegers\Mongodb\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;
use Jenssegers\Mongodb\Auth\User as Authenticatable;

class SubCategory extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $connection = 'mongodb';
    protected $collection = 'sub_categories';
    protected $primaryKey = '_id';

    protected $fillable = [
        'name',
        'category_id',
    ];

    protected $hidden = [
      'active',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

}
