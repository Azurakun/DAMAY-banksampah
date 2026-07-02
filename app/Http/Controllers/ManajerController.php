<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ManajerController extends Controller
{
    /**
     * Display manager administrative portal
     */
    public function dashboard()
    {
        $manager = Auth::user();

        // Global stats
        $totalStudents = User::where('role', 'siswa')->count();
        
        $totalWeightRecycled = Transaction::where('type', 'setor')
            ->where('status', 'Berhasil')
            ->sum('weight');

        $totalSchoolBalance = User::where('role', 'siswa')->sum('balance');

        // Gamifikasi: Total poin terakumulasi di seluruh sekolah
        $totalSchoolPoints = User::where('role', 'siswa')->sum('points');

        $totalTransactionsCount = Transaction::count();

        // Get recent 10 transactions across the entire school
        $allRecentTransactions = Transaction::with(['student', 'wasteCategory', 'operator'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        // Calculate class-by-class recycling performance
        $classPerformance = User::where('role', 'siswa')
            ->selectRaw('class, SUM(balance) as total_balance, SUM(points) as total_points, COUNT(id) as student_count')
            ->groupBy('class')
            ->orderBy('total_points', 'desc')
            ->get();

        // Gamifikasi: Top 5 siswa teraktif dengan poin tertinggi
        $topStudents = User::where('role', 'siswa')
            ->orderBy('points', 'desc')
            ->take(5)
            ->get();

        return view('manajer.dashboard', compact(
            'manager', 'totalStudents', 'totalWeightRecycled', 
            'totalSchoolBalance', 'totalSchoolPoints', 'totalTransactionsCount', 
            'allRecentTransactions', 'classPerformance', 'topStudents'
        ));
    }

    /**
     * Show Manager profile page
     */
    public function profile()
    {
        $manager = Auth::user();
        return view('manajer.profile', compact('manager'));
    }

    /**
     * Update Manager profile details
     */
    public function updateProfile(Request $request)
    {
        $manager = Auth::user();

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
        ], [
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
            'password.min' => 'Password baru minimal 6 karakter.'
        ]);

        $manager->name = $request->name;
        $manager->phone = $request->phone;

        if ($request->filled('password')) {
            $manager->password = Hash::make($request->password);
        }

        $manager->save();

        return redirect()->route('manajer.profile')->with('success', 'Profil Anda berhasil diperbarui.');
    }

    /**
     * Handle registration of new staff (operator or walikelas)
     */
    public function registerStaff(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', 'string', 'in:operator,walikelas'],
            'class' => ['required_if:role,walikelas', 'nullable', 'string', 'max:50'],
        ], [
            'email.unique' => 'Email ini sudah terdaftar.',
            'password.min' => 'Password minimal 6 karakter.',
            'class.required_if' => 'Kolom Kelas wajib diisi untuk peran Wali Kelas.'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'class' => $request->role === 'walikelas' ? $request->class : null,
            'status' => 'approved', // Auto-approved
            'balance' => 0,
            'points' => 0,
        ]);

        // Assign Spatie Role
        $user->assignRole($request->role);

        return redirect()->route('manajer.users')->with('success', 'Staf baru (' . ucfirst($request->role) . ') berhasil didaftarkan.');
    }

    /**
     * Show all registered users with filters (search, role, status)
     */
    public function indexUsers(Request $request)
    {
        $manager = Auth::user();

        $roleFilter = $request->input('role');
        $statusFilter = $request->input('status');
        $searchQuery = $request->input('search');

        $query = User::query();

        // Apply search query
        if ($searchQuery) {
            $query->where(function ($q) use ($searchQuery) {
                $q->where('name', 'like', '%' . $searchQuery . '%')
                  ->orWhere('email', 'like', '%' . $searchQuery . '%')
                  ->orWhere('nisn', 'like', '%' . $searchQuery . '%')
                  ->orWhere('class', 'like', '%' . $searchQuery . '%')
                  ->orWhere('phone', 'like', '%' . $searchQuery . '%');
            });
        }

        // Apply role filter
        if ($roleFilter) {
            $query->where('role', $roleFilter);
        }

        // Apply status filter
        if ($statusFilter) {
            $query->where('status', $statusFilter);
        }

        // Fetch users sorted by created_at desc
        $users = $query->orderBy('created_at', 'desc')->get();

        return view('manajer.users', compact('manager', 'users', 'roleFilter', 'statusFilter', 'searchQuery'));
    }

    /**
     * Delete a user account (excluding the logged-in manager themselves)
     */
    public function destroyUser($id)
    {
        $manager = Auth::user();

        // Prevent self-deletion
        if ((int)$id === (int)$manager->id) {
            return redirect()->route('manajer.users')->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $user = User::findOrFail($id);
        
        // Custom check to only allow deleting operator, walikelas, and siswa
        if (!in_array($user->role, ['operator', 'walikelas', 'siswa'])) {
            return redirect()->route('manajer.users')->with('error', 'Peran akun ini tidak dapat dihapus.');
        }

        $user->delete();

        return redirect()->route('manajer.users')->with('success', 'Akun ' . $user->name . ' (' . ucfirst($user->role) . ') berhasil dihapus.');
    }
}
