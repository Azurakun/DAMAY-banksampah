<?php

namespace App\Http\Controllers;

use App\Models\Distribution;
use App\Models\DistributionItem;
use App\Models\WasteCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DistributionController extends Controller
{
    /**
     * Display a listing of distributions.
     */
    public function index()
    {
        $distributions = Distribution::with(['items.wasteCategory', 'creator'])
            ->orderBy('batch_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('manajer.distributions.index', compact('distributions'));
    }

    /**
     * Show the form for creating a new distribution.
     */
    public function create()
    {
        $categories = WasteCategory::all();
        return view('manajer.distributions.create', compact('categories'));
    }

    /**
     * Store a newly created distribution in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'batch_date' => ['required', 'date'],
            'route' => ['required', 'string', 'in:agent,unit'],
            'agent_name' => ['required_if:route,agent', 'nullable', 'string', 'max:150'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.waste_category_id' => ['required', 'exists:waste_categories,id'],
            'items.*.weight' => ['required', 'numeric', 'min:0.01'],
            'items.*.price_per_kg' => ['required_if:route,agent', 'nullable', 'integer', 'min:0'],
        ], [
            'batch_date.required' => 'Tanggal batch wajib diisi.',
            'route.required' => 'Jalur distribusi wajib dipilih.',
            'agent_name.required_if' => 'Nama agen pembeli wajib diisi jika jalur berupa Penjualan ke Agen.',
            'items.required' => 'Minimal satu jenis sampah wajib dimasukkan untuk didistribusikan.',
            'items.*.weight.min' => 'Berat sampah harus lebih besar dari 0.',
        ]);

        DB::beginTransaction();
        try {
            $totalWeight = 0;
            $totalValue = 0;
            $itemsData = [];

            foreach ($request->items as $item) {
                $category = WasteCategory::findOrFail($item['waste_category_id']);
                $weight = (float)$item['weight'];
                
                // Validate stock limit
                $availableStock = $category->available_stock;
                if ($weight > $availableStock) {
                    return back()->withInput()->with('error', "Stok tidak mencukupi untuk kategori '{$category->name}'. Stok tersedia: {$availableStock} kg, diajukan: {$weight} kg.");
                }

                $price = $request->route === 'agent' ? (int)$item['price_per_kg'] : 0;
                $value = round($weight * $price);

                $totalWeight += $weight;
                $totalValue += $value;

                $itemsData[] = [
                    'waste_category_id' => $category->id,
                    'weight' => $weight,
                    'price_per_kg' => $price,
                    'value' => $value,
                ];
            }

            // Create Distribution
            $distribution = Distribution::create([
                'batch_date' => $request->batch_date,
                'route' => $request->route,
                'total_weight' => $totalWeight,
                'total_value' => $totalValue,
                'agent_name' => $request->route === 'agent' ? $request->agent_name : 'Unit Pengolahan Internal',
                'notes' => $request->notes,
                'created_by' => Auth::id(),
            ]);

            // Save items
            foreach ($itemsData as $itemData) {
                $itemData['distribution_id'] = $distribution->id;
                DistributionItem::create($itemData);
            }

            DB::commit();
            $routePrefix = Auth::user()->role === 'operator' ? 'operator' : 'manajer';
            return redirect()->route($routePrefix . '.distributions.index')->with('success', 'Batch distribusi sampah berhasil dicatat!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal mencatat distribusi: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified distribution details.
     */
    public function show($id)
    {
        $distribution = Distribution::with(['items.wasteCategory', 'creator'])->findOrFail($id);
        return view('manajer.distributions.show', compact('distribution'));
    }

    /**
     * Show printable receipt (nota surat jalan) for a distribution batch.
     */
    public function printReceipt($id)
    {
        $distribution = Distribution::with(['items.wasteCategory', 'creator'])->findOrFail($id);
        $role = Auth::user()->role;
        $backUrl = route($role . '.distributions.show', $id);
        return view('shared.distribution_receipt', compact('distribution', 'backUrl'));
    }
}
