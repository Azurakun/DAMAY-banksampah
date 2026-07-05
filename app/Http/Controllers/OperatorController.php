<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\WasteCategory;
use App\Models\Transaction;
use App\Models\Classroom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreSetoranRequest;
use App\Http\Requests\RegisterSiswaRequest;
use App\Exports\TransactionsExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class OperatorController extends Controller
{
    /**
     * Display operator console
     */
    public function dashboard()
    {
        $operator = Auth::user();
        
        // General stats for operator today
        $todayDepositsCount = Transaction::whereDate('created_at', today())
            ->where('type', 'setor')
            ->where('status', 'Berhasil')
            ->count();

        $todayWeight = Transaction::whereDate('created_at', today())
            ->where('type', 'setor')
            ->where('status', 'Berhasil')
            ->sum('weight');

        $pendingTarik = Transaction::where('type', 'tarik')
            ->where('status', 'Menunggu')
            ->with('student')
            ->orderBy('created_at', 'asc')
            ->get();

        $totalStudents = User::where('role', 'siswa')->count();

        return view('operator.dashboard', compact('operator', 'todayDepositsCount', 'todayWeight', 'pendingTarik', 'totalStudents'));
    }

    /**
     * Search student by NISN or Name (AJAX Endpoint)
     */
    public function searchStudents(Request $request)
    {
        $query = $request->input('query');
        
        if (empty($query)) {
            return response()->json([]);
        }

        $students = User::where('role', 'siswa')
            ->where(function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('nisn', 'like', "%{$query}%");
            })
            ->take(5)
            ->get(['id', 'name', 'nisn', 'class']);

        return response()->json($students);
    }

    /**
     * Show deposit form for student
     */
    public function showSetorForm($id)
    {
        $student = User::findOrFail($id);
        $categories = WasteCategory::all();
        
        return view('operator.setor', compact('student', 'categories'));
    }

    /**
     * Store trash deposit transaction
     */
    public function storeSetor(StoreSetoranRequest $request, $id)
    {
        $student = User::findOrFail($id);
        $operator = Auth::user();

        $category = WasteCategory::findOrFail($request->waste_category_id);

        // Calculate amount and points based on category specs
        $amount = round($request->weight * $category->price_per_kg);
        $points = round($request->weight * $category->points_per_kg);

        // Create transaction
        $transaction = Transaction::create([
            'user_id' => $student->id,
            'operator_id' => $operator->id,
            'type' => 'setor',
            'waste_category_id' => $category->id,
            'weight' => $request->weight,
            'amount' => $amount,
            'points' => $points,
            'status' => 'Berhasil',
            'note' => $request->note ?? 'Setoran ' . $category->name
        ]);

        // Update student balance and points
        $student->balance += $amount;
        $student->points += $points;
        $student->weekly_points += $points;
        $student->save();

        return redirect()->route('operator.confirm', $transaction->id)->with('success', 'Setoran sampah berhasil disimpan!');
    }

    /**
     * Show setoran receipt / confirmation slip
     */
    public function confirmSetor($id)
    {
        $transaction = Transaction::with(['student', 'wasteCategory', 'operator'])->findOrFail($id);
        return view('operator.confirm', compact('transaction'));
    }

    /**
     * Display all transactions processed by operator
     */
    public function history()
    {
        $operator = Auth::user();
        
        $transactions = Transaction::where('operator_id', $operator->id)
            ->with(['student', 'wasteCategory'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('operator.history', compact('transactions'));
    }

    /**
     * Export history to Excel
     */
    public function exportExcel()
    {
        return Excel::download(new TransactionsExport, 'riwayat_transaksi_'.date('Ymd').'.xlsx');
    }

    /**
     * Export history to PDF
     */
    public function exportPdf()
    {
        // For PDF, we usually render a view, but we can also use DomPDF to generate it directly
        // We'll create a simple PDF from the same data
        $transactions = Transaction::with(['student', 'wasteCategory'])->orderBy('created_at', 'desc')->get();
        
        $pdf = Pdf::loadView('operator.pdf.transactions', compact('transactions'));
        return $pdf->download('riwayat_transaksi_'.date('Ymd').'.pdf');
    }

    /**
     * Operator profile overview
     */
    public function profile()
    {
        $operator = Auth::user();
        return view('operator.profile', compact('operator'));
    }

    /**
     * Operator profile update
     */
    public function updateProfile(Request $request)
    {
        $operator = Auth::user();
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
        ], [
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password.min' => 'Password minimal 6 karakter.'
        ]);

        $operator->name = $request->name;
        $operator->phone = $request->phone;
        
        if ($request->filled('password')) {
            $operator->password = \Illuminate\Support\Facades\Hash::make($request->password);
        }

        $operator->save();

        return redirect()->route('operator.profile')->with('success', 'Profil Anda berhasil diubah.');
    }


    /**
     * Approve student cash out request
     */
    public function approveTarik($id)
    {
        $transaction = Transaction::findOrFail($id);
        $student = User::findOrFail($transaction->user_id);
        $operator = Auth::user();

        if ($student->balance < $transaction->amount) {
            $transaction->status = 'Batal';
            $transaction->note = 'Dibatalkan oleh sistem: Saldo siswa tidak mencukupi.';
            $transaction->save();
            return back()->with('error', 'Penarikan gagal: Saldo siswa tidak mencukupi.');
        }

        // Deduct balance and approve
        $student->balance -= $transaction->amount;
        $student->save();

        $transaction->status = 'Berhasil';
        $transaction->operator_id = $operator->id;
        $transaction->save();

        return back()->with('success', 'Penarikan dana sebesar Rp ' . number_format($transaction->amount, 0, ',', '.') . ' untuk ' . $student->name . ' telah disetujui.');
    }

    /**
     * Cancel student cash out request
     */
    public function cancelTarik($id)
    {
        $transaction = Transaction::findOrFail($id);
        $operator = Auth::user();

        $transaction->status = 'Batal';
        $transaction->operator_id = $operator->id;
        $transaction->note = 'Pengajuan ditolak oleh operator';
        $transaction->save();

        return back()->with('success', 'Pengajuan penarikan dana telah ditolak.');
    }

    /**
     * Show student registration form (manual & bulk)
     */
    public function showRegisterForm()
    {
        $operator = Auth::user();
        $classrooms = Classroom::orderBy('name')->get();
        return view('operator.register', compact('operator', 'classrooms'));
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
        $firstLine = fgets(fopen($path, 'r'));
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

        // Normalize header names to lowercase and trim spaces
        $headers = array_map(function($h) {
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
