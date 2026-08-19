{{-- Cabecera del doctor para los documentos que se entregan al paciente:
     receta, resumen clínico y órdenes para imprimir.

     Espera:
       $doctor   User        — dueño del documento
       $clinic   Clinic|null — solo como respaldo si el doctor no llenó
                               el campo "Consultorio o clínica" de su perfil
       $pdfMode  bool        — true solo desde DomPDF (ver nota del logo)

     Los estilos (.header, .logo, .info, .doctor-name, .doctor-info,
     .extra-header, .clinic-info) los define cada documento, porque el
     resumen y las órdenes se imprimen desde el navegador y la receta
     se genera con DomPDF, que no soporta flexbox.

     Todo sale del perfil del doctor y del registro de la clínica: nada
     fijo en el código, para que sirva en cualquier instalación. El
     Exequatur solo aparece si el doctor lo tiene cargado. --}}
@php
    // El doctor escribe la linea completa tal como quiere verla impresa
    // ("HHWC, consultorio 301"), y puede tener una distinta por clinica.
    $locationLine = $doctor->printAddressFor($clinic);

    // DomPDF resuelve mejor una ruta del disco que una URL: pedirle la imagen
    // por HTTP al propio servidor puede colgarse cuando corre en un solo hilo.
    $logoSrc = null;
    if ($doctor->print_logo_path) {
        $localLogo = public_path('storage/' . $doctor->print_logo_path);
        $logoSrc = (($pdfMode ?? false) && is_file($localLogo))
            ? $localLogo
            : asset('storage/' . $doctor->print_logo_path);
    }
@endphp
<div class="header">
    @if($logoSrc)
        <img src="{{ $logoSrc }}" alt="Logo" class="logo">
    @endif
    <div class="info">
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
    </div>
</div>
