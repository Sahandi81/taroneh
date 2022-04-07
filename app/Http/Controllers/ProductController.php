<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\SubCategory;
use App\Traits\Paginator;
use App\Traits\ProductBuilder;
use DateTime;


class ProductController extends Controller
{
    use Paginator, ProductBuilder;

    /**
     * Display a listing of the resource.
     *
     * @return Product[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Http\Response|\Illuminate\Pagination\LengthAwarePaginator
     * @throws \Exception
     */
    public function index()
    {


		$fields = \request()->validate([
            'per_page'   => 'numeric|regex:/^^(?!(\d)\1{9})\d{1,3}$/',
            'page'      => 'numeric|regex:/^^(?!(\d)\1{9})\d{1,4}$/',
            'category'  => 'string|regex:/^[a-f\d]{24}$/',
            's_category'=> 'string|regex:/^[a-f\d]{24}$/',
        ]);

        $prepage    = $fields['per_page'] ?? 5;
        $page       = $fields['page'] ?? 1;

        if (isset($fields['category'])) {
            $category = Category::find($fields['category']);
            if (!$category)
                return response(['success' => false, 'msg' => 'CATEGORY_NOT_FOUND'], 404);
            $subCategories = $category->subCategory()->get(['_id']);
            if (!$subCategories)
                return response(['success' => false, 'msg' => 'SUBCATEGORY_NOT_FOUND'], 404);
            $subCategories = $subCategories->toArray();
            $subCategoriesID = [];
            foreach ($subCategories as $subCategory) {
                $subCategoriesID[] = $subCategory['_id'];
            }
            $products = Product::whereIn('sub_category_id', $subCategoriesID)
                ->with(['offers','subCategory','specialSale','comments',])
                ->orderByDesc('created_at')
                ->paginate((int)$prepage)
//                ->get(['title', 'price', 'rank', 'unit_measurement', 'created_at'])
//                ->sortByDesc('created_at');
;
        }elseif (isset($fields['s_category'])){

            $subCategory = SubCategory::find($fields['s_category']);
            if (!$subCategory)
                return response(['success' => false, 'msg' => 'SUBCATEGORY_NOT_FOUND'], 404);
            $products = Product::where('sub_category_id', $fields['s_category'])
                ->with(['offers','subCategory','specialSale','comments',])
                ->orderByDesc('created_at')
                ->paginate((int)$prepage)
            ;
            ;
        }else {

			$products = Product::with(['offers', 'subCategory', 'specialSale', 'comments',])
//                ->sortByDesc('created_at')
				->paginate((int)$prepage);
			echo '<pre>';
			var_export('asd');
			die('here');
		}

		# for more options u can do it on vendor\laravel\framework\src\Illuminate\Pagination\ .php
        # Function `toArray`
;
//        $response   = $this->getProductDetails($products);

//        $response   = $this->paginator((array)$response, $prepage, $page);




        return $products;

    }

    public function singleProduct()
    {
        $fields = \request()->validate([
            'id'   => 'string|regex:/^[a-f\d]{24}$/|required',
        ]);

        $product = Product::with(['offers','subCategory','specialSale','comments',])->find($fields['id']);
        return $product;
    }





}
