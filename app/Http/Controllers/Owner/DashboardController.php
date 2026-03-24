<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\ParkingSpace;
use App\Models\ParkingSlot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    /**
     * Display owner dashboard
     */
    public function index()
    {
        $user = Auth::user();

        // Get owner's parking spaces
        $spaces = ParkingSpace::with(['slots', 'bookings'])
            ->where('owner_id', $user->id)
            ->get();

        // Calculate stats
        $totalSpaces = $spaces->count();
        $totalSlots = $spaces->sum('total_slots');

        // Active bookings (confirmed and ongoing)
        $activeBookings = Booking::whereHas('parkingSpace', function ($query) use ($user) {
            $query->where('owner_id', $user->id);
        })
        ->where('status', 'confirmed')
        ->where('check_out', '>', now())
        ->with(['user', 'parkingSpace', 'parkingSlot'])
        ->count();

        // Today's bookings
        $todayBookings = Booking::whereHas('parkingSpace', function ($query) use ($user) {
            $query->where('owner_id', $user->id);
        })
        ->whereDate('check_in', today())
        ->with(['user', 'parkingSpace', 'parkingSlot'])
        ->get();

        // Total earnings this month
        $monthlyEarnings = Booking::whereHas('parkingSpace', function ($query) use ($user) {
            $query->where('owner_id', $user->id);
        })
        ->where('status', 'confirmed')
        ->whereMonth('created_at', now()->month)
        ->whereYear('created_at', now()->year)
        ->sum('total_amount');

        // Recent bookings
        $recentBookings = Booking::whereHas('parkingSpace', function ($query) use ($user) {
            $query->where('owner_id', $user->id);
        })
        ->with(['user', 'parkingSpace', 'parkingSlot'])
        ->latest()
        ->limit(10)
        ->get();

        // Spaces with low availability (less than 3 slots)
        $lowAvailabilitySpaces = $spaces->map(function ($space) {
            $bookedSlots = $space->slots()
                ->whereHas('bookings', function ($query) {
                    $query->where('status', 'confirmed')
                        ->where('check_out', '>', now());
                })
                ->count();

            $availableSlots = $space->total_slots - $bookedSlots;

            return [
                'id' => $space->id,
                'name' => $space->name,
                'total_slots' => $space->total_slots,
                'available_slots' => $availableSlots,
                'occupancy_rate' => $space->total_slots > 0
                    ? round(($bookedSlots / $space->total_slots) * 100)
                    : 0,
            ];
        })->filter(function ($space) {
            return $space['available_slots'] < 3;
        })->values();

        return Inertia::render('Owner/Dashboard', [
            'stats' => [
                'total_spaces' => $totalSpaces,
                'total_slots' => $totalSlots,
                'active_bookings' => $activeBookings,
                'monthly_earnings' => $monthlyEarnings,
            ],
            'todayBookings' => $todayBookings,
            'recentBookings' => $recentBookings,
            'lowAvailabilitySpaces' => $lowAvailabilitySpaces,
            'spaces' => $spaces,
        ]);
    }
}
