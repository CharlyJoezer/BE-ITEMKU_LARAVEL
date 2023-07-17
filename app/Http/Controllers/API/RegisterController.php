<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    public function processRegister(Request $request){
        $validator = Validator::make($request->all(), [
            'username' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8'
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => 'errors',
                'errors' => $validator->errors()
            ]);
        }
        $setInsertData = [
            'username' => $request->username,
            'email' => $request->email,
            'password' => $request->password
        ];

        try{
            $createUser = User::create($setInsertData);
            if($createUser){
                return response()->json([
                    'status' => 'success',
                    'message' => 'Register berhasil, Silahkan lakukan Login!'
                ], 201);
            }else{
                throw new \Exception();
            }
        }catch(\Exception){
            return response()->json([
                'status' => 'Server Error',
                'error' => 'Server Error'
            ], 500);
        }
    }
}
