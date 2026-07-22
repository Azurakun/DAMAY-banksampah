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
     * Store trash deposit transaction (multi-category batch)
     */
    public function storeSetor(StoreSetoranRequest $request, $id)
    {
        $student  = User::findOrFail($id);
        $operator = Auth::user();

        $items           = $request->input('items', []);
        $totalAmount     = 0;
        $totalPoints     = 0;
        $transactionIds  = [];

        foreach ($items as $item) {
            $category = WasteCategory::findOrFail($item['waste_category_id']);
            $weight   = (float) $item['weight'];

            $amount = round($weight * $category->price_per_kg);
            $points = round($weight * $category->points_per_kg);

            $tx = Transaction::create([
                'user_id'          => $student->id,
                'operator_id'      => $operator->id,
                'type'             => 'setor',
                'waste_category_id'=> $category->id,
                'weight'           => $weight,
                'amount'           => $amount,
                'points'           => $points,
                'status'           => 'Berhasil',
                'note'             => $request->note ?? 'Setoran ' . $category->name,
            ]);

            $transactionIds[] = $tx->id;
            $totalAmount      += $amount;
            $totalPoints      += $points;
        }

        // Update student balance and points once
        $student->balance       += $totalAmount;
        $student->points        += $totalPoints;
        $student->weekly_points += $totalPoints;
        $student->save();

        // Redirect to batch confirmation using comma-joined IDs in query string
        return redirect()
            ->route('operator.confirm.batch', ['ids' => implode(',', $transactionIds)])
            ->with('success', 'Setoran sampah berhasil disimpan!');
    }

    /**
     * Show single setoran receipt / confirmation slip
     */
    public function confirmSetor($id)
    {
        $transaction = Transaction::with(['student', 'wasteCategory', 'operator'])->findOrFail($id);
        return view('operator.confirm', compact('transaction'));
    }

    /**
     * Show batch setoran confirmation (multiple categories in one session)
     */
    public function confirmBatch(Request $request)
    {
        $ids = array_filter(explode(',', $request->query('ids', '')));
        $transactions = Transaction::with(['student', 'wasteCategory', 'operator'])
            ->whereIn('id', $ids)
            ->get();

        if ($transactions->isEmpty()) {
            return redirect()->route('operator.dashboard');
        }

        $student     = $transactions->first()->student;
        $totalAmount = $transactions->sum('amount');
        $totalPoints = $transactions->sum('points');
        $totalWeight = $transactions->sum('weight');

        return view('operator.confirm', compact('transactions', 'student', 'totalAmount', 'totalPoints', 'totalWeight'));
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
     * Show printable receipt for a specific transaction
     */
    public function transactionReceipt($id)
    {
        $transaction = Transaction::with(['student', 'wasteCategory', 'operator'])
            ->findOrFail($id);

        $backUrl = route('operator.history');
        return view('shared.receipt', compact('transaction', 'backUrl'));
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

}
