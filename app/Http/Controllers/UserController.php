<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->role === 'admin') {
            $request = $request->query();
            $query = User::query();

            if (isset($request['search'])) {
                $searchKeyword = $request['search'];
                $query->keywordSearch($searchKeyword);
            }

            $query->orderBy('created_at', 'desc');

            if (isset($request['limit']) || isset($request['page'])) {
                $limit = $request['limit'] ?? 10;
                $result = $query->paginate($limit);
            } else {
                $result = $query->get(); // Untuk Print atau Download
            }
        } else {
            $result = User::where('id', $user->id)->first();
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
        try {
            $pasien = User::findOrFail($id);

            return response()->json([
                'code'      => 200,
                'status'    => true,
                'data'      => $pasien
            ]);
        } catch (\Throwable $th) {
            if ($th instanceof ModelNotFoundException) {
                return response()->json(['error' => 'User tidak ditemukan'], 404);
            } else {
                return response()->json(['error' => $th->getMessage()], 500);
            }
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'username'  => ['required', 'string'],
            'email' => ['required', 'string', 'email'],
            'password'  => ['required', 'string'],
        ]);

        try {
            $auth = auth()->user();
            if ($auth->role !== 'admin' && $auth->id !== $user->id) {
                return response()->json([
                    'code'      => 403,
                    'status'    => true,
                    'message'   => 'Anda tidak memiliki hak untuk mengupdate user ini.',
                ], 403);
            }

            $user->update($request->all());

            return response()->json([
                'code'      => 200,
                'status'    => true,
                'message'   => 'User berhasil diupdate.',
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
