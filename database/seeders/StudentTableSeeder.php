<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StudentTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Insert users (assuming standard Laravel users table)
        $users = [
            [
                'username' => '123456789101',
                'password' => Hash::make('123456789101'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'username' => '123456789102',
                'password' => Hash::make('123456789102'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'username' => '123456789103',
                'password' => Hash::make('123456789103'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('users')->insert($users);

        // Insert students
        DB::table('students')->insert([
            [
                'student_lrn' => '123456789101',
                'user_id' => DB::table('users')->where('username', '123456789101')->first()->id,
                'rfid_no' => '901234567',
                'first_name' => 'Juan',
                'middle_name' => 'Dela',
                'last_name' => 'Cruz',
                'extension_name' => null,
                'province_code' => 837,
                'municipality_code' => 83701,
                'barangay_code' => 83701001,
                'zip_code' => '6510',
                'religion' => 'Roman Catholic',
                'birthday' => '2001-05-15',
                'sex' => 'Male',
                'disability' => 'None',
                'email' => 'juancruz@gmail.com',
                'parent_contact' => '(+63) 905-123-4567',
                'contact' => '(+63) 912-345-6789',
                'present_province_code' => 837,
                'present_municipality_code' => 83701,
                'present_barangay_code' => 83701001,
                'present_zip_code' => '6510',
                'mother_first_name' => 'Maria',
                'mother_middle_name' => 'Luz',
                'mother_last_name' => 'Cruz',
                'mother_address' => 'Brgy. San Roque, Abuyog, Leyte',
                'father_first_name' => 'Jose',
                'father_middle_name' => 'Santos',
                'father_last_name' => 'Cruz',
                'father_suffix' => null,
                'father_address' => 'Brgy. San Roque, Abuyog, Leyte',
                'guardian' => 'Pedro Cruz',
                'guardian_address' => 'Brgy. San Roque, Abuyog, Leyte',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'student_lrn' => '123456789102',
                'user_id' => DB::table('users')->where('username', '123456789102')->first()->id,
                'rfid_no' => '901234568',
                'first_name' => 'Ana',
                'middle_name' => 'Torres',
                'last_name' => 'Santos',
                'extension_name' => null,
                'province_code' => 837,
                'municipality_code' => 83701,
                'barangay_code' => 83701002,
                'zip_code' => '6511',
                'religion' => 'Iglesia Ni Cristo',
                'birthday' => '2002-03-10',
                'sex' => 'Female',
                'disability' => 'None',
                'email' => 'anasantos@gmail.com',
                'parent_contact' => '(+63) 917-987-6543',
                'contact' => '(+63) 923-456-7890',
                'present_province_code' => 837,
                'present_municipality_code' => 83701,
                'present_barangay_code' => 83701002,
                'present_zip_code' => '6511',
                'mother_first_name' => 'Luz',
                'mother_middle_name' => null,
                'mother_last_name' => 'Torres',
                'mother_address' => 'Brgy. San Juan, Abuyog, Leyte',
                'father_first_name' => 'Ramon',
                'father_middle_name' => 'Dela',
                'father_last_name' => 'Santos',
                'father_suffix' => null,
                'father_address' => 'Brgy. San Juan, Abuyog, Leyte',
                'guardian' => 'Teresa Santos',
                'guardian_address' => 'Brgy. San Juan, Abuyog, Leyte',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'student_lrn' => '123456789103',
                'user_id' => DB::table('users')->where('username', '123456789103')->first()->id,
                'rfid_no' => '901234569',
                'first_name' => 'Roberto',
                'middle_name' => 'Villanueva',
                'last_name' => 'Reyes',
                'extension_name' => null,
                'province_code' => 837,
                'municipality_code' => 83701,
                'barangay_code' => 83701003,
                'zip_code' => '6512',
                'religion' => 'Baptist',
                'birthday' => '2003-01-20',
                'sex' => 'Male',
                'disability' => 'None',
                'email' => 'robertoresyes@gmail.com',
                'parent_contact' => '(+63) 928-123-9876',
                'contact' => '(+63) 910-987-1234',
                'present_province_code' => 837,
                'present_municipality_code' => 83701,
                'present_barangay_code' => 83701003,
                'present_zip_code' => '6512',
                'mother_first_name' => 'Carmen',
                'mother_middle_name' => 'Delos',
                'mother_last_name' => 'Reyes',
                'mother_address' => 'Brgy. Victory, Abuyog, Leyte',
                'father_first_name' => 'Rogelio',
                'father_middle_name' => 'Valdez',
                'father_last_name' => 'Reyes',
                'father_suffix' => null,
                'father_address' => 'Brgy. Victory, Abuyog, Leyte',
                'guardian' => 'Rafael Reyes',
                'guardian_address' => 'Brgy. Victory, Abuyog, Leyte',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
