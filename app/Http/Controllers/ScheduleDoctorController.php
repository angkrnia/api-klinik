<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

class ScheduleDoctorController extends Controller
{
    public function index(Request $request)
    {

        if(!$request->has('day')) {
            $dayNumber = Carbon::now()->dayOfWeek;
            $currentDay = $this->dayName($dayNumber);
        } else {
            $currentDay = $request->day;
        }

        $currentHour = Carbon::now()->format('H:i');

        $schedule = Schedule::with(DOKTER)->where('day', $currentDay)
        ->where('start', '<=', $currentHour)
        ->where('end', '>=', $currentHour)
        ->first();

        if ($schedule) {

            return response()->json([
                'status' => true,
                'data'  => $schedule
            ]);
            // Lakukan sesuatu dengan $schedule, karena jadwal saat ini ditemukan
            // Contoh: $schedule->day, $schedule->start, $schedule->end
        } else {
            // Tidak ada jadwal yang sesuai saat ini
        }
    }

    protected function dayName($dayNumber): string
    {
        switch ($dayNumber) {
            case 0:
                return 'Minggu';
            case 1:
                return 'Senin';
            case 2:
                return 'Selasa';
            case 3:
                return 'Rabu';
            case 4:
                return 'Kamis';
            case 5:
                return 'Jumat';
            case 6:
                return 'Sabtu';
            default:
                return '';
        }
    }
}
