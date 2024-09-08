<?php

namespace App\Http\Controllers;

use App\Events\AntrianEvent;
use App\Http\Requests\Queue\QueueRequest;
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
        $patientIds = $user->patient->pluck('id')->toArray();
        $request = $request->query();
        $query = Queue::query();

        $lastQueueId = Queue::where('is_last_queue', true)->orderByDesc('created_at')->value('id');

        if(!$lastQueueId) {
            $lastQueueId = 0;
        }

        if (isset($request['search'])) {
            $searchKeyword = $request['search'];
            $query->keywordSearch($searchKeyword);
        }

        // if (!isset($request['data']) && @$request['data'] !== 'all') {
        // }
        if ($user->role === PASIEN) {
            $antrianSaya = Queue::whereIn('patient_id', $patientIds)
            ->where('id', '>', $lastQueueId)
            ->where(function ($query) {
                $query->where('status', 'waiting')
                ->orWhere('status', 'on waiting')
                ->orWhere('status', 'on process');
            })
            ->value('queue');
            $query->whereIn('patient_id', $patientIds);
        } elseif ($user->role === DOKTER) {
            $query->whereHas(DOKTER, function ($q) use ($user) {
                $q->where('user_id', $user->doctor->id);
            });
        }

        if (isset($request['date']) && !empty($request['date'])) {
            $date = $request['date'];
            $query->whereDate('created_at', $date);
        } else {
            $query->where('id', '>', $lastQueueId);
        }

        if (isset($request['status']) && !empty($request['status'])) {
            $status = $request['status'];
            $query->whereStatus($status);
        }

        if ($user->role === DOKTER) {
            $query->orderBy('created_at', 'asc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        if (isset($request['limit']) || isset($request['page'])) {
            $limit = $request['limit'] ?? 25;
            $result = $query->with([PASIEN, DOKTER, HISTORY])->paginate($limit)->appends(request()->query());
        } else {
            $result = $query->with([PASIEN, DOKTER, HISTORY])->get(); // Untuk Print atau Download
        }

        // Menghitung jumlah antrian pada hari ini
        $queueCount = DB::select('CALL GET_TOTAL_ANTRIAN()')[0]->total_antrian;
        $sisaAntrian = DB::select('CALL GET_SISA_ANTRIAN_ALL()')[0]->sisa_antrian;
        $currentAntrian = DB::select('CALL GET_ANTRIAN_SAAT_INI()')[0]->antrian_saat_ini;

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

        // Menghitung jumlah antrian pada hari ini
        $queueCount = DB::select('CALL GET_TOTAL_ANTRIAN()')[0]->total_antrian;
        $currentAntrian = DB::select('CALL GET_ANTRIAN_SAAT_INI')[0]->antrian_saat_ini;

        if ($user->role == ADMIN || $user->role == DOKTER || $user->role == PERAWAT) {
            $sisaAntrian = DB::select('CALL GET_SISA_ANTRIAN_ALL()')[0]->sisa_antrian;

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
            $lastQueueId = Queue::where('is_last_queue', true)->orderByDesc('created_at')->value('id');
            if(!$lastQueueId) {
                $lastQueueId = 0;
            }
            $patientIds = $user->patient->pluck('id')->toArray();
            $antrianSaya = Queue::whereIn('patient_id', $patientIds)
            ->where('id', '>', $lastQueueId)
            ->where(function ($query) {
                $query->whereIn('status', ['waiting', 'on waiting', 'on process']);
            })
            ->pluck('queue');
            $sisaAntrian = Queue::whereIn('status', ['waiting', 'on waiting', 'on process'])
                ->where('id', '>', $lastQueueId)
                ->whereIn('queue', $antrianSaya)
                ->pluck('queue');
            $antrian = Queue::with([DOKTER, PASIEN])
                ->where('id', '>', $lastQueueId)
                ->whereIn('patient_id', $patientIds)
                ->where(function ($query) {
                    $query->where('status', 'waiting')
                        ->orWhere('status', 'on waiting')
                ->orWhere('status', 'on process');
                })
                ->get();
            $antrianSaatIni = Queue::where('id', '>', $lastQueueId)->where('status', 'waiting')->first();
            if(!$antrianSaatIni) {
                $antrianSaatIni = null;
            } else {
                $antrianSaatIni = $antrianSaatIni->queue;
            }
            return response()->json([
                'code'      => 200,
                'status'    => true,
                'data' => [
                    'antrian_hari_ini' => $queueCount,
                    'antrian_saat_ini' => $currentAntrian,
                    'antrian_saya'     => $antrianSaya,
                    'sisa_antrian'     => $sisaAntrian->map(function ($item) use ($antrianSaatIni) {
                        return abs($item - $antrianSaatIni);
                    }),
                    'antrian'          => $antrian
                ]
            ]);
        }
    }

    public function publicAntrian()
    {
        $queueCount = DB::select('CALL GET_TOTAL_ANTRIAN()')[0]->total_antrian;
        $currentAntrian = DB::select('CALL GET_ANTRIAN_SAAT_INI')[0]->antrian_saat_ini;
        return response()->json([
            'code'      => 200,
            'status'    => true,
            'message'    => 'Sukses',
            'data' => [
                'antrian_hari_ini' => $queueCount,
                'antrian_saat_ini' => $currentAntrian,
            ]
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
    public function store(QueueRequest $request)
    {
        try {
            DB::beginTransaction();
            $patientId = $request->input('patient_id');
            $lastQueueId = Queue::where('is_last_queue', true)->orderByDesc('created_at')->value('id');
            if (!$lastQueueId) {
                $lastQueueId = 0;
            }
            $existingWaitingQueue = Queue::where('id', '>', $lastQueueId)
                ->where('patient_id', $patientId)
                ->whereIn('status', ['waiting', 'on waiting'])
                ->first();

            if ($existingWaitingQueue) {
                return response()->json(['message' => 'Anda masih memiliki antrian yang sedang ditunggu. Tidak bisa membuat antrian baru'], 422);
            }

            // $lastQueue = Queue::whereDate('created_at', $currentDate)
            // ->orderByDesc('queue')
            // ->first();
            $lastQueue = Queue::latest()->first();
            $newQueueNumber = $lastQueue ? ($lastQueue->is_last_queue ? 1 : $lastQueue->queue + 1) : 1;

            // CARI DOKTER YANG SEDANG BERTUGAS
            // $query = "SELECT id, fullname FROM doctors WHERE DAYNAME(NOW()) BETWEEN start_day AND end_day AND TIME(NOW()) BETWEEN start_time AND end_time LIMIT 1";

            $query = "SELECT id, fullname
            FROM doctors
            WHERE 
                DAYOFWEEK(NOW()) BETWEEN 
                    FIELD(LOWER(start_day), ?, ?, ?, ?, ?, ?, ?) AND 
                    FIELD(LOWER(end_day), ?, ?, ?, ?, ?, ?, ?)
                AND
                (
                    (start_time < end_time AND TIME(NOW()) BETWEEN start_time AND end_time)
                    OR
                    (start_time > end_time AND (TIME(NOW()) >= start_time OR TIME(NOW()) <= end_time))
                )";

            $days = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
            $params = [...$days, ...$days];

            $doctor = DB::select($query, $params);

            $status = empty($request->input('height')) || empty($request->input('weight')) || empty($request->input('temperature')) ? 'on waiting' : 'waiting';

            $queue = Queue::create([
                'status' => $status,
                'queue' => $newQueueNumber,
                'patient_id' => $request->input('patient_id'),
                'doctor_id' => $doctor[0]->id ?? 1,
            ]);

            $queue->history()->create([
                'complaint' => $request->input('complaint'),
                'blood_pressure' => $request->input('blood_pressure'),
                'height' => $request->input('height'),
                'weight' => $request->input('weight'),
                'temperature' => $request->input('temperature'),
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
            Log::info($th);
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
        abort(403);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Queue $queue)
    {
        Log::info($queue);
        $user = Auth::user();
        $doctor = $user->doctor;
        $queue->update([
            'status' => $request->status,
            'doctor_id' => $doctor->id
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

        // events
        AntrianEvent::dispatch("$queue->queue|$soundUrl|$doctor->fullname");

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

    public function resetAntrian()
    {
        $latestQueue = Queue::latest()->first();
        if ($latestQueue->status !== 'waiting' && $latestQueue->status !== 'on waiting') {
            $latestQueue->update(['is_last_queue' => true]);
            return response()->json(['message' => 'Antrian berhasil direset']);
        } else {
            return response()->json(['message' => 'Masih ada antrian yang belum selesai, tidak bisa direset. Mohon selesaikan seluruh antrian terlebih dahulu'], 400);
        }
    }

    public function vitalSign(Request $request, Queue $queue)
    {
        $request->validate([
            'blood_pressure' => ['required', 'string', 'max:255'],
            'height' => ['required', 'string', 'max:255'],
            'weight' => ['required', 'string', 'max:255'],
            'temperature' => ['required', 'string', 'max:255'],
            'complaint' => ['required', 'string', 'max:255'],
        ]);

        try {
            $queue->update([
                'status' => 'waiting'
            ]);

            $queue->history()->update([
                'blood_pressure' => $request->blood_pressure,
                'height' => $request->height,
                'weight' => $request->weight,
                'temperature' => $request->temperature,
                'complaint' => $request->complaint,
            ]);

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
}
