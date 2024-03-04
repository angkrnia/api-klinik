<?php

namespace App\Http\Controllers;

use App\Http\Requests\Queue\QueueRequest;
use App\Models\Doctor;
use App\Models\Queue;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class QueueController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $request = $request->query();
        $query = Queue::query();
        $currentDate = Carbon::now()->toDateString();

        if (isset($request['search'])) {
            $searchKeyword = $request['search'];
            $query->keywordSearch($searchKeyword);
        }

        if (!isset($request['data']) && @$request['data'] !== 'all') {
            if ($user->role === PASIEN) {
                $antrianSaya = Queue::where('patient_id', $user->patient->id)->whereDate('created_at', $currentDate)->whereStatus('waiting')->value('queue');
                $query->where('patient_id', $user->patient->id);
            } elseif ($user->role === DOKTER) {
                $query->whereHas(DOKTER, function ($q) use ($user) {
                    $q->where('user_id', $user->doctor->id);
                });
            }
        }

        if (isset($request['date']) && !empty($request['date'])) {
            $date = $request['date'];
            $query->whereDate('created_at', $date);
        }

        if (isset($request['status']) && !empty($request['status'])) {
            $status = $request['status'];
            $query->whereStatus($status);
        }

        if($user->role === DOKTER) {
            $query->orderBy('created_at', 'asc');
        } else {
            $query->orderBy('created_at', 'desc');
        }


        if (isset($request['limit']) || isset($request['page'])) {
            $limit = $request['limit'] ?? 10;
            $result = $query->with([PASIEN, DOKTER, HISTORY])->paginate($limit);
        } else {
            $result = $query->with([PASIEN, DOKTER, HISTORY])->get(); // Untuk Print atau Download
        }

        // Menghitung jumlah antrian pada hari ini
        $queueCount = Queue::whereDate('created_at', $currentDate)->count();
        $sisaAntrian = Queue::where('status', 'waiting')->whereDate('created_at', $currentDate)->count();
        $currentAntrian = Queue::where('status', 'on process')->whereDate('created_at', $currentDate)->value('queue');

        return response()->json([
            'code'             => 200,
            'status'           => true,
            'antrian_hari_ini' => $queueCount,
            'antrian_saat_ini' => $currentAntrian,
            'antrian_saya'     => isset($antrianSaya) ? $antrianSaya : null,
            'sisa_antrian'     => $sisaAntrian,
            'data'             => $result,
        ]);
    }

    public function checkAntrian()
    {
        $user = Auth::user();
        $currentDate = Carbon::now()->toDateString();
        
        // Menghitung jumlah antrian pada hari ini
        $queueCount = Queue::whereDate('created_at', $currentDate)->count();
        $currentAntrian = Queue::where('status', 'on process')->whereDate('created_at', $currentDate)->value('queue');
        
        if ($user->role == ADMIN || $user->role == DOKTER) {
            $sisaAntrian = Queue::where('status', 'waiting')->whereDate('created_at', $currentDate)->count();

            return response()->json([
                'code'      => 200,
                'status'    => true,
                'data'      => [
                    'antrian_hari_ini' => $queueCount,
                    'sisa_antrian' => $sisaAntrian,
                    'antrian_saat_ini' => $currentAntrian,
                ]
            ]);
        } else {
            $antrianSaya = Queue::where('patient_id', $user->patient->id)->whereDate('created_at', $currentDate)->whereStatus('waiting')->value('queue');
            $sisaAntrian = Queue::where('status', 'waiting')
            ->whereDate('created_at', $currentDate)
            ->where('queue', $antrianSaya)
            ->value('queue') - 
            Queue::where('status', 'waiting')
            ->whereDate('created_at', $currentDate)
            ->min('queue');

            return response()->json([
                'code'      => 200,
                'status'    => true,
                'data' => [
                    'antrian_hari_ini' => $queueCount,
                    'antrian_saat_ini' => $currentAntrian,
                    'antrian_saya'     => $antrianSaya,
                    'sisa_antrian'     => (int) $sisaAntrian + 1,
                ]
            ]);
        }
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
    public function store(QueueRequest $request)
    {
        try {
            DB::beginTransaction();
            $currentDate = Carbon::now()->toDateString();
            $patientId = $request->input('patient_id');

            $existingWaitingQueue = Queue::whereDate('created_at', $currentDate)
                ->where('status', 'waiting')
                ->where('patient_id', $patientId)
                ->first();

            if ($existingWaitingQueue) {
                return response()->json(['message' => 'Anda masih memiliki antrian yang sedang ditunggu. Tidak bisa membuat antrian baru'], 422);
            }

            $lastQueue = Queue::whereDate('created_at', $currentDate)
                ->orderByDesc('queue')
                ->first();

            if ($lastQueue) {
                $newQueueNumber = $lastQueue->queue + 1;
            } else {
                $newQueueNumber = 1;
            }

            // CARI DOKTER YANG SEDANG BERTUGAS
            $query = "SELECT id, fullname FROM doctors WHERE DAYNAME(NOW()) BETWEEN start_day AND end_day AND TIME(NOW()) BETWEEN start_time AND end_time LIMIT 1";

            $doctor = DB::select($query);
            
            $queue = Queue::create([
                'queue' => $newQueueNumber,
                'patient_id' => $request->input('patient_id'),
                'doctor_id' => $doctor[0]->id ?? 2,
            ]);

            $queue->history()->create([
                'complaint' => $request->input('complaint'),
                'patient_id' => $request->input('patient_id'),
            ]);

            DB::commit();
            return response()->json([
                'code'      => 200,
                'status'    => true,
                'message'   => 'Antrian baru berhasil ditambahkan.',
                'data'      => $queue
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'code'      => 500,
                'status'    => false,
                'message'   => $th->getMessage() ?? '',
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $queue = Queue::findOrFail($id);

            return response()->json([
                'code'      => 200,
                'status'    => true,
                'data'      => $queue
            ]);
        } catch (\Throwable $th) {
            if ($th instanceof ModelNotFoundException) {
                return response()->json(['error' => 'Antrian tidak ditemukan'], 404);
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
    public function update(Request $request, Queue $queue)
    {
        $queue->update([
            'status' => $request->status
        ]);

        $soundPath = public_path('assets/voice-announcement/' . $queue->queue . '.mp3');

        // Memeriksa apakah file suara ada
        if (File::exists($soundPath)) {
            // Jika file suara ada, mengembalikan URL untuk file tersebut
            $soundUrl = asset('assets/voice-announcement/' . $queue->queue . '.mp3');
        } else {
            // Jika file suara tidak ada, mengembalikan URL untuk file 'selanjutnya.mp3'
            $soundUrl = asset('assets/voice-announcement/selanjutnya.mp3');
        }

        return response()->json([
            'code'      => 200,
            'status'    => true,
            'message'   => 'Antrian berhasil diupdate.',
            'sound'     => $soundUrl,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Queue $queue)
    {
        $queue->delete();

        return response()->json([
            'code'      => 200,
            'status'    => true,
            'message'   => 'Antrian berhasil dihapus.',
        ]);
    }

    public function selesai(Request $request, Queue $queue)
    {
        $request->validate([
            'diagnosa'  => ['nullable', 'string', 'max:255'],
            'saran'     => ['nullable', 'string', 'max:255']
        ]);

        try {
            $queue->update([
                'status' => Queue::SELESAI
            ]);

            $queue->history()->updateOrCreate(
                ['queue_id' => $queue->id],
                [
                    'diagnosa' => $request->diagnosa,
                    'saran' => $request->saran,
                    'patient_id' => $queue->patient_id,
                ]
            );

            return response()->json([
                'code' => 200,
                'status' => true,
                'message' => 'Antrian berhasil diupdate.',
            ]);
        } catch (\Throwable $th) {
            if ($th instanceof ModelNotFoundException) {
                return response()->json(['error' => 'Antrian tidak ditemukan'], 404);
            } else {
                return response()->json(['error' => $th->getMessage()], 500);
            }
        }
    }
    public function batal(Request $request, Queue $queue)
    {
        $request->validate([
            'status'  => ['required', 'string', 'max:255'],
        ]);

        try {
            $queue->update([
                'status' => Queue::BATAL
            ]);

            return response()->json([
                'code' => 200,
                'status' => true,
                'message' => 'Antrian berhasil dibatalkan.',
            ]);
        } catch (\Throwable $th) {
            if ($th instanceof ModelNotFoundException) {
                return response()->json(['error' => 'Antrian tidak ditemukan'], 404);
            } else {
                return response()->json(['error' => $th->getMessage()], 500);
            }
        }
    }
}
