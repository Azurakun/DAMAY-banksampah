<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Events\TransactionCreated;

class SiswaController extends Controller
{
    /**
     * Display student portal dashboard
     */
    public function dashboard()
    {
        $user = Auth::user();
        
        // Sum total recycled weight
        $totalWeight = Transaction::where('user_id', $user->id)
            ->where('type', 'setor')
            ->where('status', 'Berhasil')
            ->sum('weight');

        // Sum total cash earned (setor)
        $totalEarned = Transaction::where('user_id', $user->id)
            ->where('type', 'setor')
            ->where('status', 'Berhasil')
            ->sum('amount');

        // Get latest 3 transactions
        $recentTransactions = Transaction::where('user_id', $user->id)
            ->with('wasteCategory')
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get();

        // Calculate progress towards personal target (e.g. 50 kg)
        $targetWeight = 50.00;
        $progressPercent = min(100, round(($totalWeight / $targetWeight) * 100));

        return view('siswa.dashboard', compact('user', 'totalWeight', 'totalEarned', 'recentTransactions', 'progressPercent', 'targetWeight'));
    }

    /**
     * Display student transaction ledger
     */
    public function history()
    {
        $user = Auth::user();
        
        $transactions = Transaction::where('user_id', $user->id)
            ->with(['wasteCategory', 'operator'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('siswa.history', compact('transactions'));
    }

    /**
     * Display gamification leaderboard ranking
     */
    public function leaderboard()
    {
        $user = Auth::user();

        // Optimasi: Cache hasil query leaderboard selama 10 menit dan hanya ambil kolom yang diperlukan
        $students = Cache::remember('leaderboard_students', 600, function () {
            return User::where('role', 'siswa')
                ->orderBy('points', 'desc')
                ->get(['id', 'name', 'points', 'class', 'avatar']);
        });

        // Find my rank
        $myRank = 1;
        foreach ($students as $index => $student) {
            if ($student->id === $user->id) {
                $myRank = $index + 1;
                break;
            }
        }

        return view('siswa.leaderboard', compact('students', 'myRank'));
    }

    /**
     * Show cash withdrawal request form
     */
    public function showWithdrawForm()
    {
        $user = Auth::user();
        return view('siswa.withdraw', compact('user'));
    }

    /**
     * Store new withdrawal request
     */
    public function requestWithdraw(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'amount' => ['required', 'integer', 'min:5000', 'max:' . $user->balance],
            'note' => ['nullable', 'string', 'max:255']
        ], [
            'amount.max' => 'Saldo Anda tidak mencukupi untuk melakukan penarikan jumlah ini.',
            'amount.min' => 'Batas minimum penarikan adalah Rp 5.000.'
        ]);

        // Find any available operator to assign (or fallback to user id, we default to first operator in db)
        $operator = User::where('role', 'operator')->first();
        $operatorId = $operator ? $operator->id : $user->id;

        // Create transaction of type 'tarik'
        $transaction = Transaction::create([
            'user_id' => $user->id,
            'operator_id' => $operatorId,
            'type' => 'tarik',
            'amount' => $request->amount,
            'points' => 0,
            'status' => 'Menunggu', // Pending operator confirmation
            'note' => $request->note ?? 'Penarikan dana oleh siswa'
        ]);

        // Dispatch real-time event to notify admin
        broadcast(new TransactionCreated($transaction, $user->name . ' mengajukan penarikan dana sebesar Rp ' . number_format($request->amount, 0, ',', '.')))->toOthers();

        // Note: We don't deduct balance yet. The balance is only deducted when the operator approves (status -> Berhasil).

        return redirect()->route('siswa.dashboard')->with('success', 'Pengajuan penarikan dana sebesar Rp ' . number_format($request->amount, 0, ',', '.') . ' telah dikirim dan sedang menunggu konfirmasi operator.');
    }

    /**
     * Show student profile edit page
     */
    public function profile()
    {
        $user = Auth::user();
        $totalWeight = Transaction::where('user_id', $user->id)
            ->where('type', 'setor')
            ->where('status', 'Berhasil')
            ->sum('weight');

        // Aggregate monthly deposits (last 6 months) for Chart.js
        $chartData = Transaction::where('user_id', $user->id)
            ->where('type', 'setor')
            ->where('status', 'Berhasil')
            ->where('created_at', '>=', now()->subMonths(5)->startOfMonth())
            ->selectRaw('strftime("%m-%Y", created_at) as month, sum(amount) as total')
            ->groupBy('month')
            ->orderBy('created_at')
            ->get();

        $labels = [];
        $data = [];
        // Fill empty months
        for ($i = 5; $i >= 0; $i--) {
            $monthStr = now()->subMonths($i)->format('m-Y');
            $labels[] = now()->subMonths($i)->format('M Y');
            $match = $chartData->firstWhere('month', $monthStr);
            $data[] = $match ? $match->total : 0;
        }

        return view('siswa.profile', compact('user', 'totalWeight', 'labels', 'data'));
    }

    /**
     * Update student profile (name, phone, and avatar upload)
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048']
        ]);

        // Handle Avatar File Upload
        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $filename = 'avatar_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            
            // Ensure destination directory exists
            $destinationPath = public_path('uploads/avatars');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }

            // Delete old avatar if it exists
            if ($user->avatar && file_exists(public_path($user->avatar))) {
                @unlink(public_path($user->avatar));
            }

            // Move file
            $file->move($destinationPath, $filename);
            $user->avatar = '/uploads/avatars/' . $filename;
        }

        $user->name = $request->name;
        $user->phone = $request->phone;
        $user->save();

        return redirect()->route('siswa.profile')->with('success', 'Profil Anda telah berhasil diperbarui!');
    }
}
