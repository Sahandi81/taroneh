<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\SubCategory;
use App\Traits\Paginator;

class CategoryController extends Controller
{
    use Paginator;


	public function category()
	{
		$fields = \request()->validate([
			'q'   		=> 'string|regex:/^[a-f\d]{24}$/',
			'per_page'  => 'numeric|regex:/^^(?!(\d)\1{9})\d{1,3}$/',
			'page'      => 'numeric|regex:/^^(?!(\d)\1{9})\d{1,4}$/',
		]);
		$per_page = (int)($fields['per_page'] ?? 15);
		if (isset($fields['q'])){
            $list = Category::with('subCategory')->find($fields['q']);
            if (!$list)
                return ['success' => false, 'msg' => 'CATEGORY_NOT_FOUND'];
        } else {
			$list = Category::with('subCategory')->where('deleted_at', null)
				->orderByDesc('created_at')
				->paginate($per_page)
			;
		}
		return ['success' => true, 'details' => $list];
	}

	public function subCategory()
	{
		$fields = \request()->validate([
			'q'   		=> 'string|regex:/^[a-f\d]{24}$/',
			'per_page'  => 'numeric|regex:/^^(?!(\d)\1{9})\d{1,3}$/',
			'page'      => 'numeric|regex:/^^(?!(\d)\1{9})\d{1,4}$/',
		]);
		$per_page = (int)($fields['per_page'] ?? 15);
		if (isset($fields['q'])){
			$list = SubCategory::with('category')->find($fields['q']);
			if (!$list)
				return ['success' => false, 'msg' => 'SUBCATEGORY_NOT_FOUND'];
		} else {
			$list = SubCategory::where('deleted_at', null)
				->orderByDesc('created_at')
				->paginate($per_page);
		}

		return ['success' => true, 'details' => $list];
	}

//    public function index()
//    {
//        $fields = \request()->validate([
//            'q'   			=> 'string|regex:/^[a-f\d]{24}$/',
//			'per_page'   	=> 'numeric|regex:/^^(?!(\d)\1{9})\d{1,3}$/',
//			'page'      	=> 'numeric|regex:/^^(?!(\d)\1{9})\d{1,4}$/',
//        ]);
//        $prepage    = $fields['per_page'] ?? 5;
//        $page       = $fields['page'] ?? 1;
//        if (isset($fields['q'])){
//            $category[] = Category::find($fields['q']);
//            if (!$category)
//                return ['success' => false, 'msg' => 'PRODUCT_NOT_FOUND'];
//        } else {
//            $category = Category::all();
//        }
//
//        $response = [];
//        foreach ($category as $index => $item) {
//            $article = Category::find($item->id);
//            $subCategory = $article->subCategory();
//            $response[] = [
//                'category'      => $item->toArray(),
//                'sub_category'  => $subCategory->get(),
//            ];
//        }
//        return $this->paginator((array)$response, $prepage, $page);
//    }
}
