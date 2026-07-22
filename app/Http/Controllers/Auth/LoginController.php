<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Show the login view.
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    /**
     * Handle an authentication attempt.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'กรุณากรอกอีเมล',
            'email.email' => 'รูปแบบอีเมลไม่ถูกต้อง',
            'password.required' => 'กรุณากรอกรหัสผ่าน',
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if ($user && ! $user->is_active) {
            AuditLogService::log(
                action: 'Login Failed (Inactive Account)',
                module: 'Authentication',
                oldValues: ['email' => $credentials['email']]
            );

            throw ValidationException::withMessages([
                'email' => 'บัญชีผู้ใช้งานของคุณถูกระงับการใช้งาน กรุณาติดต่อผู้ดูแลระบบ',
            ]);
        }

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            /** @var User $loggedUser */
            $loggedUser = Auth::user();
            $loggedUser->update([
                'last_login_at' => now(),
                'last_login_ip' => $request->ip(),
            ]);

            AuditLogService::log(
                action: 'Login Successful',
                module: 'Authentication',
                recordId: (string) $loggedUser->id
            );

            if ($loggedUser->must_change_password) {
                return redirect()->route('password.change')->with('warning', 'กรุณาเปลี่ยนรหัสผ่านสำหรับการเข้าใช้งานครั้งแรก');
            }

            return redirect()->intended(route('dashboard'));
        }

        AuditLogService::log(
            action: 'Login Failed (Invalid Credentials)',
            module: 'Authentication',
            oldValues: ['email' => $credentials['email']]
        );

        throw ValidationException::withMessages([
            'email' => 'อีเมลหรือรหัสผ่านไม่ถูกต้อง',
        ]);
    }

    /**
     * Log the user out of the application.
     */
    public function logout(Request $request)
    {
        if (Auth::check()) {
            AuditLogService::log(
                action: 'Logout',
                module: 'Authentication',
                recordId: (string) Auth::id()
            );
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'ออกจากระบบเรียบร้อยแล้ว');
    }
}
