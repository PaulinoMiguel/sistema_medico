<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class InstallationSetting extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['modules' => 'array'];
    }

    /** Modulos cuyo toggle esta soportado (true = habilitado por defecto). */
    public const MODULE_DEFAULTS = [
        'prescriptions' => true,
        'expenses' => true,
        'cash_register' => true,
        'services' => true,
    ];

    /**
     * Singleton: retorna la fila unica, creandola con defaults si no existe.
     * Cacheado en memoria por request para evitar N queries en el layout.
     */
    public static function current(): self
    {
        return Cache::driver('array')->rememberForever('installation_settings.current', function () {
            return static::firstOrCreate(
                ['id' => 1],
                [
                    'brand_name' => 'MediApp',
                    'brand_tagline' => 'Sistema Medico',
                    'primary_color' => '#2563eb',
                    'modules' => self::MODULE_DEFAULTS,
                ],
            );
        });
    }

    public static function forget(): void
    {
        Cache::driver('array')->forget('installation_settings.current');
    }

    public function moduleEnabled(string $module): bool
    {
        $modules = $this->modules ?? self::MODULE_DEFAULTS;
        return (bool) ($modules[$module] ?? self::MODULE_DEFAULTS[$module] ?? true);
    }

    public function logoUrl(): ?string
    {
        return $this->logo_path ? asset('storage/' . $this->logo_path) : null;
    }
}
