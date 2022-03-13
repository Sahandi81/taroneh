<?php


namespace App\Traits;


use App\Models\Product;
use App\Models\SpecialSale;
use App\Models\SubCategory;
use Jenssegers\Mongodb\Eloquent\Model;

trait ProductBuilder
{

    private function categories(Model $product): ?array
    {
        $subCategory = $product->subCategory()->first();
        if ($subCategory === null) return null;
        $category = SubCategory::find($subCategory->id)->category()->first();
        return [
            'sub_category'  => $subCategory,
            'category'      => $category,
        ];
    }

    public function getProductDetails($products, $foreignKey = 'id',bool $complete = false, bool $getOffer = true): array
    {
        $response = [];
        foreach ($products as $product){
			if (!isset($product->$foreignKey)){$response[] = $product; continue;};
            $details = Product::find($product->$foreignKey);
			if (!$details) continue;
			$categories = $this->categories($details);
			echo '<pre>';var_export($categories);die('here');

			# when modals change ( true > product \ false > specialSale )
            if ($getOffer)
            	# Product model
            	$offers = $this->getOffer($product);
            else
            	# special model
            	$offers = $product;
            if ($complete){
                $product = $details;
            }
            $response[] = [
                'product_info'  => $product,
                'categories'    => $categories,
                'offer'         => $offers ?? null,
            ];
        }
        return $response;
    }

    public function getOffer(Model $product)
	{
		$specialSale = $product->specialSale()->get()->last();
		return $specialSale ?? $product->offers()->get()->last();
	}

}
