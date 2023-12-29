<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RegisterController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $request->validate([
            'username' => ['required', 'max:50', Rule::unique('users', 'username')],
            'email' => ['required', 'email', Rule::unique('users', 'email')],
            'password' => ['required', 'min:6'],
        ]);

        try {
            $user = User::create($request->all());
    
            return response()->json([
                'code'      => 201,
                'status'    => true,
                'message'   => 'Registrasi berhasil.',
                'data'      => $user,
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'code'    => 500,
                'status'  => false,
                'message' => $th->getMessage() ?: '',
            ], 500);
        }
    }
}
