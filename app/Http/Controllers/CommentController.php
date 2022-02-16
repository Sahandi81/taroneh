<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Product;

class CommentController extends Controller
{

    public function index()
    {
        return Comment::with(['user', 'product'])->get();
    }

    public function store()
    {
        $fields = \request()->validate([
            'product_id'    => 'required|string|regex:/^[a-f\d]{24}$/',
            'body'          => 'required|string|max:2500',
            'rate'          => 'numeric|max:1',
        ]);
        $user = \request()->user();
        $fields['user_id'] = $user->id;
        $product = Product::find($fields['product_id']);
        if (! $product)
            return response(['success' => false, 'msg' => 'PRODUCT_NOT_FOUND'], 404);

        Comment::create($fields);
        return response(['success' => true, 'msg' => 'SUCCESSFULLY']);
    }

}
