<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $request->validate([
            'email' => ['bail', 'required', 'string', 'max:254', 'email'],
        ]);

        try {
            Password::sendResetLink($request->only('email'));
        } catch (\Symfony\Component\Mailer\Exception\TransportExceptionInterface $e) {
            report($e);
        }

        return back()->with('status', __('If an account exists, you will receive a password reset link.'));
    }
}
