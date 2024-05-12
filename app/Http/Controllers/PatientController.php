<?php

namespace App\Http\Controllers;

use App\Http\Requests\Patient\StorePatientRequest;
use App\Http\Requests\Patient\UpdatePatientRequest;
use App\Models\Patient;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PatientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->role === ADMIN || $user->role === PERAWAT) {
            $request = $request->query();
            $query = Patient::query();

            if (isset($request['search'])) {
                $searchKeyword = $request['search'];
                $query->keywordSearch($searchKeyword);
            }

            $query->orderBy('created_at', 'desc');

            if (isset($request['limit']) || isset($request['page'])) {
                $limit = $request['limit'] ?? 10;
                $result = $query->with(USER)->paginate($limit)->appends(request()->query());
            } else {
                $result = $query->with(USER)->get(); // Untuk Print atau Download
            }
        } else {
            $result = Patient::with(USER)->where('user_id', $user->id)->first();
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
    public function store(StorePatientRequest $request)
    {
        $data = $request->validated();

        if (auth()->user()->role === PASIEN) {
            $data = array_merge($data, ['user_id' => auth()->user()->id]);
        }

        $patient = Patient::create($data);

        return response()->json([
            'code'      => 201,
            'status'    => true,
            'message'   => 'Pasien baru berhasil ditambahkankan.',
            'data'      => $patient
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $pasien = Patient::findOrFail($id);

            return response()->json([
                'code'      => 200,
                'status'    => true,
                'data'      => $pasien
            ]);
        } catch (\Throwable $th) {
            if ($th instanceof ModelNotFoundException) {
                return response()->json(['error' => 'Pasien tidak ditemukan'], 404);
            } else {
                return response()->json(['error' => $th->getMessage()], 500);
            }
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePatientRequest $request, Patient $patient)
    {
        $patient->update($request->validated());

        return response()->json([
            'code'      => 200,
            'status'    => true,
            'message'   => 'Pasien berhasil diupdate.',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Patient $patient)
    {
        $patient->delete();

        return response()->json([
            'code'      => 200,
            'status'    => true,
            'message'   => 'Pasien berhasil dihapus.',
        ]);
    }

    /**
     * Get All Patient
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     * 
    */
    public function patientListByUser(Request $request)
    {
        $userId = auth()->user()->id;
        $patients = Patient::where('user_id', $userId)->get();
        return response()->json([
            'code'      => 200,
            'status'    => true,
            'data'      => $patients
        ]);
    }
}
