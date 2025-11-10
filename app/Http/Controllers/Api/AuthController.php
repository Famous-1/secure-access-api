<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Vendor;
use App\Models\Estate;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Notifications\VerifyEmailNotification;
use App\Notifications\ResetPasswordNotification;


class AuthController extends Controller
{
    // Register new user
    public function register(Request $request)
{
    // Validation rules - estate handling
    $rules = [
        'firstname' => 'required|string|max:255',
        'lastname' => 'required|string|max:255',
        'phone' => 'required|string|max:20|unique:users,phone',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:6|confirmed',
        'avatar' => 'nullable|image|max:2048',
        'usertype' => 'required|string|in:user,vendor,admin,installer,resident,maintainer',
        'address' => 'nullable|string|max:255',
        'company_name' => 'nullable|string|max:255',
    ];

    // If admin, require estate creation data OR estate_id
    if ($request->usertype === 'admin') {
        $rules['estate'] = 'required_without:estate_id|array';
        $rules['estate.name'] = 'required_with:estate|string|max:255';
        $rules['estate.code'] = 'nullable|string|max:50|unique:estates,code';
        $rules['estate.address'] = 'nullable|string';
        $rules['estate_id'] = 'required_without:estate|exists:estates,id';
    } else {
        // For non-admin users, require estate_id
        $rules['estate_id'] = 'required|exists:estates,id';
    }

    $validator = Validator::make($request->all(), $rules);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $validator->errors()
        ], 422);
    }

    // Handle estate creation for admin
    $estateId = null;
    if ($request->usertype === 'admin' && $request->has('estate')) {
        $estateData = $request->estate;
        $code = $estateData['code'] ?? Str::slug($estateData['name']) . '-' . Str::random(6);
        
        $estate = Estate::create([
            'name' => $estateData['name'],
            'code' => $code,
            'address' => $estateData['address'] ?? null,
            'phone' => $estateData['phone'] ?? null,
            'email' => $estateData['email'] ?? null,
            'description' => $estateData['description'] ?? null,
            'is_active' => true
        ]);
        
        $estateId = $estate->id;
    } elseif ($request->has('estate_id')) {
        $estateId = $request->estate_id;
    }

    // Prepare user data
    $data = $request->only(['firstname', 'lastname', 'phone', 'email', 'usertype', 'address', 'company_name']);
    $data['password'] = Hash::make($request->password);
    $data['estate_id'] = $estateId;

    // Save avatar if uploaded
    if ($request->hasFile('avatar')) {
        $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
    }

    // Generate a verification token
    $verificationToken = random_int(100000, 999999);
    $data['verification_token'] = $verificationToken;
    $data['verification_token_expires_at'] = now()->addMinutes(10);

    // Create user
    $user = User::create($data);

    // If user is a vendor, create vendor profile
    if ($request->input('usertype') === 'vendor') {
        Vendor::create([
            'user_id' => $user->id,
            'company_name' => $request->input('company_name'),
            'address' => $request->input('address'),
        ]);
    }

    // Send verification email
    $user->notify(new VerifyEmailNotification($user, $verificationToken));

    return response()->json([
        'success' => true,
        'message' => 'User registered successfully. Please check your email to verify your account.',
        'data' => $user->load('estate'),
    ], 201);
}

    // Login user
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    // Logout user
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout successful'
        ]);
    }

    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Generate UUID token
        $resetToken = (string) Str::uuid();

        // Store token and expiration
        $user->password_reset_token = $resetToken;
        $user->password_reset_expires_at = now()->addMinutes(10);
        $user->save();

        // Notify user
        $user->notify(new ResetPasswordNotification($user, $resetToken));

        return response()->json(['message' => 'Password reset link sent to your email.']);
    }


    // Reset password
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'code' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $user = User::where('email', $request->email)
            ->where('password_reset_token', $request->code)
            ->first();

        if (!$user) {
            return response()->json(['message' => 'Invalid reset token or email.'], 400);
        }

        if (now()->greaterThan($user->password_reset_expires_at)) {
            return response()->json(['message' => 'Reset token has expired.'], 400);
        }

        $user->update([
            'password' => Hash::make($request->password),
            'password_reset_token' => null,
            'password_reset_expires_at' => null,
        ]);

        return response()->json(['message' => 'Password reset successful']);
    }


    public function verifyEmailWithCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'code' => 'required|digits:6',
        ]);
    
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
    
        // Retrieve the user with the provided email and verification code
        $user = User::where('email', $request->email)
                    ->where('verification_token', $request->code)
                    ->first();
    
        // Check if user exists and if the verification token has expired
        if (!$user || now()->greaterThan($user->verification_token_expires_at)) {
            return response()->json(['message' => 'Invalid or expired verification code'], 400);
        }
    
        // Mark email as verified and clear the verification token
        $user->update([
            'verification_token' => null,
            'email_verified_at' => now(),
        ]);
    
        return response()->json(['message' => 'Email verified successfully.']);
    }
    

public function resendVerificationCode(Request $request)
{
    // Validate the email input
    $validator = Validator::make($request->all(), [
        'email' => 'required|email',
    ]);

    if ($validator->fails()) {
        return response()->json($validator->errors(), 400);
    }

    // Find the user by email
    $user = User::where('email', $request->email)->first();

    if (!$user) {
        return response()->json(['message' => 'User not found'], 404);
    }

    // Generate a new verification code
    $verificationToken = random_int(100000, 999999); // Generate a 6-digit code
    $user->update([
        'verification_token' => $verificationToken,
        'verification_token_expires_at' => now()->addMinutes(10), // Set expiry time
    ]);

    // Send the verification email
    $user->notify(new VerifyEmailNotification($user, $verificationToken));

    return response()->json([
        'message' => 'A new verification code has been sent to your email.',
    ]);
}

public function changePassword(Request $request)
{
    try {
        $user = $request->user();

        // Validate input
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if current password is correct
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'Current password is incorrect'
            ], 401);
        }

        // Update the password
        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'message' => 'Password changed successfully'
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'message' => 'An error occurred while changing the password',
            'error' => $e->getMessage()
        ], 500);
    }
}

public function deleteUser(Request $request)
{
    try {
        // Validate input
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Find user by email
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }

        // Verify password
        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        // Delete user tokens if they exist
        $user->tokens()->delete();

        // Delete the user
        $user->delete();

        return response()->json([
            'message' => 'User account deleted successfully'
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'message' => 'An error occurred while deleting the user account',
            'error' => $e->getMessage()
        ], 500);
    }
}

/**
 * Set password using token (for admin-created accounts)
 */
public function setPassword(Request $request)
{
    $validator = Validator::make($request->all(), [
        'email' => 'required|email',
        'token' => 'required|string',
        'password' => 'required|string|min:6|confirmed',
    ]);

    if ($validator->fails()) {
        return response()->json($validator->errors(), 400);
    }

    $user = User::where('email', $request->email)
        ->where('password_setup_token', $request->token)
        ->first();

    if (!$user) {
        return response()->json(['message' => 'Invalid token or email.'], 400);
    }

    if (now()->greaterThan($user->password_setup_token_expires_at)) {
        return response()->json(['message' => 'Password setup token has expired.'], 400);
    }

    $user->update([
        'password' => Hash::make($request->password),
        'password_setup_token' => null,
        'password_setup_token_expires_at' => null,
    ]);

    return response()->json(['message' => 'Password set successfully. You can now login.']);
}


}
