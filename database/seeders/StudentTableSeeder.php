<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Student;
use Illuminate\Support\Facades\Hash;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class StudentTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure foreign keys exist (assumed to be seeded elsewhere)
        $regionCode = '8'; // Example: NCR
        $provinceCode = '837'; // Example: Metro Manila
        $municipalityCode = '83701'; // Example: Manila City
        $barangayCodes = ['83701001', '83701002', '83701003']; // Example barangay codes

        $students = [
            [
                'user' => [
                    'username' => '123456789101',
                    'password' => Hash::make('123456789101'),
                    'role' => User::ROLE_STUDENT,
                    'is_active' => true,
                ],
                'student' => [
                    'student_lrn' => '123456789101',
                    'rfid_no' => '901234567',
                    'first_name' => 'Juan',
                    'middle_name' => 'Dela',
                    'last_name' => 'Cruz',
                    'extension_name' => null,
                    'province_code' => $provinceCode,
                    'municipality_code' => $municipalityCode,
                    'barangay_code' => $barangayCodes[0],
                    'zip_code' => '6510',
                    'religion' => 'Roman Catholic',
                    'birthday' => '2001-05-15',
                    'sex' => 'Male',
                    'disability' => 'None',
                    'email' => 'juancruz@gmail.com',
                    'parent_contact' => '(+63) 905-123-4567',
                    'contact' => '(+63) 912-345-6789',
                    'present_province_code' => $provinceCode,
                    'present_municipality_code' => $municipalityCode,
                    'present_barangay_code' => $barangayCodes[0],
                    'present_zip_code' => '6510',
                    'mother_first_name' => 'Maria',
                    'mother_middle_name' => 'Luz',
                    'mother_last_name' => 'Cruz',
                    'mother_address' => 'Abuyog, Leyte',
                    'father_address' => 'Abuyog, Leyte',
                    'father_first_name' => 'Jose',
                    'father_middle_name' => 'Santos',
                    'father_last_name' => 'Cruz',
                    'father_suffix' => null,
                    'guardian' => 'Pedro Cruz',
                ],
                'avatar' => 'https://placehold.co/150x150?text=Avatar',
            ],
            [
                'user' => [
                    'username' => '123456789102',
                    'password' => Hash::make('123456789102'),
                    'role' => User::ROLE_STUDENT,
                    'is_active' => true,
                ],
                'student' => [
                    'student_lrn' => '123456789102',
                    'rfid_no' => '901234568',
                    'first_name' => 'Ana',
                    'middle_name' => 'Torres',
                    'last_name' => 'Santos',
                    'extension_name' => null,
                    'province_code' => $provinceCode,
                    'municipality_code' => $municipalityCode,
                    'barangay_code' => $barangayCodes[1],
                    'zip_code' => '6511',
                    'religion' => 'Iglesia Ni Cristo',
                    'birthday' => '2002-03-10',
                    'sex' => 'Female',
                    'disability' => 'None',
                    'email' => 'anasantos@gmail.com',
                    'parent_contact' => '(+63) 917-987-6543',
                    'contact' => '(+63) 923-456-7890',
                    'present_province_code' => $provinceCode,
                    'present_municipality_code' => $municipalityCode,
                    'present_barangay_code' => $barangayCodes[1],
                    'present_zip_code' => '6511',
                    'mother_first_name' => 'Luz',
                    'mother_middle_name' => null,
                    'mother_last_name' => 'Torres',
                    'mother_address' => 'Abuyog, Leyte',
                    'father_address' => 'Abuyog, Leyte',
                    'father_first_name' => 'Ramon',
                    'father_middle_name' => 'Dela',
                    'father_last_name' => 'Santos',
                    'father_suffix' => null,
                    'guardian' => 'Teresa Santos',
                ],
                'avatar' => 'https://placehold.co/150x150?text=Avatar',
            ],
            [
                'user' => [
                    'username' => '123456789103',
                    'password' => Hash::make('123456789103'),
                    'role' => User::ROLE_STUDENT,
                    'is_active' => true,
                ],
                'student' => [
                    'student_lrn' => '123456789103',
                    'rfid_no' => '901234569',
                    'first_name' => 'Roberto',
                    'middle_name' => 'Villanueva',
                    'last_name' => 'Reyes',
                    'extension_name' => null,
                    'province_code' => $provinceCode,
                    'municipality_code' => $municipalityCode,
                    'barangay_code' => $barangayCodes[2],
                    'zip_code' => '6512',
                    'religion' => 'Baptist',
                    'birthday' => '2003-01-20',
                    'sex' => 'Male',
                    'disability' => 'None',
                    'email' => 'robertoresyes@gmail.com',
                    'parent_contact' => '(+63) 928-123-9876',
                    'contact' => '(+63) 910-987-1234',
                    'present_province_code' => $provinceCode,
                    'present_municipality_code' => $municipalityCode,
                    'present_barangay_code' => $barangayCodes[2],
                    'present_zip_code' => '6512',
                    'mother_first_name' => 'Carmen',
                    'mother_middle_name' => 'Delos',
                    'mother_last_name' => 'Reyes',
                    'mother_address' => 'Abuyog, Leyte',
                    'father_address' => 'Abuyog, Leyte',
                    'father_first_name' => 'Rogelio',
                    'father_middle_name' => 'Valdez',
                    'father_last_name' => 'Reyes',
                    'father_suffix' => null,
                    'guardian' => 'Rafael Reyes',
                ],
                'avatar' => 'https://placehold.co/150x150?text=Avatar',
            ],
        ];

        foreach ($students as $studentData) {
            // Create or update User
            $user = User::updateOrCreate(
                ['username' => $studentData['user']['username']],
                array_merge($studentData['user'], [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );

            // Create or update Student
            $student = Student::updateOrCreate(
                ['student_lrn' => $studentData['student']['student_lrn']],
                array_merge($studentData['student'], [
                    'user_id' => $user->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );

            // Add avatar to media collection if not already present
            if ($student->getMedia('avatar')->isEmpty()) {
                $student->addMediaFromUrl($studentData['avatar'])
                    ->toMediaCollection('avatar');
            }
        }
    }
}
