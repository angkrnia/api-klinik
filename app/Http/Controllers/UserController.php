<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

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
        } else if($user->role === 'dokter') {
            $result = User::with('doctor.schedules')->where('id', $user->id)->first();
        } else if($user->role === 'patient' || $user->role === 'pasien') {
            $result = User::with('patient.history')->where('id', $user->id)->first();
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

            if ($pasien->role === 'dokter') {
                $pasien->load('doctor');
            } else if ($pasien->role === 'pasien') {
                $pasien->load('patient');
            }

            return response()->json([
                'code'   => 200,
                'status' => true,
                'data'   => $pasien
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
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'record_no' => ['nullable', 'string', 'max:255'],
            'fullname' => ['required', 'string', 'max:255'],
            'gender' => ['nullable', 'string', 'max:12'],
            'birthday' => ['nullable', 'string', 'max:15'],
            'age' => ['nullable', 'integer'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
        ]);

        DB::beginTransaction();
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

            if($user->role === 'pasien') {
                // UPDATE DATA PASIEN
                $patient = Patient::where('user_id', $user->id)->first();
                if ($patient) {
                    $updateData = [];
    
                    if ($request->filled('fullname')) {
                        $updateData['fullname'] = $request->fullname;
                    }
                    if ($request->filled('record_no')) {
                        $updateData['record_no'] = $request->record_no;
                    }
                    if ($request->filled('gender')) {
                        $updateData['gender'] = $request->gender;
                    }
                    if ($request->filled('birthday')) {
                        $updateData['birthday'] = $request->birthday;
                    }
                    if ($request->filled('age')) {
                        $updateData['age'] = $request->age;
                    }
                    if ($request->filled('phone')) {
                        $updateData['phone'] = $request->phone;
                    }
                    if ($request->filled('address')) {
                        $updateData['address'] = $request->address;
                    }
    
                    if (!empty($updateData)) {
                        $patient->update($updateData);
                    }
                } else {
                    DB::rollBack();
                    return response()->json([
                        'code' => 404,
                        'status' => false,
                        'message' => 'Data pasien tidak ditemukan.',
                    ], 404);
                }
            } else if($user->role === 'dokter') {
                // UPDATE DATA DOKTER
                $doctor = Doctor::where('user_id', $user->id)->first();
                if ($doctor) {
                    $updateData = [];

                    if ($request->filled('fullname')) {
                        $updateData['fullname'] = $request->fullname;
                    }
                    if ($request->filled('phone')) {
                        $updateData['phone'] = $request->phone;
                    }

                    if (!empty($updateData)) {
                        $doctor->update($updateData);
                    }
                } else {
                    DB::rollBack();
                    return response()->json([
                        'code' => 404,
                        'status' => false,
                        'message' => 'Data Dokter tidak ditemukan.',
                    ], 404);
                }
            }

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
