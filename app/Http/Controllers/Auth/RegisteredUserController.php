<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
{
    // Convert the email to lowercase before validation
    $request->merge([
        'email' => strtolower($request->email),
    ]);

    // Validate the request data
    $validatedData = $request->validate([
        'firstname' => ['required', 'string', 'max:255'],
        'lastname' => ['required', 'string', 'max:255'],
        'phone' => ['required', 'string', 'max:25'],
        'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
        'password' => ['required', 'confirmed', Rules\Password::defaults()],
        'avatar' => ['nullable', 'mimes:jpeg,png', 'max:2096'], // max size in KB
    ]);

    // Create the new user
    $user = User::create([
        'firstname' => $validatedData['firstname'],
        'lastname' => $validatedData['lastname'],
        'phone' => $validatedData['phone'],
        'email' => $validatedData['email'],
        'password' => Hash::make($validatedData['password']),
    ]);

    // Handle avatar upload if it exists, using storeAs to control the filename
    if ($request->hasFile('avatar')) {
        $avatar = $request->file('avatar');
        $avatarFileName = 'avatar_' . $user->id . '_' . time() . '.' . $avatar->getClientOriginalExtension(); // e.g., avatar_1_1618123456.jpg
        $avatar->storeAs('avatars', $avatarFileName, 'public');
        $user->update(['avatar' => $avatarFileName]);
    }

    // Fire the registered event
    event(new Registered($user));

    // Log the user in
    Auth::login($user);

    // Redirect to home
    return redirect(RouteServiceProvider::HOME);
}

}
