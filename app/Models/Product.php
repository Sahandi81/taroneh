<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Jenssegers\Mongodb\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;
use Jenssegers\Mongodb\Auth\User as Authenticatable;

/**
 * @method static whereIn(string $string, array $subCategoriesID)
 * @method static find(mixed $id)
 */
class Product extends Authenticatable
{
	use HasFactory, Notifiable, HasApiTokens, SoftDeletes;


    protected $connection = 'mongodb';
    protected $collection = 'products';
    protected $primaryKey = '_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'title',
        'code',
        'quality',
        'unit_measurement',
        'amount',
        'rank',
        'scores',
        'buyers',
        'types',
        'price',
        'short_explanation',
        'Description',
        'attributes',
        'photos',
        'sub_category_id',
    ];


    protected $hidden = [
        'sub_category_id',
    ];

	/**
     * The attributes that should be cast.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany|\Jenssegers\Mongodb\Relations\HasMany
     * @var array
     */
    public function offers()
    {
        $details = $this->hasMany(Discount::class);

        $details->getQuery()
            ->whereBetween('expire',
                array(Carbon::createFromDate(), # Date now
                    Carbon::createFromDate(3000))); # Maximum expire time

        return $details;
    }

    public function subCategory()
    {
        return $this->belongsTo(SubCategory::class);
    }

	public function specialSale()
	{
		$details = $this->hasMany(SpecialSale::class);

		$details->getQuery()
			->whereBetween('expire',
				array(Carbon::createFromDate(), # Date now
					Carbon::createFromDate(3000))); # Maximum expire time

		return $details;
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

}
