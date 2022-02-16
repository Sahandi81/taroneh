<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\Admin;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AdminController extends Controller
{
    public function login(): array
	{

        \request()->validate([
            'email' => 'email',
            'password' => 'required'
        ]);
        $user = Admin::where('email', 'admin@admin.com')->first();
        if (! $user || ! Hash::check(\request('password'), $user->password)) {
            throw ValidationException::withMessages([
                'login' => ['WRONG_DETAILS'],
                'success' => false,
            ]);
        }

        return  ['success' => true, 'token' => $user->createToken('token_base_name')->plainTextToken];
    }

    public function dashboard()
    {
        return \request()->user();
    }

}
