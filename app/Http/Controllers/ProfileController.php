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
        return view('profile.print', ['user' => $request->user()]);
    }

    public function updatePrintProfile(Request $request)
    {
        $validated = $request->validate([
            'print_address' => 'nullable|string|max:255',
            'print_website' => 'nullable|string|max:255',
            'print_extra_header' => 'nullable|string|max:1000',
        ]);

        $request->user()->update($validated);

        return redirect()->route('profile.print')
            ->with('success', 'Perfil de impresion actualizado.');
    }

    public function updatePrintLogo(Request $request)
    {
        $request->validate([
            'print_logo' => 'required|image|mimes:jpg,jpeg,png,webp,svg|max:2048',
        ]);

        $user = $request->user();

        if ($user->print_logo_path) {
            Storage::disk('public')->delete($user->print_logo_path);
        }

        $path = $request->file('print_logo')->store('print-logos', 'public');
        $user->update(['print_logo_path' => $path]);

        return redirect()->route('profile.print')
            ->with('success', 'Logo actualizado.');
    }

    public function deletePrintLogo(Request $request)
    {
        $user = $request->user();

        if ($user->print_logo_path) {
            Storage::disk('public')->delete($user->print_logo_path);
            $user->update(['print_logo_path' => null]);
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
            return back()->withErrors(['current_password' => 'La contrasena actual no es correcta.']);
        }

        $user->update(['password' => Hash::make($validated['password'])]);

        return redirect()->route('profile.edit')
            ->with('success', 'Contrasena actualizada exitosamente.');
    }
}
