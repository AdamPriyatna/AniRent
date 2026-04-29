<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UnitController extends Controller
{
    public function index(Request $request)
    {
        $query = Unit::with('categories')
            ->where('status', '!=', 'rusak');

        if ($request->search) {
            $query->where('nama_unit', 'like', '%' . $request->search . '%');
        }

        $units = $query->latest()->paginate(12)->withQueryString();

        return Inertia::render('Units/Index', [
            'units'   => $units,
            'filters' => $request->only('search'),
        ]);
    }
}