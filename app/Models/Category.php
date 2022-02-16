<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Jenssegers\Mongodb\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;
use Jenssegers\Mongodb\Auth\User as Authenticatable;

/**
 * @method static find(mixed $category)
 */
class Category extends Authenticatable
{
	use HasFactory, Notifiable, HasApiTokens;


	protected $connection = 'mongodb';
    protected $collection = 'categories';
    protected $primaryKey = '_id';

    protected $fillable = [
        'name',
    ];

    protected $hidden = [
        'active',
    ];

    public function subCategory()
    {
        return $this->hasMany(SubCategory::class);
    }

}
