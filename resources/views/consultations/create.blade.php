<x-layouts.tenant :title="'Nueva Consulta'">
    <div class="mb-6">
        <a href="{{ route('consultations.index') }}" class="text-blue-600 hover:underline text-sm">&larr; Volver a consultas</a>
        <h2 class="text-2xl font-bold text-gray-800 mt-2">Nueva Consulta (sin turno)</h2>
    </div>

    <form method="POST" action="{{ route('consultations.store') }}">
        @csrf
        <div class="bg-white rounded-lg shadow p-6 max-w-lg">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Paciente *</label>
                    <select name="patient_id" required class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Seleccionar...</option>
                        @foreach($patients as $p)
                            <option value="{{ $p->id }}">{{ $p->full_name }} ({{ $p->medical_record_number }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de consulta *</label>
                    <select name="type" required class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="initial">Consulta inicial</option>
                        <option value="follow_up">Control</option>
                        <option value="pre_operative">Pre-quirurgico</option>
                        <option value="post_operative">Post-quirurgico</option>
                        <option value="emergency">Urgencia</option>
                        <option value="urodynamic">Urodinamia</option>
                        <option value="procedure">Procedimiento</option>
                    </select>
                </div>
            </div>
            <div class="mt-6 flex gap-3">
                <a href="{{ route('consultations.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50">Cancelar</a>
                <button type="submit" style="background-color:#2563eb;color:#fff;" class="px-6 py-2 rounded-md text-sm font-medium">Iniciar consulta</button>
            </div>
        </div>
    </form>
</x-layouts.tenant>
