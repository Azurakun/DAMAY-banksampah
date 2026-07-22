<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

#[Fillable(['name', 'key', 'price_per_kg', 'points_per_kg', 'icon'])]
class WasteCategory extends Model
{
    use HasFactory;

    /**
     * Get transactions associated with this waste category
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'waste_category_id');
    }

    /**
     * Get dynamic available stock in the school warehouse.
     */
    public function getAvailableStockAttribute()
    {
        $totalSetor = $this->transactions()
            ->where('type', 'setor')
            ->where('status', 'Berhasil')
            ->sum('weight');

        $totalDistributed = \App\Models\DistributionItem::where('waste_category_id', $this->id)
            ->sum('weight');

        return max(0.00, (float)($totalSetor - $totalDistributed));
    }
}
