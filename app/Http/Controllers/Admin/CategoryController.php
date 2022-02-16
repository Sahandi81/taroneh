<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\SubCategory;

class CategoryController extends Controller
{

	# Category
	public function storeCategory()
	{
		$fields = request()->validate([
			'name'	=> 'required|string|max:80'
		]);

		if (Category::where('name' , $fields['name'])->exists())
			return response(['success' => false, 'msg' => 'ALREADY_EXISTS']);

		Category::create([
			'name' 	=> $fields['name'],
			'active'=> 1
		]);
		return [
			'success' => true,
			'msg'	  => 'SUCCESSFULLY'
		];
    }

	public function updateCategory()
	{
		$fields = request()->validate([
			'id'		=> 'required|string|regex:/^[a-f\d]{24}$/',
			'name'		=> 'required|string|max:80',
			'active'	=> 'bool' # timestamp
		]);

		$category = Category::find($fields['id']);

		if ($category->count() <= 0)
			return response(['success' => false, 'msg' => 'CATEGORY_DOESNT_FOUND'],422);

		try {
			$category->update($fields);
			return response([
				'success' 	=> true,
				'msg' 		=> 'EDIT_SUCCESSFULLY',
				'category' 	=> Category::find($fields['id'])
			]);

		} catch (\PDOException $e){
			return response([
				'success' 	=> false,
				'msg' 		=> 'CALL_BACKEND_DEVELOPER' ,
				[
				'line' => $e->getLine(),
				'file' => $e->getFile()
				]
			],422);
		}
	}

	# Sub category
	public function storeSubCategory()
	{
		$fields = request()->validate([
			'name'			=> 'required|string|max:80',
			'category_id'	=> 'required|string|regex:/^[a-f\d]{24}$/',
		]);

		if ( ! Category::find($fields['category_id']))
			return response(['success' => false, 'msg' => 'CATEGORY_NOT_FOUND']);

		if (SubCategory::where('name' , $fields['name'])->exists())
			return response(['success' => false, 'msg' => 'ALREADY_EXISTS']);

		SubCategory::create([
			'name' 			=> $fields['name'],
			'category_id' 	=> $fields['category_id'],
			'active'		=> 1  # timestamp
		]);
		return [
			'success' => true,
			'msg'	  => 'SUCCESSFULLY'
		];
	}

	public function updateSubCategory()
	{
		$fields = request()->validate([
			'id'			=> 'required|string|regex:/^[a-f\d]{24}$/',
			'name'			=> 'required|string|max:80',
			'category_id'	=> 'required|string|regex:/^[a-f\d]{24}$/',
			'active'		=> 'bool' # timestamp
		]);

		$subCategory = SubCategory::find($fields['id']);
		if ($subCategory->count() <= 0)
			return response(['success' => false, 'msg' => 'SUBCATEGORY_DOESNT_FOUND'],422);

		$category = Category::find($fields['category_id']);
		if ($category->count() <= 0)
			return response(['success' => false, 'msg' => 'CATEGORY_DOESNT_FOUND'],422);

		try {
			$subCategory->update($fields);
			return response([
				'success' 	=> true,
				'msg' 		=> 'EDIT_SUCCESSFULLY',
				'category' 	=> SubCategory::find($fields['id'])
			]);

		} catch (\PDOException $e){
			return response([
				'success' 	=> false,
				'msg' 		=> 'CALL_BACKEND_DEVELOPER' ,
				[
					'line' => $e->getLine(),
					'file' => $e->getFile()
				]
			],422);
		}
	}

	public function destroyCategory()
	{
		$fields = \request()->validate([
			'id' => 'required|string|regex:/^[a-f\d]{24}$/',
		]);

		$user = Category::find($fields['id']);
		if (!$user) {
			if (Category::withTrashed()->find($fields['id'])) {
				return response(['success' => false, 'msg' => 'CATEGORY_ALREADY_DELETED'], 400);
			}
			return response(['success' => false, 'msg' => 'CATEGORY_NOT_FOUND'], 400);
		}
		$user->delete();

		return response(['success' => true, 'msg' => 'SUCCESSFULLY']);
	}

	public function destroySubCategory()
	{
		$fields = \request()->validate([
			'id' => 'required|string|regex:/^[a-f\d]{24}$/',
		]);

		$user = SubCategory::find($fields['id']);
		if (!$user) {
			if (SubCategory::withTrashed()->find($fields['id'])) {
				return response(['success' => false, 'msg' => 'SUBCATEGORY_ALREADY_DELETED'], 400);
			}
			return response(['success' => false, 'msg' => 'SUBCATEGORY_NOT_FOUND'], 400);
		}
		$user->delete();

		return response(['success' => true, 'msg' => 'SUCCESSFULLY']);
	}

}
