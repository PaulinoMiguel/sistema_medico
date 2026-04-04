<x-layouts.tenant :title="'Nuevo Cobro'">
    <div class="mb-6">
        <a href="{{ route('payments.index') }}" class="text-blue-600 hover:underline text-sm">&larr; Volver a cobros</a>
        <h2 class="text-2xl font-bold text-gray-800 mt-2">Registrar Cobro</h2>
    </div>

    <form method="POST" action="{{ route('payments.store') }}">
        @csrf
        <div class="bg-white rounded-lg shadow p-6 max-w-2xl">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Paciente *</label>
                    <select name="patient_id" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Seleccionar paciente...</option>
                        @foreach($patients as $patient)
                            <option value="{{ $patient->id }}" {{ old('patient_id', $selectedPatientId) == $patient->id ? 'selected' : '' }}>
                                {{ $patient->full_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('patient_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Servicio</label>
                    <select name="service_id" id="service_select"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="" data-price="">Sin servicio (monto libre)</option>
                        @foreach($services as $service)
                            <option value="{{ $service->id }}" data-price="{{ $service->price }}" {{ old('service_id') == $service->id ? 'selected' : '' }}>
                                {{ $service->name }} - ${{ number_format($service->price, 2) }}
                            </option>
                        @endforeach
                    </select>
                    @error('service_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Concepto *</label>
                    <input type="text" name="concept" id="concept_input" value="{{ old('concept') }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Descripcion del cobro">
                    @error('concept') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Monto *</label>
                    <div class="relative">
                        <span class="absolute left-3 top-2 text-gray-500">$</span>
                        <input type="number" name="amount" id="amount_input" value="{{ old('amount') }}" required step="0.01" min="0.01"
                               class="w-full pl-7 pr-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                               placeholder="0.00">
                    </div>
                    @error('amount') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                @if($selectedAppointmentId)
                    <input type="hidden" name="appointment_id" value="{{ $selectedAppointmentId }}">
                @endif

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notas</label>
                    <textarea name="notes" rows="2"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                              placeholder="Notas adicionales (opcional)">{{ old('notes') }}</textarea>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <a href="{{ route('payments.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">Cancelar</a>
                <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 font-medium">Registrar cobro</button>
            </div>
        </div>
    </form>

    <script>
        document.getElementById('service_select').addEventListener('change', function() {
            const option = this.options[this.selectedIndex];
            const price = option.dataset.price;
            const name = option.text.split(' - ')[0];

            if (price) {
                document.getElementById('amount_input').value = price;
                document.getElementById('concept_input').value = name !== 'Sin servicio (monto libre)' ? name : '';
            }
        });
    </script>
</x-layouts.tenant>
