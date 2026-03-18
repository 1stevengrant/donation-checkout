<?php

namespace Ghijk\DonationCheckout\Http\Controllers;

use Statamic\Facades\User;
use Illuminate\Http\Request;
use Statamic\Http\Controllers\Controller;
use Ghijk\DonationCheckout\Actions\SendMagicLink;

class MagicLinkController extends Controller
{
    public function store(Request $request, SendMagicLink $sendMagicLink)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::findByEmail($request->input('email'));

        if ($user && $user->get('stripe_customer_id')) {
            $sendMagicLink($request->input('email'));
        }

        return back()->with('success', 'If an account exists with that email, a login link has been sent.');
    }

    public function verify(Request $request)
    {
        $email = $request->query('email');
        abort_unless($email, 403);

        $user = User::findByEmail($email);
        abort_unless($user, 403);

        auth()->login($user);

        return redirect('/donation-checkout/portal');
    }
}
