<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

#[Fillable(['distribution_id', 'waste_category_id', 'weight', 'price_per_kg', 'value'])]
class DistributionItem extends Model
{
    use HasFactory;

    /**
     * Get the distribution batch associated with this item.
     */
    public function distribution()
    {
        return $this->belongsTo(Distribution::class);
    }

    /**
     * Get the waste category associated with this item.
     */
    public function wasteCategory()
    {
        return $this->belongsTo(WasteCategory::class);
    }
}
