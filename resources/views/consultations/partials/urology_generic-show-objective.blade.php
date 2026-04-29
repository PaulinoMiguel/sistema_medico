@if($consultation->genitourinary_exam) <div><span class="font-medium text-gray-700">Genitourinario:</span> {{ $consultation->genitourinary_exam }}</div> @endif
@if($consultation->rectal_exam) <div><span class="font-medium text-gray-700">Tacto rectal:</span> {{ $consultation->rectal_exam }}</div> @endif
@if($consultation->vaginal_exam) <div><span class="font-medium text-gray-700">Tacto vaginal:</span> {{ $consultation->vaginal_exam }}</div> @endif
@if($consultation->physical_exam) <div><span class="font-medium text-gray-700">Otros:</span> {{ $consultation->physical_exam }}</div> @endif
