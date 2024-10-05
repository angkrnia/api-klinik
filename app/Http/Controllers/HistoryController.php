<?php

namespace App\Http\Controllers;

use App\Models\History;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class HistoryController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = Auth::user();
        $query = History::query();
        $history = [];

        if ($user->role === PASIEN) {
            $patientIds = $user->patient->pluck('id')->toArray();
            $query = $query->whereIn('patient_id', $patientIds);
        }

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

        if (isset($request['patient_id']) && !empty($request['patient_id'])) {
            $patient_id = $request['patient_id'];
            $query->where('patient_id', $patient_id);
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

        if (isset($request['patient_name']) && !empty($request['patient_name'])) {
            $patientName = $request['patient_name'];
            $query->whereHas('patient', function ($query) use ($patientName) {
                $query->where('fullname', 'LIKE', "%{$patientName}%");
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

        if (isset($request['limit']) || isset($request['page'])) {
            $limit = $request['limit'] ?? 10;
            $history = $query->with(['queue.doctor', 'patient'])->paginate($limit)->appends(request()->query());
        } else {
            $history = $query->with(['queue.doctor', 'patient'])->get(); // Untuk Print atau Download
        }

        return response()->json([
            'code'      => 200,
            'status'    => true,
            'data'      => $history
        ]);
    }
}
