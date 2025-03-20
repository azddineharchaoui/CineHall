<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'user' 
        ]);

        return response()->json(['message' => 'User created successfully'], 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = auth()->user();
        
        return response()->json([
            'message' => "You are logged in successfully",
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role
            ]
        ]);
    }

    public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken());
        return response()->json(['message' => 'Successfully logged out']);
    }

    public function userProfile()
    {
        return response()->json(auth()->user());
    }
    
    public function updateProfile(Request $request)
    {
        $user = auth()->user();
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
            'password' => 'nullable|string|min:6'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
        
        $user->name = $request->name;
        $user->email = $request->email;
        
        if ($request->has('password') && !empty($request->password)) {
            $user->password = Hash::make($request->password);
        }
        
        $user->save();
        
        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user
        ]);
    }
    
    public function deleteAccount()
    {
        $user = auth()->user();
        $user->delete();
        
        JWTAuth::invalidate(JWTAuth::getToken());
        
        return response()->json([
            'message' => 'Account deleted successfully'
        ]);
    }
    
    public function createAdmin(Request $request)
    {
        // Vérifier si l'utilisateur actuel est un admin
        if (auth()->user()->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $admin = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'admin'
        ]);

        return response()->json([
            'message' => 'Admin created successfully',
            'admin' => $admin
        ], 201);
    }
}