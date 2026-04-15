<x-layouts.tenant :title="'Editar Clinica'">
    <div class="mb-6">
        <a href="{{ route('clinics.index') }}" class="text-blue-600 hover:underline text-sm">&larr; Volver a clinicas</a>
        <h2 class="text-2xl font-bold text-gray-800 mt-2">Editar: {{ $clinic->name }}</h2>
    </div>

    <form method="POST" action="{{ route('clinics.update', $clinic) }}">
        @csrf
        @method('PUT')

        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nombre *</label>
                    <input type="text" name="name" value="{{ old('name', $clinic->name) }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipo *</label>
                    <select name="type" required class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="office" {{ old('type', $clinic->type) == 'office' ? 'selected' : '' }}>Consultorio</option>
                        <option value="hospital" {{ old('type', $clinic->type) == 'hospital' ? 'selected' : '' }}>Hospital</option>
                        <option value="surgical_center" {{ old('type', $clinic->type) == 'surgical_center' ? 'selected' : '' }}>Centro quirurgico</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Telefono</label>
                    <input type="tel" name="phone" value="{{ old('phone', $clinic->phone) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email', $clinic->email) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">RFC / ID Fiscal</label>
                    <input type="text" name="tax_id" value="{{ old('tax_id', $clinic->tax_id) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Direccion</label>
                    <input type="text" name="address" value="{{ old('address', $clinic->address) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ciudad</label>
                    <input type="text" name="city" value="{{ old('city', $clinic->city) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                    <input type="text" name="state" value="{{ old('state', $clinic->state) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div class="md:col-span-2">
                    <label class="flex items-center">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $clinic->is_active) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="ml-2 text-sm text-gray-700">Clinica activa</span>
                    </label>
                </div>
            </div>
        </div>

        @can('expenses.manage-split')
            @php
                $doctors = $clinic->doctors()->get();
                $splitConfig = $clinic->expense_split_config ?? [];
            @endphp
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Reparto de gastos compartidos</h3>
                <p class="text-sm text-gray-500 mb-4">
                    Define como se reparte el pool de gastos compartidos entre los doctores de la clinica.
                </p>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Metodo</label>
                    <select name="expense_split_method" id="split_method"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="equal" {{ old('expense_split_method', $clinic->expense_split_method) == 'equal' ? 'selected' : '' }}>Partes iguales (reparte entre doctores activos)</option>
                        <option value="percentage" {{ old('expense_split_method', $clinic->expense_split_method) == 'percentage' ? 'selected' : '' }}>Porcentajes personalizados</option>
                    </select>
                </div>

                @if($doctors->isNotEmpty())
                    <div id="split_percentages" class="space-y-2 {{ old('expense_split_method', $clinic->expense_split_method) === 'percentage' ? '' : 'hidden' }}">
                        <p class="text-xs text-gray-500">Debe sumar 100%.</p>
                        @foreach($doctors as $doc)
                            <div class="flex items-center gap-3">
                                <span class="flex-1 text-sm text-gray-700">{{ $doc->name }}</span>
                                <input type="number" name="expense_split_config[{{ $doc->id }}]"
                                       value="{{ old('expense_split_config.' . $doc->id, $splitConfig[$doc->id] ?? '') }}"
                                       min="0" max="100" step="0.01"
                                       class="w-24 px-2 py-1 border border-gray-300 rounded-md text-right">
                                <span class="text-sm text-gray-500">%</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <script>
                document.getElementById('split_method')?.addEventListener('change', function () {
                    const pct = document.getElementById('split_percentages');
                    if (!pct) return;
                    pct.classList.toggle('hidden', this.value !== 'percentage');
                });
            </script>
        @endcan

        <div class="flex justify-end gap-3">
            <a href="{{ route('clinics.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">Cancelar</a>
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 font-medium">Guardar cambios</button>
        </div>
    </form>
</x-layouts.tenant>
