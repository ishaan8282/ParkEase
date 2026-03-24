<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class BookingController extends Controller
{
    /**
     * Display owner's bookings
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = Booking::whereHas('parkingSpace', function ($q) use ($user) {
            $q->where('owner_id', $user->id);
        })->with(['user', 'parkingSpace', 'parkingSlot']);

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->has('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->has('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $bookings = $query->latest()->paginate(20)->withQueryString();

        // Stats
        $stats = [
            'total' => Booking::whereHas('parkingSpace', function ($q) use ($user) {
                $q->where('owner_id', $user->id);
            })->count(),
            'confirmed' => Booking::whereHas('parkingSpace', function ($q) use ($user) {
                $q->where('owner_id', $user->id);
            })->where('status', 'confirmed')->count(),
            'pending' => Booking::whereHas('parkingSpace', function ($q) use ($user) {
                $q->where('owner_id', $user->id);
            })->where('status', 'pending')->count(),
            'completed' => Booking::whereHas('parkingSpace', function ($q) use ($user) {
                $q->where('owner_id', $user->id);
            })->where('status', 'completed')->count(),
        ];

        return Inertia::render('Owner/Bookings', [
            'bookings' => $bookings,
            'stats' => $stats,
            'filters' => $request->only(['status', 'from_date', 'to_date']),
        ]);
    }

    /**
     * Confirm a booking
     */
    public function confirm(Booking $booking)
    {
        $user = Auth::user();

        // Verify ownership
        if ($booking->parkingSpace->owner_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $booking->update(['status' => 'confirmed']);

        return back()->with('success', 'Booking confirmed successfully');
    }

    /**
     * Cancel a booking
     */
    public function cancel(Request $request, Booking $booking)
    {
        $user = Auth::user();

        // Verify ownership
        if ($booking->parkingSpace->owner_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $booking->update([
            'status' => 'cancelled',
            'cancellation_reason' => $request->reason ?? 'Cancelled by owner',
        ]);

        return back()->with('success', 'Booking cancelled successfully');
    }
}
