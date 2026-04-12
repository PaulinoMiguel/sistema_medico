<x-layouts.tenant :title="'Configuracion inicial'">
    <div class="max-w-2xl mx-auto mt-10">
        <div class="text-center mb-8">
            <div class="bg-blue-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            </div>
            <h2 class="text-2xl font-bold text-gray-800">Bienvenido a MediApp</h2>
            <p class="text-gray-500 mt-2">Para comenzar, crea tu primera clinica o consultorio. Una vez creada, se activaran las demas opciones del sistema (pacientes, turnos, consultas, etc.).</p>
        </div>

        <div class="bg-white rounded-lg shadow p-8">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Pasos para configurar tu cuenta:</h3>
            <ol class="space-y-4">
                <li class="flex items-start">
                    <span class="flex-shrink-0 w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center text-sm font-bold">1</span>
                    <div class="ml-4">
                        <p class="font-medium text-gray-800">Crea tu(s) clinica(s)</p>
                        <p class="text-sm text-gray-500">Agrega los consultorios o hospitales donde trabajas.</p>
                    </div>
                </li>
                <li class="flex items-start">
                    <span class="flex-shrink-0 w-8 h-8 bg-gray-200 text-gray-600 rounded-full flex items-center justify-center text-sm font-bold">2</span>
                    <div class="ml-4">
                        <p class="font-medium text-gray-800">Agrega secretarias (opcional)</p>
                        <p class="text-sm text-gray-500">Crea cuentas para tus secretarias y asignalas a cada clinica.</p>
                    </div>
                </li>
                <li class="flex items-start">
                    <span class="flex-shrink-0 w-8 h-8 bg-gray-200 text-gray-600 rounded-full flex items-center justify-center text-sm font-bold">3</span>
                    <div class="ml-4">
                        <p class="font-medium text-gray-800">Comienza a trabajar</p>
                        <p class="text-sm text-gray-500">Registra pacientes, agenda turnos y atiende consultas.</p>
                    </div>
                </li>
            </ol>

            <div class="mt-8 text-center">
                <a href="{{ route('clinics.create') }}" class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-md hover:bg-blue-700 font-medium">
                    Crear mi primera clinica
                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>
    </div>
</x-layouts.tenant>
