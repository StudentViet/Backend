<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    public function handle(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:128',
            'email' => 'required|email|max:128|unique:users',
            'schoolName' => 'required|string|max:128',
            'schoolClass' => 'required|string|max:5',
            'password' => 'required|string|max:32|confirmed',
            'birthday' => 'required|max:20'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'isError' => true,
                'messages' => [$validator->errors()]
            ]);
        } else {
            $User = new User;
            $User->uuid = Str::uuid();
            $User->name = $request->name;
            $User->email = $request->email;
            $User->password = bcrypt($request->password);
            $User->schoolName = $request->schoolName;
            $User->schoolClass = $request->schoolClass;
            $User->birthday = $request->birthday;
            $User->role_id = (int) $request->role;
            $User->save();
            return response()->json([
                'isError' => false,
                'message' => 'Đăng ký thành công'
            ]);
        }
    }
}
