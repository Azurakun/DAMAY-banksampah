<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Show the login form
     */
    public function showLogin()
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }
        return view('auth.login');
    }

    /**
     * Handle authentication attempt
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::user();
            if ($user->status === 'pending') {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Akun Anda masih dalam proses persetujuan oleh Wali Kelas.',
                ])->onlyInput('email');
            } elseif ($user->status === 'rejected') {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Pendaftaran akun Anda ditolak oleh Wali Kelas.',
                ])->onlyInput('email');
            }
            
            $request->session()->regenerate();
            
            return $this->redirectBasedOnRole($user);
        }

        return back()->withErrors([
            'email' => 'Email atau password yang Anda masukkan salah.',
        ])->onlyInput('email');
    }

    /**
     * Show the student registration form
     */
    public function showRegister()
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }
        return view('auth.register');
    }

    /**
     * Handle student registration
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'nisn' => ['required', 'string', 'max:20', 'unique:users'],
            'class' => ['required', 'string', 'max:50'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'email.unique' => 'Email ini sudah terdaftar.',
            'nisn.unique' => 'NISN ini sudah terdaftar.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password.min' => 'Password minimal 6 karakter.'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'nisn' => $request->nisn,
            'class' => $request->class,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role' => 'siswa',
            'status' => 'pending', // Needs Wali Kelas approval
            'balance' => 0,
            'points' => 0,
        ]);

        // Assign Spatie Role
        $user->assignRole('siswa');

        return redirect()->route('login')->with('success', 'Pendaftaran berhasil! Akun Anda sedang menunggu persetujuan dari Wali Kelas.');
    }

    /**
     * Log the user out of the application
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah berhasil keluar dari sistem.');
    }

    /**
     * Helper to redirect users to their respective dashboards based on role
     */
    private function redirectBasedOnRole($user)
    {
        switch ($user->role) {
            case 'siswa':
                return redirect()->route('siswa.dashboard');
            case 'operator':
                return redirect()->route('operator.dashboard');
            case 'walikelas':
                return redirect()->route('walikelas.dashboard');
            case 'manajer':
                return redirect()->route('manajer.dashboard');
            default:
                Auth::logout();
                return redirect()->route('login')->with('error', 'Peran user tidak dikenali.');
        }
    }
}
