<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos de autenticacion invalidos.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $email = (string) $request->input('email');
        $password = (string) $request->input('password');

        $user = User::where('email', $email)->first();
        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Credenciales incorrectas',
            ], 401);
        }

        if (isset($user->is_active) && (int) $user->is_active === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario desactivado',
            ], 403);
        }

        $storedPassword = (string) $user->password;
        $hashInfo = Hash::info($storedPassword);
        $validPassword = false;

        if (! empty($hashInfo['algo'])) {
            $validPassword = Hash::check($password, $storedPassword);
        } else {
            $validPassword = hash_equals($storedPassword, $password);
            if ($validPassword) {
                // Auto-upgrade plain legacy password.
                $user->password = Hash::make($password);
                $user->save();
            }
        }

        if (! $validPassword) {
            return response()->json([
                'success' => false,
                'message' => 'Credenciales incorrectas',
            ], 401);
        }

        Auth::login($user);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ]);
    }

    public function me(Request $request)
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated',
            ], 401);
        }

        return response()->json([
            'success' => true,
            'user' => $user,
        ]);
    }

    public function logout(Request $request)
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated',
            ], 401);
        }

        if ($user->currentAccessToken()) {
            $user->currentAccessToken()->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'User logged out successfully',
        ]);
    }
}
