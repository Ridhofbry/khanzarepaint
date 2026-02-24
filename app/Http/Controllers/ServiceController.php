<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\View\View;

class ServiceController extends Controller
{
    /**
     * Display all services with proper eager loading
     */
    public function index(): View
    {
        // Eager load to avoid N+1 queries
        $services = Service::where('is_active', true)
            ->orderBy('type')
            ->get();

        $repaintServices = $services->where('type', 'repaint');
        $generalServices = $services->where('type', 'general');

        return view('pages.service', [
            'repaintServices' => $repaintServices,
            'generalServices' => $generalServices,
        ]);
    }
}
