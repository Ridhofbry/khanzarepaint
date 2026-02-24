<?php

namespace App\Http\Controllers;

use App\Models\Voucher;
use App\Models\VoucherClaim;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class VoucherController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show available vouchers to claim
     */
    public function claim(): View
    {
        $availableVouchers = Voucher::valid()
            ->where('starts_at', '<=', now())
            ->orderBy('expires_at')
            ->get();

        $userClaimedVouchers = Auth::user()->voucherClaims()
            ->pluck('voucher_id')
            ->toArray();

        return view('pages.voucher.claim', [
            'vouchers' => $availableVouchers,
            'claimedVouchers' => $userClaimedVouchers,
        ]);
    }

    /**
     * Store voucher claim with duplicate prevention
     */
    public function store(Voucher $voucher): RedirectResponse
    {
        $user = Auth::user();

        // Check for duplicate claims
        if (VoucherClaim::hasDuplicateClaim($voucher->id, $user->id)) {
            return back()->withErrors(['error' => 'You have already claimed this voucher']);
        }

        // Validate voucher
        if (!$voucher->canBeUsed()) {
            return back()->withErrors(['error' => 'This voucher is no longer available']);
        }

        // Create claim
        VoucherClaim::create([
            'voucher_id' => $voucher->id,
            'user_id' => $user->id,
            'claimed_at' => now(),
            'status' => 'pending',
        ]);

        return redirect()->route('voucher.my')
                       ->with('success', 'Voucher claimed successfully! Use it on your next booking.');
    }

    /**
     * Show user's claimed vouchers
     */
    public function myVouchers(): View
    {
        $vouchers = Auth::user()->voucherClaims()
            ->with('voucher')
            ->orderBy('status')
            ->orderBy('claimed_at', 'desc')
            ->paginate(10);

        return view('pages.voucher.my-vouchers', [
            'vouchers' => $vouchers,
        ]);
    }
}
