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
        'label' => 'Pre-quirurgicas',
        'templates' => [
            'pre_anestesica' => [
                'label' => 'Evaluacion Pre-Anestesica',
                'lines' => [
                    '> Piso M',
                    '',
                    'Plan: ___________________________________',
                    '',
                    '___________________________________________',
                ],
            ],
            'pre_operatoria' => [
                'label' => 'Evaluacion Pre-Operatoria',
                'lines' => [
                    'Medico: ___________________________________',
                    'Consultorio: _______________________________',
                    '',
                    'Plan: ______________________________________',
                    '',
                    '____________________________________________',
                ],
            ],
            'rx_torax' => [
                'label' => 'Rx Torax',
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
                'label' => 'Analitica Quirofano',
                'lines' => [
                    'Hemograma',
                    'Glicosilada',
                    'Triglicéridos',
                    'VDRL',
                    'Orina',
                    'AST',
                    'Urea',
                    'ACT',
                    'Creatinina',
                    'TP',
                    'Glicemia',
                    'TPT',
                    'Hemoglobina Glico',
                    'HIV',
                    'Colesterol',
                    'Hep B',
                    'Hep C',
                ],
            ],
            'analitica_hombre' => [
                'label' => 'Analitica concreta hombre',
                'lines' => [
                    'Hemograma',
                    'Orina',
                    'Urea',
                    'Creatinina',
                    'Glicemia',
                    'Hemograma glicosilada',
                    'PSA Total',
                    'PSA Libre',
                    '% PSA',
                ],
            ],
            'analitica_normal' => [
                'label' => 'Analitica consulta normal',
                'lines' => [
                    'Hemograma',
                    'Orina',
                    'Urea',
                    'Creatinina',
                    'Glicemia',
                    'Hemograma glicosilada',
                ],
            ],
        ],
    ],

    'imagenologia' => [
        'label' => 'Imagenologia',
        'templates' => [
            'sonografia_masculino' => [
                'label' => 'Sonografia Abdomen Pelvica (Masculino)',
                'lines' => [
                    'Medir volumen prostatico',
                    'Medir volumen pre o post miccion',
                ],
            ],
            'sonografia_femenino' => [
                'label' => 'Sonografia Abdomen Pelvica (Femenino)',
                'lines' => [
                    'Medir volumen pre o post miccion',
                ],
            ],
        ],
    ],
];
