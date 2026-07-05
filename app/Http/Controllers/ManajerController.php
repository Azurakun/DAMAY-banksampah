<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Transaction;
use App\Models\Classroom;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

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
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', 'string', 'in:operator,walikelas'],
        ];

        if ($request->role === 'walikelas') {
            if ($request->has('classroom_ids')) {
                $rules['classroom_ids'] = ['required', 'array'];
                $rules['classroom_ids.*'] = ['exists:classrooms,id'];
            } elseif ($request->has('class')) {
                $rules['class'] = ['required', 'string', 'max:50'];
            } else {
                $rules['classroom_ids'] = ['required', 'array'];
            }
        }

        $request->validate($rules, [
            'email.unique' => 'Email ini sudah terdaftar.',
            'password.min' => 'Password minimal 6 karakter.',
            'classroom_ids.required' => 'Kolom Kelas wajib dipilih untuk peran Wali Kelas.'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'status' => 'approved', // Auto-approved
            'balance' => 0,
            'points' => 0,
        ]);

        // Assign Spatie Role
        $user->assignRole($request->role);

        // Assign Wali Kelas to classrooms
        if ($request->role === 'walikelas') {
            if ($request->has('classroom_ids')) {
                $user->classrooms()->sync($request->classroom_ids);
                
                // Sync to class column for read-only listings
                $classNames = Classroom::whereIn('id', $request->classroom_ids)->pluck('name')->toArray();
                $user->class = implode(', ', $classNames);
                $user->save();
            } elseif ($request->has('class') && $request->class) {
                $classroom = Classroom::firstOrCreate(['name' => trim($request->class)]);
                $user->classrooms()->sync([$classroom->id]);
                $user->class = $classroom->name;
                $user->save();
            }
        }

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
        $classrooms = Classroom::orderBy('name')->get();

        return view('manajer.users', compact('manager', 'users', 'roleFilter', 'statusFilter', 'searchQuery', 'classrooms'));
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

    /**
     * Update user details and roles/classroom assignments by manager.
     */
    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $id],
            'phone' => ['nullable', 'string', 'max:20'],
            'status' => ['required', 'string', 'in:approved,pending,rejected'],
        ];

        if ($request->filled('password')) {
            $rules['password'] = ['required', 'string', 'min:6'];
        }

        if ($user->role === 'siswa') {
            $rules['classroom_id'] = ['required', 'exists:classrooms,id'];
        }

        $request->validate($rules);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->status = $request->status;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        if ($user->role === 'siswa') {
            $user->classroom_id = $request->classroom_id;
        }

        $user->save();

        if ($user->role === 'walikelas') {
            $classroomIds = $request->input('classroom_ids', []);
            $user->classrooms()->sync($classroomIds);
            
            $classroomNames = Classroom::whereIn('id', $classroomIds)->pluck('name')->toArray();
            $user->class = implode(', ', $classroomNames);
            $user->save();
        }

        return redirect()->route('manajer.users')->with('success', 'Akun ' . $user->name . ' (' . ucfirst($user->role) . ') berhasil diperbarui.');
    }

    /**
     * Display classrooms list, homeroom teacher info, and year rollover options.
     */
    public function indexClassrooms()
    {
        $manager = Auth::user();
        $classrooms = Classroom::withCount('students')->orderBy('name')->get();
        $currentSchoolYear = Setting::getValue('school_year', '2025/2026');
        
        return view('manajer.classrooms', compact('manager', 'classrooms', 'currentSchoolYear'));
    }

    /**
     * Store new classroom.
     */
    public function storeClassroom(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:50', 'unique:classrooms,name'],
        ], [
            'name.required' => 'Nama kelas wajib diisi.',
            'name.unique' => 'Kelas dengan nama ini sudah terdaftar.',
        ]);

        Classroom::create([
            'name' => trim($request->name),
        ]);

        return redirect()->route('manajer.classrooms')->with('success', 'Kelas baru berhasil ditambahkan.');
    }

    /**
     * Destroy classroom.
     */
    public function destroyClassroom($id)
    {
        $classroom = Classroom::findOrFail($id);
        
        // Prevent deleting if it has active students
        if ($classroom->students()->count() > 0) {
            return redirect()->route('manajer.classrooms')->with('error', 'Kelas tidak dapat dihapus karena masih memiliki nasabah (siswa) aktif.');
        }

        $classroom->delete();

        return redirect()->route('manajer.classrooms')->with('success', 'Kelas berhasil dihapus.');
    }

    /**
     * Handle Year-End Rollover (Tahun Ajaran Baru)
     */
    public function rollOverSchoolYear(Request $request)
    {
        $currentYear = Setting::getValue('school_year', '2025/2026');
        
        // Parse and increment school year (e.g. 2025/2026 -> 2026/2027)
        if (preg_match('/^(\d{4})\/(\d{4})$/', $currentYear, $matches)) {
            $nextYearStart = intval($matches[1]) + 1;
            $nextYearEnd = intval($matches[2]) + 1;
            $newSchoolYear = $nextYearStart . '/' . $nextYearEnd;
        } else {
            $newSchoolYear = '2026/2027'; // Fallback
        }

        DB::beginTransaction();
        try {
            // Save new school year
            Setting::setValue('school_year', $newSchoolYear);

            // Fetch all student users
            $students = User::where('role', 'siswa')->get();

            foreach ($students as $student) {
                $currentClass = $student->class;
                $nextClass = $this->getNextClass($currentClass);

                if ($nextClass === 'Lulus') {
                    $student->class = 'Lulus';
                    $student->classroom_id = null;
                } else {
                    // Find or create classroom with the new name
                    $classroom = Classroom::firstOrCreate(['name' => $nextClass]);
                    $student->classroom_id = $classroom->id;
                    $student->class = $nextClass;
                }
                
                $student->save();
            }

            DB::commit();
            return redirect()->route('manajer.classrooms')->with('success', 'Berhasil memulai Tahun Pelajaran Baru ' . $newSchoolYear . '! Seluruh tingkat kelas siswa telah dinaikkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('manajer.classrooms')->with('error', 'Terjadi kesalahan saat roll-over: ' . $e->getMessage());
        }
    }

    /**
     * Update School Year manually.
     */
    public function updateSchoolYear(Request $request)
    {
        $request->validate([
            'school_year' => ['required', 'string', 'max:20'],
        ], [
            'school_year.required' => 'Tahun pelajaran wajib diisi.',
            'school_year.max' => 'Tahun pelajaran maksimal 20 karakter.',
        ]);

        Setting::setValue('school_year', trim($request->school_year));

        return redirect()->route('manajer.classrooms')->with('success', 'Tahun pelajaran berhasil diubah secara manual menjadi ' . trim($request->school_year));
    }

    /**
     * Helper to calculate next class level.
     */
    private function getNextClass($currentClass)
    {
        $currentClass = trim($currentClass);
        if (empty($currentClass) || strtolower($currentClass) === 'lulus') {
            return 'Lulus';
        }
        
        // Match Roman numerals first (XII, XI, X)
        if (preg_match('/^XII\b/i', $currentClass)) {
            return 'Lulus';
        }
        if (preg_match('/^XI\b/i', $currentClass)) {
            return preg_replace('/^XI\b/i', 'XII', $currentClass);
        }
        if (preg_match('/^X\b/i', $currentClass)) {
            return preg_replace('/^X\b/i', 'XI', $currentClass);
        }
        
        // Match numeric grades (12, 11, 10)
        if (preg_match('/^12\b/', $currentClass)) {
            return 'Lulus';
        }
        if (preg_match('/^11\b/', $currentClass)) {
            return preg_replace('/^11\b/', '12', $currentClass);
        }
        if (preg_match('/^10\b/', $currentClass)) {
            return preg_replace('/^10\b/', '11', $currentClass);
        }
        
        return $currentClass; // Fallback unchanged if it doesn't match standard grade prefixes
    }
}
