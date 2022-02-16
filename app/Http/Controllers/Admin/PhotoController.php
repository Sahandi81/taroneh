<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class PhotoController extends Controller
{
	public function store()
	{

		\request()->validate([
			'file'	=> 'required|image|mimes:png,webm,jpeg,jpg|max:2048'
		]);

		$image =  time() .'-'. rand(999, 9999) .'.'. request()->file('file')->getClientOriginalExtension();
		$file = request()->file('file')->storeAs('public', $image);

		if ($file) {
			return response([
				'success' => true,
				'file' => basename($image),
				'size' => request()->file('file')->getSize()
			]);
		}

		return response(['success' => false, 'msg' => 'UNKNOWN_ERROR']);
	}

	public static function movePhotos(array $photos, ?int $code, $alreadyPhotos = [])
	{
        if ($alreadyPhotos == null) $alreadyPhotos = [];
        $result = [];
		foreach ($photos as $photo) {
			if (Storage::disk('public')->exists($photo)){
                if ($code){
                    Storage::disk('public')->move($photo, $code . DIRECTORY_SEPARATOR . $photo);
                }
			}else{
                if (! in_array($photo, $alreadyPhotos))
                    continue;
            }
            $result[] = $photo;
        }

		return $result;
	}
}
