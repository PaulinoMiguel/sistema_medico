<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

/**
 * Calculo de z-scores y percentiles contra las tablas LMS de WHO (0-24m)
 * y CDC (2-20y). Datos en resources/data/growth/*.json.
 *
 * Indicadores soportados (clave publica → archivo):
 *   - weight_for_age  → WHO 0-36m o CDC 2-20y segun edad
 *   - height_for_age  → WHO length 0-36m o CDC height 2-20y
 *   - head_circumference_for_age → WHO 0-36m (no aplica > 36m)
 *   - bmi_for_age     → CDC 2-20y
 *   - weight_for_length → WHO 0-36m (usa longitud como eje, no edad)
 */
class GrowthService
{
    /**
     * Mapa de indicadores a archivos JSON.
     * Para indicadores con 2 tablas (WHO+CDC) se define un umbral en meses
     * y la tabla a usar por tramo.
     */
    private const INDICATORS = [
        'weight_for_age' => [
            ['range' => [0, 36],   'file' => 'weight-for-age-0-36m.json'],
            ['range' => [24, 240], 'file' => 'weight-for-age-2-20y.json'],
        ],
        'height_for_age' => [
            ['range' => [0, 36],   'file' => 'length-for-age-0-36m.json'],
            ['range' => [24, 240], 'file' => 'height-for-age-2-20y.json'],
        ],
        'head_circumference_for_age' => [
            ['range' => [0, 36], 'file' => 'head-circumference-for-age-0-36m.json'],
        ],
        'bmi_for_age' => [
            ['range' => [24, 240], 'file' => 'bmi-for-age-2-20y.json'],
        ],
        'weight_for_length' => [
            ['range' => [45, 110], 'file' => 'weight-for-length-0-36m.json'],
        ],
    ];

    /**
     * Z-score para una medicion.
     *
     * @param string      $indicator  clave de INDICATORS
     * @param string      $sex        'male' o 'female'
     * @param float       $xValue     edad en meses (o longitud en cm para weight_for_length)
     * @param float       $value      medicion (peso kg, talla cm, etc.)
     * @return float|null  null si fuera de rango/datos insuficientes
     */
    public function zScore(string $indicator, string $sex, float $xValue, float $value): ?float
    {
        $lms = $this->lookupLMS($indicator, $sex, $xValue);
        if ($lms === null) {
            return null;
        }

        [$L, $M, $S] = [$lms['L'], $lms['M'], $lms['S']];

        if ($M <= 0 || $S <= 0 || $value <= 0) {
            return null;
        }

        // Formula LMS: Z = ((X/M)^L - 1) / (L * S)
        // Caso L=0 (degenerate): Z = ln(X/M) / S
        if (abs($L) < 1e-7) {
            $z = log($value / $M) / $S;
        } else {
            $z = (pow($value / $M, $L) - 1) / ($L * $S);
        }

        return round($z, 2);
    }

    /** Convierte un z-score a percentil (0-100). */
    public function percentile(float $z): float
    {
        return round($this->normalCDF($z) * 100, 1);
    }

    /**
     * Edad corregida en meses para prematuros: descuenta las semanas que le
     * faltaban para 40. Solo aplica hasta los 24 meses cronologicos post-termino
     * (practica estandar). Retorna null si no aplica.
     */
    public function correctedAgeMonths(int $gestationalAgeWeeks, float $chronologicalAgeMonths): ?float
    {
        if ($gestationalAgeWeeks >= 37) {
            return null;
        }

        $missingWeeks = 40 - $gestationalAgeWeeks;
        $correction = $missingWeeks / 4.345; // semanas a meses

        $corrected = $chronologicalAgeMonths - $correction;

        // Practica estandar: ya no se corrige pasados los 24 meses post-termino.
        if ($corrected >= 24) {
            return null;
        }

        return max(0, round($corrected, 2));
    }

    /**
     * Devuelve las curvas P3/P15/P50/P85/P97 como arrays (x, p3, p15, p50, p85, p97)
     * para graficar. Usa la tabla que corresponde al rango pedido (0-36m WHO,
     * 2-20y CDC). Se cachea en memoria por request.
     */
    public function curveData(string $indicator, string $sex, float $xFrom, float $xTo): array
    {
        $rows = $this->loadRangeForXRange($indicator, $sex, $xFrom, $xTo);

        $curves = ['x' => [], 'p3' => [], 'p15' => [], 'p50' => [], 'p85' => [], 'p97' => []];

        foreach ($rows as $row) {
            if ($row['x'] < $xFrom || $row['x'] > $xTo) continue;
            $curves['x'][]   = $row['x'];
            $curves['p3'][]  = $this->fromZ($row, -1.88);
            $curves['p15'][] = $this->fromZ($row, -1.04);
            $curves['p50'][] = $row['M'];
            $curves['p85'][] = $this->fromZ($row, 1.04);
            $curves['p97'][] = $this->fromZ($row, 1.88);
        }

        return $curves;
    }

    // ────────────────────────── internals ──────────────────────────

    /** Inverso de la formula LMS: dado un z, devuelve la medicion. */
    private function fromZ(array $lms, float $z): float
    {
        [$L, $M, $S] = [$lms['L'], $lms['M'], $lms['S']];
        if (abs($L) < 1e-7) {
            return round($M * exp($z * $S), 3);
        }
        return round($M * pow(1 + $L * $S * $z, 1 / $L), 3);
    }

    /**
     * Busca el trio LMS para un punto x usando interpolacion lineal entre los
     * dos puntos mas cercanos de la tabla.
     */
    private function lookupLMS(string $indicator, string $sex, float $xValue): ?array
    {
        $rows = $this->loadForX($indicator, $sex, $xValue);
        if (empty($rows)) {
            return null;
        }

        $sexKey = $sex === 'male' ? '1' : '2';
        $data = $rows['data'][$sexKey] ?? [];
        if (empty($data)) return null;

        // Fuera de rango → null (no extrapolamos).
        $first = $data[0]['x'];
        $last = $data[count($data) - 1]['x'];
        if ($xValue < $first || $xValue > $last) {
            return null;
        }

        // Busqueda binaria del intervalo.
        $lo = 0; $hi = count($data) - 1;
        while ($lo + 1 < $hi) {
            $mid = intdiv($lo + $hi, 2);
            if ($data[$mid]['x'] <= $xValue) $lo = $mid; else $hi = $mid;
        }

        $a = $data[$lo];
        $b = $data[$hi];

        if ($a['x'] == $b['x'] || $b['x'] == $xValue) {
            return ['L' => $b['L'], 'M' => $b['M'], 'S' => $b['S']];
        }
        if ($a['x'] == $xValue) {
            return ['L' => $a['L'], 'M' => $a['M'], 'S' => $a['S']];
        }

        // Interpolacion lineal entre a y b.
        $t = ($xValue - $a['x']) / ($b['x'] - $a['x']);
        return [
            'L' => $a['L'] + $t * ($b['L'] - $a['L']),
            'M' => $a['M'] + $t * ($b['M'] - $a['M']),
            'S' => $a['S'] + $t * ($b['S'] - $a['S']),
        ];
    }

    private function loadForX(string $indicator, string $sex, float $xValue): ?array
    {
        $spec = self::INDICATORS[$indicator] ?? null;
        if (! $spec) return null;

        // Elegir la tabla correcta por el rango de x.
        // Regla: la primera tabla cuyo rango lo contenga.
        foreach ($spec as $segment) {
            [$from, $to] = $segment['range'];
            if ($xValue >= $from && $xValue <= $to) {
                return $this->loadFile($segment['file']);
            }
        }
        // Si esta por encima del ultimo rango, usar la ultima tabla.
        return $this->loadFile(end($spec)['file']);
    }

    private function loadRangeForXRange(string $indicator, string $sex, float $from, float $to): array
    {
        $spec = self::INDICATORS[$indicator] ?? null;
        if (! $spec) return [];

        $sexKey = $sex === 'male' ? '1' : '2';
        $rows = [];

        foreach ($spec as $segment) {
            [$segFrom, $segTo] = $segment['range'];
            if ($segTo < $from || $segFrom > $to) continue;
            $file = $this->loadFile($segment['file']);
            foreach ($file['data'][$sexKey] ?? [] as $row) {
                $rows[] = $row;
            }
        }

        // Ordenar por x y remover duplicados exactos (el overlap 24m ocurre
        // entre WHO y CDC; preferir el primero que aparezca).
        usort($rows, fn ($a, $b) => $a['x'] <=> $b['x']);
        $seen = [];
        $out = [];
        foreach ($rows as $r) {
            $k = (string) $r['x'];
            if (isset($seen[$k])) continue;
            $seen[$k] = true;
            $out[] = $r;
        }

        return $out;
    }

    private function loadFile(string $name): array
    {
        return Cache::driver('array')->rememberForever("growth:$name", function () use ($name) {
            $path = resource_path("data/growth/$name");
            return json_decode(file_get_contents($path), true) ?: ['data' => []];
        });
    }

    /**
     * Aproximacion de la funcion de distribucion acumulada normal estandar
     * (error < 7.5e-8). Abramowitz & Stegun 26.2.17.
     */
    private function normalCDF(float $z): float
    {
        $t = 1.0 / (1.0 + 0.2316419 * abs($z));
        $d = 0.3989422804014327 * exp(-$z * $z / 2);
        $p = $d * $t * ((((1.330274429 * $t - 1.821255978) * $t + 1.781477937) * $t - 0.356563782) * $t + 0.319381530);
        return $z > 0 ? 1 - $p : $p;
    }
}
