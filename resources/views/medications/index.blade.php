<x-layouts.tenant :title="'Banco de Medicamentos'">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Banco de Medicamentos</h2>
            <p class="text-gray-500 text-sm">Tus medicamentos guardados. Al crear una receta puedes elegirlos para llenar la fila automáticamente, y los que recetes nuevos se agregan solos.</p>
        </div>
    </div>

    {{-- Carga masiva --}}
    <details class="mb-6 bg-white rounded-lg shadow">
        <summary class="px-6 py-4 cursor-pointer font-semibold text-gray-800 select-none">
            Carga masiva (pegar lista)
        </summary>
        <div class="px-6 pb-6 border-t pt-4">
            <p class="text-sm text-gray-500 mb-2">
                Una línea por medicamento, con los campos separados por <code class="bg-gray-100 px-1 rounded">|</code> en este orden:
                <span class="font-medium text-gray-700">Medicamento | Dosis | Duración | Vía | Observación</span>.
                Solo el <span class="font-medium">Medicamento</span> es obligatorio; el resto puede ir vacío.
                La <span class="font-medium">Vía</span> acepta: {{ implode(', ', $routeOptions) }} (por defecto Oral).
                Los nombres que ya existan se omiten.
            </p>
            <form method="POST" action="{{ route('medications.bulk') }}">
                @csrf
                <textarea name="list" rows="8" required
                          class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm font-mono"
                          placeholder="Tamsulosina | 0.4mg | 30 días | Oral | Tomar después de cenar&#10;Ciprofloxacino | 500mg | 7 días | Oral | Cada 12 horas con alimentos&#10;Ibuprofeno | 400mg | 5 días | Oral |">{{ old('list') }}</textarea>
                @error('list') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                <button type="submit" class="mt-3 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 font-medium">
                    Cargar lista al banco
                </button>
            </form>
        </div>
    </details>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Create / edit form --}}
        <div>
            <div class="bg-white rounded-lg shadow p-6">
                <h3 id="form-title" class="text-lg font-semibold text-gray-800 mb-4">Nuevo medicamento</h3>
                <form id="medication-form" method="POST" action="{{ route('medications.store') }}" data-base-url="{{ url('medications') }}">
                    @csrf
                    <input type="hidden" name="_method" id="form-method" value="POST">

                    <div class="mb-3">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Medicamento *</label>
                        <input type="text" name="name" id="f-name" value="{{ old('name') }}" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm"
                               placeholder="Ej: Tamsulosina">
                        @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Dosis</label>
                        <input type="text" name="dosage" id="f-dosage" value="{{ old('dosage') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm"
                               placeholder="Ej: 0.4mg">
                    </div>

                    <div class="mb-3">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Duración</label>
                        <input type="text" name="duration" id="f-duration" value="{{ old('duration') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm"
                               placeholder="Ej: 30 días">
                    </div>

                    <div class="mb-3">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Vía *</label>
                        <select name="route" id="f-route" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
                            @foreach($routeOptions as $value => $label)
                                <option value="{{ $value }}" {{ old('route') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Observación</label>
                        <textarea name="instructions" id="f-instructions" rows="2"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm"
                                  placeholder="Ej: Tomar después de cenar">{{ old('instructions') }}</textarea>
                    </div>

                    <div class="flex gap-2">
                        <button type="submit" id="form-submit" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 font-medium">
                            Agregar al banco
                        </button>
                        <button type="button" id="form-cancel" onclick="resetMedicationForm()" class="hidden px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- List --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow overflow-hidden">
                @if($medications->isEmpty())
                    <div class="p-8 text-center text-gray-500">
                        <p>Tu banco está vacío.</p>
                        <p class="text-sm mt-2">Agrega los medicamentos que recetas con frecuencia para elegirlos rápido.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Medicamento</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dosis</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Duración</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vía</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Observación</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($medications as $med)
                                <tr class="hover:bg-gray-50 align-top">
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $med->name }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ $med->dosage }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ $med->duration }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ $routeOptions[$med->route] ?? $med->route }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ $med->instructions }}</td>
                                    <td class="px-4 py-3 text-sm text-right whitespace-nowrap">
                                        <button type="button" class="text-blue-600 hover:underline med-edit-btn"
                                                data-id="{{ $med->id }}"
                                                data-name="{{ $med->name }}"
                                                data-dosage="{{ $med->dosage }}"
                                                data-duration="{{ $med->duration }}"
                                                data-route="{{ $med->route }}"
                                                data-instructions="{{ $med->instructions }}">Editar</button>
                                        <form action="{{ route('medications.destroy', $med) }}" method="POST" class="inline"
                                              onsubmit="return confirm('¿Eliminar este medicamento del banco?')">
                                            @csrf @method('DELETE')
                                            <button class="text-red-600 hover:underline ml-2">Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        function resetMedicationForm() {
            const form = document.getElementById('medication-form');
            form.action = form.dataset.baseUrl;
            document.getElementById('form-method').value = 'POST';
            document.getElementById('form-title').textContent = 'Nuevo medicamento';
            document.getElementById('form-submit').textContent = 'Agregar al banco';
            document.getElementById('form-cancel').classList.add('hidden');
            form.reset();
        }

        document.querySelectorAll('.med-edit-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const d = btn.dataset;
                const form = document.getElementById('medication-form');
                form.action = form.dataset.baseUrl + '/' + d.id;
                document.getElementById('form-method').value = 'PUT';
                document.getElementById('f-name').value = d.name;
                document.getElementById('f-dosage').value = d.dosage;
                document.getElementById('f-duration').value = d.duration;
                document.getElementById('f-route').value = d.route;
                document.getElementById('f-instructions').value = d.instructions;
                document.getElementById('form-title').textContent = 'Editar medicamento';
                document.getElementById('form-submit').textContent = 'Guardar cambios';
                document.getElementById('form-cancel').classList.remove('hidden');
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        });
    </script>
</x-layouts.tenant>
