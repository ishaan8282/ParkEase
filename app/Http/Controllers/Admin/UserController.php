<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::query()
            ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%")
                ->orWhere('email', 'like', "%{$request->search}%"))
            ->when($request->role, fn($q) => $q->role($request->role))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->with('roles')
            ->latest()
            ->paginate(15)
            ->withQueryString()
            ->through(fn($u) => [
                'id'         => $u->id,
                'name'       => $u->name,
                'email'      => $u->email,
                'phone'      => $u->phone,
                'role'       => $u->getRoleNames()->first(),
                'status'     => $u->status,
                'created_at' => $u->created_at->format('d M Y'),
            ]);

        return inertia('Admin/Users/Index', [
            'users'   => $users,
            'filters' => $request->only(['search', 'role', 'status']),
        ]);
    }

    public function show(User $user)
    {
        return inertia('Admin/Users/Show', [
            'user' => [
                'id'         => $user->id,
                'name'       => $user->name,
                'email'      => $user->email,
                'phone'      => $user->phone,
                'role'       => $user->getRoleNames()->first(),
                'status'     => $user->status,
                'created_at' => $user->created_at->format('d M Y'),
                'bookings_count' => $user->bookings()->count(),
            ],
        ]);
    }

    public function updateStatus(Request $request, User $user)
    {
        $request->validate([
            'status' => ['required', 'in:active,inactive,banned'],
        ]);

        $user->update(['status' => $request->status]);

        return back()->with('success', "User status updated to {$request->status}.");
    }

    public function updateRole(Request $request, User $user)
    {
        $request->validate([
            'role' => ['required', 'in:admin,owner,driver'],
        ]);

        $user->syncRoles([$request->role]);

        return back()->with('success', "User role updated to {$request->role}.");
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully.');
    }
}
