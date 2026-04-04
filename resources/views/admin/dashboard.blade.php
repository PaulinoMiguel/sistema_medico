<x-layouts.admin :title="'Dashboard'">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-white">Dashboard</h2>
        <p class="text-gray-400">Resumen general del sistema</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="bg-blue-900/50 rounded-full p-3">
                    <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-400">Total doctores</p>
                    <p class="text-2xl font-bold text-white">{{ $totalDoctors }}</p>
                </div>
            </div>
        </div>

        <div class="bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="bg-green-900/50 rounded-full p-3">
                    <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-400">Doctores activos</p>
                    <p class="text-2xl font-bold text-white">{{ $activeDoctors }}</p>
                </div>
            </div>
        </div>

        <div class="bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="bg-purple-900/50 rounded-full p-3">
                    <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-400">Total clinicas</p>
                    <p class="text-2xl font-bold text-white">{{ $totalClinics }}</p>
                </div>
            </div>
        </div>
    </div>
</x-layouts.admin>
