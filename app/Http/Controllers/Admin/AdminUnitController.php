<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class AdminUnitController extends Controller
{
    public function index(Request $request)
    {
        $query = Unit::with('categories');

        if ($request->search) {
            $query->where('nama_unit', 'like', '%' . $request->search . '%');
        }

        if ($request->kategori) {
            $query->whereHas('categories', fn($q) => $q->where('categories.id', $request->kategori));
        }

        $units = $query->latest()->paginate(10)->withQueryString();

        return Inertia::render('Admin/Units/Index', [
            'units'      => $units,
            'categories' => Category::all(['id', 'nama_kategori']),
            'filters'    => $request->only(['search', 'kategori']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Units/Create', [
            'categories' => Category::all(['id', 'nama_kategori']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_unit'    => 'required|string|max:50|unique:units,kode_unit',
            'nama_unit'    => 'required|string|max:255',
            'deskripsi'    => 'nullable|string',
            'kondisi'      => 'nullable|string|max:100',
            'status'       => 'required|in:tersedia,dipinjam,rusak',
            'foto'         => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'categories'   => 'required|array|min:1',
            'categories.*' => 'exists:categories,id',
        ], [
            'kode_unit.required'    => 'Kode unit wajib diisi.',
            'kode_unit.unique'      => 'Kode unit sudah digunakan, pilih kode lain.',
            'kode_unit.max'         => 'Kode unit maksimal 50 karakter.',
            'nama_unit.required'    => 'Nama unit wajib diisi.',
            'status.required'       => 'Status wajib dipilih.',
            'status.in'             => 'Status tidak valid.',
            'foto.image'            => 'File harus berupa gambar.',
            'foto.max'              => 'Ukuran foto maksimal 2MB.',
            'categories.required'   => 'Pilih minimal satu kategori.',
            'categories.min'        => 'Pilih minimal satu kategori.',
            'categories.*.exists'   => 'Kategori tidak ditemukan.',
        ]);

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('units', 'public');
        }

        $unit = Unit::create([
            'kode_unit'  => $validated['kode_unit'],
            'nama_unit'  => $validated['nama_unit'],
            'deskripsi'  => $validated['deskripsi'],
            'kondisi'    => $validated['kondisi'],
            'status'     => $validated['status'],
            'foto'       => $fotoPath,
        ]);

        $unit->categories()->sync($validated['categories']);

        return redirect()->route('admin.units.index')
            ->with('success', 'Unit berhasil ditambahkan.');
    }

    public function edit(Unit $unit)
    {
        return Inertia::render('Admin/Units/Edit', [
            'unit'       => $unit->load('categories'),
            'categories' => Category::all(['id', 'nama_kategori']),
        ]);
    }

    public function update(Request $request, Unit $unit)
    {
        $validated = $request->validate([
            'kode_unit'    => 'required|string|max:50|unique:units,kode_unit,' . $unit->id,
            'nama_unit'    => 'required|string|max:255',
            'deskripsi'    => 'nullable|string',
            'kondisi'      => 'nullable|string|max:100',
            'status'       => 'required|in:tersedia,dipinjam,rusak',
            'foto'         => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'categories'   => 'required|array|min:1',
            'categories.*' => 'exists:categories,id',
        ], [
            'kode_unit.required'  => 'Kode unit wajib diisi.',
            'kode_unit.unique'    => 'Kode unit sudah digunakan, pilih kode lain.',
            'nama_unit.required'  => 'Nama unit wajib diisi.',
            'status.required'     => 'Status wajib dipilih.',
            'foto.image'          => 'File harus berupa gambar.',
            'foto.max'            => 'Ukuran foto maksimal 2MB.',
            'categories.required' => 'Pilih minimal satu kategori.',
            'categories.min'      => 'Pilih minimal satu kategori.',
        ]);

        // Handle foto
        if ($request->hasFile('foto')) {
            // Hapus foto lama
            if ($unit->foto) {
                Storage::disk('public')->delete($unit->foto);
            }
            $validated['foto'] = $request->file('foto')->store('units', 'public');
        } elseif (!$request->has('foto') && $unit->foto) {
            // User hapus foto tanpa upload baru
            Storage::disk('public')->delete($unit->foto);
            $validated['foto'] = null;
        } else {
            $validated['foto'] = $unit->foto;
        }

        $unit->update([
            'kode_unit' => $validated['kode_unit'],
            'nama_unit' => $validated['nama_unit'],
            'deskripsi' => $validated['deskripsi'],
            'kondisi'   => $validated['kondisi'],
            'status'    => $validated['status'],
            'foto'      => $validated['foto'],
        ]);

        $unit->categories()->sync($validated['categories']);

        return redirect()->route('admin.units.index')
            ->with('success', 'Unit berhasil diperbarui.');
    }

    public function destroy(Unit $unit)
    {
        // Cek apakah unit sedang dipinjam
        if ($unit->status === 'dipinjam') {
            return redirect()->route('admin.units.index')
                ->with('error', 'Unit tidak dapat dihapus karena sedang dipinjam.');
        }

        if ($unit->foto) {
            Storage::disk('public')->delete($unit->foto);
        }

        $unit->categories()->detach();
        $unit->delete();

        return redirect()->route('admin.units.index')
            ->with('success', 'Unit berhasil dihapus.');
    }
}