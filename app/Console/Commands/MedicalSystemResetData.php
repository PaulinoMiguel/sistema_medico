<?php

namespace App\Console\Commands;

use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

use function Laravel\Prompts\confirm;

class MedicalSystemResetData extends Command
{
    protected $signature = 'medicalsystem:reset-data {--force : Saltar confirmacion} {--keep-files : Conservar archivos en storage (logos, fotos, firmas)}';

    protected $description = 'Borra toda la data operativa (doctores, pacientes, consultas, etc.) preservando los super admins existentes y la estructura de roles/permisos';

    public function handle(): int
    {
        $this->warn('Esto va a BORRAR toda la data operativa del sistema:');
        $this->line('  - Doctores, secretarias, pacientes');
        $this->line('  - Clinicas y configuracion de instalacion');
        $this->line('  - Consultas, recetas, antecedentes medicos');
        $this->line('  - Cobros, gastos, corte de caja, servicios');
        $this->line('  - Turnos y mediciones pediatricas');
        $this->line('');
        $this->info('Se preserva:');
        $this->line('  - Super admins (panel /admin)');
        $this->line('  - Estructura de roles y permisos (Spatie)');
        $this->line('');

        if (! $this->option('force') && ! confirm('Continuar?', false)) {
            $this->info('Cancelado.');
            return self::SUCCESS;
        }

        $this->info('Backup de super admins...');
        $admins = DB::table('admins')->get();
        $this->line('  Encontrados: ' . $admins->count());

        $this->info('Reseteando schema (migrate:fresh)...');
        $this->call('migrate:fresh', ['--force' => true]);

        $this->info('Sembrando roles y permisos...');
        $this->call('db:seed', ['--class' => RolesAndPermissionsSeeder::class, '--force' => true]);

        $this->info('Restaurando super admins...');
        foreach ($admins as $a) {
            DB::table('admins')->insert((array) $a);
        }
        $this->line('  Restaurados: ' . $admins->count());

        if (! $this->option('keep-files')) {
            $this->info('Limpiando archivos en storage...');
            foreach (['profile-photos', 'print-logos', 'patient-photos', 'signatures', 'logos'] as $dir) {
                if (Storage::disk('public')->exists($dir)) {
                    Storage::disk('public')->deleteDirectory($dir);
                }
            }
        } else {
            $this->line('  Archivos en storage conservados (--keep-files)');
        }

        $this->line('');
        $this->info('Listo. Sistema vacio. Super admins preservados.');
        $this->line('Proximo paso sugerido: php artisan medicalsystem:install');

        return self::SUCCESS;
    }
}
