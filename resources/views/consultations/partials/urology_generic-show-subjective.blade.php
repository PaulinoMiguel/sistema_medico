@if(!empty($us))
    <div>
        <span class="font-medium text-gray-700">Sintomas urinarios:</span>
        @foreach(['frequency'=>'Frecuencia','urgency'=>'Urgencia','nocturia'=>'Nocturia','weak_stream'=>'Chorro debil','intermittency'=>'Intermitencia','straining'=>'Esfuerzo','incomplete_emptying'=>'Vaciado incompleto','hematuria'=>'Hematuria','dysuria'=>'Disuria','incontinence'=>'Incontinencia'] as $k => $l)
            @if(!empty($us[$k])) <span style="background-color:#dbeafe;color:#1e40af;" class="inline-block px-2 py-0.5 rounded text-xs mr-1 mb-1">{{ $l }}</span> @endif
        @endforeach
        @if(!empty($us['ipss_score'])) <span class="ml-2 text-gray-600">IPSS: {{ $us['ipss_score'] }}</span> @endif
    </div>
@endif
@if(!empty($sf))
    <div>
        <span class="font-medium text-gray-700">Funcion sexual:</span>
        @foreach(['erectile_dysfunction'=>'Disf. erectil','decreased_libido'=>'Libido baja','premature_ejaculation'=>'Eyac. precoz','painful_ejaculation'=>'Eyac. dolorosa','hematospermia'=>'Hematospermia'] as $k => $l)
            @if(!empty($sf[$k])) <span style="background-color:#fce7f3;color:#9d174d;" class="inline-block px-2 py-0.5 rounded text-xs mr-1 mb-1">{{ $l }}</span> @endif
        @endforeach
        @if(!empty($sf['iief_score'])) <span class="ml-2 text-gray-600">IIEF: {{ $sf['iief_score'] }}</span> @endif
    </div>
@endif
