<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreWalkInBookingRequest;
use App\Models\Booking;
use App\Models\CheckInLog;
use App\Models\ParkingSlot;
use App\Models\SlotStatusLog;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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

        // Filter by status (including walk_in virtual filter)
        if ($request->filled('status')) {
            if ($request->status === 'walk_in') {
                $query->where('is_walk_in', true);
            } else {
                $query->where('status', $request->status);
            }
        }

        // Filter by date range
        if ($request->filled('from_date')) {
            $query->whereDate('check_in', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('check_in', '<=', $request->to_date);
        }

        $bookings = $query->latest()->paginate(20)->withQueryString();

        // Owner's available slots for walk-in registration
        $availableSlots = ParkingSlot::whereHas('parkingSpace', function ($q) use ($user) {
            $q->where('owner_id', $user->id);
        })
        ->where('status', ParkingSlot::STATUS_AVAILABLE)
        ->with('parkingSpace:id,name,price_per_hour')
        ->orderBy('parking_space_id')
        ->orderBy('slot_number')
        ->get();

        // Stats
        $stats = [
            'total' => Booking::whereHas('parkingSpace', function ($q) use ($user) {
                $q->where('owner_id', $user->id);
            })->count(),
            'confirmed' => Booking::whereHas('parkingSpace', function ($q) use ($user) {
                $q->where('owner_id', $user->id);
            })->where('status', Booking::STATUS_CONFIRMED)->count(),
            'pending' => Booking::whereHas('parkingSpace', function ($q) use ($user) {
                $q->where('owner_id', $user->id);
            })->where('status', Booking::STATUS_PENDING)->count(),
            'checked_in' => Booking::whereHas('parkingSpace', function ($q) use ($user) {
                $q->where('owner_id', $user->id);
            })->where('status', Booking::STATUS_CHECKED_IN)->count(),
            'completed' => Booking::whereHas('parkingSpace', function ($q) use ($user) {
                $q->where('owner_id', $user->id);
            })->where('status', Booking::STATUS_COMPLETED)->count(),
            'walk_in' => Booking::whereHas('parkingSpace', function ($q) use ($user) {
                $q->where('owner_id', $user->id);
            })->where('is_walk_in', true)->count(),
        ];

        return Inertia::render('Owner/Bookings', [
            'bookings'       => $bookings,
            'stats'          => $stats,
            'filters'        => $request->only(['status', 'from_date', 'to_date']),
            'availableSlots' => $availableSlots,
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
            return back()->with('error', 'Unauthorized action');
        }

        $booking->update(['status' => Booking::STATUS_CONFIRMED]);

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
            return back()->with('error', 'Unauthorized action');
        }

        DB::transaction(function () use ($booking, $user, $request) {
            $booking->update([
                'status'              => Booking::STATUS_CANCELLED,
                'cancelled_by'        => 'owner',
                'cancelled_at'        => now(),
                'cancellation_reason' => $request->input('reason', 'Cancelled by owner'),
            ]);

            // Release the parking slot back to available
            if ($booking->parkingSlot) {
                $booking->parkingSlot->release($user->id, 'owner');
            }
        });

        return back()->with('success', 'Booking cancelled successfully');
    }

    /**
     * Register a Walk-In customer directly at the parking facility
     */
    public function storeWalkIn(StoreWalkInBookingRequest $request)
    {
        $user = Auth::user();
        $validated = $request->validated();

        $slot = ParkingSlot::with('parkingSpace')->findOrFail($validated['parking_slot_id']);

        // Verify owner owns this slot
        if ($slot->parkingSpace->owner_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized: Slot belongs to another facility.'], 403);
        }

        // Guard against concurrency
        if (!$slot->isAvailable()) {
            return response()->json(['message' => 'Selected slot is no longer available. Please select another slot.'], 422);
        }

        $space = $slot->parkingSpace;
        $now = now();
        $hours = isset($validated['duration_hours']) && $validated['duration_hours'] > 0
            ? (float) $validated['duration_hours']
            : 2.0;

        $checkOut = (clone $now)->addMinutes((int) ($hours * 60));

        // Amount calculation: custom amount if provided, else hours * rate
        if (isset($validated['custom_amount']) && $validated['custom_amount'] !== null && $validated['custom_amount'] !== '') {
            $amount = round((float) $validated['custom_amount'], 2);
        } else {
            $amount = round($hours * (float) $space->price_per_hour, 2);
        }

        $platformFee = 0.00; // Walk-ins at gate don't deduct platform fees
        $totalAmount = $amount + $platformFee;

        $booking = DB::transaction(function () use (
            $slot, $space, $user, $validated, $now, $checkOut, $amount, $platformFee, $totalAmount
        ) {
            $createdBooking = Booking::create([
                'booking_ref'       => $this->generateBookingRef(),
                'user_id'           => $user->id, // Associated with space owner/operator
                'parking_space_id'  => $space->id,
                'parking_slot_id'   => $slot->id,
                'check_in'          => $now,
                'check_out'         => $checkOut,
                'actual_check_in'   => $now,
                'vehicle_number'    => strtoupper(trim($validated['vehicle_number'])),
                'vehicle_type'      => $validated['vehicle_type'] ?? ($slot->type ?? 'car'),
                'amount'            => $amount,
                'platform_fee'      => $platformFee,
                'total_amount'      => $totalAmount,
                'owner_commission'  => $amount,
                'dev_commission'    => 0.00,
                'status'            => Booking::STATUS_CHECKED_IN,
                'is_walk_in'        => true,
                'customer_name'     => trim($validated['customer_name']),
                'customer_phone'    => trim($validated['customer_phone']),
                'customer_email'    => isset($validated['customer_email']) ? trim($validated['customer_email']) : null,
                'payment_status'    => $validated['payment_status'],
                'paid_at'           => in_array($validated['payment_status'], ['paid', 'cash']) ? $now : null,
                'notes'             => $validated['notes'] ?? null,
                'qr_code'           => 'QR-WALKIN-' . bin2hex(random_bytes(8)),
            ]);

            // Mark slot occupied
            $oldStatus = $slot->status;
            $slot->update([
                'status'              => ParkingSlot::STATUS_OCCUPIED,
                'reserved_by_user_id' => $user->id,
                'reserved_until'      => $checkOut,
            ]);

            // Slot status log
            SlotStatusLog::create([
                'parking_slot_id'    => $slot->id,
                'booking_id'         => $createdBooking->id,
                'old_status'         => $oldStatus,
                'new_status'         => ParkingSlot::STATUS_OCCUPIED,
                'changed_by_user_id' => $user->id,
                'trigger_source'     => 'owner',
                'notes'              => 'Walk-in customer checked in by owner',
            ]);

            // Check-in audit log
            CheckInLog::create([
                'booking_id'          => $createdBooking->id,
                'event_type'          => 'check_in',
                'method'              => 'manual',
                'performed_by_user_id'=> $user->id,
                'is_successful'       => true,
            ]);

            return $createdBooking;
        });

        // Automatically generate payslip PDF
        try {
            $this->generatePayslip($booking);
        } catch (\Throwable $e) {
            \Log::warning("Could not pre-generate payslip PDF for walk-in #{$booking->id}: " . $e->getMessage());
        }

        return response()->json([
            'message' => 'Walk-in customer successfully registered & checked in!',
            'booking' => $booking->load(['parkingSlot', 'parkingSpace']),
        ], 201);
    }

    /**
     * Check-Out a checked-in booking (Walk-In or regular)
     */
    public function checkOut(Booking $booking)
    {
        $user = Auth::user();

        if ($booking->parkingSpace->owner_id !== $user->id) {
            return back()->with('error', 'Unauthorized action');
        }

        if ($booking->status !== Booking::STATUS_CHECKED_IN) {
            return back()->with('error', 'Booking is not currently checked in.');
        }

        DB::transaction(function () use ($booking, $user) {
            $now = now();

            $booking->update([
                'status'           => Booking::STATUS_COMPLETED,
                'actual_check_out' => $now,
            ]);

            // Release slot back to available
            if ($booking->parkingSlot) {
                $booking->parkingSlot->release($user->id, 'owner');
            }

            // Check-out audit log
            CheckInLog::create([
                'booking_id'           => $booking->id,
                'event_type'           => 'check_out',
                'method'               => 'manual',
                'performed_by_user_id' => $user->id,
                'is_successful'        => true,
            ]);
        });

        return back()->with('success', "Booking #{$booking->booking_ref} checked out successfully.");
    }

    /**
     * Download or view parking receipt / payslip PDF
     */
    public function downloadPayslip(Booking $booking)
    {
        $user = Auth::user();

        if ($booking->parkingSpace->owner_id !== $user->id && $booking->user_id !== $user->id) {
            abort(403, 'Unauthorized');
        }

        $booking->load(['parkingSpace', 'parkingSlot', 'user']);

        $pdf = Pdf::loadView('payslips.booking', compact('booking'));
        return $pdf->download("ParkEase-Slip-{$booking->booking_ref}.pdf");
    }

    /**
     * Pre-generate and store payslip PDF
     */
    public function generatePayslip(Booking $booking)
    {
        $booking->load(['parkingSpace', 'parkingSlot', 'user']);
        $pdf = Pdf::loadView('payslips.booking', compact('booking'));
        $path = "payslips/booking-{$booking->id}.pdf";
        Storage::disk('public')->put($path, $pdf->output());

        $booking->update(['payslip_path' => $path]);
        return $path;
    }

    /**
     * Helper to generate unique booking reference
     */
    private function generateBookingRef(): string
    {
        do {
            $ref = 'WI-' . strtoupper(substr(md5(uniqid()), 0, 8));
        } while (Booking::where('booking_ref', $ref)->exists());

        return $ref;
    }
}
