@php $sd = $consultation->specialty_data ?? []; @endphp

@if(!empty($sd['neuro_symptoms']))
    <div>
        <span class="font-medium text-gray-700">Sintomas neurologicos:</span>
        @foreach(['headache'=>'Cefalea','seizures'=>'Convulsiones','paresthesia'=>'Parestesias','weakness'=>'Debilidad','tremor'=>'Temblor','dizziness'=>'Vertigo','speech_disorder'=>'Trast. habla','visual_disorder'=>'Trast. visual','memory_loss'=>'Perdida memoria','gait_disorder'=>'Trast. marcha','numbness'=>'Entumecimiento','syncope'=>'Sincope'] as $k => $l)
            @if(!empty($sd['neuro_symptoms'][$k])) <span style="background-color:#ede9fe;color:#5b21b6;" class="inline-block px-2 py-0.5 rounded text-xs mr-1 mb-1">{{ $l }}</span> @endif
        @endforeach
        @if(!empty($sd['glasgow_score'])) <span class="ml-2 text-gray-600">Glasgow: {{ $sd['glasgow_score'] }}</span> @endif
        @if(!empty($sd['nihss_score'])) <span class="ml-2 text-gray-600">NIHSS: {{ $sd['nihss_score'] }}</span> @endif
    </div>
@endif
