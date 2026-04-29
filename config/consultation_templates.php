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
 * To add a custom template for a client:
 *   1. Add the entry here
 *   2. Create the 4 partial files
 *   3. Assign it to the doctor from the admin panel
 */

return [
    'urology_generic' => [
        'label' => 'Urologia - Generico',
        'specialty' => 'urology',
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
