<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Transaction;
use App\Models\Classroom;
use App\Models\WasteCategory;
use App\Exports\DynamicTransactionsExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\RegisterSiswaRequest;

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

    /**
     * Display Class-Wide dynamic report console (restricted to teacher's classrooms).
     */
    public function reports(Request $request)
    {
        $teacher = Auth::user();
        $classroomIds = $this->getTeacherClassroomIds($teacher);
        $myClassrooms = $teacher->classrooms()->orderBy('name')->get();

        $categories = WasteCategory::all();

        $query = Transaction::with(['student', 'wasteCategory', 'operator'])
            ->whereHas('student', function ($q) use ($classroomIds) {
                $q->whereIn('classroom_id', $classroomIds);
            })
            ->filter($request->all());

        $transactions = $query->orderBy('created_at', 'desc')->get();

        // Totals
        $totalWeight = $transactions->where('type', 'setor')->where('status', 'Berhasil')->sum('weight');
        $totalSetorAmount = $transactions->where('type', 'setor')->where('status', 'Berhasil')->sum('amount');
        $totalTarikAmount = $transactions->where('type', 'tarik')->where('status', 'Berhasil')->sum('amount');

        return view('walikelas.reports', compact(
            'teacher', 'transactions', 'categories', 'myClassrooms',
            'totalWeight', 'totalSetorAmount', 'totalTarikAmount'
        ));
    }

    /**
     * Export class reports to Excel (restricted).
     */
    public function exportExcel(Request $request)
    {
        $teacher = Auth::user();
        $classroomIds = $this->getTeacherClassroomIds($teacher);

        $query = Transaction::with(['student', 'wasteCategory'])
            ->whereHas('student', function ($q) use ($classroomIds) {
                $q->whereIn('classroom_id', $classroomIds);
            })
            ->filter($request->all())
            ->orderBy('created_at', 'desc');

        return Excel::download(new DynamicTransactionsExport($query), 'laporan_transaksi_kelas_'.date('Ymd').'.xlsx');
    }

    /**
     * Export class reports to PDF (restricted).
     */
    public function exportPdf(Request $request)
    {
        $teacher = Auth::user();
        $classroomIds = $this->getTeacherClassroomIds($teacher);

        $filters = $request->all();

        $query = Transaction::with(['student', 'wasteCategory'])
            ->whereHas('student', function ($q) use ($classroomIds) {
                $q->whereIn('classroom_id', $classroomIds);
            })
            ->filter($filters)
            ->orderBy('created_at', 'desc');

        $transactions = $query->get();

        $totalWeight = $transactions->where('type', 'setor')->where('status', 'Berhasil')->sum('weight');
        $totalSetorAmount = $transactions->where('type', 'setor')->where('status', 'Berhasil')->sum('amount');
        $totalTarikAmount = $transactions->where('type', 'tarik')->where('status', 'Berhasil')->sum('amount');

        $categoryName = null;
        if (!empty($filters['waste_category_id'])) {
            $cat = WasteCategory::find($filters['waste_category_id']);
            $categoryName = $cat ? $cat->name : null;
        }

        $pdf = Pdf::loadView('walikelas.pdf.reports', compact(
            'transactions', 'filters', 'totalWeight', 
            'totalSetorAmount', 'totalTarikAmount', 'categoryName'
        ));

        return $pdf->download('laporan_transaksi_kelas_'.date('Ymd').'.pdf');
    }

    /**
     * Show student registration form (manual & bulk)
     */
    public function showRegisterForm()
    {
        $teacher = Auth::user();
        $classrooms = Classroom::orderBy('name')->get();
        return view('walikelas.register', compact('teacher', 'classrooms'));
    }

    /**
     * Register a single student manually
     */
    public function registerSingleStudent(RegisterSiswaRequest $request)
    {
        $classroom_id = $request->classroom_id;
        $classStr = $request->class;
        
        if (!$classroom_id && $classStr) {
            $classroom = Classroom::firstOrCreate(['name' => trim($classStr)]);
            $classroom_id = $classroom->id;
        }

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'nisn' => $request->nisn,
            'classroom_id' => $classroom_id,
            'phone' => $request->phone,
            'role' => 'siswa',
            'password' => \Illuminate\Support\Facades\Hash::make($request->input('password') ?: 'password'),
            'balance' => 0,
            'points' => 0
        ]);

        return back()->with('success', 'Nasabah baru (Siswa) atas nama "' . $request->name . '" berhasil terdaftar!');
    }

    /**
     * Register multiple students using a bulk CSV upload
     */
    public function registerBulkStudents(Request $request)
    {
        if (!$request->hasFile('file') || !$request->file('file')->isValid()) {
            return back()->with('error', 'Silakan unggah file CSV yang valid.');
        }

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());
        if ($extension !== 'csv') {
            return back()->with('error', 'Format file harus berupa CSV (.csv).');
        }

        $path = $file->getRealPath();
        
        // Detect delimiter: comma (,) or semicolon (;)
        $firstLineHandle = fopen($path, 'r');
        $firstLine = fgets($firstLineHandle);
        fclose($firstLineHandle);
        
        $delimiter = ',';
        if ($firstLine !== false) {
            $semicolonCount = substr_count($firstLine, ';');
            $commaCount = substr_count($firstLine, ',');
            if ($semicolonCount > $commaCount) {
                $delimiter = ';';
            }
        }

        $handle = fopen($path, 'r');
        $headers = fgetcsv($handle, 1000, $delimiter);

        if (!$headers) {
            fclose($handle);
            return back()->with('error', 'File CSV kosong atau tidak dapat dibaca.');
        }

        // Normalize header names: remove BOM, non-printable characters, trim and lowercase
        $headers = array_map(function($h) {
            // Remove UTF-8 BOM if present
            $h = str_replace("\xEF\xBB\xBF", "", $h);
            // Remove other potential BOMs or special/invisible/non-ascii characters
            $h = preg_replace('/[\x00-\x1F\x7F-\x9F\xE2\x80\x8B-\xE2\x80\x8D]/', '', $h);
            return trim(strtolower($h));
        }, $headers);

        // Map headers to key indices
        $nameIdx = -1;
        $emailIdx = -1;
        $nisnIdx = -1;
        $classIdx = -1;
        $phoneIdx = -1;

        foreach ($headers as $index => $header) {
            if (in_array($header, ['nama', 'name', 'nama lengkap'])) {
                $nameIdx = $index;
            } elseif (in_array($header, ['email', 'alamat email'])) {
                $emailIdx = $index;
            } elseif (in_array($header, ['nisn', 'id', 'nis'])) {
                $nisnIdx = $index;
            } elseif (in_array($header, ['kelas', 'class'])) {
                $classIdx = $index;
            } elseif (in_array($header, ['telepon', 'phone', 'wa', 'whatsapp', 'no hp', 'no telp'])) {
                $phoneIdx = $index;
            }
        }

        // Validate that required columns are mapped
        if ($nameIdx === -1 || $emailIdx === -1 || $nisnIdx === -1 || $classIdx === -1) {
            fclose($handle);
            return back()->with('error', 'Format kolom CSV salah. Pastikan memiliki kolom header: Nama, Email, NISN, Kelas (kolom Telepon opsional).');
        }

        $successCount = 0;
        $skipCount = 0;
        $skipLogs = [];
        $rowNum = 1; // Row 1 is header

        while (($row = fgetcsv($handle, 1000, $delimiter)) !== false) {
            $rowNum++;

            // Skip empty rows
            if (count($row) === 0 || (count($row) === 1 && empty($row[0]))) {
                continue;
            }

            $name = isset($row[$nameIdx]) ? trim($row[$nameIdx]) : '';
            $email = isset($row[$emailIdx]) ? trim($row[$emailIdx]) : '';
            $nisn = isset($row[$nisnIdx]) ? trim($row[$nisnIdx]) : '';
            $class = isset($row[$classIdx]) ? trim($row[$classIdx]) : '';
            $phone = ($phoneIdx !== -1 && isset($row[$phoneIdx]) && trim($row[$phoneIdx]) !== '') ? trim($row[$phoneIdx]) : null;

            // Row cell validations
            if (empty($name) || empty($email) || empty($nisn) || empty($class)) {
                $skipCount++;
                $skipLogs[] = "Baris {$rowNum}: Data tidak lengkap (Kolom Nama, Email, NISN, dan Kelas harus diisi).";
                continue;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $skipCount++;
                $skipLogs[] = "Baris {$rowNum} ({$name}): Format email '{$email}' tidak valid.";
                continue;
            }

            // Check duplicate NISN
            if (User::where('nisn', $nisn)->exists()) {
                $skipCount++;
                $skipLogs[] = "Baris {$rowNum} ({$name}): NISN '{$nisn}' sudah terdaftar.";
                continue;
            }

            // Check duplicate Email
            if (User::where('email', $email)->exists()) {
                $skipCount++;
                $skipLogs[] = "Baris {$rowNum} ({$name}): Email '{$email}' sudah terdaftar.";
                continue;
            }

            // Create student user
            User::create([
                'name' => $name,
                'email' => $email,
                'nisn' => $nisn,
                'class' => $class,
                'phone' => $phone,
                'role' => 'siswa',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'balance' => 0,
                'points' => 0
            ]);

            $successCount++;
        }
        fclose($handle);

        $message = "Pendaftaran masal selesai! {$successCount} siswa berhasil didaftarkan.";
        if ($skipCount > 0) {
            $message .= " {$skipCount} data baris dilewati karena duplikat atau tidak valid.";
        }

        return back()
            ->with('success', $message)
            ->with('bulk_success_count', $successCount)
            ->with('bulk_skip_count', $skipCount)
            ->with('bulk_skip_logs', $skipLogs);
    }
}

