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
            'phone' => [
                'required',
                'regex:/^(08[0-9]{0,13}|628[0-9]{0,12})$/', // Memastikan diawali dengan 08 atau 628
                'digits_between:10,15', // Memastikan panjang antara 10 sampai 15 angka
                Rule::unique('users', 'phone'),
            ],
            'password' => ['required', 'min:6'],
        ], [
            'fullname.required' => 'Nama Lengkap wajib diisi.',
            'phone.required' => 'No HP wajib diisi.',
            'phone.regex' => 'No HP harus diawali dengan 08 atau 628.',
            'phone.digits_between' => 'No HP harus terdiri dari antara 10 hingga 15 digit.',
            'password.required' => 'Password wajib diisi.',
            'password.max' => 'Password tidak boleh lebih dari 255 karakter.',
            'phone.unique' => 'No HP sudah pernah terdaftar.',
        ]);

        try {
            if (preg_match('/^08/',  $request->phone)) {
                $phone = '628' . substr($request->phone, 2);
                $request->merge(['phone' => $phone]);
            }

            $request->validate([
                'phone' => [
                    'required',
                    'regex:/^(08[0-9]{0,13}|628[0-9]{0,12})$/', // Memastikan diawali dengan 08 atau 628
                    'digits_between:10,15', // Memastikan panjang antara 10 sampai 15 angka
                    Rule::unique('users', 'phone'),
                ],
            ], [
                'phone.required' => 'No HP wajib diisi.',
                'phone.regex' => 'No HP harus diawali dengan 08 atau 628.',
                'phone.digits_between' => 'No HP harus terdiri dari antara 10 hingga 15 digit.',
                'phone.unique' => 'No HP sudah pernah terdaftar.',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'code'    => 422,
                'status'  => false,
                'message' => $th->getMessage() ?: 'Registrasi gagal.',
            ], 422);
        }

        DB::beginTransaction();
        try {
            $user = User::create($request->all());

            // Patient::create([
            //     'user_id' => $user->id,
            //     'fullname' => $request->fullname,
            //     'nama_keluarga' => $request->nama_keluarga,
            // ]);

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
