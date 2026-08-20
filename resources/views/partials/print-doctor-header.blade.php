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
@if($pdfMode ?? false)
    {{-- DomPDF no soporta flexbox. Se usa una tabla, que ademas permite
         centrar el logo verticalmente contra el texto con vertical-align.
         La tercera celda esta vacia a proposito: iguala el ancho de la del
         logo para que la del texto quede simetrica y su centro coincida con
         el centro de la hoja. --}}
    <table class="header">
        <tr>
            <td class="logo-cell">
                @if($logoSrc)
                    <img src="{{ $logoSrc }}" alt="Logo" class="logo">
                @endif
            </td>
            <td class="info-cell">
                @include('partials.print-doctor-header-info')
            </td>
            <td class="spacer-cell"></td>
        </tr>
    </table>
@else
    <div class="header">
        @if($logoSrc)
            <img src="{{ $logoSrc }}" alt="Logo" class="logo">
        @endif
        <div class="info">
            @include('partials.print-doctor-header-info')
        </div>
    </div>
@endif
