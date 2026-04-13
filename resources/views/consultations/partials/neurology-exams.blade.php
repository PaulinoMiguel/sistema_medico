<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Examen neurologico</label>
    <textarea name="neurological_exam" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500" placeholder="Estado mental, pares craneales, fuerza muscular (escala 0-5), sensibilidad, reflejos osteotendinosos, coordinacion, marcha, signos meningeos...">{{ old('neurological_exam', $consultation->neurological_exam) }}</textarea>
</div>
