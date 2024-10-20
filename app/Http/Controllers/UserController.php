<?php

namespace App\Http\Controllers;

use App\Models\History;
use App\Models\Patient;
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
        if (auth()->user()->role === DOKTER) {
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
        $request->validate([
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            // 'record_no' => ['nullable', 'string', 'max:255'],
            'fullname' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'lowercase', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            // 'gender' => ['nullable', 'string', 'max:12'],
            // 'birthday' => ['nullable', 'string', 'max:15'],
            // 'age' => ['nullable', 'integer'],
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

            // Jika dokter
            if (auth()->user()->role === DOKTER) {
                $user->doctor()->update([
                    'fullname' => $request->fullname,
                    'phone' => $request->phone,
                    'description' => $request->description,
                ]);
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

    public function detail(Request $request, Patient $patient)
    {
        $history = [];
        $query = History::query();
        $query->where('patient_id', $patient->id);

        // FILTER
        if (isset($request['from'])) {
            $from = $request['from'];
        }

        if (isset($request['to'])) {
            $to = $request['to'];
        }

        if (isset($request['from']) && isset($request['to'])) {
            $to = \Carbon\Carbon::parse($to)->endOfDay();
            $query->whereBetween('created_at', [$from, $to]);
        }


        if (isset($request['doctor_id']) && !empty($request['doctor_id'])) {
            $doctor_id = $request['doctor_id'];
            $query->whereHas('queue.doctor', function ($query) use ($doctor_id) {
                $query->where('id', $doctor_id);
            });
        }

        if (isset($request['status']) && !empty($request['status'])) {
            $status = $request['status'];
            $query->whereHas('queue', function ($query) use ($status) {
                $query->where('status', $status);
            });
        }

        if (isset($request['sort']) && !empty($request['sort'])) {
            $sort = $request['sort'];
            $query->orderBy('created_at', $sort);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        if (isset($request['search'])) {
            $searchKeyword = $request['search'];
            $query->keywordSearch($searchKeyword);
        }

        $limit = $request['limit'] ?? 10;
        $history = $query->with(['queue.doctor', 'patient'])->paginate($limit)->appends(request()->query());

        return response()->json([
            'code'      => 200,
            'status'    => true,
            'patient'   => $patient,
            'history'   => $history
        ]);
    }
}
