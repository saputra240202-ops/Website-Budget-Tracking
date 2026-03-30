<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use App\Models\User;

class SettingsController extends Controller
{
    public function index()
    {
        /** @var User $user */
        $user = Auth::user();

        return Inertia::render('Settings', [
            'user' => [
                'name'      => $user->name,
                'email'     => $user->email,
                'currency'  => $user->currency  ?? 'USD ($)',
                'timezone'  => $user->timezone  ?? 'UTC',
                'avatar'    => $user->avatar    ?? null,
            ]
        ]);
    }

    public function updateProfile(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users,email,' . $user->id,
            'currency' => 'nullable|string|max:20',
            'timezone' => 'nullable|string|max:60',
        ]);

        $user->update([
            'name'     => $request->name,
            'email'    => $request->email,
            'currency' => $request->currency,
            'timezone' => $request->timezone,
        ]);

        return redirect()->back()->with('success', 'Profile updated successfully.');
    }

    public function updatePassword(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $request->validate([
            'current_password'      => 'required',
            'password'              => ['required', 'confirmed', Password::min(8)],
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->back()->with('success', 'Password updated successfully.');
    }
}