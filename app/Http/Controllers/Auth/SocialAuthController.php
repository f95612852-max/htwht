<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\User;
use App\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            
            // Check if user already exists with this Google ID
            $existingUser = User::where('google_id', $googleUser->getId())->first();
            
            if ($existingUser) {
                Auth::login($existingUser);
                return redirect()->intended('/dashboard');
            }

            // Check if user exists with this email
            $userWithEmail = User::where('email', $googleUser->getEmail())->first();
            
            if ($userWithEmail) {
                // Link Google account to existing user
                $userWithEmail->update([
                    'google_id' => $googleUser->getId(),
                ]);
                
                Auth::login($userWithEmail);
                return redirect()->intended('/dashboard');
            }

            // Create new user
            $user = $this->createUserFromGoogle($googleUser);
            Auth::login($user);
            
            return redirect('/dashboard')->with('success', 'تم إنشاء حسابك بنجاح باستخدام Google!');
            
        } catch (\Exception $e) {
            return redirect('/login')->with('error', 'حدث خطأ أثناء تسجيل الدخول بـ Google. يرجى المحاولة مرة أخرى.');
        }
    }

    public function redirectToApple()
    {
        return Socialite::driver('apple')->redirect();
    }

    public function handleAppleCallback()
    {
        try {
            $appleUser = Socialite::driver('apple')->user();
            
            // Check if user already exists with this Apple ID
            $existingUser = User::where('apple_id', $appleUser->getId())->first();
            
            if ($existingUser) {
                Auth::login($existingUser);
                return redirect()->intended('/dashboard');
            }

            // Check if user exists with this email (if provided)
            $userWithEmail = null;
            if ($appleUser->getEmail()) {
                $userWithEmail = User::where('email', $appleUser->getEmail())->first();
            }
            
            if ($userWithEmail) {
                // Link Apple account to existing user
                $userWithEmail->update([
                    'apple_id' => $appleUser->getId(),
                ]);
                
                Auth::login($userWithEmail);
                return redirect()->intended('/dashboard');
            }

            // Create new user
            $user = $this->createUserFromApple($appleUser);
            Auth::login($user);
            
            return redirect('/dashboard')->with('success', 'تم إنشاء حسابك بنجاح باستخدام Apple!');
            
        } catch (\Exception $e) {
            return redirect('/login')->with('error', 'حدث خطأ أثناء تسجيل الدخول بـ Apple. يرجى المحاولة مرة أخرى.');
        }
    }

    private function createUserFromGoogle($googleUser): User
    {
        $username = $this->generateUniqueUsername($googleUser->getName() ?? 'user');
        
        $user = User::create([
            'name' => $googleUser->getName(),
            'email' => $googleUser->getEmail(),
            'username' => $username,
            'google_id' => $googleUser->getId(),
            'register_source' => 'google',
            'email_verified_at' => now(),
            'password' => Hash::make(Str::random(32)), // Random password
        ]);

        // Create profile
        $profile = Profile::create([
            'user_id' => $user->id,
            'username' => $username,
            'name' => $googleUser->getName(),
            'bio' => null,
            'website' => null,
            'is_private' => false,
        ]);

        $user->profile_id = $profile->id;
        $user->save();

        return $user;
    }

    private function createUserFromApple($appleUser): User
    {
        $name = $appleUser->getName() ?? 'Apple User';
        $email = $appleUser->getEmail() ?? $appleUser->getId() . '@privaterelay.appleid.com';
        $username = $this->generateUniqueUsername($name);
        
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'username' => $username,
            'apple_id' => $appleUser->getId(),
            'register_source' => 'apple',
            'email_verified_at' => now(),
            'password' => Hash::make(Str::random(32)), // Random password
        ]);

        // Create profile
        $profile = Profile::create([
            'user_id' => $user->id,
            'username' => $username,
            'name' => $name,
            'bio' => null,
            'website' => null,
            'is_private' => false,
        ]);

        $user->profile_id = $profile->id;
        $user->save();

        return $user;
    }

    private function generateUniqueUsername(string $baseName): string
    {
        $username = Str::slug($baseName);
        $username = preg_replace('/[^a-zA-Z0-9_]/', '', $username);
        
        if (empty($username)) {
            $username = 'user';
        }

        $originalUsername = $username;
        $counter = 1;

        while (User::where('username', $username)->exists()) {
            $username = $originalUsername . $counter;
            $counter++;
        }

        return $username;
    }

    public function unlinkGoogle(Request $request)
    {
        $user = Auth::user();
        
        // Make sure user has other login methods
        if (!$user->hasAppleAuth() && empty($user->password)) {
            return back()->with('error', 'لا يمكن إلغاء ربط Google لأنه الطريقة الوحيدة لتسجيل الدخول.');
        }

        $user->update(['google_id' => null]);
        
        return back()->with('success', 'تم إلغاء ربط حساب Google بنجاح.');
    }

    public function unlinkApple(Request $request)
    {
        $user = Auth::user();
        
        // Make sure user has other login methods
        if (!$user->hasGoogleAuth() && empty($user->password)) {
            return back()->with('error', 'لا يمكن إلغاء ربط Apple لأنه الطريقة الوحيدة لتسجيل الدخول.');
        }

        $user->update(['apple_id' => null]);
        
        return back()->with('success', 'تم إلغاء ربط حساب Apple بنجاح.');
    }
}