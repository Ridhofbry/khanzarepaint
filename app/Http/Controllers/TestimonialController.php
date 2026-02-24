<?php

namespace App\Http\Controllers;

use App\Models\Testimonial;
use Illuminate\View\View;

class TestimonialController extends Controller
{
    /**
     * Display approved testimonials
     */
    public function index(): View
    {
        $testimonials = Testimonial::approved()
            ->with('user')
            ->orderBy('is_featured', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        $averageRating = Testimonial::getAverageRating();

        return view('pages.testimonial.index', [
            'testimonials' => $testimonials,
            'averageRating' => $averageRating,
        ]);
    }
}
