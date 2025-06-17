<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Teacher;
use Illuminate\Support\Facades\Hash;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class TeacherTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure foreign keys exist in related tables (assumed to be seeded elsewhere)
        $regionCode = '8'; // Example: NCR (adjust to match your regions table)
        $provinceCode = '837'; // Example: Metro Manila (adjust to match your provinces table)
        $municipalityCode = '83724'; // Example: Manila City (adjust to match your municipalities table)
        $barangayCodes = ['83701001', '83701002', '83701003']; // Example barangay codes

        $teachers = [
            [
                'username' => 'TCHR001',
                'password' => Hash::make('TCHR001'),
                'role' => User::ROLE_TEACHER,
                'is_active' => true,
                'teacher' => [
                    'first_name' => 'Amelia',
                    'middle_name' => 'Santos',
                    'last_name' => 'De Guzman',
                    'extension_name' => null,
                    'province_code' => $provinceCode,
                    'municipality_code' => $municipalityCode,
                    'barangay_code' => $barangayCodes[0],
                    'zip_code' => '6510',
                    'religion' => 'Roman Catholic',
                    'birthday' => '1985-03-15',
                    'civil_status' => 'Married',
                    'email' => 'amelia.deguzman@gmail.com',
                    'contact' => '(+63) 912-345-6789',
                ],
                'avatar' => 'https://via.placeholder.com/150', // Placeholder image URL
            ],
            [
                'username' => 'TCHR002',
                'password' => Hash::make('TCHR002'),
                'role' => User::ROLE_TEACHER,
                'is_active' => true,
                'teacher' => [
                    'first_name' => 'Benigno',
                    'middle_name' => 'Reyes',
                    'last_name' => 'Lopez',
                    'extension_name' => null,
                    'province_code' => $provinceCode,
                    'municipality_code' => $municipalityCode,
                    'barangay_code' => $barangayCodes[1],
                    'zip_code' => '6511',
                    'religion' => 'Iglesia Ni Cristo',
                    'birthday' => '1978-11-22',
                    'civil_status' => 'Single',
                    'email' => 'benigno.lopez@gmail.com',
                    'contact' => '(+63) 917-654-3210',
                ],
                'avatar' => 'https://via.placeholder.com/150',
            ],
            [
                'username' => 'TCHR003',
                'password' => Hash::make('TCHR003'),
                'role' => User::ROLE_TEACHER,
                'is_active' => true,
                'teacher' => [
                    'first_name' => 'Cecilia',
                    'middle_name' => 'Torres',
                    'last_name' => 'Garcia',
                    'extension_name' => null,
                    'province_code' => $provinceCode,
                    'municipality_code' => $municipalityCode,
                    'barangay_code' => $barangayCodes[2],
                    'zip_code' => '6512',
                    'religion' => 'Born Again',
                    'birthday' => '1990-07-08',
                    'civil_status' => 'Widowed',
                    'email' => 'cecilia.garcia@gmail.com',
                    'contact' => '(+63) 928-111-2222',
                ],
                'avatar' => 'https://via.placeholder.com/150',
            ],
        ];

        foreach ($teachers as $teacherData) {
            // Create or update User
            $user = User::updateOrCreate(
                ['username' => $teacherData['username']],
                [
                    'password' => $teacherData['password'],
                    'role' => $teacherData['role'],
                    'is_active' => $teacherData['is_active'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            // Create or update Teacher
            $teacher = Teacher::updateOrCreate(
                ['user_id' => $user->id],
                array_merge($teacherData['teacher'], [
                    'user_id' => $user->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );

            // Add avatar to media collection if not already present
            if ($teacher->getMedia('avatar')->isEmpty()) {
                $teacher->addMediaFromUrl($teacherData['avatar'])
                    ->toMediaCollection('avatar');
            }
        }
    }
}
