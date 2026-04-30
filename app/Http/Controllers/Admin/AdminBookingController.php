<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Fine;
use App\Models\Unit;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AdminBookingController extends Controller
{
    /**
     * Daftar booking aktif (booked, active, late).
     */
    public function index(Request $request)
    {
        $query = Booking::with(['user', 'unit', 'bundle', 'fine'])
            ->whereIn('status', ['booked', 'active', 'late']);

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('kode_booking', 'like', '%' . $request->search . '%')
                  ->orWhereHas('user', fn($u) => $u->where('name', 'like', '%' . $request->search . '%'));
            });
        }

        if ($request->status && in_array($request->status, ['booked', 'active', 'late'])) {
            $query->where('status', $request->status);
        }

        // Auto-mark as late
        Booking::where('status', 'active')
            ->where('tanggal_selesai_rencana', '<', today())
            ->update(['status' => 'late']);

        $bookings = $query->latest()->paginate(15)->withQueryString();

        // Enrich with calculated days late
        $bookings->getCollection()->transform(function ($b) {
            $b->hari_terlambat = $b->status === 'late'
                ? today()->diffInDays($b->tanggal_selesai_rencana)
                : 0;
            $b->denda_estimasi = $b->hari_terlambat * $this->dendaPerHari($b);
            return $b;
        });

        return Inertia::render('Admin/Bookings/Index', [
            'bookings' => $bookings,
            'filters'  => $request->only(['search', 'status']),
            'stats'    => [
                'booked' => Booking::where('status', 'booked')->count(),
                'active' => Booking::where('status', 'active')->count(),
                'late'   => Booking::where('status', 'late')->count(),
            ],
        ]);
    }

    /**
     * Konfirmasi pengambilan unit oleh user (Booked -> Active).
     */
    public function confirmPickup(Booking $booking)
    {
        if ($booking->status !== 'booked') {
            return back()->with('error', 'Hanya booking dengan status Menunggu yang bisa diambil.');
        }

        $booking->update([
            'status' => 'active',
        ]);

        return back()->with('success', "Item telah diambil. Status sekarang: Sedang Dipinjam.");
    }

    /**
     * Proses pengembalian unit/bundle + hitung denda jika terlambat.
     */
    public function processReturn(Request $request, Booking $booking)
    {
        if (!in_array($booking->status, ['active', 'late'])) {
            return redirect()->route('admin.bookings.index')
                ->with('error', 'Hanya booking yang Sedang Dipinjam yang bisa dikembalikan.');
        }

        $tanggalKembali = Carbon::today();
        $isLate         = $tanggalKembali->gt($booking->tanggal_selesai_rencana);
        $hariTerlambat  = $isLate
            ? $tanggalKembali->diffInDays($booking->tanggal_selesai_rencana)
            : 0;

        // Update booking
        $booking->update([
            'status'                 => 'returned',
            'tanggal_selesai_aktual' => $tanggalKembali,
            'diproses_oleh'          => auth()->id(),
        ]);

        // Kembalikan status unit
        if ($booking->unit) {
            $booking->unit->update(['status' => 'tersedia']);
        }

        // Kembalikan status bundle
        if ($booking->bundle) {
            $booking->bundle->update(['status' => 'tersedia']);
        }

        // Buat denda jika terlambat
        if ($isLate && $hariTerlambat > 0) {
            $dendaPerHari  = $this->dendaPerHari($booking);
            $jumlahDenda   = $hariTerlambat * $dendaPerHari;

            Fine::updateOrCreate(
                ['booking_id' => $booking->id],
                [
                    'hari_terlambat' => $hariTerlambat,
                    'jumlah_denda'   => $jumlahDenda,
                    'status'         => 'unpaid',
                ]
            );

            return redirect()->route('admin.bookings.index')
                ->with('success', "Pengembalian berhasil diproses. Terlambat {$hariTerlambat} hari — denda Rp " . number_format($jumlahDenda, 0, ',', '.'));
        }

        return redirect()->route('admin.bookings.index')
            ->with('success', 'Pengembalian berhasil diproses. Tidak ada denda.');
    }

    /**
     * Riwayat semua booking yang sudah dikembalikan.
     */
    public function history(Request $request)
    {
        $query = Booking::with(['user', 'unit', 'bundle', 'fine', 'processedBy'])
            ->where('status', 'returned');

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('kode_booking', 'like', '%' . $request->search . '%')
                  ->orWhereHas('user', fn($u) => $u->where('name', 'like', '%' . $request->search . '%'));
            });
        }

        if ($request->bulan) {
            $query->whereMonth('tanggal_selesai_aktual', $request->bulan);
        }

        if ($request->tahun) {
            $query->whereYear('tanggal_selesai_aktual', $request->tahun);
        }

        $bookings = $query->latest('tanggal_selesai_aktual')->paginate(15)->withQueryString();

        return Inertia::render('Admin/Bookings/History', [
            'bookings' => $bookings,
            'filters'  => $request->only(['search', 'bulan', 'tahun']),
        ]);
    }

    /**
     * Hitung denda per hari berdasarkan harga unit/bundle.
     * Default: 10% dari harga sewa per hari, minimum Rp 5.000.
     */
    private function dendaPerHari(Booking $booking): float
    {
        $harga = 0;

        if ($booking->bundle) {
            $harga = $booking->bundle->harga_per_hari ?? 0;
        } elseif ($booking->unit) {
            $harga = $booking->unit->harga_per_hari ?? 0;
        }

        $denda = $harga * 0.1;
        return max($denda, 5000);
    }

    /**
     * Halaman laporan cetak PDF — menggunakan Blade biasa (bukan Inertia)
     * sehingga bisa di-print langsung dari browser.
     */
    public function report(Request $request)
    {
        $bulan = $request->bulan ? (int) $request->bulan : null;
        $tahun = $request->tahun ? (int) $request->tahun : now()->year;

        $query = Booking::with(['user', 'unit', 'bundle', 'fine', 'processedBy'])
            ->where('status', 'returned');

        if ($bulan) {
            $query->whereMonth('tanggal_selesai_aktual', $bulan);
        }
        if ($tahun) {
            $query->whereYear('tanggal_selesai_aktual', $tahun);
        }

        $bookings = $query->latest('tanggal_selesai_aktual')->get();

        // Summary stats
        $totalDenda      = $bookings->whereNotNull('fine')->sum(fn($b) => $b->fine->jumlah_denda ?? 0);
        $totalTerlambat  = $bookings->whereNotNull('fine')->count();
        $totalTepatWaktu = $bookings->whereNull('fine')->count();

        // Periode label
        $bulanNames = [
            1=>'Januari', 2=>'Februari', 3=>'Maret', 4=>'April',
            5=>'Mei', 6=>'Juni', 7=>'Juli', 8=>'Agustus',
            9=>'September', 10=>'Oktober', 11=>'November', 12=>'Desember'
        ];
        $periodeLabel = ($bulan ? $bulanNames[$bulan] . ' ' : '') . $tahun;

        return view('admin.reports.bookings', compact(
            'bookings', 'periodeLabel', 'totalDenda',
            'totalTerlambat', 'totalTepatWaktu', 'bulan', 'tahun'
        ));
    }
}
