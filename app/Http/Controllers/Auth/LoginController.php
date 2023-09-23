<?php

namespace App\Http\Controllers\Auth;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\users\UserResource;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function login(Request $request){
        $request->validate([
            'nim' => 'required',
            'password'=> 'required',
        ]);
        try{
            if (Auth::attempt(['nim' => $request->nim, 'password' => $request->password])) {
                $user = User::where('nim',$request->nim)->first();
                $token = $user->createToken($user->name)->accessToken;
                return response()->json([
                    'status' => true,
                    'message' => [
                        'user' => new UserResource($user),
                        'token' => $token
                    ]
                ]);
            }
            else{
                return response()->json([
                    'status' => false,
                    'message' => 'credential not match'
                ]);
            }
        }
        catch(Exception $e){
            return response()->json(['status' => false, 'message' => $e->message()]);

        }
    }

    public function unauthorizedMessage(){
        return response()->json([
            'status' => false,
            'message' => 'Unauthorized',
        ],401);
    }

    public function logout(Request $request){
        $removeToken = $request->user()->tokens()->delete();
        if($removeToken) {
            return response()->json([
               'success' => true,
               'message' => 'Logout Success!',  
            ]);
          }
    }
}
