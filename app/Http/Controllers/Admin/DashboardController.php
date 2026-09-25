<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\ParkingSpace;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_users'    => User::count(),
            'total_owners'   => User::role('owner')->count(),
            'total_drivers'  => User::role('driver')->count(),
            'total_spaces'   => ParkingSpace::count(),
            'pending_spaces' => ParkingSpace::where('status', 'pending')->count(),
            'total_bookings' => Booking::count(),
            'active_bookings'=> Booking::whereIn('status', ['confirmed', 'checked_in'])->count(),
            'total_revenue'  => Payment::where('status', 'success')->sum('amount'),
            'today_revenue'  => Payment::where('status', 'success')->whereDate('created_at', today())->sum('amount'),
        ];

        $recentBookings = Booking::with(['user', 'parkingSpace'])
            ->latest()
            ->take(5)
            ->get()
            ->map(fn($b) => [
                'id'           => $b->id,
                'booking_ref'  => $b->booking_ref,
                'user'         => $b->user->name,
                'space'        => $b->parkingSpace->name,
                'status'       => $b->status,
                'total_amount' => $b->total_amount,
                'created_at'   => $b->created_at->format('d M Y'),
            ]);

        $recentUsers = User::latest()->take(5)->get()->map(fn($u) => [
            'id'         => $u->id,
            'name'       => $u->name,
            'email'      => $u->email,
            'role'       => $u->getRoleNames()->first(),
            'status'     => $u->status,
            'created_at' => $u->created_at->format('d M Y'),
        ]);

        return inertia('Admin/Dashboard', compact('stats', 'recentBookings', 'recentUsers'));
    }

    public function editProfile()
    {
        $user = auth()->user();

        return inertia('Admin/Profile/Edit', [
            'user' => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
            ],
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $rules = [
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone' => ['nullable', 'string', 'max:20'],
        ];

        // If changing password, require previous password verification
        if ($request->filled('new_password')) {
            $rules['current_password'] = ['required', 'string'];
            $rules['new_password']      = ['required', 'string', 'min:8', 'confirmed'];
        }

        $validated = $request->validate($rules);

        // Verify current password if trying to change password
        if ($request->filled('new_password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'The current password is incorrect.']);
            }
            $validated['password'] = $request->new_password;
        }

        $user->update($validated);

        return back()->with('success', 'Profile updated successfully.');
    }
}
