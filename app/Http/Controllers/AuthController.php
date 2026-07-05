<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Classroom;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

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
        $classrooms = Classroom::orderBy('name')->get();
        return view('auth.register', compact('classrooms'));
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
            'classroom_id' => ['required_without:class', 'nullable', 'exists:classrooms,id'],
            'class' => ['required_without:classroom_id', 'nullable', 'string', 'max:50'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'email.unique' => 'Email ini sudah terdaftar.',
            'nisn.unique' => 'NISN ini sudah terdaftar.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password.min' => 'Password minimal 6 karakter.',
            'classroom_id.required_without' => 'Pilihan kelas wajib diisi.'
        ]);

        $classroom_id = $request->classroom_id;
        $classStr = $request->class;
        
        if (!$classroom_id && $classStr) {
            $classroom = Classroom::firstOrCreate(['name' => trim($classStr)]);
            $classroom_id = $classroom->id;
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'nisn' => $request->nisn,
            'classroom_id' => $classroom_id,
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
     * Show the forgot password request form
     */
    public function showForgotPassword()
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }
        return view('auth.forgot-password');
    }

    /**
     * Send password reset link to user email
     */
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ], [
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format alamat email tidak valid.',
            'email.exists' => 'Alamat email tidak terdaftar di sistem kami.',
        ]);

        $email = $request->email;
        $token = Str::random(60);

        // Delete any existing token for this email
        DB::table('password_reset_tokens')->where('email', $email)->delete();

        // Insert new token
        DB::table('password_reset_tokens')->insert([
            'email' => $email,
            'token' => Hash::make($token),
            'created_at' => Carbon::now(),
        ]);

        $resetUrl = route('password.reset', ['token' => $token, 'email' => $email]);

        // Send email
        Mail::send('emails.reset-password', ['resetUrl' => $resetUrl, 'email' => $email], function ($message) use ($email) {
            $message->to($email);
            $message->subject('Reset Password Akun EcoBank Anda');
        });

        return back()->with('success', 'Tautan untuk mereset password telah dikirim ke email Anda. Silakan periksa kotak masuk atau folder spam Anda.');
    }

    /**
     * Show the reset password form
     */
    public function showResetPassword(Request $request, $token)
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }

        $email = $request->query('email');
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $email
        ]);
    }

    /**
     * Handle the password reset submission
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email', 'exists:users,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format alamat email tidak valid.',
            'email.exists' => 'Alamat email tidak terdaftar.',
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password minimal harus 6 karakter.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
        ]);

        $reset = DB::table('password_reset_tokens')->where('email', $request->email)->first();

        if (!$reset || !Hash::check($request->token, $reset->token)) {
            return back()->withErrors(['email' => 'Token reset password tidak valid atau telah kedaluwarsa.']);
        }

        // Token expiry check (e.g., 60 minutes)
        $createdAt = Carbon::parse($reset->created_at);
        if ($createdAt->addMinutes(60)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return back()->withErrors(['email' => 'Tautan reset password ini telah kedaluwarsa. Silakan minta tautan baru.']);
        }

        // Update user password
        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        // Delete the token
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return redirect()->route('login')->with('success', 'Password Anda telah berhasil diperbarui! Silakan masuk dengan password baru Anda.');
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
