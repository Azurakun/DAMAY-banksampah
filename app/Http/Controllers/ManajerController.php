<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Transaction;
use App\Models\Classroom;
use App\Models\Setting;
use App\Models\WasteCategory;
use App\Models\Distribution;
use App\Exports\DynamicTransactionsExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
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

        // 6-Month Inflow/Outflow Chart Data (Weight and Value)
        $labels = [];
        $inflowWeightData = [];
        $outflowWeightData = [];
        $inflowValueData = [];
        $outflowValueData = [];

        for ($i = 5; $i >= 0; $i--) {
            $monthDate = now()->subMonths($i);
            $labels[] = $monthDate->translatedFormat('M Y');
            
            // Inflow (Setor)
            $inflowW = Transaction::where('type', 'setor')
                ->where('status', 'Berhasil')
                ->whereYear('created_at', $monthDate->year)
                ->whereMonth('created_at', $monthDate->month)
                ->sum('weight');
            $inflowWeightData[] = (float)$inflowW;

            $inflowV = Transaction::where('type', 'setor')
                ->where('status', 'Berhasil')
                ->whereYear('created_at', $monthDate->year)
                ->whereMonth('created_at', $monthDate->month)
                ->sum('amount');
            $inflowValueData[] = (int)$inflowV;

            // Outflow (Distribusi)
            $outflowW = Distribution::whereYear('batch_date', $monthDate->year)
                ->whereMonth('batch_date', $monthDate->month)
                ->sum('total_weight');
            $outflowWeightData[] = (float)$outflowW;

            $outflowV = Distribution::whereYear('batch_date', $monthDate->year)
                ->whereMonth('batch_date', $monthDate->month)
                ->sum('total_value');
            $outflowValueData[] = (int)$outflowV;
        }

        // Warehouse Stock Overview
        $warehouseStock = WasteCategory::all()->map(function($category) {
            $totalSetor = Transaction::where('waste_category_id', $category->id)
                ->where('type', 'setor')
                ->where('status', 'Berhasil')
                ->sum('weight');

            $totalDistributed = \App\Models\DistributionItem::where('waste_category_id', $category->id)
                ->sum('weight');

            $category->total_setor = $totalSetor;
            $category->total_distributed = $totalDistributed;
            $category->available_stock = max(0.00, (float)($totalSetor - $totalDistributed));
            return $category;
        });

        return view('manajer.dashboard', compact(
            'manager', 'totalStudents', 'totalWeightRecycled', 
            'totalSchoolBalance', 'totalSchoolPoints', 'totalTransactionsCount', 
            'allRecentTransactions', 'classPerformance', 'topStudents',
            'labels', 'inflowWeightData', 'outflowWeightData', 'inflowValueData', 'outflowValueData',
            'warehouseStock'
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

    /**
     * Display School-Wide dynamic report console.
     */
    public function reports(Request $request)
    {
        $manager = Auth::user();
        $categories = WasteCategory::all();
        $classrooms = Classroom::orderBy('name')->get();

        $query = Transaction::with(['student', 'wasteCategory', 'operator'])->filter($request->all());
        $transactions = $query->orderBy('created_at', 'desc')->get();

        // Totals
        $totalWeight = $transactions->where('type', 'setor')->where('status', 'Berhasil')->sum('weight');
        $totalSetorAmount = $transactions->where('type', 'setor')->where('status', 'Berhasil')->sum('amount');
        $totalTarikAmount = $transactions->where('type', 'tarik')->where('status', 'Berhasil')->sum('amount');

        return view('manajer.reports', compact(
            'manager', 'transactions', 'categories', 'classrooms', 
            'totalWeight', 'totalSetorAmount', 'totalTarikAmount'
        ));
    }

    /**
     * Export school-wide reports to Excel.
     */
    public function exportExcel(Request $request)
    {
        $query = Transaction::with(['student', 'wasteCategory'])->filter($request->all())->orderBy('created_at', 'desc');
        return Excel::download(new DynamicTransactionsExport($query), 'laporan_transaksi_sekolah_'.date('Ymd').'.xlsx');
    }

    /**
     * Export school-wide reports to PDF.
     */
    public function exportPdf(Request $request)
    {
        $filters = $request->all();
        $query = Transaction::with(['student', 'wasteCategory'])->filter($filters)->orderBy('created_at', 'desc');
        $transactions = $query->get();

        $totalWeight = $transactions->where('type', 'setor')->where('status', 'Berhasil')->sum('weight');
        $totalSetorAmount = $transactions->where('type', 'setor')->where('status', 'Berhasil')->sum('amount');
        $totalTarikAmount = $transactions->where('type', 'tarik')->where('status', 'Berhasil')->sum('amount');

        $categoryName = null;
        if (!empty($filters['waste_category_id'])) {
            $cat = WasteCategory::find($filters['waste_category_id']);
            $categoryName = $cat ? $cat->name : null;
        }

        $pdf = Pdf::loadView('manajer.pdf.reports', compact(
            'transactions', 'filters', 'totalWeight', 
            'totalSetorAmount', 'totalTarikAmount', 'categoryName'
        ));

        return $pdf->download('laporan_transaksi_sekolah_'.date('Ymd').'.pdf');
    }

    /**
     * List all waste categories for pricing management.
     */
    public function indexCategories()
    {
        $manager = Auth::user();
        $categories = WasteCategory::all();
        return view('manajer.waste_categories.index', compact('manager', 'categories'));
    }

    /**
     * Show form to create new waste category.
     */
    public function createCategory()
    {
        $manager = Auth::user();
        return view('manajer.waste_categories.create', compact('manager'));
    }

    /**
     * Store new waste category.
     */
    public function storeCategory(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'key' => ['required', 'string', 'max:50', 'unique:waste_categories,key'],
            'price_per_kg' => ['required', 'integer', 'min:0'],
            'points_per_kg' => ['required', 'integer', 'min:0'],
            'icon_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp,svg', 'max:2048'],
            'icon' => ['required_without:icon_image', 'nullable', 'string', 'max:50'],
        ], [
            'key.unique' => 'Slug/Key ini sudah digunakan oleh kategori lain.',
            'icon.required_without' => 'Ikon berupa emoji/teks wajib diisi jika Anda tidak mengunggah berkas gambar.',
        ]);

        $icon = $request->icon;

        if ($request->hasFile('icon_image')) {
            $file = $request->file('icon_image');
            $filename = 'cat_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $destinationPath = public_path('uploads/categories');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }
            $file->move($destinationPath, $filename);
            $icon = '/uploads/categories/' . $filename;
        }

        WasteCategory::create([
            'name' => $request->name,
            'key' => strtolower(trim($request->key)),
            'price_per_kg' => $request->price_per_kg,
            'points_per_kg' => $request->points_per_kg,
            'icon' => $icon,
        ]);

        return redirect()->route('manajer.categories.index')->with('success', 'Kategori sampah baru berhasil ditambahkan.');
    }

    /**
     * Show form to edit waste category.
     */
    public function editCategory($id)
    {
        $manager = Auth::user();
        $category = WasteCategory::findOrFail($id);
        return view('manajer.waste_categories.edit', compact('manager', 'category'));
    }

    /**
     * Update waste category.
     */
    public function updateCategory(Request $request, $id)
    {
        $category = WasteCategory::findOrFail($id);

        $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'key' => ['required', 'string', 'max:50', 'unique:waste_categories,key,' . $id],
            'price_per_kg' => ['required', 'integer', 'min:0'],
            'points_per_kg' => ['required', 'integer', 'min:0'],
            'icon_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp,svg', 'max:2048'],
            'icon' => ['nullable', 'string', 'max:50'],
        ], [
            'key.unique' => 'Slug/Key ini sudah digunakan oleh kategori lain.',
        ]);

        $icon = $request->filled('icon') ? $request->icon : $category->icon;

        if ($request->hasFile('icon_image')) {
            $file = $request->file('icon_image');
            $filename = 'cat_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $destinationPath = public_path('uploads/categories');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }
            $file->move($destinationPath, $filename);

            // Delete old image if it was an uploaded file
            if ($category->icon && str_starts_with($category->icon, '/uploads/') && file_exists(public_path($category->icon))) {
                @unlink(public_path($category->icon));
            }

            $icon = '/uploads/categories/' . $filename;
        }

        $category->update([
            'name' => $request->name,
            'key' => strtolower(trim($request->key)),
            'price_per_kg' => $request->price_per_kg,
            'points_per_kg' => $request->points_per_kg,
            'icon' => $icon,
        ]);

        return redirect()->route('manajer.categories.index')->with('success', 'Kategori sampah berhasil diperbarui.');
    }

    /**
     * Delete waste category.
     */
    public function destroyCategory($id)
    {
        $category = WasteCategory::findOrFail($id);
        
        // Check if there are active transactions utilizing this category
        if ($category->transactions()->count() > 0) {
            return redirect()->route('manajer.categories.index')->with('error', 'Kategori tidak dapat dihapus karena sudah memiliki riwayat transaksi setoran.');
        }

        $category->delete();
        return redirect()->route('manajer.categories.index')->with('success', 'Kategori sampah berhasil dihapus.');
    }

    /**
     * Show printable receipt for any transaction (manager view)
     */
    public function transactionReceipt($id)
    {
        $transaction = Transaction::with(['student', 'wasteCategory', 'operator'])
            ->findOrFail($id);

        $backUrl = route('manajer.dashboard');
        return view('shared.receipt', compact('transaction', 'backUrl'));
    }

    /**
     * Show detailed Warehouse Stock Inventory page.
     */
    public function stokDetail(Request $request)
    {
        $search = $request->input('search');

        $categories = WasteCategory::when($search, function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            })
            ->get()
            ->map(function ($category) {
                // Total Setor
                $totalSetor = Transaction::where('waste_category_id', $category->id)
                    ->where('type', 'setor')
                    ->where('status', 'Berhasil')
                    ->sum('weight');

                $totalSetorValue = Transaction::where('waste_category_id', $category->id)
                    ->where('type', 'setor')
                    ->where('status', 'Berhasil')
                    ->sum('amount');

                // Total Distributed
                $totalDistributed = \App\Models\DistributionItem::where('waste_category_id', $category->id)
                    ->sum('weight');

                $totalDistributedValue = \App\Models\DistributionItem::where('waste_category_id', $category->id)
                    ->sum('value');

                $category->total_setor = (float)$totalSetor;
                $category->total_setor_value = (int)$totalSetorValue;
                $category->total_distributed = (float)$totalDistributed;
                $category->total_distributed_value = (int)$totalDistributedValue;
                $category->available_stock = max(0.00, (float)($totalSetor - $totalDistributed));
                $category->estimated_value = round($category->available_stock * $category->price_per_kg);
                
                // Count transaction volume
                $category->transactions_count = Transaction::where('waste_category_id', $category->id)
                    ->where('status', 'Berhasil')
                    ->count();

                return $category;
            });

        return view('manajer.stok_detail', compact('categories', 'search'));
    }

    /**
     * Show detailed stock history and transaction list for a specific waste category.
     */
    public function stokCategoryDetail($id, Request $request)
    {
        $category = WasteCategory::findOrFail($id);

        // Calculate current stock stats for this specific category
        $totalSetor = Transaction::where('waste_category_id', $category->id)
            ->where('type', 'setor')
            ->where('status', 'Berhasil')
            ->sum('weight');

        $totalSetorValue = Transaction::where('waste_category_id', $category->id)
            ->where('type', 'setor')
            ->where('status', 'Berhasil')
            ->sum('amount');

        $totalDistributed = \App\Models\DistributionItem::where('waste_category_id', $category->id)
            ->sum('weight');

        $totalDistributedValue = \App\Models\DistributionItem::where('waste_category_id', $category->id)
            ->sum('value');

        $availableStock = max(0.00, (float)($totalSetor - $totalDistributed));
        $estimatedValue = round($availableStock * $category->price_per_kg);

        // Load paginated list of successful deposit transactions (siswa setoran)
        $deposits = Transaction::where('waste_category_id', $category->id)
            ->where('type', 'setor')
            ->with(['student', 'operator'])
            ->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'deposits_page')
            ->withQueryString();

        // Load paginated list of distributions (barang keluar)
        $distributions = \App\Models\DistributionItem::where('waste_category_id', $category->id)
            ->with(['distribution.creator'])
            ->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'dist_page')
            ->withQueryString();

        return view('manajer.stok_category_detail', compact(
            'category', 'totalSetor', 'totalSetorValue', 'totalDistributed', 
            'totalDistributedValue', 'availableStock', 'estimatedValue', 
            'deposits', 'distributions'
        ));
    }

    /**
     * Show detailed Classroom Performance page.
     */
    public function performaKelasDetail(Request $request)
    {
        $sort = $request->input('sort', 'points');

        $query = User::where('role', 'siswa')
            ->selectRaw('class, classroom_id, COUNT(id) as student_count, SUM(balance) as total_balance, SUM(points) as total_points');

        $classes = $query->groupBy('class', 'classroom_id')->get()->map(function($classItem) {
            // Calculate total weight recycled by students of this class
            $classItem->total_weight = Transaction::where('type', 'setor')
                ->where('status', 'Berhasil')
                ->whereHas('student', function($q) use ($classItem) {
                    $q->where('class', $classItem->class);
                })
                ->sum('weight');

            // Calculate average per student
            $classItem->avg_balance = $classItem->student_count > 0 ? ($classItem->total_balance / $classItem->student_count) : 0;
            $classItem->avg_weight = $classItem->student_count > 0 ? ($classItem->total_weight / $classItem->student_count) : 0;
            
            // Find most active waste category for this class
            $topCategory = Transaction::where('type', 'setor')
                ->where('status', 'Berhasil')
                ->whereHas('student', function($q) use ($classItem) {
                    $q->where('class', $classItem->class);
                })
                ->selectRaw('waste_category_id, SUM(weight) as total_cat_weight')
                ->groupBy('waste_category_id')
                ->orderBy('total_cat_weight', 'desc')
                ->first();

            $classItem->top_category_name = $topCategory && $topCategory->wasteCategory ? $topCategory->wasteCategory->name : 'N/A';

            return $classItem;
        });

        // Apply sorting
        if ($sort === 'weight') {
            $classes = $classes->sortByDesc('total_weight')->values();
        } elseif ($sort === 'balance') {
            $classes = $classes->sortByDesc('total_balance')->values();
        } elseif ($sort === 'students') {
            $classes = $classes->sortByDesc('student_count')->values();
        } else {
            $classes = $classes->sortByDesc('total_points')->values();
        }

        return view('manajer.performa_kelas_detail', compact('classes', 'sort'));
    }

    /**
     * Show detailed Transaction Logs page with pagination and filters.
     */
    public function logTransaksiDetail(Request $request)
    {
        $filters = [
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'waste_category_id' => $request->input('waste_category_id'),
            'class' => $request->input('class'),
            'type' => $request->input('type'),
            'status' => $request->input('status'),
            'search' => $request->input('search'),
        ];

        $query = Transaction::with(['student', 'wasteCategory', 'operator'])
            ->orderBy('created_at', 'desc');

        // Apply scope filters
        $query->filter($filters);

        // Text search
        if ($filters['search']) {
            $search = $filters['search'];
            $query->whereHas('student', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%");
            });
        }

        // Summary stats on filtered query
        $statsQuery = clone $query;
        $filteredWeight = $statsQuery->where('type', 'setor')->where('status', 'Berhasil')->sum('weight');
        
        $statsQuery2 = clone $query;
        $filteredSetorAmount = $statsQuery2->where('type', 'setor')->where('status', 'Berhasil')->sum('amount');
        
        $statsQuery3 = clone $query;
        $filteredTarikAmount = $statsQuery3->where('type', 'tarik')->where('status', 'Berhasil')->sum('amount');

        $transactions = $query->paginate(15)->withQueryString();

        $categories = WasteCategory::all();
        $classes = User::where('role', 'siswa')->whereNotNull('class')->distinct()->pluck('class');

        return view('manajer.log_transaksi_detail', compact(
            'transactions', 'categories', 'classes', 'filters',
            'filteredWeight', 'filteredSetorAmount', 'filteredTarikAmount'
        ));
    }

    /**
     * Show detailed Active Students ranking page.
     */
    public function siswaTeraktifDetail(Request $request)
    {
        $classFilter = $request->input('class');
        $leagueFilter = $request->input('league');
        $search = $request->input('search');

        $query = User::where('role', 'siswa');

        if ($classFilter) {
            $query->where('class', $classFilter);
        }
        if ($leagueFilter) {
            $query->where('league', $leagueFilter);
        }
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%");
            });
        }

        $students = $query->orderBy('points', 'desc')
            ->orderBy('weekly_points', 'desc')
            ->paginate(20)
            ->withQueryString();

        // Attach total weight contribution
        $students->getCollection()->transform(function($student) {
            $student->total_weight = Transaction::where('user_id', $student->id)
                ->where('type', 'setor')
                ->where('status', 'Berhasil')
                ->sum('weight');
            return $student;
        });

        $classes = User::where('role', 'siswa')->whereNotNull('class')->distinct()->pluck('class');
        $leagues = User::where('role', 'siswa')->whereNotNull('league')->distinct()->pluck('league');

        return view('manajer.siswa_teraktif_detail', compact('students', 'classes', 'leagues', 'classFilter', 'leagueFilter', 'search'));
    }
}
