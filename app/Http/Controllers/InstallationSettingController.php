<?php

namespace App\Http\Controllers;

use App\Models\InstallationSetting;
use Illuminate\Http\Request;

class InstallationSettingController extends Controller
{
    public function edit()
    {
        $settings = InstallationSetting::current();
        $moduleKeys = array_keys(InstallationSetting::MODULE_DEFAULTS);

        return view('settings.edit', compact('settings', 'moduleKeys'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'brand_name' => 'required|string|max:255',
            'brand_tagline' => 'nullable|string|max:255',
            'primary_color' => 'required|regex:/^#[0-9a-fA-F]{6}$/',
            'logo' => 'nullable|image|max:2048',
            'remove_logo' => 'nullable|boolean',
            'modules' => 'nullable|array',
        ]);

        $settings = InstallationSetting::current();

        $modules = [];
        foreach (array_keys(InstallationSetting::MODULE_DEFAULTS) as $key) {
            $modules[$key] = (bool) ($request->input("modules.$key") ?? false);
        }

        $payload = [
            'brand_name' => $validated['brand_name'],
            'brand_tagline' => $validated['brand_tagline'] ?? '',
            'primary_color' => $validated['primary_color'],
            'modules' => $modules,
        ];

        if ($request->boolean('remove_logo') && $settings->logo_path) {
            \Storage::disk('public')->delete($settings->logo_path);
            $payload['logo_path'] = null;
        } elseif ($request->hasFile('logo')) {
            if ($settings->logo_path) {
                \Storage::disk('public')->delete($settings->logo_path);
            }
            $payload['logo_path'] = $request->file('logo')->store('branding', 'public');
        }

        $settings->update($payload);
        InstallationSetting::forget();

        return redirect()->route('settings.edit')->with('success', 'Configuracion actualizada.');
    }
}
