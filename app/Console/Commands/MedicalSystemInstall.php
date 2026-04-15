<?php

namespace App\Console\Commands;

use App\Models\Admin;
use App\Models\Clinic;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\password;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

class MedicalSystemInstall extends Command
{
    protected $signature = 'medicalsystem:install {--force : Continuar aun si ya hay datos operativos}';

    protected $description = 'Instalacion interactiva: siembra roles, super admin, doctor principal, clinicas y staff segun el perfil elegido';

    public function handle(): int
    {
        $this->info('Instalacion de Sistema Medico');
        $this->line('');

        if (User::exists() && ! $this->option('force')) {
            $this->error('La base de datos ya tiene usuarios. Ejecute `php artisan migrate:fresh` primero o use --force.');
            return self::FAILURE;
        }

        $this->call('db:seed', ['--class' => RolesAndPermissionsSeeder::class, '--force' => true]);

        Admin::firstOrCreate(
            ['email' => 'admin@mediapp.local'],
            ['name' => 'Super Admin', 'password' => Hash::make('password')],
        );
        $this->info('Super admin listo: admin@mediapp.local / password');
        $this->line('');

        $profile = select(
            label: 'Perfil de instalacion',
            options: [
                'solo' => 'Doctor individual (1 doctor, 1 clinica, sin secretaria)',
                'urologist' => 'Urologo con multiples consultorios (1 doctor, N clinicas, 1 secretaria por clinica)',
                'multi_doctor' => 'Consultorio compartido (N doctores en 1 clinica, 1 secretaria compartida)',
            ],
        );

        match ($profile) {
            'solo' => $this->installSolo(),
            'urologist' => $this->installUrologist(),
            'multi_doctor' => $this->installMultiDoctor(),
        };

        $this->line('');
        $this->info('Instalacion completa.');
        return self::SUCCESS;
    }

    private function installSolo(): void
    {
        $doctor = $this->createDoctor('doctor_admin');
        $clinic = $this->createClinic();
        $doctor->clinics()->syncWithoutDetaching([$clinic->id => ['is_primary' => true]]);
    }

    private function installUrologist(): void
    {
        $doctor = $this->createDoctor('doctor_admin', defaultSpecialty: 'urology');

        $clinicCount = (int) text(
            label: 'Cuantas clinicas/consultorios?',
            default: '2',
            validate: fn (string $v) => ctype_digit($v) && (int) $v >= 1 ? null : 'Numero entero positivo',
        );

        $clinics = [];
        for ($i = 1; $i <= $clinicCount; $i++) {
            $clinic = $this->createClinic(suffix: " #{$i}");
            $doctor->clinics()->syncWithoutDetaching([$clinic->id => ['is_primary' => $i === 1]]);
            $clinics[] = $clinic;
        }

        if (confirm('Crear una secretaria por clinica (rol limited, sin ver gastos)?', default: true)) {
            foreach ($clinics as $i => $clinic) {
                $secretary = $this->createStaff('secretary_limited', "secretaria de {$clinic->name}");
                $secretary->clinics()->syncWithoutDetaching([$clinic->id => ['is_primary' => true]]);
            }
        }
    }

    private function installMultiDoctor(): void
    {
        $clinic = $this->createClinic();

        $this->line('Primer doctor: administrador de la clinica.');
        $admin = $this->createDoctor('doctor_admin');
        $admin->clinics()->syncWithoutDetaching([$clinic->id => ['is_primary' => true]]);

        $extra = (int) text(
            label: 'Cuantos doctores asociados adicionales?',
            default: '2',
            validate: fn (string $v) => ctype_digit($v) && (int) $v >= 0 ? null : 'Numero entero no negativo',
        );

        for ($i = 1; $i <= $extra; $i++) {
            $this->line("Doctor asociado {$i}:");
            $associate = $this->createDoctor('doctor_associate');
            $associate->clinics()->syncWithoutDetaching([$clinic->id => ['is_primary' => true]]);
        }

        if (confirm('Crear una secretaria compartida (rol full, ve gastos y caja)?', default: true)) {
            $secretary = $this->createStaff('secretary_full', 'secretaria compartida');
            $secretary->clinics()->syncWithoutDetaching([$clinic->id => ['is_primary' => true]]);
        }
    }

    private function createDoctor(string $role, ?string $defaultSpecialty = null): User
    {
        $specialties = collect(config('specialties'))
            ->mapWithKeys(fn ($def, $key) => [$key => $def['label']])
            ->all();

        $name = text(label: 'Nombre completo del doctor', required: true);
        $email = text(
            label: 'Email',
            required: true,
            validate: fn (string $v) => filter_var($v, FILTER_VALIDATE_EMAIL) ? null : 'Email invalido',
        );
        $pass = password(label: 'Password', required: true);
        $specialty = select(
            label: 'Especialidad',
            options: $specialties,
            default: $defaultSpecialty ?? array_key_first($specialties),
        );
        $license = text(label: 'Matricula profesional', required: true);

        $doctor = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($pass),
            'specialty' => $specialty,
            'professional_license' => $license,
            'status' => 'active',
        ]);
        $doctor->syncRoles([$role]);

        return $doctor;
    }

    private function createStaff(string $role, string $contextLabel): User
    {
        $this->line("Datos de {$contextLabel}:");
        $name = text(label: 'Nombre completo', required: true);
        $email = text(
            label: 'Email',
            required: true,
            validate: fn (string $v) => filter_var($v, FILTER_VALIDATE_EMAIL) ? null : 'Email invalido',
        );
        $pass = password(label: 'Password', required: true);

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($pass),
            'status' => 'active',
        ]);
        $user->syncRoles([$role]);

        return $user;
    }

    private function createClinic(string $suffix = ''): Clinic
    {
        $name = text(
            label: "Nombre de la clinica{$suffix}",
            required: true,
        );

        return Clinic::create([
            'name' => $name,
            'type' => 'office',
            'is_active' => true,
        ]);
    }
}
