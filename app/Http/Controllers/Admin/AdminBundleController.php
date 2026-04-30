<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bundle;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class AdminBundleController extends Controller
{
    public function index(Request $request)
    {
        $query = Bundle::with('units');

        if ($request->search) {
            $query->where('nama_bundle', 'like', '%' . $request->search . '%');
        }

        if ($request->status && in_array($request->status, ['tersedia', 'disewa'])) {
            $query->where('status', $request->status);
        }

        $bundles = $query->latest()->paginate(10)->withQueryString();

        return Inertia::render('Admin/Bundles/Index', [
            'bundles' => $bundles,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Bundles/Create', [
            'units' => Unit::where('status', 'tersedia')
                ->orWhereDoesntHave('bundles')
                ->get(['id', 'kode_unit', 'nama_unit', 'status']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_bundle'  => 'required|string|max:255',
            'deskripsi'    => 'nullable|string',
            'status'       => 'required|in:tersedia,disewa',
            'harga_per_hari' => 'required|numeric|min:0',
            'foto'         => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'units'        => 'required|array|min:1',
            'units.*'      => 'exists:units,id',
        ], [
            'nama_bundle.required'    => 'Nama bundle wajib diisi.',
            'status.required'         => 'Status wajib dipilih.',
            'status.in'               => 'Status tidak valid.',
            'harga_per_hari.required' => 'Harga sewa wajib diisi.',
            'harga_per_hari.numeric'  => 'Harga sewa harus berupa angka.',
            'harga_per_hari.min'      => 'Harga sewa tidak boleh negatif.',
            'foto.image'              => 'File harus berupa gambar.',
            'foto.max'                => 'Ukuran foto maksimal 2MB.',
            'units.required'          => 'Pilih minimal satu unit.',
            'units.min'               => 'Pilih minimal satu unit.',
            'units.*.exists'          => 'Unit tidak ditemukan.',
        ]);

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('bundles', 'public');
        }

        $bundle = Bundle::create([
            'nama_bundle'   => $validated['nama_bundle'],
            'deskripsi'     => $validated['deskripsi'] ?? null,
            'status'        => $validated['status'],
            'harga_per_hari'=> $validated['harga_per_hari'],
            'foto'          => $fotoPath,
        ]);

        $bundle->units()->sync($validated['units']);

        return redirect()->route('admin.bundles.index')
            ->with('success', 'Bundle berhasil ditambahkan.');
    }

    public function edit(Bundle $bundle)
    {
        return Inertia::render('Admin/Bundles/Edit', [
            'bundle' => $bundle->load('units'),
            'units'  => Unit::all(['id', 'kode_unit', 'nama_unit', 'status']),
        ]);
    }

    public function update(Request $request, Bundle $bundle)
    {
        $validated = $request->validate([
            'nama_bundle'   => 'required|string|max:255',
            'deskripsi'     => 'nullable|string',
            'status'        => 'required|in:tersedia,disewa',
            'harga_per_hari'=> 'required|numeric|min:0',
            'foto'          => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'units'         => 'required|array|min:1',
            'units.*'       => 'exists:units,id',
        ], [
            'nama_bundle.required'    => 'Nama bundle wajib diisi.',
            'status.required'         => 'Status wajib dipilih.',
            'harga_per_hari.required' => 'Harga sewa wajib diisi.',
            'harga_per_hari.numeric'  => 'Harga sewa harus berupa angka.',
            'foto.image'              => 'File harus berupa gambar.',
            'foto.max'                => 'Ukuran foto maksimal 2MB.',
            'units.required'          => 'Pilih minimal satu unit.',
            'units.min'               => 'Pilih minimal satu unit.',
        ]);

        // Handle foto
        if ($request->hasFile('foto')) {
            if ($bundle->foto) {
                Storage::disk('public')->delete($bundle->foto);
            }
            $validated['foto'] = $request->file('foto')->store('bundles', 'public');
        } elseif ($request->input('hapus_foto') == '1' && $bundle->foto) {
            Storage::disk('public')->delete($bundle->foto);
            $validated['foto'] = null;
        } else {
            $validated['foto'] = $bundle->foto;
        }

        $bundle->update([
            'nama_bundle'    => $validated['nama_bundle'],
            'deskripsi'      => $validated['deskripsi'] ?? null,
            'status'         => $validated['status'],
            'harga_per_hari' => $validated['harga_per_hari'],
            'foto'           => $validated['foto'],
        ]);

        $bundle->units()->sync($validated['units']);

        return redirect()->route('admin.bundles.index')
            ->with('success', 'Bundle berhasil diperbarui.');
    }

    public function destroy(Bundle $bundle)
    {
        if ($bundle->status === 'disewa') {
            return redirect()->route('admin.bundles.index')
                ->with('error', 'Bundle tidak dapat dihapus karena sedang disewa.');
        }

        if ($bundle->foto) {
            Storage::disk('public')->delete($bundle->foto);
        }

        $bundle->units()->detach();
        $bundle->delete();

        return redirect()->route('admin.bundles.index')
            ->with('success', 'Bundle berhasil dihapus.');
    }
}
