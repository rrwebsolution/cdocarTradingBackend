<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Register a new user and return a Sanctum bearer token.
     *
     * @throws ValidationException
     */
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'address' => ['required', 'string'],
            'name' => ['required', 'string', 'max:255'],
            'mobile_number' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'username' => ['required', 'string', 'max:255', 'unique:'.User::class],
            'valid_id_file' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
            'valid_id_type' => ['required', 'string', 'max:255'],
            'device_name' => ['nullable', 'string', 'max:255'],
        ]);

        $customerRole = Role::firstOrCreate(['name' => 'Customer']);

        $user = User::create([
            'role_id' => $customerRole->id,
            'name' => $request->string('name'),
            'email' => $request->string('email'),
            'username' => $request->string('username'),
            'status' => 'inactive',
            'password' => Hash::make($request->string('password')),
        ]);

        Customer::create([
            'user_id' => $user->id,
            'name' => $request->string('name'),
            'email' => $request->string('email'),
            'contact' => $request->string('mobile_number'),
            'address' => $request->string('address'),
            'valid_id_url' => Storage::url($request->file('valid_id_file')->store('valid-ids', 'public')),
            'valid_id_type' => $request->string('valid_id_type'),
            'status' => 'pending',
        ]);

        event(new Registered($user));

        return response()->json([
            'message' => 'Registration submitted for admin approval.',
            'user' => $user->load('role'),
        ], 201);
    }

    /**
     * Authenticate a user and return a Sanctum bearer token.
     *
     * @throws ValidationException
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['nullable', 'email'],
            'login' => ['nullable', 'string'],
            'login_type' => ['nullable', 'in:email,username'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:255'],
        ]);

        $loginType = $request->string('login_type', 'email')->toString();
        $login = $request->string('login', $request->string('email')->toString())->toString();

        if (! $login) {
            throw ValidationException::withMessages([
                'email' => ['Login credential is required.'],
            ]);
        }

        $user = User::where($loginType === 'username' ? 'username' : 'email', $login)->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'email' => [$loginType === 'username' ? 'Username was not found.' : 'Email address was not found.'],
            ]);
        }

        if (! Hash::check($request->string('password'), $user->password)) {
            throw ValidationException::withMessages([
                'password' => ['Password is incorrect.'],
            ]);
        }

        if ($user->status === 'inactive') {
            return response()->json([
                'message' => 'Your account is inactive. Please contact the admin.',
                'code' => 'ACCOUNT_INACTIVE',
            ], 403);
        }

        return $this->tokenResponse($user, $request->string('device_name', 'api-token')->toString());
    }

    /**
     * Revoke the user's access tokens.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Logged out.',
        ]);
    }

    /**
     * Send a password reset link.
     *
     * @throws ValidationException
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status !== Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        return response()->json([
            'status' => __($status),
        ]);
    }

    /**
     * Reset the user's password.
     *
     * @throws ValidationException
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user) use ($request): void {
                $user->forceFill([
                    'password' => Hash::make($request->string('password')),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        return response()->json([
            'status' => __($status),
        ]);
    }

    private function tokenResponse(User $user, string $deviceName, int $status = 200): JsonResponse
    {
        return response()->json([
            'user' => $user->load('role'),
            'token' => $user->createToken($deviceName)->plainTextToken,
            'token_type' => 'Bearer',
        ], $status);
    }
}
