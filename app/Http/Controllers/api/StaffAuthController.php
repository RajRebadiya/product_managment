<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\Staff;

class StaffAuthController extends Controller
{
    //
    // Register Staff
    public function register(Request $request)
    {
        // dd( $request->all() );  
        $rules = [
            'name' => 'required|string|max:255',
            'mobile_no' => 'required|string|max:255|min:10|max:10|unique:tbl_staff,mobile_no',
            'emp_code' => 'required|string|max:255|unique:tbl_staff,emp_code',
            'password' => 'required|string|min:8',
            'permission_id' => 'required|exists:permissions,id',
        ];

        // Validate the incoming request
        $validator = Validator::make($request->all(), $rules);

        // Check if validation fails
        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            $errorMessage = implode(' ', $errors);
            return response()->json([
                'status_code' => 400,
                'message' => $errorMessage,
                'data' => []
            ]);
        }


        $staff = Staff::create([
            'name' => $request->name,
            'mobile_no' => $request->mobile_no,
            'emp_code' => $request->emp_code,
            'status' => 0,
            'permission_id' => $request->permission_id,
            'password' => $request->password,
        ]);
        // dd('fineee');

        return response()->json([
            'status_code' => 200,
            'message' => 'Staff registered successfully',
        ]);
    }

    // Login Staff
    public function login(Request $request)
    {
        $rulse = [
            'mobile_no' => 'required|exists:tbl_staff,mobile_no',
            'password' => 'required',
        ];

        $validation = Validator::make($request->all(), $rulse);

        if ($validation->fails()) {
            $errors = $validation->errors()->all();
            $errorMessage = implode(' ', $errors);
            return response()->json([
                'code' => 400,
                'status' => 0,
                'message' => $errorMessage
            ]);
        }
        $user = Staff::where('mobile_no', $request->mobile_no)->first();
        if ($user && $request->password == $user->password) {
            // dd('fine');
            Auth::login($user);
            // dd(Auth::login($user));
            return response()->json([
                'code' => 200,
                'status' => 1,
                'message' => 'Login successful',
                'data' => [
                    "id" => $user->id,
                    'name' => $user->name
                ]
            ]);
        } else {
            return response()->json([
                'code' => 400,
                'status' => 0,
                'message' => 'Login failed'
            ]);
        }
    }

    public function checkAuth()
    {

        $user = Auth::user(); // Get logged-in user's details
        dd($user);
        return response()->json([
            'code' => 200,
            'status' => 1,
            'message' => 'User is authenticated',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
            ]
        ]);
        if (Auth::user() == null) { {
                return response()->json([
                    'code' => 401,
                    'status' => 0,
                    'message' => 'User is not authenticated'
                ]);
            }
        }
    }
}
