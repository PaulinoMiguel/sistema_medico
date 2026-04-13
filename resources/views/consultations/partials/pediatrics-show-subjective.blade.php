@php $sd = $consultation->specialty_data ?? []; @endphp

@if(!empty($sd))
    @if(!empty($sd['head_circumference']) || !empty($sd['weight_percentile']) || !empty($sd['height_percentile']))
    <div>
        <span class="font-medium text-gray-700">Crecimiento:</span>
        @if(!empty($sd['head_circumference'])) <span class="text-gray-600 ml-2">PC: {{ $sd['head_circumference'] }}cm</span> @endif
        @if(!empty($sd['weight_percentile'])) <span class="text-gray-600 ml-2">P.Peso: {{ $sd['weight_percentile'] }}</span> @endif
        @if(!empty($sd['height_percentile'])) <span class="text-gray-600 ml-2">P.Talla: {{ $sd['height_percentile'] }}</span> @endif
    </div>
    @endif

    @if(!empty($sd['development']))
    <div>
        <span class="font-medium text-gray-700">Desarrollo:</span>
        @foreach(['head_control'=>'Sostiene cabeza','sits'=>'Se sienta','crawls'=>'Gatea','walks'=>'Camina','speaks_words'=>'Palabras','speaks_sentences'=>'Oraciones','sphincter_control'=>'Esfinteres','social_smile'=>'Sonrisa social'] as $k => $l)
            @if(!empty($sd['development'][$k])) <span style="background-color:#dbeafe;color:#1e40af;" class="inline-block px-2 py-0.5 rounded text-xs mr-1 mb-1">{{ $l }}</span> @endif
        @endforeach
    </div>
    @endif

    @if(!empty($sd['feeding_type']))
    <div>
        <span class="font-medium text-gray-700">Alimentacion:</span>
        <span class="text-gray-600">{{ ['breastfeeding'=>'Lactancia materna','formula'=>'Formula','mixed'=>'Mixta','complementary'=>'Complementaria','family_diet'=>'Dieta familiar'][$sd['feeding_type']] ?? $sd['feeding_type'] }}</span>
    </div>
    @endif

    @if(!empty($sd['vaccines_up_to_date']))
    <div>
        <span class="font-medium text-gray-700">Vacunacion:</span>
        <span style="background-color:#dcfce7;color:#166534;" class="inline-block px-2 py-0.5 rounded text-xs">Esquema al dia</span>
    </div>
    @endif
@endif
