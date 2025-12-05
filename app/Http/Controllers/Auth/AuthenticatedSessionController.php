<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Support\Facades\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();
        $user = User::where('email', $request->email)->first();

        $token = $user->createToken('api-token')->plainTextToken;
        $request->session()->put('api_token', $token);
        $request->session()->put('user_name', $user->name);
        $request->session()->put('user_id', $user->id);
        if ($request->expectsJson()) {
            return response()->json([
                'user' => $user->only(['id', 'name', 'email']),
                'token' => $token
            ]);
        }

        // Store token in session for web clients (optional, for Blade/JS access)
        $request->session()->put('api_token', $token);

        // Redirect web browsers to dashboard
        return redirect()->intended(route('dashboard', absolute: false));

    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = Auth::user();

        // Revoke all API tokens for the user
        if ($user) {
            $user->tokens()->delete();
        }
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
