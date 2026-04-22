<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->to($this->redirectPathForUser($request->user()));
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        return redirect()->to($this->redirectPathForUser($request->user()));
    }

    private function redirectPathForUser(User $user): string
    {
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
}
