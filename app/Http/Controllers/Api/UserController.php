<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;

class UserController extends Controller
{

    public function login(LoginRequest $request)
    {
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            return response()->json([
                'status'    => true,
                'message'   => 'Đăng nhập tài khoản thành công'
            ], 200);
        } else {
            return response()->json([
                'status'    => false,
                'message'   => 'Sai tài khoản hoặc mật khẩu hãy thử lại'
            ], 200);
        }
    }

    public function register(RegisterRequest $request)
    {
        $User = new User;
        $User->email = $request->email;
        $User->password = bcrypt($User->password);
        $User->schoolName = $request->schoolName;
        $User->class = $request->class;
        $User->birthDay = $request->birthDay;
        $User->save();
        return response()->json([
            'status'    => true,
            'message'   => 'Đăng ký tài khoản thành công'
        ], 200);
    }
}
