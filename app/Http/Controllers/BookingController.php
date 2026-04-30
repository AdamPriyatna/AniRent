<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Unit;
use App\Models\Bundle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Inertia\Inertia;

class BookingController extends Controller
{
    /**
     * Simpan booking baru dari anggota.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'unit_id'                => 'nullable|exists:units,id',
            'bundle_id'              => 'nullable|exists:bundles,id',
            'tanggal_mulai'          => 'required|date|after_or_equal:today',
            'tanggal_selesai_rencana'=> 'required|date|after:tanggal_mulai',
        ], [
            'tanggal_mulai.required'           => 'Tanggal mulai wajib diisi.',
            'tanggal_mulai.after_or_equal'     => 'Tanggal mulai tidak boleh di masa lalu.',
            'tanggal_selesai_rencana.required' => 'Tanggal selesai wajib diisi.',
            'tanggal_selesai_rencana.after'    => 'Tanggal selesai harus setelah tanggal mulai.',
        ]);

        if (empty($validated['unit_id']) && empty($validated['bundle_id'])) {
            return back()->withErrors(['item' => 'Pilih unit atau bundle yang ingin dipinjam.']);
        }

        $user = auth()->user();

        // 1. Cek batas pinjaman aktif (max 2)
        $pinjamanAktif = Booking::where('user_id', $user->id)
            ->whereIn('status', ['booked', 'active', 'late'])
            ->count();

        if ($pinjamanAktif >= 2) {
            return back()->withErrors(['item' => 'Anda sudah memiliki 2 pinjaman aktif. Selesaikan dulu sebelum meminjam lagi.']);
        }

        // 2. Cek durasi maks 5 hari
        $mulai   = Carbon::parse($validated['tanggal_mulai']);
        $selesai = Carbon::parse($validated['tanggal_selesai_rencana']);
        $durasi  = $mulai->diffInDays($selesai);

        if ($durasi > 5) {
            return back()->withErrors(['tanggal_selesai_rencana' => 'Durasi maksimal peminjaman adalah 5 hari.']);
        }

        // 3. Cek status unit / bundle
        if (!empty($validated['unit_id'])) {
            $unit = Unit::findOrFail($validated['unit_id']);
            if ($unit->status !== 'tersedia') {
                return back()->withErrors(['item' => 'Unit ini sudah tidak tersedia.']);
            }
        }

        if (!empty($validated['bundle_id'])) {
            $bundle = Bundle::with('units')->findOrFail($validated['bundle_id']);
            if ($bundle->status !== 'tersedia') {
                return back()->withErrors(['item' => 'Bundle ini sudah tidak tersedia.']);
            }
            
            // Cek ketersediaan tiap unit di dalam bundle
            foreach ($bundle->units as $u) {
                if ($u->status !== 'tersedia') {
                    return back()->withErrors(['item' => "Salah satu unit dalam paket ini ({$u->nama_unit}) sedang tidak tersedia."]);
                }
            }
        }

        // 4. Buat booking + update status item (dalam transaction)
        $kode = null;

        DB::transaction(function () use ($validated, $user, &$kode) {
            $kode = 'BK-' . strtoupper(uniqid());

            Booking::create([
                'kode_booking'           => $kode,
                'user_id'                => $user->id,
                'unit_id'                => $validated['unit_id']   ?? null,
                'bundle_id'              => $validated['bundle_id'] ?? null,
                'tanggal_mulai'          => $validated['tanggal_mulai'],
                'tanggal_selesai_rencana'=> $validated['tanggal_selesai_rencana'],
                'status'                 => 'booked',
            ]);

            // Update status item
            if (!empty($validated['unit_id'])) {
                Unit::where('id', $validated['unit_id'])->update(['status' => 'dipinjam']);
            }
            if (!empty($validated['bundle_id'])) {
                $bundle = Bundle::with('units')->find($validated['bundle_id']);
                $bundle->update(['status' => 'disewa']);
                
                // Update SEMUA unit di dalam bundle menjadi dipinjam
                foreach ($bundle->units as $bu) {
                    $bu->update(['status' => 'dipinjam']);
                }
            }
        });

        return redirect()->route('bookings.mine')
            ->with('success', "Booking berhasil! Kode: {$kode}");
    }

    /**
     * Riwayat pinjaman milik anggota yang sedang login.
     */
    public function myBookings(Request $request)
    {
        $user  = $request->user();
        $query = Booking::with(['unit', 'bundle', 'fine'])
            ->where('user_id', $user->id);

        // Filter tab: semua | aktif | selesai
        $tab = $request->tab ?? 'semua';
        if ($tab === 'aktif') {
            $query->whereIn('status', ['booked', 'active', 'late']);
        } elseif ($tab === 'selesai') {
            $query->where('status', 'returned');
        }

        $bookings = $query->latest()->paginate(10)->withQueryString();

        // Hitung stats ringkasan
        $stats = [
            'total'    => Booking::where('user_id', $user->id)->count(),
            'aktif'    => Booking::where('user_id', $user->id)->whereIn('status', ['booked', 'active', 'late'])->count(),
            'selesai'  => Booking::where('user_id', $user->id)->where('status', 'returned')->count(),
            'late'     => Booking::where('user_id', $user->id)->where('status', 'late')->count(),
        ];

        return Inertia::render('Member/MyBookings', [
            'bookings' => $bookings,
            'stats'    => $stats,
            'filters'  => ['tab' => $tab],
        ]);
    }
}
