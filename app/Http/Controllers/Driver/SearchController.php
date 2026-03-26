<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\ParkingSpace;
use App\Models\ParkingSlot;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class SearchController extends Controller
{
    /**
     * Display parking search page
     */
    public function index(Request $request)
    {
        $query = ParkingSpace::with(['slots', 'owner'])
            ->where('status', 'active')
            ->where('is_verified', true);

        // City filter - accept both 'city' and 'location' from frontend
        if ($request->location) {
            $query->where('city', 'like', '%' . $request->location . '%');
        } elseif ($request->city) {
            $query->where('city', $request->city);
        }

        // Price filter
        if ($request->max_price) {
            $query->where('price_per_hour', '<=', $request->max_price);
        }

        // Amenities filter
        if ($request->amenities) {
            foreach (explode(',', $request->amenities) as $amenity) {
                $query->whereJsonContains('amenities', trim($amenity));
            }
        }

        $spaces = $query->latest()->paginate(12);

        $cities = ParkingSpace::where('status', 'active')
            ->where('is_verified', true)
            ->distinct()
            ->pluck('city')
            ->filter()
            ->values();

        return Inertia::render('Driver/Search', [
            'spaces' => $spaces,
            'cities' => $cities,
            'filters' => $request->only(['city', 'location', 'max_price', 'amenities']),
        ]);
    }

    /**
     * Display parking space details
     */
    public function show(Request $request, ParkingSpace $space)
    {
        $space->load(['slots', 'owner', 'reviews']);

        // Calculate available slots (simplified - would check bookings)
        $totalSlots = $space->slots->count();
        $occupiedSlots = $space->slots()->where('status', 'booked')->count();
        $space->current_bookings = $occupiedSlots;
        $space->total_slots = $totalSlots;

        // Get rating from reviews
        if ($space->reviews->count() > 0) {
            $space->rating = round($space->reviews->avg('rating'), 1);
        }

        // Get total bookings count
        $space->total_bookings = $space->bookings()->count();

        // Check for reservation data from redirect after booking flow
        $reservation = null;
        if ($request->has('reserved')) {
            $decoded = base64_decode($request->input('reserved'));
            $reservation = json_decode($decoded, true);
        }

        return Inertia::render('Driver/SpaceDetail', [
            'space' => $space,
            'user' => Auth::user(),
            'reservation' => $reservation,
        ]);
    }
}
