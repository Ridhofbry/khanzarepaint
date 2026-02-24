<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Service;
use App\Models\Voucher;
use App\Services\BookingService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    protected BookingService $bookingService;

    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
        $this->middleware('auth');
    }

    /**
     * Show booking creation form
     */
    public function create(): View
    {
        $services = Service::where('is_active', true)->get();
        $userVouchers = Auth::user()->voucherClaims()
            ->where('status', 'pending')
            ->with('voucher')
            ->get();

        return view('pages.booking.create', [
            'services' => $services,
            'userVouchers' => $userVouchers,
        ]);
    }

    /**
     * Store booking in database with atomic transaction
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'service_id' => 'required|exists:services,id',
            'scheduled_date' => 'required|date|after:now',
            'notes' => 'nullable|string|max:500',
            'voucher_id' => 'nullable|exists:vouchers,id',
        ]);

        $service = Service::findOrFail($validated['service_id']);
        $user = Auth::user();

        $result = $this->bookingService->createBooking(
            $user,
            $service,
            $validated['scheduled_date'],
            $validated['notes'] ?? null,
            $validated['voucher_id'] ?? null
        );

        if ($result['success']) {
            return redirect()->route('booking.show', $result['booking'])
                           ->with('success', 'Booking created successfully! Your booking code: ' . $result['booking']->booking_code);
        }

        return back()->withErrors(['error' => $result['message']])->withInput();
    }

    /**
     * Display user's bookings
     */
    public function userBookings(): View
    {
        $bookings = Auth::user()->bookings()
            ->with('service', 'voucher')
            ->orderBy('scheduled_date', 'desc')
            ->paginate(10);

        return view('pages.booking.user-bookings', [
            'bookings' => $bookings,
        ]);
    }

    /**
     * Show single booking details
     */
    public function show(Booking $booking): View
    {
        $this->authorize('view', $booking);

        return view('pages.booking.show', [
            'booking' => $booking->load('service', 'user', 'voucher'),
        ]);
    }

    /**
     * Cancel booking
     */
    public function cancel(Booking $booking): RedirectResponse
    {
        $this->authorize('update', $booking);

        $result = $this->bookingService->cancelBooking($booking, request()->input('reason'));

        if ($result['success']) {
            return redirect()->route('booking.user')
                           ->with('success', 'Booking cancelled successfully');
        }

        return back()->withErrors(['error' => $result['message']]);
    }
}
