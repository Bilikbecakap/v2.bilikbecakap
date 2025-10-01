<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class UserController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:view users', only: ['index', 'show']),
            new Middleware('permission:create users', only: ['create', 'store']),
            new Middleware('permission:edit users', only: ['edit', 'update']),
            new Middleware('permission:delete users', only: ['destroy']),
        ];
    }

    public function index()
    {
        $users = User::with(['roles', 'profile'])->get();

        return Inertia::render('Users/Index', [
            'users' => $users
        ]);
    }

    public function create()
    {
        $roles = Role::all();

        return Inertia::render('Users/Create', [
            'roles' => $roles
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:20',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        if ($request->roles) {
            $user->assignRole($request->roles);
        }

        // Handle profile data
        $profileData = [
            'address' => $request->address,
            'phone' => $request->phone,
        ];

        // Handle photo upload
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('profile-photos', 'public');
            $profileData['photo'] = $photoPath;
        }

        // Create profile
        $user->profile()->create($profileData);

        // Log activity
        activity()
            ->causedBy(auth()->user())
            ->performedOn($user)
            ->withProperties(['roles' => $request->roles])
            ->log('Created new user');

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    public function show(User $user)
    {
        return Inertia::render('Users/Show', [
            'user' => $user->load(['roles', 'profile'])
        ]);
    }

    public function edit(User $user)
    {
        $roles = Role::all();

        return Inertia::render('Users/Edit', [
            'user' => $user->load(['roles', 'profile']),
            'roles' => $roles
        ]);
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:20',
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        if ($request->password) {
            $user->update(['password' => bcrypt($request->password)]);
        }

        // Handle profile data
        $profileData = [
            'address' => $request->address,
            'phone' => $request->phone,
        ];

        // Handle photo upload
        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($user->profile && $user->profile->photo) {
                Storage::disk('public')->delete($user->profile->photo);
            }

            $photoPath = $request->file('photo')->store('profile-photos', 'public');
            $profileData['photo'] = $photoPath;
        }

        // Update or create profile
        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            $profileData
        );

        if ($request->has('roles')) {
            $user->syncRoles($request->roles);
        }

        activity()
            ->causedBy(auth()->user())
            ->performedOn($user)
            ->log('Updated user and profile');

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        // Delete profile photo if exists
        if ($user->profile && $user->profile->photo) {
            Storage::disk('public')->delete($user->profile->photo);
        }

        // Log before delete
        activity()
            ->causedBy(auth()->user())
            ->performedOn($user)
            ->withProperties(['deleted_user' => $user->toArray()])
            ->log('Deleted user');

        // Delete user (profile akan terhapus otomatis karena cascade)
        $user->delete();
        
        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }
}