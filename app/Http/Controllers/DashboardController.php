<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Unit;
use App\Models\Bundle;
use App\Models\Booking;
use App\Models\User;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->role === 'admin') {
            return $this->adminDashboard();
        }

        return $this->anggotaDashboard($user);
    }

    private function adminDashboard()
    {
        $stats = [
            'totalUnit'          => Unit::count(),
            'unitDipinjam'       => Unit::where('status', 'dipinjam')->count(),
            'unitTerlambat'      => Booking::where('status', 'late')->count(),
            'totalAnggota'       => User::where('role', 'anggota')->count(),
            'totalBundle'        => Bundle::count(),
            'totalBookingHariIni'=> Booking::whereDate('created_at', today())->count(),
        ];

        $bookingTerbaru = Booking::with(['user', 'unit', 'bundle'])
            ->latest()
            ->take(5)
            ->get()
            ->map(fn($b) => [
                'id'            => $b->id,
                'kode_booking'  => $b->kode_booking,
                'status'        => $b->status,
                'tanggal_mulai' => $b->tanggal_mulai->format('d M Y'),
                'tanggal_selesai_rencana' => $b->tanggal_selesai_rencana->format('d M Y'),
                'user'   => $b->user ? ['name' => $b->user->name] : null,
                'unit'   => $b->unit ? ['nama_unit' => $b->unit->nama_unit, 'kode_unit' => $b->unit->kode_unit] : null,
                'bundle' => $b->bundle ? ['nama_bundle' => $b->bundle->nama_bundle] : null,
            ]);

        $unitPopuler = Unit::withCount('bookings')
            ->orderBy('bookings_count', 'desc')
            ->take(5)
            ->get(['id', 'kode_unit', 'nama_unit', 'status']);

        return Inertia::render('Dashboard', [
            'stats'          => $stats,
            'bookingTerbaru' => $bookingTerbaru,
            'unitPopuler'    => $unitPopuler,
        ]);
    }

    private function anggotaDashboard(User $user)
    {
        $pinjamanAktif = Booking::where('user_id', $user->id)
            ->whereIn('status', ['booked', 'active', 'late'])
            ->count();

        $totalRiwayat = Booking::where('user_id', $user->id)->count();

        $stats = [
            'pinjamanAktif' => $pinjamanAktif,
            'totalRiwayat'  => $totalRiwayat,
        ];

        $bookingTerbaru = Booking::with(['unit', 'bundle'])
            ->where('user_id', $user->id)
            ->whereIn('status', ['booked', 'active', 'late'])
            ->latest()
            ->take(3)
            ->get()
            ->map(fn($b) => [
                'id'            => $b->id,
                'kode_booking'  => $b->kode_booking,
                'status'        => $b->status,
                'tanggal_mulai' => $b->tanggal_mulai->format('d M Y'),
                'tanggal_selesai_rencana' => $b->tanggal_selesai_rencana->format('d M Y'),
                'unit'   => $b->unit ? ['nama_unit' => $b->unit->nama_unit, 'kode_unit' => $b->unit->kode_unit] : null,
                'bundle' => $b->bundle ? ['nama_bundle' => $b->bundle->nama_bundle] : null,
            ]);

        return Inertia::render('Dashboard', [
            'stats'          => $stats,
            'bookingTerbaru' => $bookingTerbaru,
            'unitPopuler'    => [],
        ]);
    }
}