<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\Paginator;
use App\Traits\ProductBuilder;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
	use Paginator, ProductBuilder;

	public function index()
	{
		$fields = \request()->validate([
			'per_page'  => 'numeric|regex:/^^(?!(\d)\1{9})\d{1,3}$/',
			'page'      => 'numeric|regex:/^^(?!(\d)\1{9})\d{1,4}$/',
			'sort'      => 'string',
			'id'		=> 'string|regex:/^[a-f\d]{24}$/',
		]);

		# search user
		if (isset($fields['id'])){
			$user = User::find($fields['id']);
			if ( ! $user)
				return response(['success' => false, 'msg' => 'USER_NOT_FOUND']);
			else
				return $user;
		}

		# return all users
		$prepage    = $fields['per_page'] ?? 5;
		$page       = $fields['page'] ?? 1;

		$users = User::with(['history' => fn($q) => $q->where('status', 'paid')])->where('_id', '!=', null)->get()->toArray();
        foreach ($users as $key => $user) {
            $users[$key]['history'] = count($user['history']);
        }
        if (!empty($fields['sort']) && $fields['sort'] == 'shopping'){
            $history = array_column($users, 'history');
            array_multisort($history, SORT_DESC, $users);
        }else{
            $users = array_reverse($users);
        }
		$users = $this->paginator((array)$users, $prepage, $page);
		return $users;
	}

	public function update()
	{
		$fields = \request()->validate([
			'id' 		=> 'required|string|regex:/^[a-f\d]{24}$/',
			'f_name' 	=> 'string|min:2',
			'l_name' 	=> 'string|min:2',
			'email' 	=> 'email|min:10',
			'address' 	=> 'array',
			'password' 	=> 'string|min:6',
		]);

		# search user
		$user = User::find($fields['id']);
		if ( ! $user)
			return response(['success' => false, 'msg' => 'USER_NOT_FOUND']);

		if (isset($fields['password']))
			$fields['password'] = Hash::make($fields['password']);

		try {

			$user->update($fields);
			return response([
				'success' => true,
				'msg' => 'EDIT_SUCCESSFULLY',
				'user' => User::find($fields['id'])
			]);

		} catch (\PDOException $e){

			return response(['success' => false, 'msg' => 'CALL_BACKEND_DEVELOPER' , [
				'line' => $e->getLine(),
				'file' => $e->getFile()
			]],422);
		}
	}


	public function destroy()
	{
		$fields = \request()->validate([
			'id' 		=> 'required|string|regex:/^[a-f\d]{24}$/',
		]);

		$user = User::find($fields['id']);
		if ( ! $user){
			if (User::withTrashed()->find($fields['id'])){
				return response(['success' => false, 'msg' => 'USER_ALREADY_DELETED'], 400);
			}
			return response(['success' => false, 'msg' => 'USER_NOT_FOUND'], 400);
		}
		$user->delete();

		return response(['success' => true, 'msg' => 'SUCCESSFULLY']);
	}


}
