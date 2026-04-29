<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Clinic;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Roles y permisos primero (los usuarios de abajo dependen de ellos)
        $this->call(RolesAndPermissionsSeeder::class);

        // Super Admin (panel /admin, separado del sistema de roles spatie)
        Admin::firstOrCreate(
            ['email' => 'admin@mediapp.local'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
            ]
        );

        $this->seedUrologistScenario();
        $this->seedMultiDoctorScenario();
    }

    /**
     * Scenario A — single urologist with 2 clinics, 2 isolated secretaries.
     * Each secretary belongs to ONE clinic and must NOT see the other's patients.
     */
    private function seedUrologistScenario(): void
    {
        $urologist = User::firstOrCreate(
            ['email' => 'urologa@mediapp.local'],
            [
                'name' => 'Dra. Maria Lopez',
                'password' => Hash::make('password'),
                'specialty' => 'urology',
                'professional_license' => 'MED-001',
                'status' => 'active',
            ]
        );
        $urologist->syncRoles(['doctor_admin']);

        $clinicA = Clinic::firstOrCreate(
            ['name' => 'Consultorio Centro'],
            ['is_active' => true]
        );

        $clinicB = Clinic::firstOrCreate(
            ['name' => 'Consultorio Norte'],
            ['is_active' => true]
        );

        $urologist->clinics()->syncWithoutDetaching([
            $clinicA->id => ['is_primary' => true],
            $clinicB->id => ['is_primary' => false],
        ]);

        $secretaryA = User::firstOrCreate(
            ['email' => 'secre.centro@mediapp.local'],
            [
                'name' => 'Ana Perez',
                'password' => Hash::make('password'),
                'status' => 'active',
            ]
        );
        $secretaryA->syncRoles(['secretary_limited']);
        $secretaryA->clinics()->syncWithoutDetaching([$clinicA->id => ['is_primary' => true]]);

        $secretaryB = User::firstOrCreate(
            ['email' => 'secre.norte@mediapp.local'],
            [
                'name' => 'Lucia Gomez',
                'password' => Hash::make('password'),
                'status' => 'active',
            ]
        );
        $secretaryB->syncRoles(['secretary_limited']);
        $secretaryB->clinics()->syncWithoutDetaching([$clinicB->id => ['is_primary' => true]]);

        // One patient in each clinic, both owned by the urologist
        $patientA = Patient::firstOrCreate(
            ['document_number' => 'URO-A-001'],
            [
                'first_name' => 'Pedro',
                'last_name' => 'Ramirez',
                'date_of_birth' => '1970-05-12',
                'gender' => 'male',
                'document_type' => 'cedula',
                'registered_by' => $urologist->id,
                'is_active' => true,
            ]
        );
        $patientA->doctors()->syncWithoutDetaching([$urologist->id => ['is_primary' => true]]);
        $patientA->clinics()->syncWithoutDetaching([$clinicA->id]);
        $patientA->medicalHistory()->firstOrCreate([]);

        $patientB = Patient::firstOrCreate(
            ['document_number' => 'URO-B-001'],
            [
                'first_name' => 'Carmen',
                'last_name' => 'Diaz',
                'date_of_birth' => '1985-09-30',
                'gender' => 'female',
                'document_type' => 'cedula',
                'registered_by' => $urologist->id,
                'is_active' => true,
            ]
        );
        $patientB->doctors()->syncWithoutDetaching([$urologist->id => ['is_primary' => true]]);
        $patientB->clinics()->syncWithoutDetaching([$clinicB->id]);
        $patientB->medicalHistory()->firstOrCreate([]);
    }

    /**
     * Scenario B — 3 doctors sharing 1 clinic and 1 secretary.
     * Each doctor sees only their patients; secretary sees all 3 in one panel.
     */
    private function seedMultiDoctorScenario(): void
    {
        $clinic = Clinic::firstOrCreate(
            ['name' => 'Policlinico Compartido'],
            ['is_active' => true]
        );

        $doctorsData = [
            ['email' => 'doctor1@mediapp.local', 'name' => 'Dr. Juan Torres',   'specialty' => 'general'],
            ['email' => 'doctor2@mediapp.local', 'name' => 'Dra. Sofia Ruiz',   'specialty' => 'pediatrics'],
            ['email' => 'doctor3@mediapp.local', 'name' => 'Dr. Luis Mendoza',  'specialty' => 'neurology'],
        ];

        $doctors = [];
        foreach ($doctorsData as $i => $data) {
            $doctor = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('password'),
                    'specialty' => $data['specialty'],
                    'professional_license' => 'MED-MULTI-' . ($i + 1),
                    'status' => 'active',
                ]
            );
            // El primer doctor del policlinico es el admin (configura el sistema);
            // los otros dos son associates con permisos clinicos y financieros completos
            // pero sin acceso a configuracion global.
            $doctor->syncRoles([$i === 0 ? 'doctor_admin' : 'doctor_associate']);
            $doctor->clinics()->syncWithoutDetaching([$clinic->id => ['is_primary' => true]]);
            $doctors[] = $doctor;
        }

        $secretary = User::firstOrCreate(
            ['email' => 'secre.compartida@mediapp.local'],
            [
                'name' => 'Rosa Martinez',
                'password' => Hash::make('password'),
                'status' => 'active',
            ]
        );
        $secretary->syncRoles(['secretary_full']);
        $secretary->clinics()->syncWithoutDetaching([$clinic->id => ['is_primary' => true]]);

        // One patient per doctor
        foreach ($doctors as $i => $doctor) {
            $patient = Patient::firstOrCreate(
                ['document_number' => 'MULTI-00' . ($i + 1)],
                [
                    'first_name' => ['Ricardo', 'Elena', 'Miguel'][$i],
                    'last_name' => ['Castro', 'Vargas', 'Ortiz'][$i],
                    'date_of_birth' => ['1990-01-15', '2015-06-20', '1965-11-03'][$i],
                    'gender' => ['male', 'female', 'male'][$i],
                    'document_type' => 'cedula',
                    'registered_by' => $doctor->id,
                    'is_active' => true,
                ]
            );
            $patient->doctors()->syncWithoutDetaching([$doctor->id => ['is_primary' => true]]);
            $patient->clinics()->syncWithoutDetaching([$clinic->id]);
            $patient->medicalHistory()->firstOrCreate([]);
        }
    }
}
