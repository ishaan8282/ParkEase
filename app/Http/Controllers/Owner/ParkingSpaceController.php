<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\ParkingSpace;
use App\Models\ParkingSlot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ParkingSpaceController extends Controller
{
    /**
     * Display owner's parking spaces
     */
    public function index()
    {
        $spaces = ParkingSpace::with(['slots', 'bookings'])
            ->where('owner_id', Auth::id())
            ->latest()
            ->get();

        return Inertia::render('Owner/MySpaces', [
            'spaces' => $spaces,
        ]);
    }

    /**
     * Show form to create a new parking space
     */
    public function create()
    {
        return Inertia::render('Owner/CreateSpace');
    }

    /**
     * Store a new parking space
     */
    public function store(Request $request)
    {
        // dd($request->all());
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'total_slots' => 'required|integer|min:1|max:1000',
            'slots' => 'required|array',
            'slots.car' => 'nullable|integer|min:0',
            'slots.bike' => 'nullable|integer|min:0',
            'slots.suv' => 'nullable|integer|min:0',
            'slots.bus' => 'nullable|integer|min:0',
            'price_per_hour' => 'required|numeric|min:0',
            'price_per_day' => 'nullable|numeric|min:0',
            'amenities' => 'nullable|array',
            'images' => 'nullable|array',
            'operating_hours' => 'nullable|array',
        ]);

        $space = ParkingSpace::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'address' => $validated['address'],
            'city' => $validated['city'],
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'total_slots' => $validated['total_slots'],
            'price_per_hour' => $validated['price_per_hour'],
            'price_per_day' => $validated['price_per_day'] ?? null,
            'amenities' => $validated['amenities'] ?? [],
            'images' => $validated['images'] ?? [],
            'operating_hours' => $validated['operating_hours'] ?? null,
            'owner_id' => Auth::id(),
            'status' => 'pending',
            'is_verified' => false,
        ]);

        // Create slots for each vehicle type based on owner's specification
        $slotTypes = $validated['slots'];
        $slotNumber = 1;

        foreach (['car', 'bike', 'suv', 'bus'] as $type) {
            $count = $slotTypes[$type] ?? 0;
            for ($i = 1; $i <= $count; $i++) {
                ParkingSlot::create([
                    'parking_space_id' => $space->id,
                    'slot_number' => 'SLOT-' . str_pad($slotNumber, 3, '0', STR_PAD_LEFT),
                    'type' => $type,
                    'status' => 'available',
                ]);
                $slotNumber++;
            }
        }

        return redirect()->route('owner.spaces.index')
            ->with('success', 'Parking space created successfully! It will be reviewed shortly.');
    }

    /**
     * Display a specific parking space
     */
    public function show(ParkingSpace $space)
    {
        $this->authorizeOwner($space);

        $space->load(['slots', 'bookings.user', 'reviews']);

        return Inertia::render('Owner/SpaceDetail', [
            'space' => $space,
        ]);
    }

    /**
     * Show form to edit a parking space
     */
    public function edit(ParkingSpace $space)
    {
        $this->authorizeOwner($space);

        // Load slots for the edit form
        $space->load('slots');

        return Inertia::render('Owner/EditSpace', [
            'space' => $space,
        ]);
    }

    /**
     * Update a parking space
     */
    public function update(Request $request, ParkingSpace $space)
    {
        $this->authorizeOwner($space);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'total_slots' => 'required|integer|min:1|max:1000',
            'slots' => 'required|array',
            'slots.car' => 'nullable|integer|min:0',
            'slots.bike' => 'nullable|integer|min:0',
            'slots.suv' => 'nullable|integer|min:0',
            'slots.bus' => 'nullable|integer|min:0',
            'price_per_hour' => 'required|numeric|min:0',
            'price_per_day' => 'nullable|numeric|min:0',
            'amenities' => 'nullable|array',
            'images' => 'nullable|array',
            'operating_hours' => 'nullable|array',
            'status' => 'required|in:active,inactive',
        ]);

        // Update space details
        $space->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'address' => $validated['address'],
            'city' => $validated['city'],
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'total_slots' => $validated['total_slots'],
            'price_per_hour' => $validated['price_per_hour'],
            'price_per_day' => $validated['price_per_day'] ?? null,
            'amenities' => $validated['amenities'] ?? [],
            'images' => $validated['images'] ?? [],
            'operating_hours' => $validated['operating_hours'] ?? null,
            'status' => $validated['status'],
        ]);

        // Get current slot counts by type
        $currentSlots = $space->slots()->get()->groupBy('type')->map(fn($group) => $group->count())->toArray();
        $newSlots = $validated['slots'];

        // Add new slots if needed
        foreach (['car', 'bike', 'suv', 'bus'] as $type) {
            $currentCount = $currentSlots[$type] ?? 0;
            $newCount = $newSlots[$type] ?? 0;
            $toAdd = $newCount - $currentCount;

            if ($toAdd > 0) {
                // Add new slots
                $lastSlotNumber = $space->slots()->max(\DB::raw('CAST(SUBSTRING(slot_number, 6) AS UNSIGNED)')) ?? 0;
                for ($i = 1; $i <= $toAdd; $i++) {
                    $space->slots()->create([
                        'slot_number' => 'SLOT-' . str_pad($lastSlotNumber + $i, 3, '0', STR_PAD_LEFT),
                        'type' => $type,
                        'status' => 'available',
                    ]);
                }
            } elseif ($toAdd < 0) {
                // Remove excess slots (only available ones)
                $toRemove = abs($toAdd);
                $slotsToRemove = $space->slots()
                    ->where('type', $type)
                    ->where('status', 'available')
                    ->limit($toRemove)
                    ->get();

                foreach ($slotsToRemove as $slot) {
                    $slot->delete();
                }
            }
        }

        return redirect()->route('owner.spaces.index')
            ->with('success', 'Parking space updated successfully!');
    }

    /**
     * Delete a parking space
     */
    public function destroy(ParkingSpace $space)
    {
        $this->authorizeOwner($space);

        // Check for active bookings
        $activeBookings = $space->bookings()
            ->where('status', 'confirmed')
            ->where('check_out', '>', now())
            ->count();

        if ($activeBookings > 0) {
            return back()->with('error', 'Cannot delete space with active bookings.');
        }

        $space->delete();

        return redirect()->route('owner.spaces.index')
            ->with('success', 'Parking space deleted successfully!');
    }

    /**
     * Verify the space belongs to the authenticated owner
     */
    private function authorizeOwner(ParkingSpace $space)
    {
        if ($space->owner_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
    }
}
