<?php

namespace App\Http\Controllers;

use App\Models\Bundle;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BundleController extends Controller
{
    public function index(Request $request)
    {
        $bundles = Bundle::with(['units'])
            ->where('status', 'tersedia')
            ->latest()
            ->paginate(12);

        return Inertia::render('Bundles/Index', [
            'bundles' => $bundles,
        ]);
    }

    public function show(Bundle $bundle)
    {
        $bundle->load('units');

        return Inertia::render('Bundles/Show', [
            'bundle' => $bundle,
        ]);
    }
}