<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class RegisterController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $request->validate([
            'fullname' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users', 'email')],
            'password' => ['required', 'min:6'],
        ]);

        DB::beginTransaction();
        try {
            $user = User::create($request->all());

            Patient::create([
                'user_id' => $user->id,
                'fullname' => $request->fullname,
            ]);

            DB::commit();
    
            return response()->json([
                'code'      => 201,
                'status'    => true,
                'message'   => 'Registrasi berhasil.',
                'data'      => $user,
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'code'    => 500,
                'status'  => false,
                'message' => $th->getMessage() ?: 'Registrasi gagal.',
            ], 500);
        }
    }
}
