<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

#[Fillable(['batch_date', 'route', 'total_weight', 'total_value', 'agent_name', 'notes', 'created_by'])]
class Distribution extends Model
{
    use HasFactory;

    /**
     * Get items associated with this distribution batch.
     */
    public function items()
    {
        return $this->hasMany(DistributionItem::class);
    }

    /**
     * Get the manager who created this distribution batch.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
