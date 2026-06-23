<?php


namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

  public function login(Request $request)
{
    $request->validate([
        'UserID' => 'required',
        'Password' => 'required',
    ]);

    $user = User::where('UserID', $request->UserID)->first();

    if ($user) {
        // Check password
        if (Hash::check($request->Password, $user->Password)) {

            // ✅ Check access_type for web login
            if (in_array($user->access_type, ['web', 'both'])) {
                Auth::login($user, $request->filled('remember'));
                return redirect()->intended('/');
            } else {
                // User cannot login via web
                return back()->withErrors([
                    'UserID' => 'You do not have permission to login via web.',
                ])->withInput();
            }

        } elseif ($request->Password === $user->Password) {
            // Optional: convert plain password to hash (only if DB has plain text)
            $user->Password = Hash::make($user->Password);
            $user->save();

            if (in_array($user->access_type, ['web', 'both'])) {
                Auth::login($user, $request->filled('remember'));
                return redirect()->intended('/');
            } else {
                return back()->withErrors([
                    'UserID' => 'You do not have permission to login via web.',
                ])->withInput();
            }
        }
    }

    return back()->withErrors([
        'UserID' => 'Invalid credentials.',
    ])->withInput();
}

   public function logout(Request $request)
{
    $request->session()->forget('selected_scheme_id');

    Auth::logout();
    $request->session()->invalidate();     
    $request->session()->regenerateToken();

    return redirect()->route('login');
}
}
