<?php

namespace App\Http\Controllers;

use App\Models\Bundle;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BundleController extends Controller
{
    public function index(Request $request)
    {
        $bundles = Bundle::latest()->paginate(10);

        return Inertia::render('Admin/Bundles/Index', [
            'bundles' => $bundles,
        ]);
    }

    public function show(Bundle $bundle)
    {
        return Inertia::render('Admin/Bundles/Show', [
            'bundle' => $bundle,
        ]);
    }
}