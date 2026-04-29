<?php

namespace App\Support;

class ConsultationTemplate
{
    public static function resolvePartial(?string $slug, string $section): ?string
    {
        if (!$slug) {
            return null;
        }

        $config = config("consultation_templates.{$slug}");
        if (!$config) {
            return null;
        }

        $viewName = "consultations.partials.{$slug}-{$section}";
        if (view()->exists($viewName)) {
            return $viewName;
        }

        if (!empty($config['extends'])) {
            return self::resolvePartial($config['extends'], $section);
        }

        return null;
    }
}
