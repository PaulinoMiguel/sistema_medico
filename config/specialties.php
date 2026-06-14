<?php

/**
 * Specialty definitions for the medical system.
 *
 * Each specialty key maps to a doctor's `specialty` field (stored as slug).
 * The consultation form dynamically loads the corresponding partial views
 * based on the attending doctor's specialty.
 *
 * Partial naming convention:
 *   resources/views/consultations/partials/{key}-symptoms.blade.php
 *   resources/views/consultations/partials/{key}-exams.blade.php
 *   resources/views/consultations/partials/{key}-show.blade.php
 */

return [
    'urology' => [
        'label' => 'Urología',
        'consultation_types' => [
            'initial' => 'Consulta inicial',
            'follow_up' => 'Control',
            'pre_operative' => 'Pre-quirúrgico',
            'post_operative' => 'Post-quirúrgico',
            'urodynamic' => 'Urodinamia',
            'flowmetry' => 'Flujometría',
            'procedure' => 'Procedimiento',
        ],
    ],

    'pediatrics' => [
        'label' => 'Pediatría',
        'consultation_types' => [
            'initial' => 'Consulta inicial',
            'follow_up' => 'Control',
            'well_child' => 'Niño sano',
            'vaccination' => 'Vacunación',
            'emergency' => 'Urgencia',
        ],
    ],

    'neurology' => [
        'label' => 'Neurología',
        'consultation_types' => [
            'initial' => 'Consulta inicial',
            'follow_up' => 'Control',
            'emergency' => 'Urgencia',
            'procedure' => 'Procedimiento',
        ],
    ],

    'general' => [
        'label' => 'Medicina General',
        'consultation_types' => [
            'initial' => 'Consulta inicial',
            'follow_up' => 'Control',
            'emergency' => 'Urgencia',
        ],
    ],
];
