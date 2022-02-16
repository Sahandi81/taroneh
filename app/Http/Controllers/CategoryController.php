<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Traits\Paginator;

class CategoryController extends Controller
{
    use Paginator;


    public function index()
    {
        $fields = \request()->validate([
            'q'   => 'string|regex:/^[a-f\d]{24}$/',
                'per_page'   => 'numeric|regex:/^^(?!(\d)\1{9})\d{1,3}$/',
                'page'      => 'numeric|regex:/^^(?!(\d)\1{9})\d{1,4}$/',
        ]);
        $prepage    = $fields['per_page'] ?? 5;
        $page       = $fields['page'] ?? 1;
        if (isset($fields['q'])){
            $category[] = Category::find($fields['q']);
            if (!$category)
                return ['success' => false, 'msg' => 'PRODUCT_NOT_FOUND'];
        } else {
            $category = Category::all();
        }

        $response = [];
        foreach ($category as $index => $item) {
            $article = Category::find($item->id);
            $subCategory = $article->subCategory();
            $response[] = [
                'category'      => $item->toArray(),
                'sub_category'  => $subCategory->get(),
            ];
        }
        return $this->paginator((array)$response, $prepage, $page);
    }
}
