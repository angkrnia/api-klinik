<?php

namespace App\Http\Controllers;

use App\Http\Requests\Doctor\DoctorRequest;
use App\Models\Doctor;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DoctorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // $request = $request->query();
        // $query = Doctor::query();

        // if (isset($request['search'])) {
        //     $searchKeyword = $request['search'];
        //     $query->keywordSearch($searchKeyword);
        // }

        // $query->orderBy('created_at', 'desc');

        // if (isset($request['limit']) || isset($request['page'])) {
        //     $limit = $request['limit'] ?? 10;
        //     $result = $query->with([USER])->paginate($limit)->appends(request()->query());
        // } else {
        //     $result = $query->with([USER])->get(); // Untuk Print atau Download
        // }

        // return response()->json([
        //     'code'      => 200,
        //     'status'    => true,
        //     'data'      => $result
        // ]);

        $query = "SELECT 
            d.id,
            d.fullname,
            d.phone,
            d.avatar,
            d.description,
            GROUP_CONCAT(DISTINCT CONCAT(ds.day, '|', ds.start_time, '-', ds.end_time) ORDER BY ds.day SEPARATOR ', ') AS schedule
        FROM doctor_schedules ds
        JOIN doctors d ON ds.doctor_id = d.id
        WHERE (? IS NULL OR d.fullname LIKE CONCAT('%', ?, '%'))
        GROUP BY d.id, d.fullname, d.phone, d.avatar, d.description
        ORDER BY d.fullname";

        $searchTerm = $request->search ?? null;

        $data = DB::select($query, [
            $searchTerm,
            $searchTerm,
        ]);

        return response()->json([
            'code'      => 200,
            'status'    => true,
            'data'      => $data
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
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
            $imageUrl = null;

            if ($request->has('image')) {
                $imageFile = $request->file('image');
                $imageName = date('md_His') . '_' . $imageFile->getClientOriginalName();
                $imagePath = $imageFile->move(public_path('doctors'), $imageName);
                $imageUrl = url('doctors/' . $imageName);
            }

            // Membuat dokter baru
            $user = User::create([
                'fullname'      => $request->fullname,
                'email'         => $request->email,
                'role'          => DOKTER,
                'password'      => bcrypt($request->password),
                'email_verified_at' => Carbon::now()
            ]);

            // Membuat user baru
            $doctor = $user->doctor()->create([
                'fullname' => $request->fullname,
                'phone' => $request->phone,
                'avatar' => $imageUrl,
                'gender' => $request->gender,
                'description' => $request->description,
                'start_day' => $request->start_day,
                'end_day' => $request->end_day,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time
            ]);

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

    /**
     * List All Dokter
     */
    public function listAllDoctor()
    {
        $currentDay = Carbon::now('Asia/Jakarta')->format('l');
        $currentTime = Carbon::now('Asia/Jakarta')->format('H:i:s');

        $query = "SELECT d.id AS id, d.fullname as fullname FROM doctor_schedules s JOIN doctors d ON s.doctor_id = d.id WHERE s.day = UPPER(?) AND ? BETWEEN s.start_time AND s.end_time AND s.status = 1";
        $onDutyDoctors = DB::select($query, [
            $currentDay,
            $currentTime
        ]);
        $allDoctors = Doctor::all(['id', 'fullname']);
        $onDutyDoctorIds = collect($onDutyDoctors)->pluck('id')->toArray();
        $result = $allDoctors->map(function ($doctor) use ($onDutyDoctorIds) {
            return [
                'id' => $doctor->id,
                'fullname' => $doctor->fullname,
                'is_on_duty' => in_array($doctor->id, $onDutyDoctorIds),
            ];
        });

        return response()->json([
            'code'      => 200,
            'status'    => true,
            'data'      => $result
        ]);
    }
}
