<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ForgotPasswordController extends Controller
{
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'phone' => [
                'required',
                'regex:/^(08[0-9]{0,13}|628[0-9]{0,12})$/',
                'digits_between:10,15',
            ]
        ], [
            'phone.required' => 'No HP wajib diisi.',
            'phone.regex' => 'No HP harus diawali dengan 08 atau 628.',
            'phone.digits_between' => 'No HP harus terdiri dari antara 10 hingga 15 digit.'
        ]);

        if (preg_match('/^08/',  $request->phone)) {
            $phone = '628' . substr($request->phone, 2);
            $request->merge(['phone' => $phone]);
        }

        $user = User::where('phone', $request->phone)->first();

        if (!$user) {
            return
                response()->json([
                    'code' => 404,
                    'status' => true,
                    'message' => 'User tidak ditemukan.'
                ]);
        }
        $key = Str::random(6);
        $user->update(['password' => $key]);

        // send link to phone
        $this->sendLinkResetPassword($key, $request->phone);

        return response()->json([
            'code' => 200,
            'status' => true,
            'message' => 'Password baru telah dikirim ke Whatsapp Anda.'
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
        // kirim ke wa
        $token = env('FONTE_KEY');
        $message = "Hi!\n\n";
        $message .= "Berikut adalah password baru untuk akun kamu:\n\n";
        $message .= "🔑Password: *" . $key . "*\n\n";
        $message .= "Harap segera login dan ganti password kamu setelah berhasil masuk untuk keamanan akunmu.\n\n";
        $message .= "Jika kamu tidak merasa melakukan permintaan reset password, silakan abaikan pesan ini.\n\n";
        $message .= "Terima kasih.\n";
        $url = 'https://api.fonnte.com/send';

        $response = Http::withHeaders([
            'Authorization' => $token,
        ])->withOptions([
            'verify' => false,
        ])->post($url, [
            'target' => $target,
            'message' => $message
        ]);

        Log::info($response);

        return $response->json();
    }
}
