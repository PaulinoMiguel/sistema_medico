<x-layouts.tenant :title="'Nuevo Gasto'">
    <div class="mb-6">
        <a href="{{ route('expenses.index') }}" class="text-blue-600 hover:underline text-sm">&larr; Volver a gastos</a>
        <h2 class="text-2xl font-bold text-gray-800 mt-2">Registrar Gasto</h2>
    </div>

    @if($categories->isEmpty() && !auth()->user()->can('expense-categories.manage'))
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-center">
            <p class="text-yellow-800 mb-3">No hay categorias de gasto configuradas. Pidele al administrador del consultorio que cree las categorias necesarias.</p>
        </div>
    @else
        <form method="POST" action="{{ route('expenses.store') }}">
            @csrf
            <div class="bg-white rounded-lg shadow p-6 max-w-2xl">
                <div class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-sm font-medium text-gray-700">Categoria *</label>
                                @can('expense-categories.manage')
                                    <button type="button" id="open_quick_category"
                                            class="text-xs text-blue-600 hover:text-blue-800 font-medium">
                                        + Nueva categoria
                                    </button>
                                @endcan
                            </div>
                            <select name="expense_category_id" id="category_select" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Seleccionar...</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('expense_category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('expense_category_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Fecha *</label>
                            <input type="date" name="expense_date" value="{{ old('expense_date', now()->format('Y-m-d')) }}" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            @error('expense_date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Concepto *</label>
                        <input type="text" name="concept" value="{{ old('concept') }}" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Descripcion del gasto">
                        @error('concept') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Monto *</label>
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-gray-500">$</span>
                            <input type="number" name="amount" value="{{ old('amount') }}" required step="0.01" min="0.01"
                                   class="w-full pl-7 pr-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="0.00">
                        </div>
                        @error('amount') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notas</label>
                        <textarea name="notes" rows="2"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                  placeholder="Observaciones (opcional)">{{ old('notes') }}</textarea>
                    </div>

                    <div>
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" name="is_recurring" value="1" {{ old('is_recurring') ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span class="ml-2 text-sm text-gray-700">Es un gasto recurrente (mensual)</span>
                        </label>
                    </div>

                    @if($doctors->count() > 1 && auth()->user()->isDoctor())
                        <div class="border-t pt-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Atribucion del gasto</label>
                            <select name="owner_doctor_id"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Compartido (se reparte entre todos los doctores)</option>
                                @foreach($doctors as $doc)
                                    @if(auth()->user()->hasRole('doctor_admin') || $doc->id === auth()->id())
                                        <option value="{{ $doc->id }}" {{ old('owner_doctor_id') == $doc->id ? 'selected' : '' }}>
                                            Personal de {{ $doc->name }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-gray-500">Compartido: entra al pool. Personal: solo afecta el neto del doctor indicado.</p>
                        </div>
                    @endif
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <a href="{{ route('expenses.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">Cancelar</a>
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 font-medium">Registrar gasto</button>
                </div>
            </div>
        </form>

        @can('expense-categories.manage')
            {{-- Quick category creation modal --}}
            <dialog id="quick_category_dialog" class="rounded-lg shadow-xl p-0 backdrop:bg-black/40 max-w-md w-full">
                <form id="quick_category_form" method="dialog" class="p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Nueva categoria de gasto</h3>

                    <div id="quick_category_errors" class="hidden mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded text-sm"></div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nombre *</label>
                        <input type="text" name="name" id="qc_name" required maxlength="255"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                               placeholder="ej. Suministros, Alquiler, Servicios publicos">
                    </div>

                    <div class="mt-5 flex justify-end gap-2">
                        <button type="button" id="qc_cancel"
                                class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 text-sm">Cancelar</button>
                        <button type="button" id="qc_submit"
                                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm font-medium">Crear categoria</button>
                    </div>
                </form>
            </dialog>

            <script>
                (function () {
                    const dialog = document.getElementById('quick_category_dialog');
                    const openBtn = document.getElementById('open_quick_category');
                    const cancelBtn = document.getElementById('qc_cancel');
                    const submitBtn = document.getElementById('qc_submit');
                    const errorBox = document.getElementById('quick_category_errors');
                    const nameInput = document.getElementById('qc_name');
                    const categorySelect = document.getElementById('category_select');

                    function resetDialog() {
                        nameInput.value = '';
                        errorBox.classList.add('hidden');
                        errorBox.textContent = '';
                    }

                    openBtn.addEventListener('click', () => {
                        resetDialog();
                        dialog.showModal();
                        nameInput.focus();
                    });

                    cancelBtn.addEventListener('click', () => dialog.close());

                    submitBtn.addEventListener('click', async () => {
                        submitBtn.disabled = true;
                        errorBox.classList.add('hidden');

                        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

                        try {
                            const res = await fetch('{{ route('expense-categories.quick-store') }}', {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': csrfToken,
                                    'Accept': 'application/json',
                                    'Content-Type': 'application/json',
                                },
                                body: JSON.stringify({ name: nameInput.value }),
                            });

                            if (!res.ok) {
                                const data = await res.json().catch(() => ({}));
                                const messages = data.errors
                                    ? Object.values(data.errors).flat().join(' ')
                                    : (data.message || 'No se pudo crear la categoria.');
                                errorBox.textContent = messages;
                                errorBox.classList.remove('hidden');
                                return;
                            }

                            const category = await res.json();

                            // Append new option to dropdown and select it
                            const opt = document.createElement('option');
                            opt.value = category.id;
                            opt.text = category.name;
                            categorySelect.appendChild(opt);
                            categorySelect.value = category.id;

                            dialog.close();
                        } catch (err) {
                            errorBox.textContent = 'Error de red. Intenta nuevamente.';
                            errorBox.classList.remove('hidden');
                        } finally {
                            submitBtn.disabled = false;
                        }
                    });
                })();
            </script>
        @endcan
    @endif
</x-layouts.tenant>
