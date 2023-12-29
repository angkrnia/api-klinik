<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;

class LoginController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function index(Request $request)
    {
        $request->validate([
            'username' => ['required'],
            'password' => ['required']
        ]);

        try {

            $credentials = $request->only('username', 'password');
            $token = Auth::attempt($credentials);
            
            if (!$token) {
                return response()->json([
                    'code' => 401,
                    'status' => false,
                    'message' => 'Username atau password salah.',
                ], 401);
            }

            $user = Auth::user();
            $refreshToken = JWTAuth::fromUser($user);

            $user->update([
                'refresh_token' => $refreshToken
            ]);

            return response()->json([
                'code' => 200,
                'status' => true,
                'message' => 'Login berhasil.',
                'data' => [
                    'token' => $token,
                    'refresh_token' => $refreshToken
                ]
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'code'    => 500,
                'status'  => false,
                'message' => $th->getMessage() ?: '',
            ], 500);
        }
    }

    public function refreshToken(Request $request)
    {
        $request->validate([
            'refresh_token' => ['required', 'string', 'exists:users,refresh_token']
        ]);

        $user = User::where('refresh_token', $request->refresh_token)->first();

        if ($user) {
            Auth::login($user);

            $newToken = JWTAuth::fromUser($user);

            return response()->json([
                'code' => 200,
                'status' => true,
                'data' => [
                    'token' => $newToken,
                ]
            ]);
        } else {
            return response()->json(['error' => 'Invalid refresh token'], 401);
        }

    }
}
