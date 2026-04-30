<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
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

        $kode = 'BK-' . strtoupper(uniqid());

        Booking::create([
            'kode_booking'           => $kode,
            'user_id'                => auth()->id(),
            'unit_id'                => $validated['unit_id']   ?? null,
            'bundle_id'              => $validated['bundle_id'] ?? null,
            'tanggal_mulai'          => $validated['tanggal_mulai'],
            'tanggal_selesai_rencana'=> $validated['tanggal_selesai_rencana'],
            'status'                 => 'booked',
        ]);

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
