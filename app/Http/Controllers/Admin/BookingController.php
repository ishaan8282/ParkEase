<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $bookings = Booking::query()
            ->when($request->search, fn($q) => $q->where('booking_ref', 'like', "%{$request->search}%")
                ->orWhere('vehicle_number', 'like', "%{$request->search}%"))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->with(['user', 'parkingSpace'])
            ->latest()
            ->paginate(15)
            ->withQueryString()
            ->through(fn($b) => [
                'id'             => $b->id,
                'booking_ref'    => $b->booking_ref,
                'user'           => $b->user->name,
                'space'          => $b->parkingSpace->name,
                'vehicle_number' => $b->vehicle_number,
                'check_in'       => $b->check_in,
                'check_out'      => $b->check_out,
                'total_amount'   => $b->total_amount,
                'status'         => $b->status,
                'created_at'     => $b->created_at->format('d M Y'),
            ]);

        return inertia('Admin/Bookings/Index', [
            'bookings' => $bookings,
            'filters'  => $request->only(['search', 'status']),
        ]);
    }

    public function show(Booking $booking)
    {
        $booking->load(['user', 'parkingSpace', 'parkingSlot', 'payment']);

        return inertia('Admin/Bookings/Show', [
            'booking' => [
                'id'              => $booking->id,
                'booking_ref'     => $booking->booking_ref,
                'user'            => ['name' => $booking->user->name, 'email' => $booking->user->email, 'phone' => $booking->user->phone],
                'space'           => ['name' => $booking->parkingSpace->name, 'address' => $booking->parkingSpace->address],
                'slot'            => $booking->parkingSlot->slot_number ?? '-',
                'vehicle_number'  => $booking->vehicle_number,
                'vehicle_type'    => $booking->vehicle_type,
                'check_in'        => $booking->check_in,
                'check_out'       => $booking->check_out,
                'actual_check_in' => $booking->actual_check_in,
                'actual_check_out'=> $booking->actual_check_out,
                'amount'          => $booking->amount,
                'platform_fee'    => $booking->platform_fee,
                'total_amount'    => $booking->total_amount,
                'status'          => $booking->status,
                'payment_status'  => $booking->payment->status ?? 'pending',
                'created_at'      => $booking->created_at->format('d M Y H:i'),
            ],
        ]);
    }
}
