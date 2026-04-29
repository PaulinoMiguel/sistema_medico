<?php

/**
 * Consultation templates registry.
 *
 * Each key is a template slug used to load Blade partials:
 *   resources/views/consultations/partials/{slug}-symptoms.blade.php
 *   resources/views/consultations/partials/{slug}-exams.blade.php
 *   resources/views/consultations/partials/{slug}-show-subjective.blade.php
 *   resources/views/consultations/partials/{slug}-show-objective.blade.php
 *
 * 'specialty' links the template to a specialty from config/specialties.php.
 * Each specialty must have at least one '_generic' template as fallback.
 *
 * 'extends' (optional) names another template to inherit from. If a partial
 * file is missing on the child, the resolver falls through to the parent.
 * The {especialidad}_generic templates are the canonical bases — custom
 * doctor templates should extend them and only ship the partials they
 * actually override.
 *
 * To add a custom template for a client:
 *   1. Add the entry here with 'extends' pointing to the base
 *   2. Create only the partial files that differ from the base
 *   3. Assign it to the doctor from the admin panel
 */

return [
    'urology_generic' => [
        'label' => 'Urologia - Generico',
        'specialty' => 'urology',
    ],

    'dra_peralta_cons_inicial' => [
        'label' => 'Dra. Peralta - Consulta inicial',
        'specialty' => 'urology',
        'extends' => 'urology_generic',
    ],

    'pediatrics_generic' => [
        'label' => 'Pediatria - Generico',
        'specialty' => 'pediatrics',
    ],

    'neurology_generic' => [
        'label' => 'Neurologia - Generico',
        'specialty' => 'neurology',
    ],

    'general_generic' => [
        'label' => 'Medicina General - Generico',
        'specialty' => 'general',
    ],
];
