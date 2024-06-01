<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function getSummaryPatient()
    {
        return response()->json([
            'code'      => 200,
            'status'    => true,
            'data'      => [
                'total_patient' => Patient::count(),
                'patient_today' => Patient::whereDate('created_at', date('Y-m-d'))->count()
            ]
        ]);
    }
}
