<x-layouts.tenant :title="'Configuracion inicial'">
    <div class="max-w-2xl mx-auto mt-10">
        <div class="text-center mb-8">
            <div class="bg-blue-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            </div>
            <h2 class="text-2xl font-bold text-gray-800">Bienvenido a MediApp</h2>
            <p class="text-gray-500 mt-2">Tu cuenta aun no tiene clinicas asignadas.</p>
        </div>

        <div class="bg-white rounded-lg shadow p-8">
            <div class="text-center">
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 mb-6">
                    <svg class="w-12 h-12 text-yellow-500 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">Contacta al administrador</h3>
                    <p class="text-sm text-gray-600">
                        Las clinicas y la asignacion de doctores son gestionadas por el administrador del sistema.
                        Solicita que te asigne a una clinica para comenzar a trabajar.
                    </p>
                </div>
                <p class="text-xs text-gray-400">Una vez asignado, podras ver pacientes, agendar turnos y atender consultas.</p>
            </div>
        </div>
    </div>
</x-layouts.tenant>
