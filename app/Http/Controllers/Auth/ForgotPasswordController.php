<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class ForgotPasswordController extends Controller
{
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => ['required', 'max:15'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return
            response()->json([
                'code' => 404,
                'status' => true,
                'message' => 'Nomor whatsapp tidak ditemukan.'
            ]);
        }

        $key = Str::random(100);
        $user->update(['remember_token' => $key]);

        // send link to email
        $this->sendLinkResetPassword($key, $request->email);

        return response()->json([
            'code' => 200,
            'status' => true,
            'message' => 'Link pergantian password telah dikirim ke nomor whatsapp Anda.'
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'remember_token' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $token = User::where('remember_token', $request->remember_token)->first();

        if (!$token || $token === '' || $token === null) {
            return response()->json([
                'code' => 422,
                'status' => false,
                'message' => 'Remember token tidak valid.'
            ]);
        }

        $token->update([
            'password' => $request->password,
            'remember_token' => '',
        ]);

        return response()->json([
            'code' => 200,
            'status' => true,
            'message' => 'Password berhasil diubah.'
        ]);
    }

    public function sendLinkResetPassword($key, $target)
    {
        // kirim email
    }
}
