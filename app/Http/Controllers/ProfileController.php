<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Profile;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Tampilkan form edit profil.
     */
    public function edit(Request $request): Response
    {
        $user    = $request->user();
        $profile = $user->profile;

        return Inertia::render('Profile/Edit', [
            'user'    => $user,
            'profile' => $profile,
        ]);
    }

    /**
     * Update informasi akun (name, email).
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
        ], [
            'name.required'  => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email'    => 'Format email tidak valid.',
            'email.unique'   => 'Email sudah digunakan akun lain.',
        ]);

        if ($user->email !== $validated['email']) {
            $user->email_verified_at = null;
        }

        $user->fill($validated)->save();

        return Redirect::route('profile.edit')->with('success', 'Informasi akun berhasil diperbarui.');
    }

    /**
     * Update detail profil (foto, alamat, no_telepon, nim_nip).
     */
    public function updateProfile(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'no_telepon' => 'nullable|string|max:20',
            'nim_nip'    => 'nullable|string|max:50',
            'alamat'     => 'nullable|string|max:500',
            'foto'       => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'hapus_foto' => 'nullable|string',
        ], [
            'foto.image' => 'File harus berupa gambar.',
            'foto.max'   => 'Ukuran foto maksimal 2MB.',
        ]);

        // Ambil atau buat profil
        $profile = $user->profile ?? new Profile(['user_id' => $user->id]);

        // Handle foto
        if ($request->hasFile('foto')) {
            if ($profile->foto) {
                Storage::disk('public')->delete($profile->foto);
            }
            $profile->foto = $request->file('foto')->store('profiles', 'public');
        } elseif ($request->input('hapus_foto') === '1' && $profile->foto) {
            Storage::disk('public')->delete($profile->foto);
            $profile->foto = null;
        }

        $profile->user_id    = $user->id;
        $profile->no_telepon = $validated['no_telepon'] ?? null;
        $profile->nim_nip    = $validated['nim_nip']    ?? null;
        $profile->alamat     = $validated['alamat']     ?? null;
        $profile->save();

        return Redirect::route('profile.edit')->with('success', 'Profil berhasil diperbarui.');
    }

    /**
     * Update password.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password'      => ['required', 'current_password'],
            'password'              => ['required', 'min:8', 'confirmed'],
        ], [
            'current_password.required'      => 'Password saat ini wajib diisi.',
            'current_password.current_password' => 'Password saat ini tidak cocok.',
            'password.required'              => 'Password baru wajib diisi.',
            'password.min'                   => 'Password minimal 8 karakter.',
            'password.confirmed'             => 'Konfirmasi password tidak cocok.',
        ]);

        $request->user()->update([
            'password' => Hash::make($request->password),
        ]);

        return Redirect::route('profile.edit')->with('success', 'Password berhasil diperbarui.');
    }

    /**
     * Hapus akun user.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ], [
            'password.current_password' => 'Password tidak sesuai.',
        ]);

        $user = $request->user();
        Auth::logout();
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
