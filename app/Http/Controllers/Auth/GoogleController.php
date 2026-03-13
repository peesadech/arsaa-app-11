<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Setting;
use Exception;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    /**
     * Create a redirect method to google api.
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function redirectToGoogle()
    {
        $setting = Setting::first();

        // Check if Google Login is enabled
        if ($setting && !$setting->google_login_enabled) {
            return redirect()->route('login')->with('error', 'Google Login is currently disabled.');
        }

        $googleConfig = config('services.google');

        if ($setting) {
            if ($setting->google_client_id) {
                $googleConfig['client_id'] = $setting->google_client_id;
            }
            if ($setting->google_client_secret) {
                $googleConfig['client_secret'] = $setting->google_client_secret;
            }
            if ($setting->google_redirect_url) {
                $googleConfig['redirect'] = $setting->google_redirect_url;
            }
        }

        if (empty($googleConfig['client_id']) || empty($googleConfig['redirect'])) {
            return redirect()->route('login')->with('error', 'Google Login is not properly configured. Please check App Settings.');
        }

        config(['services.google' => $googleConfig]);

        return Socialite::driver('google')
            ->redirectUrl($googleConfig['redirect'])
            ->redirect();
    }

    /**
     * Return a callback method from google api.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handleGoogleCallback()
    {
        try {
            $setting = Setting::first();

            // Check if Google Login is enabled
            if ($setting && !$setting->google_login_enabled) {
                return redirect()->route('login')->with('error', 'Google Login is currently disabled.');
            }

            $googleConfig = config('services.google');

            if ($setting) {
                if ($setting->google_client_id) {
                    $googleConfig['client_id'] = $setting->google_client_id;
                }
                if ($setting->google_client_secret) {
                    $googleConfig['client_secret'] = $setting->google_client_secret;
                }
                if ($setting->google_redirect_url) {
                    $googleConfig['redirect'] = $setting->google_redirect_url;
                }
            }

            config(['services.google' => $googleConfig]);

            $user = Socialite::driver('google')
                ->redirectUrl($googleConfig['redirect'])
                ->user();

            $finduser = User::where('google_id', $user->id)
                            ->orWhere('email', $user->email)
                            ->first();

            if ($finduser) {
                // Update google_id and email_verified_at if missing
                if (!$finduser->google_id || !$finduser->email_verified_at) {
                    $updateData = [];
                    if (!$finduser->google_id) $updateData['google_id'] = $user->id;
                    if (!$finduser->email_verified_at) $updateData['email_verified_at'] = now();
                    $finduser->update($updateData);
                }

                Auth::login($finduser);
                $this->setAcademicSession();
                $token = auth('api')->login($finduser);

                return view('auth.social_callback', [
                    'token' => $token,
                    'redirect' => route('home')
                ]);
            } else {
                // Check if registration is enabled
                $setting = Setting::first();
                if ($setting && !$setting->registration_enabled) {
                    return redirect()->route('login')->with('error', 'Registration is currently disabled.');
                }

                $newUser = User::create([
                    'name' => $user->name,
                    'email' => $user->email,
                    'google_id'=> $user->id,
                    'password' => encrypt('my-google'), // Dummy password
                    'role' => 'user',
                    'status' => 1,
                    'image_path' => $user->avatar,
                    'email_verified_at' => now(),
                ]);

                // Assign role via Spatie Permission
                $newUser->assignRole('user');

                Auth::login($newUser);
                $this->setAcademicSession();
                $token = auth('api')->login($newUser);

                return view('auth.social_callback', [
                    'token' => $token,
                    'redirect' => route('home')
                ]);
            }

        } catch (Exception $e) {
            return redirect()->route('login')->with('error', 'Login failed: ' . $e->getMessage());
        }
    }

    private function setAcademicSession()
    {
        $setting = \App\Models\CurrentAcademicSetting::first();
        if ($setting) {
            session(['current_academic_year_id' => $setting->academic_year_id]);
            session(['current_semester_id' => $setting->semester_id]);
        }
    }
}
