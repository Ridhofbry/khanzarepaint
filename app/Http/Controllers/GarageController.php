<?php

namespace App\Http\Controllers;

use App\Models\Car;
use Illuminate\View\View;
use Illuminate\Http\Request;

class GarageController extends Controller
{
    /**
     * Display all available cars with search and filtering
     */
    public function index(Request $request): View
    {
        $query = Car::where('status', 'available');

        // Search functionality with null input handling
        if ($search = $request->input('search')) {
            $query->search($search);
        }

        // Price range filtering
        if ($minPrice = $request->input('min_price')) {
            $query->where('price', '>=', $minPrice * 100); // Convert to cents
        }

        if ($maxPrice = $request->input('max_price')) {
            $query->where('price', '<=', $maxPrice * 100);
        }

        // Fuel type filtering
        if ($fuelType = $request->input('fuel_type')) {
            $query->fuelType($fuelType);
        }

        // Year filtering
        if ($year = $request->input('year')) {
            $query->year($year);
        }

        // Eager load seller to avoid N+1 queries
        $cars = $query->with('seller')
                     ->orderBy('created_at', 'desc')
                     ->paginate(12);

        return view('pages.garage.index', [
            'cars' => $cars,
            'fuelTypes' => ['petrol', 'diesel', 'hybrid', 'electric'],
        ]);
    }

    /**
     * Show single car details with view increment
     */
    public function show(Car $car): View
    {
        // Increment view count
        $car->incrementViewCount();

        return view('pages.garage.show', [
            'car' => $car->load('seller'),
        ]);
    }
}
