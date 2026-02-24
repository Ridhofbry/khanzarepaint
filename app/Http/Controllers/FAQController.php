<?php

namespace App\Http\Controllers;

use App\Models\FAQ;
use Illuminate\View\View;

class FAQController extends Controller
{
    /**
     * Display FAQs grouped by category
     */
    public function index(): View
    {
        $faqs = FAQ::active()
            ->ordered()
            ->get()
            ->groupBy('category');

        return view('pages.faq.index', [
            'faqs' => $faqs,
        ]);
    }
}
