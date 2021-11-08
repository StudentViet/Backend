<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{

    public function handle(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user' => 'required|string|max:128',
            'password' => 'required|string|max:32',
        ]);

        if ($validator->fails()) {

            return response()->json([
                'isError' => true,
                'message' => $validator->errors()->first()
            ]);
        } else {

            $fieldType = filter_var($request->user, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

            if (Auth::attempt([$fieldType => $request->user, 'password' => $request->password])) {
                $token = $request->user()->createToken('Access Token');
                return response()->json([
                    'isError' => false,
                    'message' => 'Đăng nhập thành công',
                    'data'    => [
                        'user' => [
                            $request->user()
                        ],
                        'access_token' => [
                            'accessToken' => $token->accessToken,
                            'token' => [
                                'expires_at' => $token->token->expires_at
                            ],
                        ],
                    ],
                ]);
            } else {
                return response()->json([
                    'isError' => true,
                    'message' => 'Tài khoản hoặc mật khẩu không khớp'
                ]);
            }
        }
    }
}
