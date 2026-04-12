@php $isDirect = ($channel ?? 'cash_register') === 'doctor_direct'; @endphp
<x-layouts.tenant :title="$isDirect ? 'Cobro Personal' : 'Nuevo Cobro'">
    <div class="mb-6">
        @if($isDirect)
            <a href="{{ route('payments.index', ['channel' => 'doctor_direct']) }}" class="text-blue-600 hover:underline text-sm">&larr; Volver a mis cobros</a>
            <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200 mt-2">Registrar cobro personal</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Este cobro no pasa por caja y solo es visible para ti.</p>
        @else
            <a href="{{ route('cash-registers.index') }}" class="text-blue-600 hover:underline text-sm">&larr; Volver a caja</a>
            <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200 mt-2">Registrar Cobro</h2>
        @endif
    </div>

    <form method="POST" action="{{ route('payments.store') }}">
        @csrf
        <input type="hidden" name="channel" value="{{ $channel ?? 'cash_register' }}">
        <div class="bg-white rounded-lg shadow p-6 max-w-2xl">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Paciente *</label>
                    <select name="patient_id" id="patient_select" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Seleccionar paciente...</option>
                        @foreach($patients as $patient)
                            <option value="{{ $patient->id }}"
                                    data-doctor-id="{{ $patient->primary_doctor_id }}"
                                    {{ old('patient_id', $selectedPatientId) == $patient->id ? 'selected' : '' }}>
                                {{ $patient->full_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('patient_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-sm font-medium text-gray-700">Servicio</label>
                        @can('services.manage')
                            <button type="button" id="open_quick_service"
                                    class="text-xs text-blue-600 hover:text-blue-800 font-medium">
                                + Nuevo servicio
                            </button>
                        @endcan
                    </div>
                    <select name="service_id" id="service_select"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="" data-price="" data-doctor-id="">Sin servicio (monto libre)</option>
                        @foreach($services as $service)
                            <option value="{{ $service->id }}"
                                    data-price="{{ $service->price }}"
                                    data-doctor-id="{{ $service->doctor_id }}"
                                    {{ old('service_id') == $service->id ? 'selected' : '' }}>
                                {{ $service->name }} - ${{ number_format($service->price, 2) }}
                            </option>
                        @endforeach
                    </select>
                    @error('service_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    <p id="service_filter_hint" class="mt-1 text-xs text-gray-500 hidden">
                        Mostrando solo servicios del doctor responsable del paciente seleccionado.
                    </p>
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
                @if($isDirect)
                    <a href="{{ route('payments.index', ['channel' => 'doctor_direct']) }}" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">Cancelar</a>
                @else
                    <a href="{{ route('cash-registers.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">Cancelar</a>
                @endif
                <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 font-medium">
                    {{ $isDirect ? 'Registrar cobro personal' : 'Registrar cobro' }}
                </button>
            </div>
        </div>
    </form>

    @can('services.manage')
        {{-- Quick service creation modal --}}
        <dialog id="quick_service_dialog" class="rounded-lg shadow-xl p-0 backdrop:bg-black/40 max-w-md w-full">
            <form id="quick_service_form" method="dialog" class="p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Nuevo servicio</h3>

                <div id="quick_service_errors" class="hidden mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded text-sm"></div>

                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nombre *</label>
                        <input type="text" name="name" id="qs_name" required maxlength="255"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Precio *</label>
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-gray-500">$</span>
                            <input type="number" name="price" id="qs_price" required step="0.01" min="0"
                                   class="w-full pl-7 pr-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="0.00">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Descripcion (opcional)</label>
                        <textarea name="description" id="qs_description" rows="2" maxlength="500"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"></textarea>
                    </div>
                </div>

                <div class="mt-5 flex justify-end gap-2">
                    <button type="button" id="qs_cancel"
                            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 text-sm">Cancelar</button>
                    <button type="button" id="qs_submit"
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm font-medium">Crear servicio</button>
                </div>
            </form>
        </dialog>
    @endcan

    <script>
        (function () {
            const patientSelect = document.getElementById('patient_select');
            const serviceSelect = document.getElementById('service_select');
            const conceptInput  = document.getElementById('concept_input');
            const amountInput   = document.getElementById('amount_input');
            const filterHint    = document.getElementById('service_filter_hint');

            // Filter services to those owned by the selected patient's doctor.
            // The "Sin servicio" option always stays visible.
            function filterServicesByPatient() {
                const selectedPatientOption = patientSelect.options[patientSelect.selectedIndex];
                const patientDoctorId = selectedPatientOption?.dataset.doctorId || '';

                let visibleCount = 0;
                Array.from(serviceSelect.options).forEach(opt => {
                    if (opt.value === '') { opt.hidden = false; return; }
                    const matches = !patientDoctorId || opt.dataset.doctorId === patientDoctorId;
                    opt.hidden = !matches;
                    if (matches) visibleCount++;
                });

                // If the currently selected service is now hidden, reset to "Sin servicio".
                const current = serviceSelect.options[serviceSelect.selectedIndex];
                if (current && current.hidden) {
                    serviceSelect.value = '';
                }

                filterHint.classList.toggle('hidden', !patientDoctorId);
            }

            patientSelect.addEventListener('change', filterServicesByPatient);
            // Apply filter on initial load (in case patient is pre-selected)
            filterServicesByPatient();

            // When a service is picked, prefill concept and amount.
            serviceSelect.addEventListener('change', function () {
                const option = this.options[this.selectedIndex];
                const price = option.dataset.price;
                const name = option.text.split(' - ')[0];

                if (price) {
                    amountInput.value = price;
                    conceptInput.value = name !== 'Sin servicio (monto libre)' ? name : '';
                }
            });

            // === Quick service creation modal (only present when user has permission) ===
            const dialog = document.getElementById('quick_service_dialog');
            if (dialog) {
                const openBtn = document.getElementById('open_quick_service');
                const cancelBtn = document.getElementById('qs_cancel');
                const submitBtn = document.getElementById('qs_submit');
                const errorBox = document.getElementById('quick_service_errors');
                const nameInput = document.getElementById('qs_name');
                const priceInput = document.getElementById('qs_price');
                const descInput = document.getElementById('qs_description');

                function resetDialog() {
                    nameInput.value = '';
                    priceInput.value = '';
                    descInput.value = '';
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
                        const res = await fetch('{{ route('services.quick-store') }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                name: nameInput.value,
                                price: priceInput.value,
                                description: descInput.value || null,
                            }),
                        });

                        if (!res.ok) {
                            const data = await res.json().catch(() => ({}));
                            const messages = data.errors
                                ? Object.values(data.errors).flat().join(' ')
                                : (data.message || 'No se pudo crear el servicio.');
                            errorBox.textContent = messages;
                            errorBox.classList.remove('hidden');
                            return;
                        }

                        const service = await res.json();

                        // Append new option to the dropdown
                        const opt = document.createElement('option');
                        opt.value = service.id;
                        opt.dataset.price = service.price;
                        opt.dataset.doctorId = service.doctor_id;
                        opt.text = `${service.name} - $${Number(service.price).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
                        serviceSelect.appendChild(opt);

                        // Select it and trigger the change handler so concept/amount get prefilled
                        serviceSelect.value = service.id;
                        serviceSelect.dispatchEvent(new Event('change'));

                        dialog.close();
                    } catch (err) {
                        errorBox.textContent = 'Error de red. Intenta nuevamente.';
                        errorBox.classList.remove('hidden');
                    } finally {
                        submitBtn.disabled = false;
                    }
                });
            }
        })();
    </script>
</x-layouts.tenant>
