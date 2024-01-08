<?php

namespace App\Http\Controllers;

use App\Http\Requests\Queue\QueueRequest;
use App\Models\Queue;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class QueueController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->role === 'admin') {
            $request = $request->query();
            $query = Queue::query();

            if (isset($request['search'])) {
                $searchKeyword = $request['search'];
                $query->keywordSearch($searchKeyword);
            }

            $query->orderBy('created_at', 'desc');

            if (isset($request['limit']) || isset($request['page'])) {
                $limit = $request['limit'] ?? 10;
                $result = $query->with(['patient', 'doctor'])->paginate($limit);
            } else {
                $result = $query->with(['patient', 'doctor'])->get(); // Untuk Print atau Download
            }
        } else {
            $result = Queue::with(['patient', 'doctor'])->where('user_id', $user->id)->first();
        }

        $currentDate = Carbon::now()->toDateString();

        // Menghitung jumlah antrian pada hari ini
        $queueCount = Queue::whereDate('created_at', $currentDate)->count();

        return response()->json([
            'code'          => 200,
            'status'        => true,
            'data'          => $result,
            'total_antrian' => $queueCount,
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

            $queue = Queue::create([
                'queue' => $newQueueNumber,
                'patient_id' => $request->input('patient_id'),
                'doctor_id' => $request->input('doctor_id'),
            ]);

            $queue->history()->create([
                'complaint' => $request->input('complaint'),
                'patient_id' => $request->input('patient_id'),
            ]);

            DB::commit();
            return response()->json([
                'code'      => 200,
                'status'    => true,
                'message'   => 'Antrian baru berhasil ditambahkankan.',
                'data'      => $queue
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'code'      => 200,
                'status'    => true,
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
    public function update(QueueRequest $request, Queue $queue)
    {
        $queue->update($request->validated());

        return response()->json([
            'code'      => 200,
            'status'    => true,
            'message'   => 'Antrian berhasil diupdate.',
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
}
