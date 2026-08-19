{{-- Plantillas de ordenes para imprimir y entregar al paciente.
     No persiste selección: es utilidad de impresión.

     Se incluye en los dos formatos de consulta (el SOAP de la inicial y el
     simple de controles/pre-post quirúrgicos), porque la doctora puede
     ordenar estudios desde cualquier tipo de consulta. Las dos ramas son
     excluyentes, así que los id de este bloque nunca se duplican en la página.

     Por ahora solo urología (las plantillas son urológicas). Cuando
     el set se generalice o haya sets por especialidad, abrir este gate. --}}
@if ($specialtyKey === 'urology')
<div class="border border-gray-200 rounded-lg p-4">
    <div class="flex items-center justify-between mb-3">
        <div>
            <h4 class="text-sm font-semibold text-gray-700">Ordenes para imprimir</h4>
            <p class="text-xs text-gray-500">Marca las que vas a entregar al paciente. No se guardan en la consulta — solo se imprimen.</p>
        </div>
        <button type="button" id="print-orders-btn"
                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm font-medium">
            Imprimir ordenes
        </button>
    </div>
    <label class="flex items-center text-sm font-medium text-gray-700 mb-3">
        <input type="checkbox" id="select-all-orders" class="rounded border-gray-300 text-blue-600 mr-2">
        Marcar todas
    </label>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        @foreach(config('order_templates') as $catSlug => $cat)
            <div>
                <h5 class="text-xs font-semibold text-gray-600 uppercase mb-2">{{ $cat['label'] }}</h5>
                <div class="space-y-1">
                    @foreach($cat['templates'] as $tplSlug => $tpl)
                        <label class="flex items-start text-sm">
                            <input type="checkbox" class="order-template-check rounded border-gray-300 text-blue-600 mr-2 mt-0.5"
                                   value="{{ $tplSlug }}">
                            <span>{{ $tpl['label'] }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</div>

<script>
    (function () {
        const orderChecks = document.querySelectorAll('.order-template-check');
        const selectAll = document.getElementById('select-all-orders');

        selectAll.addEventListener('change', () => {
            orderChecks.forEach(cb => { cb.checked = selectAll.checked; });
        });

        orderChecks.forEach(cb => {
            cb.addEventListener('change', () => {
                const total = orderChecks.length;
                const checked = Array.from(orderChecks).filter(c => c.checked).length;
                selectAll.checked = checked === total;
                selectAll.indeterminate = checked > 0 && checked < total;
            });
        });

        document.getElementById('print-orders-btn').addEventListener('click', () => {
            const checked = Array.from(orderChecks).filter(c => c.checked).map(cb => encodeURIComponent(cb.value));
            if (checked.length === 0) {
                alert('Marca al menos una orden para imprimir.');
                return;
            }
            const params = checked.map(v => 'items[]=' + v).join('&');
            const url = '{{ route('consultations.print-orders', $consultation) }}?' + params;
            window.open(url, '_blank');
        });
    })();
</script>
@endif
