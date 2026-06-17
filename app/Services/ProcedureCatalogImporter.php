<?php

namespace App\Services;

use App\Models\Insurer;
use App\Models\Procedure;
use Illuminate\Support\Facades\DB;

/**
 * Importa/actualiza el catálogo de procedimientos desde filas planas:
 *   [PROCEDIMIENTO, ARS, CODIGO, SIMON, MONTO]
 * Crea procedimientos y aseguradoras que falten y hace upsert del cruce.
 * Lo usan el importador CSV (secretaria) y el seeder de carga inicial.
 */
class ProcedureCatalogImporter
{
    /**
     * @param  iterable<array{0:string,1:string,2:?string,3:?string,4:?string}>  $rows
     * @return array{procedures:int, insurers:int, links:int, skipped:int}
     */
    public function import(iterable $rows): array
    {
        $procedures = [];   // name => Procedure (cache)
        $insurers = [];     // name => Insurer (cache)
        $links = 0;
        $skipped = 0;

        DB::transaction(function () use ($rows, &$procedures, &$insurers, &$links, &$skipped) {
            foreach ($rows as $row) {
                $procName = trim((string) ($row[0] ?? ''));
                $arsName = trim((string) ($row[1] ?? ''));

                if ($procName === '' || $arsName === '') {
                    $skipped++;
                    continue;
                }

                $procedure = $procedures[$procName] ??= Procedure::firstOrCreate(['name' => $procName]);
                $insurer = $insurers[$arsName] ??= Insurer::firstOrCreate(['name' => $arsName]);

                $pivot = [
                    'code' => trim((string) ($row[2] ?? '')) ?: null,
                    'simon' => trim((string) ($row[3] ?? '')) ?: null,
                    'monto' => $this->parseMonto($row[4] ?? null),
                ];

                if ($procedure->insurers()->where('insurers.id', $insurer->id)->exists()) {
                    $procedure->insurers()->updateExistingPivot($insurer->id, $pivot);
                } else {
                    $procedure->insurers()->attach($insurer->id, $pivot);
                }

                $links++;
            }
        });

        return [
            'procedures' => count($procedures),
            'insurers' => count($insurers),
            'links' => $links,
            'skipped' => $skipped,
        ];
    }

    private function parseMonto(mixed $value): ?float
    {
        $clean = preg_replace('/[^0-9.\-]/', '', (string) $value);

        return ($clean === '' || $clean === '-') ? null : (float) $clean;
    }
}
