<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Dokter
        $userFriska = \App\Models\User::create([
            'fullname' => 'dr Friska Yeni Sinamo',
            'email' => 'dr.friska@gmail.com',
            'phone' => '6282210157601',
            'password' => bcrypt('123456'),
            'role' => DOKTER,
        ]);
        $userAnwar  = \App\Models\User::create([
            'fullname' => 'dr Anwar',
            'email' => 'dr.anwar@gmail.com',
            'phone' => '6282210157602',
            'password' => bcrypt('123456'),
            'role' => DOKTER,
        ]);
        $userTiwi = \App\Models\User::create([
            'fullname' => 'dr Tiwi',
            'email' => 'dr.tiwi@gmail.com',
            'phone' => '6282210157603',
            'password' => bcrypt('123456'),
            'role' => DOKTER,
        ]);
        $userLena = \App\Models\User::create([
            'fullname' => 'dr Lena',
            'email' => 'dr.lena@gmail.com',
            'phone' => '6282210157604',
            'password' => bcrypt('123456'),
            'role' => DOKTER,
        ]);
        \App\Models\User::create([
            'fullname' => 'Angga kurnia',
            'email' => 'angga@gmail.com',
            'phone' => '6282210157605',
            'password' => bcrypt('123456'),
            'role' => ADMIN,
        ]);
        \App\Models\User::create([
            'fullname' => 'Perawat',
            'email' => 'perawat@gmail.com',
            'phone' => '6282210157606',
            'password' => bcrypt('123456'),
            'role' => PERAWAT,
        ]);

        // DOKTER
        $doctorFriska = \App\Models\Doctor::create([
            'fullname' => $userFriska->fullname,
            'phone' => $userFriska->phone,
            'avatar' => 'https://ketik.co.id/assets/upload/20231224081016img-20231223-wa00950.webp',
            'gender' => 'P',
            'description' => 'Dokter berpengalaman sudah lebih dari 10 tahun',
            'user_id' => $userFriska->id,
        ]);
        $doctorAnwar = \App\Models\Doctor::create([
            'fullname' => $userAnwar->fullname,
            'phone' => $userAnwar->phone,
            'avatar' => 'https://cdn-images.hipwee.com/wp-content/uploads/2018/04/hipwee-anton-tanjung.jpg',
            'gender' => 'L',
            'description' => 'Dokter berpengalaman sudah lebih dari 10 tahun',
            'user_id' => $userAnwar->id,
        ]);
        $doctorTiwi = \App\Models\Doctor::create([
            'fullname' => $userTiwi->fullname,
            'phone' => $userTiwi->phone,
            'avatar' => 'https://asset-a.grid.id/crop/0x0:0x0/x/photo/2019/08/07/2058946134.jpg',
            'gender' => 'P',
            'description' => 'Dokter berpengalaman sudah lebih dari 10 tahun',
            'user_id' => $userTiwi->id,
        ]);
        $doctorLena = \App\Models\Doctor::create([
            'fullname' => $userLena->fullname,
            'phone' => $userLena->phone,
            'avatar' => 'https://bekasisatu.id/wp-content/uploads/2024/09/IMG-20240909-WA0010-e1725853006677.jpg',
            'gender' => 'P',
            'description' => 'Dokter berpengalaman sudah lebih dari 10 tahun',
            'user_id' => $userLena->id,
        ]);

        // Jadwal Dokter
        \App\Models\DoctorSchedule::insert([
            [
                'day' => 'MONDAY',
                'status' => true,
                'doctor_id' => $doctorAnwar->id,
                'start_time' => '00:00',
                'end_time' => '09:00',
            ],
            [
                'day' => 'MONDAY',
                'status' => true,
                'doctor_id' => $doctorTiwi->id,
                'start_time' => '09:00',
                'end_time' => '13:00',
            ],
            [
                'day' => 'MONDAY',
                'status' => true,
                'doctor_id' => $doctorFriska->id,
                'start_time' => '13:00',
                'end_time' => '20:00',
            ],
            [
                'day' => 'MONDAY',
                'status' => true,
                'doctor_id' => $doctorTiwi->id,
                'start_time' => '20:00',
                'end_time' => '23:59',
            ],
            [
                'day' => 'TUESDAY',
                'status' => true,
                'doctor_id' => $doctorTiwi->id,
                'start_time' => '00:00',
                'end_time' => '13:00',
            ],
            [
                'day' => 'TUESDAY',
                'status' => true,
                'doctor_id' => $doctorFriska->id,
                'start_time' => '13:00',
                'end_time' => '20:00',
            ],
            [
                'day' => 'TUESDAY',
                'status' => true,
                'doctor_id' => $doctorTiwi->id,
                'start_time' => '20:00',
                'end_time' => '23:59',
            ],
            [
                'day' => 'WEDNESDAY',
                'status' => true,
                'doctor_id' => $doctorTiwi->id,
                'start_time' => '00:00',
                'end_time' => '13:00',
            ],
            [
                'day' => 'WEDNESDAY',
                'status' => true,
                'doctor_id' => $doctorFriska->id,
                'start_time' => '13:00',
                'end_time' => '20:00',
            ],
            [
                'day' => 'WEDNESDAY',
                'status' => true,
                'doctor_id' => $doctorTiwi->id,
                'start_time' => '20:00',
                'end_time' => '23:59',
            ],
            [
                'day' => 'THURSDAY',
                'status' => true,
                'doctor_id' => $doctorTiwi->id,
                'start_time' => '00:00',
                'end_time' => '13:00',
            ],
            [
                'day' => 'THURSDAY',
                'status' => true,
                'doctor_id' => $doctorFriska->id,
                'start_time' => '13:00',
                'end_time' => '20:00',
            ],
            [
                'day' => 'THURSDAY',
                'status' => true,
                'doctor_id' => $doctorTiwi->id,
                'start_time' => '20:00',
                'end_time' => '23:59',
            ],
            [
                'day' => 'FRIDAY',
                'status' => true,
                'doctor_id' => $doctorTiwi->id,
                'start_time' => '00:00',
                'end_time' => '09:00',
            ],
            [
                'day' => 'FRIDAY',
                'status' => true,
                'doctor_id' => $doctorLena->id,
                'start_time' => '09:00',
                'end_time' => '13:00',
            ],
            [
                'day' => 'FRIDAY',
                'status' => true,
                'doctor_id' => $doctorFriska->id,
                'start_time' => '13:00',
                'end_time' => '20:00',
            ],
            [
                'day' => 'FRIDAY',
                'status' => true,
                'doctor_id' => $doctorLena->id,
                'start_time' => '20:00',
                'end_time' => '23:59',
            ],
            [
                'day' => 'SATURDAY',
                'status' => true,
                'doctor_id' => $doctorLena->id,
                'start_time' => '00:00',
                'end_time' => '13:00',
            ],
            [
                'day' => 'SATURDAY',
                'status' => true,
                'doctor_id' => $doctorFriska->id,
                'start_time' => '13:00',
                'end_time' => '20:00',
            ],
            [
                'day' => 'SATURDAY',
                'status' => true,
                'doctor_id' => $doctorLena->id,
                'start_time' => '20:00',
                'end_time' => '23:59',
            ],
            [
                'day' => 'SUNDAY',
                'status' => true,
                'doctor_id' => $doctorLena->id,
                'start_time' => '00:00',
                'end_time' => '09:00',
            ],
            [
                'day' => 'SUNDAY',
                'status' => true,
                'doctor_id' => $doctorLena->id,
                'start_time' => '09:00',
                'end_time' => '23:59',
            ],
        ]);
    }
}
