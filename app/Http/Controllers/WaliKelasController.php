<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WaliKelasController extends Controller
{
    /**
     * Display homeroom class dashboard with optional class/angkatan filter
     */
    public function dashboard(Request $request)
    {
        $teacher = Auth::user();

        // Get all distinct classes from students for filter dropdown
        $allClasses = User::where('role', 'siswa')
            ->select('class')
            ->distinct()
            ->orderBy('class')
            ->pluck('class')
            ->filter()
            ->values();

        // Derive all available angkatan (e.g. "XII", "XI", "X") from class names
        $allAngkatan = $allClasses->map(function ($cls) {
            $parts = explode(' ', trim($cls));
            return strtoupper($parts[0]); // "XII RPL 1" -> "XII"
        })->unique()->sort()->values();

        // Resolve selected filters (default to teacher's own class)
        $selectedClass    = $request->input('kelas', $teacher->class ?? null);
        $selectedAngkatan = $request->input('angkatan', null);

        // Build base query for students
        $studentsQuery = User::where('role', 'siswa')->orderBy('points', 'desc');

        if ($selectedAngkatan) {
            // Filter by angkatan prefix (e.g. 'XII' matches 'XII RPL 1', 'XII TKJ 2', etc.)
            $studentsQuery->where('class', 'like', $selectedAngkatan . ' %');
            $className = 'Angkatan ' . $selectedAngkatan;
        } elseif ($selectedClass) {
            $studentsQuery->where('class', $selectedClass);
            $className = $selectedClass;
        } else {
            $className = 'Semua Kelas';
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
        $className = $teacher->class;

        // Pending students in the teacher's class
        $pendingStudents = User::where('role', 'siswa')
            ->where('status', 'pending')
            ->where('class', $className)
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

        // Ensure we only approve pending students in this teacher's class
        $count = User::whereIn('id', $request->ids)
            ->where('role', 'siswa')
            ->where('status', 'pending')
            ->where('class', $teacher->class)
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

        // Ensure we only reject pending students in this teacher's class
        $count = User::whereIn('id', $request->ids)
            ->where('role', 'siswa')
            ->where('status', 'pending')
            ->where('class', $teacher->class)
            ->update(['status' => 'rejected']);

        return redirect()->route('walikelas.pendaftar')->with('success', "$count pendaftaran siswa telah ditolak.");
    }
}
