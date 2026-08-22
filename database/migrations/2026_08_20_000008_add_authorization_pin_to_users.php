<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * PIN de autorizacion del doctor, para aprobar la caja en la pantalla de la
 * secretaria.
 *
 * Va aparte de la contrasena a proposito: ese PIN se teclea delante de la
 * secretaria y en su computadora, todos los dias. Si fuera la contrasena de
 * acceso, terminaria aprendida por observacion y daria entrada a los
 * expedientes y a las finanzas. Asi, lo unico que permite es aprobar una
 * caja.
 *
 * Se guarda cifrado, nunca en claro.
 *
 * Aditiva: sin PIN definido, el doctor aprueba desde su propia sesion.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('authorization_pin')->nullable()->after('password');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('authorization_pin');
        });
    }
};
