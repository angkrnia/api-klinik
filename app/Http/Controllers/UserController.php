<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if(auth()->user()->role === DOKTER) {
            $result = User::with([DOKTER])->findOrFail(auth()->user()->id);
        } else {
            $result = User::with(PASIEN)->findOrFail(auth()->user()->id);
        }

        return response()->json([
            'code'      => 200,
            'status'    => true,
            'data'      => $result
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        abort(404);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        Log::info($user);
        $request->validate([
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            // 'record_no' => ['nullable', 'string', 'max:255'],
            'fullname' => ['required', 'string', 'max:255'],
            // 'gender' => ['nullable', 'string', 'max:12'],
            // 'birthday' => ['nullable', 'string', 'max:15'],
            // 'age' => ['nullable', 'integer'],
            // 'phone' => ['nullable', 'string', 'max:20'],
            // 'address' => ['nullable', 'string', 'max:255'],
        ]);

        DB::beginTransaction();
        try {
            $auth = auth()->user();
            if ($auth->role !== ADMIN && $auth->id !== $user->id) {
                return response()->json([
                    'code'      => 403,
                    'status'    => true,
                    'message'   => 'Anda tidak memiliki hak untuk mengupdate user ini.',
                ], 403);
            }

            $user->update($request->all());

            DB::commit();
            return response()->json([
                'code'      => 200,
                'status'    => true,
                'message'   => 'User berhasil diupdate.',
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function changePassword(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'password' => ['required', 'string', 'min:4', 'confirmed'],
        ]);

        try {
            $user->password = bcrypt($request->password);
            $user->save();

            return response()->json([
                'code'      => 200,
                'status'    => true,
                'message'   => 'Password berhasil diupdate.',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $user->delete();

        return response()->json([
            'code'      => 200,
            'status'    => true,
            'message'   => 'User berhasil dihapus.',
        ]);
    }
}
