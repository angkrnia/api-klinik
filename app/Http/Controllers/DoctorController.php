<?php

namespace App\Http\Controllers;

use App\Http\Requests\Doctor\DoctorRequest;
use App\Models\Doctor;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DoctorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $request = $request->query();
        $query = Doctor::query();

        if (isset($request['search'])) {
            $searchKeyword = $request['search'];
            $query->keywordSearch($searchKeyword);
        }

        $query->orderBy('created_at', 'desc');

        if (isset($request['limit']) || isset($request['page'])) {
            $limit = $request['limit'] ?? 10;
            $result = $query->with([USER])->paginate($limit);
        } else {
            $result = $query->with([USER])->get(); // Untuk Print atau Download
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
        abort(404);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(DoctorRequest $request)
    {
        try {
            DB::beginTransaction();

            // Membuat dokter baru
            $user = User::create([
                'email'         => $request->email,
                'role'          => DOKTER,
                'password'      => $request->password,
                'email_verified_at' => Carbon::now()
            ]);

            // Membuat user baru
            $doctor = $user->doctor()->create($request->validated());

            DB::commit();

            return response()->json([
                'code'      => 201,
                'status'    => true,
                'message'   => 'Dokter baru berhasil ditambahkan.',
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'code'      => 500,
                'status'    => false,
                'message'   => $th->getMessage(),
            ], 500);
        }
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
        abort(404);
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
