<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\ParkingSpace;
use App\Models\ParkingSlot;
use App\Models\Payment;
use App\Models\CancellationSetting;
use App\Models\Refund;
use App\Services\ReservationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class BookingController extends Controller
{
    public function __construct(private ReservationService $reservationService) {}

    // ──────────────────────────────────────────────────────────────────────────
    // Driver: list own bookings
    // ──────────────────────────────────────────────────────────────────────────

    public function index()
    {
        $bookings = Booking::with(['parkingSpace', 'parkingSlot', 'payment'])
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        return Inertia::render('Driver/MyBookings', [
            'bookings' => $bookings,
            'user'     => Auth::user(),
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Driver: booking detail
    // ──────────────────────────────────────────────────────────────────────────

    public function show(Booking $booking)
    {
        $this->authorizeBooking($booking);
        $booking->load(['parkingSpace', 'parkingSlot', 'payment', 'refund']);

        return Inertia::render('Driver/BookingDetail', [
            'booking' => $booking,
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // STEP 1 — Reserve a slot (returns slot + pricing to the payment page)
    //
    // POST /driver/bookings/reserve
    // Called when user clicks "Reserve & Pay" on the space detail page.
    // The frontend then immediately opens the Razorpay payment modal.
    // ──────────────────────────────────────────────────────────────────────────

    public function reserve(Request $request)
    {
        // dd($request->all());
        $validated = $request->validate([
            'space_id'       => 'required|exists:parking_spaces,id',
            'slot_id'        => 'nullable|exists:parking_slots,id',
            'check_in'       => 'required|date|after:now',
            'check_out'      => 'required|date|after:check_in',
            'vehicle_type'   => 'required|in:car,bike,suv,bus',
            'vehicle_number' => 'required|string|max:20',
        ]);

        $space = ParkingSpace::with('slots')->findOrFail($validated['space_id']);

        // Find the best available slot for this vehicle type
        $slot = $this->findAvailableSlot($space, $validated['slot_id'] ?? null, $validated['vehicle_type']);

        if (!$slot) {
            return back()->withErrors(['slot' => 'No available slots for this vehicle type. Please try again.']);
        }

        try {
            $reservedSlot = $this->reservationService->reserveSlot($slot, Auth::id());
        } catch (\RuntimeException $e) {
            return back()->withErrors(['slot' => $e->getMessage()]);
        }

        // Calculate price so the frontend can show the Razorpay order amount
        $checkIn   = new \DateTime($validated['check_in']);
        $checkOut  = new \DateTime($validated['check_out']);
        $hours     = max(1, ($checkOut->getTimestamp() - $checkIn->getTimestamp()) / 3600);
        $base      = round($hours * $space->price_per_hour, 2);
        $fee       = round($base * (ReservationService::PLATFORM_FEE_PCT / 100), 2);
        $total     = $base + $fee;

        // Store booking intent in session so confirm() can retrieve it
        // without re-sending all fields from the frontend
        session()->put('booking_intent', [
            'space_id'       => $space->id,
            'slot_id'        => $reservedSlot->id,
            'check_in'       => $validated['check_in'],
            'check_out'      => $validated['check_out'],
            'vehicle_type'   => $validated['vehicle_type'],
            'vehicle_number' => $validated['vehicle_number'],
            'base_amount'    => $base,
            'platform_fee'   => $fee,
            'total_amount'   => $total,
            'reserved_until' => $reservedSlot->reserved_until->toIso8601String(),
        ]);

        // Return to the same page with reservation data so Vue can
        // open the payment modal and start the countdown timer
        // Use location() for client-side redirect to preserve component state
        return Inertia::location(route('spaces.show', [
            'space' => $space->id,
            'reserved' => base64_encode(json_encode([
                'slot_id'        => $reservedSlot->id,
                'slot_number'    => $reservedSlot->slot_number,
                'amount'         => $total,
                'reserved_until' => $reservedSlot->reserved_until->toIso8601String(),
                'expires_in_sec' => ReservationService::RESERVATION_MINUTES * 60,
            ])),
        ]));
    }

    // ──────────────────────────────────────────────────────────────────────────
    // STEP 2 — Confirm booking after Razorpay payment success
    //
    // POST /driver/bookings/confirm
    // Called by the frontend after Razorpay's onSuccess callback fires.
    // ──────────────────────────────────────────────────────────────────────────

    public function confirm(Request $request)
    {
        $validated = $request->validate([
            'razorpay_payment_id' => 'required|string',
            'razorpay_order_id'   => 'required|string',
            'razorpay_signature'  => 'required|string',
        ]);

        $intent = session('booking_intent');

        if (!$intent) {
            return back()->withErrors(['booking' => 'Session expired. Please start the booking again.']);
        }

        $slot = ParkingSlot::findOrFail($intent['slot_id']);

        // TODO Phase 3: verify Razorpay signature here before confirming
        // RazorpayService::verifySignature($validated) — will be added in Phase 3

        try {
            $booking = $this->reservationService->confirmBooking(
                slot:          $slot,
                userId:        Auth::id(),
                data:          $intent,
                transactionId: $validated['razorpay_payment_id']
            );
        } catch (\RuntimeException $e) {
            return back()->withErrors(['booking' => $e->getMessage()]);
        }

        // Create the Payment record (gateway details)
        Payment::create([
            'booking_id'       => $booking->id,
            'user_id'          => Auth::id(),
            'transaction_id'   => $validated['razorpay_payment_id'],
            'order_id'         => $validated['razorpay_order_id'],
            'amount'           => $intent['total_amount'],
            'currency'         => 'INR',
            'method'           => 'upi', // Razorpay tells us the actual method in Phase 3
            'status'           => 'success',
            'gateway_response' => $validated,
        ]);

        session()->forget('booking_intent');

        return redirect()
            ->route('driver.bookings.index', ['success' => 'true'])
            ->with('success', 'Booking confirmed! Check your QR code.');
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Legacy store() — kept for backward compat, delegates to reserve()
    // Remove once frontend is updated to the two-step flow
    // ──────────────────────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        return $this->reserve($request);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Cancel booking with refund policy
    // ──────────────────────────────────────────────────────────────────────────

    public function cancel(Request $request, Booking $booking)
    {
        $this->authorizeBooking($booking);

        if (!$booking->isCancellable()) {
            return back()->withErrors(['booking' => 'This booking cannot be cancelled.']);
        }

        // Find the applicable cancellation policy based on time elapsed
        $minutesElapsed = $booking->minutesSinceCreated();
        $policy = CancellationSetting::policyFor($minutesElapsed);

        // Default to full refund if no policy found (shouldn't happen with proper setup)
        $refundPercentage = $policy?->refund_percentage ?? 100;

        // Calculate refund amounts
        $totalAmount = $booking->total_amount;
        $refundAmount = round($totalAmount * ($refundPercentage / 100), 2);
        $cancellationFee = $totalAmount - $refundAmount;

        // Calculate commission splits
        $ownerShare = 0;
        $devShare = 0;

        if ($policy) {
            $ownerShare = round($cancellationFee * ($policy->owner_share_percentage / 100), 2);
            $devShare = round($cancellationFee * ($policy->dev_share_percentage / 100), 2);
        }

        DB::transaction(function () use ($booking, $policy, $refundAmount, $cancellationFee, $ownerShare, $devShare, $request) {
            // Update booking status
            $booking->update([
                'status'               => Booking::STATUS_CANCELLED,
                'cancelled_by'         => 'user',
                'cancelled_at'         => now(),
                'cancellation_reason'  => $request->input('reason'),
                'refund_amount'        => $refundAmount,
                'refunded_at'          => now(),
                'owner_commission'     => $ownerShare,
                'dev_commission'       => $devShare,
            ]);

            // Release the slot
            $booking->parkingSlot->release($booking->user_id, 'user');

            // Create refund record
            if ($booking->payment) {
                Refund::create([
                    'booking_id'                 => $booking->id,
                    'user_id'                   => $booking->user_id,
                    'payment_id'                => $booking->payment->id,
                    'cancellation_setting_id'  => $policy?->id,
                    'booking_amount'            => $booking->total_amount,
                    'refund_amount'             => $refundAmount,
                    'cancellation_fee'          => $cancellationFee,
                    'owner_earnings'            => $ownerShare,
                    'dev_earnings'              => $devShare,
                    'status'                    => 'completed',
                    'initiated_by'              => 'user',
                    'processed_at'              => now(),
                ]);

                // Update payment status
                $booking->payment->update(['status' => 'refunded']);
            }
        });

        $message = $refundAmount > 0
            ? "Booking cancelled. Refund of ₹{$refundAmount} will be processed."
            : 'Booking cancelled. No refund applicable as per cancellation policy.';

        return back()->with('success', $message);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Slot availability check (AJAX — used by the space detail page)
    //
    // GET /driver/slots/{slot}/availability
    // Returns current slot status so the frontend can poll every 5–10s
    // and show live updates before Phase 4 WebSockets are wired up.
    // ──────────────────────────────────────────────────────────────────────────

    public function checkSlotAvailability(ParkingSlot $slot)
    {
        return response()->json([
            'slot_id'        => $slot->id,
            'status'         => $slot->status,
            'is_available'   => $slot->isAvailable(),
            'reserved_until' => $slot->reserved_until?->toIso8601String(),
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Private helpers
    // ──────────────────────────────────────────────────────────────────────────

    private function findAvailableSlot(ParkingSpace $space, ?int $preferredSlotId, string $vehicleType): ?ParkingSlot
    {
        // If user picked a specific slot, try that first
        if ($preferredSlotId) {
            $slot = ParkingSlot::where('id', $preferredSlotId)
                ->where('parking_space_id', $space->id)
                ->where('status', ParkingSlot::STATUS_AVAILABLE)
                ->first();

            if ($slot) return $slot;
        }

        // Preferred: matching vehicle type
        $slot = $space->slots()
            ->where('status', ParkingSlot::STATUS_AVAILABLE)
            ->where('type', $vehicleType)
            ->first();

        if ($slot) return $slot;

        // Fallback: any available slot in the space
        return $space->slots()
            ->where('status', ParkingSlot::STATUS_AVAILABLE)
            ->first();
    }

    private function authorizeBooking(Booking $booking): void
    {
        if ($booking->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
    }
}
