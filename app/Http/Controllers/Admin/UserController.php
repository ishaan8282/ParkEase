<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query()
            ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%")
                ->orWhere('email', 'like', "%{$request->search}%"))
            ->when($request->role, fn($q) => $q->role($request->role))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->with('roles');

        // Handle sorting - if sort is provided, use it; otherwise default to created_at desc
        if ($request->sort && $request->dir) {
            $query->orderBy($request->sort, $request->dir);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $users = $query
            ->paginate($request->per_page ?? 15)
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
            'filters' => $request->only(['search', 'role', 'status', 'sort', 'dir', 'per_page']),
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

    public function edit(User $user)
    {
        return inertia('Admin/Users/Edit', [
            'user' => [
                'id'         => $user->id,
                'name'       => $user->name,
                'email'      => $user->email,
                'phone'      => $user->phone,
                'role'       => $user->getRoleNames()->first(),
                'status'     => $user->status,
                'is_self'    => $user->id === auth()->id(),
            ],
        ]);
    }

    public function update(Request $request, User $user)
    {
        $isSelf = $user->id === auth()->id();

        $rules = [
            'name'   => ['required', 'string', 'max:255'],
            'email'  => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone'  => ['nullable', 'string', 'max:20'],
        ];

        // Only require status when editing other users
        if (!$isSelf) {
            $rules['status'] = ['required', 'in:active,inactive,banned'];
        }

        // If changing password and editing self, require current password
        if ($request->filled('password') && $isSelf) {
            $rules['current_password'] = ['required', 'string'];
            $rules['password'] = ['required', 'string', 'min:8'];
        } elseif ($request->filled('password')) {
            // Editing other user - direct password allowed
            $rules['password'] = ['required', 'string', 'min:8'];
        }

        $validated = $request->validate($rules);

        // If editing self and changing password, verify current password
        if ($request->filled('password') && $isSelf) {
            if (!Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'The current password is incorrect.']);
            }
        }

        // Only update password if provided
        if ($request->filled('password')) {
            $validated['password'] = $request->password;
        } else {
            unset($validated['password']);
        }

        // Only update status when editing others
        if (!$isSelf && isset($validated['status'])) {
            $user->update($validated);
        } else {
            // For self, only update name, email, phone, password (if changed)
            $user->update(
                array_filter($validated, fn($key) => in_array($key, ['name', 'email', 'phone', 'password']), ARRAY_FILTER_USE_KEY)
            );
        }

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
    }
}
