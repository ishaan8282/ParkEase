<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ParkingSpace;
use Illuminate\Http\Request;

class ParkingSpaceController extends Controller
{
    public function index(Request $request)
    {
        $spaces = ParkingSpace::query()
            ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%")
                ->orWhere('address', 'like', "%{$request->search}%"))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->with('owner')
            ->latest()
            ->paginate(15)
            ->withQueryString()
            ->through(fn($s) => [
                'id'           => $s->id,
                'name'         => $s->name,
                'address'      => $s->address,
                'city'         => $s->city,
                'owner'        => $s->owner->name,
                'total_slots'  => $s->total_slots,
                'price_per_hour' => $s->price_per_hour,
                'status'       => $s->status,
                'is_verified'  => $s->is_verified,
                'created_at'   => $s->created_at->format('d M Y'),
            ]);

        return inertia('Admin/Spaces/Index', [
            'spaces'  => $spaces,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function show(ParkingSpace $space)
    {
        $space->load('owner', 'slots');

        return inertia('Admin/Spaces/Show', [
            'space' => [
                'id'             => $space->id,
                'name'           => $space->name,
                'description'    => $space->description,
                'address'        => $space->address,
                'city'           => $space->city,
                'latitude'       => $space->latitude,
                'longitude'      => $space->longitude,
                'total_slots'    => $space->total_slots,
                'price_per_hour' => $space->price_per_hour,
                'price_per_day'  => $space->price_per_day,
                'amenities'      => $space->amenities,
                'images'         => $space->images,
                'status'         => $space->status,
                'is_verified'    => $space->is_verified,
                'owner'          => [
                    'id'    => $space->owner->id,
                    'name'  => $space->owner->name,
                    'email' => $space->owner->email,
                    'phone' => $space->owner->phone,
                ],
                'bookings_count' => $space->bookings()->count(),
                'created_at'     => $space->created_at->format('d M Y'),
            ],
        ]);
    }

    public function verify(Request $request, ParkingSpace $space)
    {
        $request->validate([
            'action' => ['required', 'in:approve,reject'],
            'reason' => ['required_if:action,reject', 'nullable', 'string'],
        ]);

        if ($request->action === 'approve') {
            $space->update(['status' => 'active', 'is_verified' => true]);
            $message = 'Parking space approved successfully.';
        } else {
            $space->update(['status' => 'rejected']);
            $message = 'Parking space rejected.';
        }

        return back()->with('success', $message);
    }

    public function destroy(ParkingSpace $space)
    {
        $space->delete();
        return redirect()->route('admin.spaces.index')->with('success', 'Parking space deleted.');
    }
}
