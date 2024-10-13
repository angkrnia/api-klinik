<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS GetDoctorOnDuty');
        DB::unprepared('
            CREATE PROCEDURE GetDoctorOnDuty(IN input_day VARCHAR(10), IN input_time VARCHAR(10))
            BEGIN
                SELECT d.fullname AS doctor_name, s.start_time, s.end_time, s.day
                FROM doctor_schedules s
                JOIN doctors d ON s.doctor_id = d.id
                WHERE s.day = UPPER(input_day)
                  AND input_time BETWEEN s.start_time AND s.end_time
                  AND s.status = 1;
            END;
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS GetDoctorOnDuty');
    }
};
