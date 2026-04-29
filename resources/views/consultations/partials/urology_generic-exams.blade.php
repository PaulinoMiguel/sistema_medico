<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Examen genitourinario</label>
    <textarea name="genitourinary_exam" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500" placeholder="Genitales externos, meato uretral, testiculos, pene, inguinal...">{{ old('genitourinary_exam', $consultation->genitourinary_exam) }}</textarea>
</div>
<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Tacto rectal</label>
    <textarea name="rectal_exam" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500" placeholder="Prostata: tamano, consistencia, nodulos, sensibilidad, surco medio...">{{ old('rectal_exam', $consultation->rectal_exam) }}</textarea>
</div>
<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Tacto vaginal</label>
    <textarea name="vaginal_exam" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500">{{ old('vaginal_exam', $consultation->vaginal_exam) }}</textarea>
</div>
<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Otros</label>
    <textarea name="physical_exam" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500">{{ old('physical_exam', $consultation->physical_exam) }}</textarea>
</div>
