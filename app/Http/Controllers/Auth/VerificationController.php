<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\VerifiesEmails;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class VerificationController extends Controller implements HasMiddleware
{
    use VerifiesEmails;

    protected $redirectTo = '/home';

    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
            new Middleware('signed', only: ['verify']),
            new Middleware('throttle:6,1', only: ['verify', 'resend']),
        ];
    }
}
