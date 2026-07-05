<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Transaction;
use App\Models\Classroom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WaliKelasController extends Controller
{
    /**
     * Helper to get classroom IDs and automatically heal pivot if empty but class is present.
     */
    protected function getTeacherClassroomIds($teacher)
    {
        $ids = $teacher->classrooms->pluck('id')->toArray();
        if (empty($ids) && $teacher->class) {
            $classNames = array_map('trim', explode(',', $teacher->class));
            $ids = [];
            foreach ($classNames as $name) {
                if ($name) {
                    $classroom = Classroom::firstOrCreate(['name' => $name]);
                    $ids[] = $classroom->id;
                }
            }
            $teacher->classrooms()->sync($ids);
        }
        return $ids;
    }

    /**
     * Display homeroom class dashboard with optional class/angkatan filter
     */
    public function dashboard(Request $request)
    {
        $teacher = Auth::user();
        $teacherClassroomIds = $this->getTeacherClassroomIds($teacher);
        $myClassrooms = $teacher->classrooms()->orderBy('name')->get();

        // Get all distinct classes from teacher's own classrooms
        $allClasses = $myClassrooms->pluck('name');

        // Derive all available angkatan (e.g. "XII", "XI", "X") from class names
        $allAngkatan = $allClasses->map(function ($cls) {
            $parts = explode(' ', trim($cls));
            return strtoupper($parts[0]); // "XII RPL 1" -> "XII"
        })->unique()->sort()->values();

        // Resolve selected filters
        $selectedClass    = $request->input('kelas', null);
        $selectedAngkatan = $request->input('angkatan', null);

        // Build base query for students in teacher's classrooms
        $studentsQuery = User::where('role', 'siswa')
            ->whereIn('classroom_id', $teacherClassroomIds)
            ->orderBy('points', 'desc');

        if ($selectedAngkatan) {
            // Filter by angkatan prefix (e.g. 'XII' matches 'XII RPL 1', 'XII TKJ 2', etc.)
            $studentsQuery->where('class', 'like', $selectedAngkatan . ' %');
            $className = 'Angkatan ' . $selectedAngkatan;
        } elseif ($selectedClass) {
            $studentsQuery->where('class', $selectedClass);
            $className = $selectedClass;
        } else {
            $className = $myClassrooms->count() === 1 ? $myClassrooms->first()->name : 'Semua Kelas Asuhan';
        }

        $students   = $studentsQuery->get();
        $studentIds = $students->pluck('id')->toArray();

        // Calculate class total weight and balance
        $classTotalWeight = Transaction::whereIn('user_id', $studentIds)
            ->where('type', 'setor')
            ->where('status', 'Berhasil')
            ->sum('weight');

        $classTotalBalance = $students->sum('balance');

        return view('walikelas.dashboard', compact(
            'teacher',
            'className',
            'students',
            'classTotalWeight',
            'classTotalBalance',
            'allClasses',
            'allAngkatan',
            'selectedClass',
            'selectedAngkatan'
        ));
    }

    /**
     * Show pending student registrations for the homeroom teacher
     */
    public function showPendaftar()
    {
        $teacher = Auth::user();
        $teacherClassroomIds = $this->getTeacherClassroomIds($teacher);
        $teacherClassroomNames = $teacher->classrooms->pluck('name')->toArray();
        $className = implode(', ', $teacherClassroomNames) ?: 'Belum Ada Kelas';

        // Pending students in the teacher's classrooms
        $pendingStudents = User::where('role', 'siswa')
            ->where('status', 'pending')
            ->whereIn('classroom_id', $teacherClassroomIds)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('walikelas.pendaftar', compact('teacher', 'className', 'pendingStudents'));
    }

    /**
     * Approve selected student registrations in bulk
     */
    public function approveBulk(Request $request)
    {
        $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['exists:users,id']
        ]);

        $teacher = Auth::user();
        $teacherClassroomIds = $this->getTeacherClassroomIds($teacher);

        // Ensure we only approve pending students in this teacher's classrooms
        $count = User::whereIn('id', $request->ids)
            ->where('role', 'siswa')
            ->where('status', 'pending')
            ->whereIn('classroom_id', $teacherClassroomIds)
            ->update(['status' => 'approved']);

        return redirect()->route('walikelas.pendaftar')->with('success', "$count siswa berhasil disetujui.");
    }

    /**
     * Reject selected student registrations in bulk
     */
    public function rejectBulk(Request $request)
    {
        $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['exists:users,id']
        ]);

        $teacher = Auth::user();
        $teacherClassroomIds = $this->getTeacherClassroomIds($teacher);

        // Ensure we only reject pending students in this teacher's classrooms
        $count = User::whereIn('id', $request->ids)
            ->where('role', 'siswa')
            ->where('status', 'pending')
            ->whereIn('classroom_id', $teacherClassroomIds)
            ->update(['status' => 'rejected']);

        return redirect()->route('walikelas.pendaftar')->with('success', "$count pendaftaran siswa telah ditolak.");
    }

    /**
     * Wali Kelas profile overview
     */
    public function profile()
    {
        $teacher = Auth::user();
        return view('walikelas.profile', compact('teacher'));
    }

    /**
     * Wali Kelas profile update
     */
    public function updateProfile(Request $request)
    {
        $teacher = Auth::user();
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
        ], [
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password.min' => 'Password minimal 6 karakter.'
        ]);

        $teacher->name = $request->name;
        $teacher->phone = $request->phone;
        
        if ($request->filled('password')) {
            $teacher->password = \Illuminate\Support\Facades\Hash::make($request->password);
        }

        $teacher->save();

        return redirect()->route('walikelas.profile')->with('success', 'Profil Anda berhasil diubah.');
    }

    /**
     * Get specific student details including waste category breakdown and recent transaction history.
     */
    public function studentDetail($id)
    {
        try {
            $teacher = Auth::user();
            $teacherClassroomIds = $this->getTeacherClassroomIds($teacher);
            
            // Find student in teacher's classrooms
            $student = User::where('role', 'siswa')
                ->where('id', $id)
                ->whereIn('classroom_id', $teacherClassroomIds)
                ->firstOrFail();

            // Calculate weight per waste category for this student
            $categoriesBreakdown = \App\Models\WasteCategory::leftJoin('transactions', function ($join) use ($student) {
                    $join->on('waste_categories.id', '=', 'transactions.waste_category_id')
                        ->where('transactions.user_id', $student->id)
                        ->where('transactions.type', 'setor')
                        ->where('transactions.status', 'Berhasil');
                })
                ->selectRaw('waste_categories.name, COALESCE(SUM(transactions.weight), 0) as total_weight, COALESCE(SUM(transactions.amount), 0) as total_amount')
                ->groupBy('waste_categories.id', 'waste_categories.name')
                ->get();

            // Get recent transactions for this student
            $transactions = Transaction::with('wasteCategory')
                ->where('user_id', $student->id)
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            return response()->json([
                'student' => [
                    'name' => $student->name,
                    'nisn' => $student->nisn,
                    'email' => $student->email,
                    'phone' => $student->phone ?? '—',
                    'class' => $student->class,
                    'balance' => number_format($student->balance, 0, ',', '.'),
                    'points' => number_format($student->points, 0, ',', '.'),
                ],
                'categories' => $categoriesBreakdown,
                'transactions' => $transactions->map(function($t) {
                    return [
                        'date' => $t->created_at->format('d M Y H:i'),
                        'type' => $t->type === 'setor' ? 'Setor' : 'Tarik',
                        'category' => $t->wasteCategory ? $t->wasteCategory->name : 'Tarik Dana',
                        'weight' => $t->weight ? number_format($t->weight, 1, ',', '.') : null,
                        'amount' => number_format($t->amount, 0, ',', '.'),
                        'status' => $t->status,
                    ];
                })
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('WaliKelas studentDetail error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Gagal memuat data: ' . $e->getMessage()
            ], 500);
        }
    }
}

