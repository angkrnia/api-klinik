<?php

namespace App\Http\Controllers;

use App\Http\Requests\Doctor\DoctorRequest;
use App\Models\Doctor;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DoctorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->role === 'admin') {
            $request = $request->query();
            $query = Doctor::query();

            if (isset($request['search'])) {
                $searchKeyword = $request['search'];
                $query->keywordSearch($searchKeyword);
            }

            $query->orderBy('created_at', 'desc');

            if (isset($request['limit']) || isset($request['page'])) {
                $limit = $request['limit'] ?? 10;
                $result = $query->with('user')->paginate($limit);
            } else {
                $result = $query->with('user')->get(); // Untuk Print atau Download
            }
        } else {
            $result = Doctor::with('user')->where('user_id', $user->id)->first();
        }

        return response()->json([
            'code'      => 200,
            'status'    => true,
            'data'      => $result
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(DoctorRequest $request)
    {
        $doctor = Doctor::create($request->validated());

        return response()->json([
            'code'      => 200,
            'status'    => true,
            'message'   => 'Dokter baru berhasil ditambahkankan.',
            'data'      => $doctor
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $doctor = Doctor::findOrFail($id);

            return response()->json([
                'code'      => 200,
                'status'    => true,
                'data'      => $doctor
            ]);
        } catch (\Throwable $th) {
            if ($th instanceof ModelNotFoundException) {
                return response()->json(['error' => 'Dokter tidak ditemukan'], 404);
            } else {
                return response()->json(['error' => $th->getMessage()], 500);
            }
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(DoctorRequest $request, Doctor $doctor)
    {
        $doctor->update($request->validated());

        return response()->json([
            'code'      => 200,
            'status'    => true,
            'message'   => 'Dokter berhasil diupdate.',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Doctor $doctor)
    {
        $doctor->delete();

        return response()->json([
            'code'      => 200,
            'status'    => true,
            'message'   => 'Dokter berhasil dihapus.',
        ]);
    }
}
