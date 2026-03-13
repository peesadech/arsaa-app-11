<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Setting;
use Exception;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class FacebookController extends Controller
{
    /**
     * Create a redirect method to facebook api.
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function redirectToFacebook()
    {
        $setting = Setting::first();

        // Check if Facebook Login is enabled
        if ($setting && !$setting->facebook_login_enabled) {
            return redirect()->route('login')->with('error', 'Facebook Login is currently disabled.');
        }

        $facebookConfig = config('services.facebook');

        if ($setting) {
            if ($setting->facebook_client_id) {
                $facebookConfig['client_id'] = $setting->facebook_client_id;
            }
            if ($setting->facebook_client_secret) {
                $facebookConfig['client_secret'] = $setting->facebook_client_secret;
            }
            if ($setting->facebook_redirect_url) {
                $facebookConfig['redirect'] = $setting->facebook_redirect_url;
            }
        }

        if (empty($facebookConfig['client_id']) || empty($facebookConfig['redirect'])) {
            return redirect()->route('login')->with('error', 'Facebook Login is not properly configured. Please check App Settings.');
        }

        config(['services.facebook' => $facebookConfig]);

        return Socialite::driver('facebook')
            ->redirectUrl($facebookConfig['redirect'])
            ->redirect();
    }

    /**
     * Return a callback method from facebook api.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handleFacebookCallback()
    {
        try {
            $setting = Setting::first();

            // Check if Facebook Login is enabled
            if ($setting && !$setting->facebook_login_enabled) {
                return redirect()->route('login')->with('error', 'Facebook Login is currently disabled.');
            }

            $facebookConfig = config('services.facebook');

            if ($setting) {
                if ($setting->facebook_client_id) {
                    $facebookConfig['client_id'] = $setting->facebook_client_id;
                }
                if ($setting->facebook_client_secret) {
                    $facebookConfig['client_secret'] = $setting->facebook_client_secret;
                }
                if ($setting->facebook_redirect_url) {
                    $facebookConfig['redirect'] = $setting->facebook_redirect_url;
                }
            }

            config(['services.facebook' => $facebookConfig]);

            $user = Socialite::driver('facebook')
                ->redirectUrl($facebookConfig['redirect'])
                ->user();

            $finduser = User::where('facebook_id', $user->id)
                            ->orWhere('email', $user->email)
                            ->first();

            if ($finduser) {
                // Update facebook_id and email_verified_at if missing
                if (!$finduser->facebook_id || !$finduser->email_verified_at) {
                    $updateData = [];
                    if (!$finduser->facebook_id) $updateData['facebook_id'] = $user->id;
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
                    'facebook_id'=> $user->id,
                    'password' => encrypt('my-facebook'), // Dummy password
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
