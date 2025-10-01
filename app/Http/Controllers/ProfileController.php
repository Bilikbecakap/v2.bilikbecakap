<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ProfileController extends Controller
{
    public function show()
    {
        $user = auth()->user()->load('profile', 'roles');

        return Inertia::render('Profile/Show', [
            'user' => $user
        ]);
    }

    public function edit()
    {
        $user = auth()->user()->load('profile');

        return Inertia::render('Profile/Edit', [
            'user' => $user
        ]);
    }

    public function update(Request $request)
    {
        $user = auth()->user();

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
            // Delete old photo if exists and not default
            if ($user->profile && $user->profile->photo && $user->profile->photo !== 'default.jpg') {
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

        activity()
            ->causedBy($user)
            ->performedOn($user)
            ->log('Updated own profile');

        return redirect()->route('profile.show')->with('success', 'Profile updated successfully.');
    }
}