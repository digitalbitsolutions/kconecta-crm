<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\UserLevel;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.auth', [
            'mode' => 'login',
            'userLevels' => UserLevel::orderBy('id')->get(),
            'documentTypes' => $this->documentTypes(),
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();
        $intendedUrl = (string) $request->session()->pull('url.intended', '');
        if ($this->isEmailVerificationUrl($intendedUrl)) {
            return redirect()->to($intendedUrl);
        }

        $user = $request->user();
        $redirectPath = $user ? $this->redirectPathForUser($user) : route('dashboard', absolute: false);

        return redirect()->to($redirectPath);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }

    private function documentTypes(): array
    {
        return [
            'DNI',
            'NIE',
            'Pasaporte',
            'Otro',
        ];
    }

    private function redirectPathForUser(User $user): string
    {
        if ($this->requiresEmailVerification($user) && ! $user->hasVerifiedEmail()) {
            return route('verification.notice', absolute: false);
        }

        if ($user->isAdmin()) {
            return route('dashboard', absolute: false);
        }

        if ($user->canManageServices() && ! $user->canManageProperties()) {
            return url('/post/services');
        }

        if ($user->canManageProperties()) {
            return url('/post/my_posts');
        }

        return route('dashboard', absolute: false);
    }

    private function requiresEmailVerification(User $user): bool
    {
        return in_array((int) $user->user_level_id, [
            User::LEVEL_SERVICE_PROVIDER,
            User::LEVEL_AGENT,
        ], true);
    }

    private function isEmailVerificationUrl(string $url): bool
    {
        if ($url === '') {
            return false;
        }

        return str_contains($url, '/verify-email/');
    }
}
