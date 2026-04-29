@php $sd = $consultation->specialty_data ?? []; @endphp

@if(!empty($sd))
    @php
        $anthroMeasurement = \App\Models\PediatricMeasurement::where('consultation_id', $consultation->id)->first();
    @endphp
    @if($anthroMeasurement || !empty($sd['weight_kg']) || !empty($sd['height_cm']) || !empty($sd['head_circumference_cm']))
    <div class="bg-gray-50 border border-gray-200 rounded p-3">
        <span class="font-medium text-gray-700 block mb-1">Antropometria</span>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-2 text-xs">
            @if($anthroMeasurement?->weight_kg)
                <div><span class="text-gray-500">Peso:</span> {{ $anthroMeasurement->weight_kg }}kg
                    @if($anthroMeasurement->weight_z !== null)
                        <span class="text-gray-400">(Z={{ $anthroMeasurement->weight_z }})</span>
                    @endif
                </div>
            @endif
            @if($anthroMeasurement?->height_cm)
                <div><span class="text-gray-500">Talla:</span> {{ $anthroMeasurement->height_cm }}cm
                    @if($anthroMeasurement->height_z !== null)
                        <span class="text-gray-400">(Z={{ $anthroMeasurement->height_z }})</span>
                    @endif
                </div>
            @endif
            @if($anthroMeasurement?->head_circumference_cm)
                <div><span class="text-gray-500">PC:</span> {{ $anthroMeasurement->head_circumference_cm }}cm
                    @if($anthroMeasurement->head_circumference_z !== null)
                        <span class="text-gray-400">(Z={{ $anthroMeasurement->head_circumference_z }})</span>
                    @endif
                </div>
            @endif
            @if($anthroMeasurement?->bmi)
                <div><span class="text-gray-500">IMC:</span> {{ $anthroMeasurement->bmi }}
                    @if($anthroMeasurement->bmi_z !== null)
                        <span class="text-gray-400">(Z={{ $anthroMeasurement->bmi_z }})</span>
                    @endif
                </div>
            @endif
        </div>
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
