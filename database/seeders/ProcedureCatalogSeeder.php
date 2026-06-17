<?php

namespace Database\Seeders;

use App\Services\ProcedureCatalogImporter;
use Illuminate\Database\Seeder;

class ProcedureCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seeders/data/procedimientos.csv');

        if (! is_file($path)) {
            $this->command?->warn("CSV no encontrado: {$path}");
            return;
        }

        $rows = [];
        if (($h = fopen($path, 'r')) !== false) {
            $first = true;
            while (($d = fgetcsv($h, 0, ',')) !== false) {
                if ($first) {
                    $first = false;
                    if (stripos((string) ($d[0] ?? ''), 'procedimiento') !== false) {
                        continue;
                    }
                }
                $rows[] = $d;
            }
            fclose($h);
        }

        $r = app(ProcedureCatalogImporter::class)->import($rows);

        $this->command?->info("Catálogo importado: {$r['procedures']} procedimientos, {$r['insurers']} aseguradoras, {$r['links']} códigos.");
    }
}
