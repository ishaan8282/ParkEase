<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\ParkingSpace;
use App\Models\ParkingSlot;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class BookingController extends Controller
{
    /**
     * Display driver's bookings
     */
    public function index()
    {
        $bookings = Booking::with(['parkingSpace', 'parkingSlot'])
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        return Inertia::render('Driver/MyBookings', [
            'bookings' => $bookings,
            'user' => Auth::user(),
        ]);
    }

    /**
     * Show booking details
     */
    public function show(Booking $booking)
    {
        $this->authorizeBooking($booking);

        $booking->load(['parkingSpace', 'parkingSlot', 'payment']);

        return Inertia::render('Driver/BookingDetail', [
            'booking' => $booking,
        ]);
    }

    /**
     * Create a new booking
     */
    public function store(Request $request)
    {
        // dd($request->all());
        $validated = $request->validate([
            'space_id' => 'required|exists:parking_spaces,id',
            'check_in' => 'required|date|after:now',
            'check_out' => 'required|date|after:check_in',
            'vehicle_type' => 'required|in:car,bike,suv,bus',
            'vehicle_number' => 'required|string|max:20',
            'payment_method' => 'required|in:upi,card,wallet',
        ]);

        $space = ParkingSpace::findOrFail($validated['space_id']);

        // Find an available slot automatically based on vehicle type
        $slot = $space->slots()
            ->where('status', 'available')
            ->where('type', $validated['vehicle_type'])
            ->first();

        if (!$slot) {
            // Try any available slot if specific type not found
            $slot = $space->slots()
                ->where('status', 'available')
                ->first();
        }

        if (!$slot) {
            return back()->with('error', 'No slots available for this space.');
        }

        // Calculate duration and total
        $checkIn = new \DateTime($validated['check_in']);
        $checkOut = new \DateTime($validated['check_out']);
        $hours = ($checkOut->getTimestamp() - $checkIn->getTimestamp()) / 3600;
        $amount = $hours * $space->price_per_hour;
        $platformFee = $amount * 0.05; // 5% platform fee
        $totalAmount = $amount + $platformFee;

        // Create booking
        $booking = Booking::create([
            'booking_ref' => 'BK-' . strtoupper(uniqid()),
            'user_id' => Auth::id(),
            'parking_space_id' => $validated['space_id'],
            'parking_slot_id' => $slot->id,
            'check_in' => $validated['check_in'],
            'check_out' => $validated['check_out'],
            'vehicle_type' => $validated['vehicle_type'],
            'vehicle_number' => $validated['vehicle_number'],
            'amount' => $amount,
            'platform_fee' => $platformFee,
            'total_amount' => $totalAmount,
            'status' => 'confirmed',
        ]);

        // Create payment record
        Payment::create([
            'booking_id' => $booking->id,
            'user_id' => Auth::id(),
            'amount' => $totalAmount,
            'method' => $validated['payment_method'],
            'status' => 'success',
            'transaction_id' => 'TXN-' . uniqid(),
        ]);

        // Update slot status
        $slot->update(['status' => 'booked']);

        return redirect()->route('driver.bookings.index')
            ->with('success', 'Booking confirmed! Your spot is reserved.');
    }

    /**
     * Cancel a booking
     */
    public function cancel(Request $request, Booking $booking)
    {
        $this->authorizeBooking($booking);

        if (!in_array($booking->status, ['confirmed', 'pending'])) {
            return back()->with('error', 'This booking cannot be cancelled.');
        }

        // Check if within cancellation policy (e.g., 1 hour before check_in)
        $checkIn = new \DateTime($booking->check_in);
        $now = new \DateTime();
        $hoursUntilCheckIn = ($checkIn->getTimestamp() - $now->getTimestamp()) / 3600;

        if ($hoursUntilCheckIn < 1) {
            return back()->with('error', 'Cannot cancel within 1 hour of check-in time.');
        }

        // Update booking status
        $booking->update(['status' => 'cancelled']);

        // Update slot status
        $booking->parkingSlot->update(['status' => 'available']);

        // Process refund (simplified)
        if ($booking->payment) {
            $booking->payment->update(['status' => 'refunded']);
        }

        return back()->with('success', 'Booking cancelled successfully. Refund will be processed shortly.');
    }

    /**
     * Verify booking belongs to user
     */
    private function authorizeBooking(Booking $booking)
    {
        if ($booking->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
    }
}
