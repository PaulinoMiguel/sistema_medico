{{-- Bloque de texto de la cabecera. Se usa desde print-doctor-header en sus
     dos maquetados (flex para pantalla, tabla para el PDF), para no repetir
     estas lineas en ambos. Espera $doctor y $locationLine ya resueltos. --}}
<p class="doctor-name">{{ $doctor->name }}</p>
@if($doctor->professional_license)
    <p class="doctor-info">Exequatur: {{ $doctor->professional_license }}</p>
@endif
@if($doctor->print_extra_header)
    <p class="extra-header">{!! nl2br(e($doctor->print_extra_header)) !!}</p>
@endif
@if($doctor->phone)
    <p class="doctor-info">{{ $doctor->phone }}</p>
@endif
@if($locationLine)
    <p class="clinic-info">{{ $locationLine }}</p>
@endif
