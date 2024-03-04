<?php

namespace App\Http\Controllers;

use App\Models\History;
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
            $patient = $user->patient()->first();
            $query = $query->where('patient_id', $patient->id);
        }

        if (isset($request['search'])) {
            $searchKeyword = $request['search'];
            $query->keywordSearch($searchKeyword);
        }

        $query->orderBy('created_at', 'desc');

        if (isset($request['limit']) || isset($request['page'])) {
            $limit = $request['limit'] ?? 10;
            $history = $query->with('queue.doctor')->paginate($limit);
        } else {
            $history = $query->with('queue.doctor')->get(); // Untuk Print atau Download
        }

        return response()->json([
            'code'      => 200,
            'status'    => true,
            'data'      => $history
        ]);
    }
}
