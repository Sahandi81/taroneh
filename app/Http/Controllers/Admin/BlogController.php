<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\BlogRequest;
use App\Models\Blog;

class BlogController extends Controller
{
    public function store(BlogRequest $request)
    {
        $fields = $request->validated();
        $fields['photo'] = PhotoController::movePhotos([$fields['photo']], false);

        try {
            $result = Blog::create($fields);
            return response(['success' => true, 'msg' => 'BLOG_ADD_SUCCESSFULLY', 'details' => $result]);
        } catch (\PDOException $e){
            return response(['success' => false, 'msg' => 'CALL_BACKEND_DEVELOPER' , [
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]],422);
        }

    }
}
