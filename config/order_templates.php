<?php

/**
 * Diagnostic order templates organized by category.
 *
 * Each template renders as a separate page when printed. The doctor selects
 * one or more templates inside a consultation and prints them; the printout
 * goes to the patient who delivers it to the lab/imaging facility.
 *
 * 'lines' rendering rules:
 *   - empty string '' renders as blank line
 *   - contains '___' renders as a fill-in line for handwriting (no checkbox)
 *   - starts with '> ' renders as info text without checkbox (the prefix is
 *     stripped on render). Use this for location/address notes
 *   - everything else renders as an item with a checkbox the lab/biller marks
 *
 * Nothing about the doctor's selection is persisted — this is a print
 * utility, not a clinical record. The 'diagnostic_orders' field on the
 * consultation is independent and used for the medical record itself.
 */

return [
    'pre_quirurgicas' => [
        'label' => 'Pre-quirúrgicas',
        'templates' => [
            'pre_anestesica' => [
                'label' => 'Evaluación Pre-Anestésica',
                'lines' => [
                    '> Piso M',
                    '',
                    'Plan: ___________________________________',
                    '',
                    '___________________________________________',
                ],
            ],
            'pre_operatoria' => [
                'label' => 'Evaluación Pre-Operatoria',
                'lines' => [
                    'Médico: ___________________________________',
                    'Consultorio: _______________________________',
                    '',
                    'Plan: ______________________________________',
                    '',
                    '____________________________________________',
                ],
            ],
            'rx_torax' => [
                'label' => 'Rx Tórax PA',
                'lines' => [
                    'Eval. Pre-Operatoria',
                ],
            ],
        ],
    ],

    'laboratorios' => [
        'label' => 'Laboratorios',
        'templates' => [
            'analitica_quirofano' => [
                'label' => 'Analíticas para cirugía',
                'lines' => [
                    'Hemograma',
                    'Orina',
                    'Urea',
                    'Creatinina',
                    'Glicemia',
                    'Hemoglobina Glicosilada',
                    'Colesterol',
                    'Triglicéridos',
                    'ALT',
                    'AST',
                    'VDRL',
                    'HIV',
                    'Hep B',
                    'Hep C',
                    'TP',
                    'TPT',
                ],
            ],
            'analitica_hombre' => [
                'label' => 'Analíticas concretas hombre',
                'lines' => [
                    'Hemograma',
                    'Orina',
                    'Urea',
                    'Creatinina',
                    'Glicemia',
                    'Hemoglobina glicosilada',
                    'PSA Total',
                    'PSA Libre',
                    '% PSA',
                ],
            ],
            'analitica_normal' => [
                'label' => 'Analíticas consulta normal',
                'lines' => [
                    'Hemograma',
                    'Orina',
                    'Urea',
                    'Creatinina',
                    'Glicemia',
                    'Hemoglobina glicosilada',
                ],
            ],
        ],
    ],

    'imagenologia' => [
        'label' => 'Imagenología',
        'templates' => [
            'sonografia_masculino' => [
                'label' => 'Sonografía Abdomen Pélvica (Masculino)',
                'lines' => [
                    'Medir volumen prostático',
                    'Medir volumen pre o post micción',
                ],
            ],
            'sonografia_femenino' => [
                'label' => 'Sonografía Abdomen Pélvica (Femenino)',
                'lines' => [
                    'Medir volumen pre o post micción',
                ],
            ],
        ],
    ],
];
