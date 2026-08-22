<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function edit(Request $request)
    {
        return view('profile.edit', ['user' => $request->user()]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:50',
            'specialty' => 'nullable|string|max:255',
            'professional_license' => 'nullable|string|max:255',
            'bio' => 'nullable|string|max:1000',
        ]);

        $user->update($validated);

        return redirect()->route('profile.edit')
            ->with('success', 'Perfil actualizado exitosamente.');
    }

    public function updatePhoto(Request $request)
    {
        $request->validate([
            'profile_photo' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $user = $request->user();

        // Delete old photo
        if ($user->profile_photo_path) {
            Storage::disk('public')->delete($user->profile_photo_path);
        }

        $path = $request->file('profile_photo')->store('profile-photos', 'public');
        $user->update(['profile_photo_path' => $path]);

        return redirect()->route('profile.edit')
            ->with('success', 'Foto de perfil actualizada.');
    }

    public function deletePhoto(Request $request)
    {
        $user = $request->user();

        if ($user->profile_photo_path) {
            Storage::disk('public')->delete($user->profile_photo_path);
            $user->update(['profile_photo_path' => null]);
        }

        return redirect()->route('profile.edit')
            ->with('success', 'Foto de perfil eliminada.');
    }

    public function editPrintProfile(Request $request)
    {
        $user = $request->user();
        $user->load('clinics');

        return view('profile.print', compact('user'));
    }

    public function updatePrintProfile(Request $request)
    {
        $validated = $request->validate([
            'print_address' => 'nullable|string|max:255',
            'print_website' => 'nullable|string|max:255',
            'print_extra_header' => 'nullable|string|max:1000',
            'clinic_print_address' => 'nullable|array',
            'clinic_print_address.*' => 'nullable|string|max:255',
        ]);

        $user = $request->user();

        $perClinic = $validated['clinic_print_address'] ?? [];
        unset($validated['clinic_print_address']);

        $user->update($validated);

        // Se recorren las clinicas del propio doctor, no las que vengan en el
        // formulario: asi un id ajeno no puede tocar el pivote de otra persona.
        foreach ($user->clinics as $clinic) {
            if (! array_key_exists($clinic->id, $perClinic)) {
                continue;
            }

            $overrides = json_decode($clinic->pivot->print_overrides ?? '', true) ?: [];
            $line = trim((string) $perClinic[$clinic->id]);

            if ($line === '') {
                unset($overrides['print_address']);
            } else {
                $overrides['print_address'] = $line;
            }

            $user->clinics()->updateExistingPivot($clinic->id, [
                'print_overrides' => $overrides ? json_encode($overrides) : null,
            ]);
        }

        return redirect()->route('profile.print')
            ->with('success', 'Perfil de impresión actualizado.');
    }

    /**
     * PIN de autorizacion: el doctor lo teclea en la pantalla de la secretaria
     * para recibir la caja. Va aparte de la contrasena porque se escribe
     * delante de ella todos los dias; si fuera la de acceso, terminaria
     * sabiendola y con ella entraria a todo el sistema.
     */
    public function updateAuthorizationPin(Request $request)
    {
        $user = $request->user();

        abort_unless($user->isDoctor(), 403, 'Solo los doctores usan PIN de autorización.');

        $validated = $request->validate([
            'current_password' => 'required|current_password',
            'authorization_pin' => 'required|digits_between:4,8|confirmed',
        ], [
            'current_password.required' => 'Confirma tu contraseña para cambiar el PIN.',
            'current_password.current_password' => 'La contraseña no es correcta.',
            'authorization_pin.required' => 'Escribe el PIN.',
            'authorization_pin.digits_between' => 'El PIN debe tener entre 4 y 8 dígitos.',
            'authorization_pin.confirmed' => 'Los dos PIN no coinciden.',
        ]);

        $user->update(['authorization_pin' => $validated['authorization_pin']]);

        return redirect()->route('profile.print')
            ->with('success', 'PIN de autorización actualizado.');
    }

    /** Firma escaneada, para el acta de entrega de caja. */
    public function updateSignature(Request $request)
    {
        $request->validate([
            'signature' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $user = $request->user();

        if ($user->digital_signature_path) {
            Storage::disk('public')->delete($user->digital_signature_path);
        }

        $user->update([
            'digital_signature_path' => $request->file('signature')->store('signatures', 'public'),
        ]);

        return redirect()->route('profile.print')->with('success', 'Firma actualizada.');
    }

    public function deleteSignature(Request $request)
    {
        $user = $request->user();

        if ($user->digital_signature_path) {
            Storage::disk('public')->delete($user->digital_signature_path);
            $user->update(['digital_signature_path' => null]);
        }

        return redirect()->route('profile.print')->with('success', 'Firma eliminada.');
    }

    /**
     * La cabecera impresa admite dos logos: el del doctor a la izquierda y
     * el del hospital o centro a la derecha. Ambos se suben igual, asi que
     * el lado llega por la ruta y aqui se traduce a su columna.
     */
    private function printLogoColumn(string $side): string
    {
        abort_unless(in_array($side, ['left', 'right'], true), 404);

        return $side === 'right' ? 'print_logo_right_path' : 'print_logo_path';
    }

    public function updatePrintLogo(Request $request, string $side = 'left')
    {
        $request->validate([
            'print_logo' => 'required|image|mimes:jpg,jpeg,png,webp,svg|max:2048',
        ]);

        $columna = $this->printLogoColumn($side);
        $user = $request->user();

        if ($user->{$columna}) {
            Storage::disk('public')->delete($user->{$columna});
        }

        $path = $request->file('print_logo')->store('print-logos', 'public');
        $user->update([$columna => $path]);

        return redirect()->route('profile.print')
            ->with('success', 'Logo actualizado.');
    }

    public function deletePrintLogo(Request $request, string $side = 'left')
    {
        $columna = $this->printLogoColumn($side);
        $user = $request->user();

        if ($user->{$columna}) {
            Storage::disk('public')->delete($user->{$columna});
            $user->update([$columna => null]);
        }

        return redirect()->route('profile.print')
            ->with('success', 'Logo eliminado.');
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required',
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
        ]);

        $user = $request->user();

        if (! Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'La contraseña actual no es correcta.']);
        }

        $user->update(['password' => Hash::make($validated['password'])]);

        return redirect()->route('profile.edit')
            ->with('success', 'Contraseña actualizada exitosamente.');
    }
}
