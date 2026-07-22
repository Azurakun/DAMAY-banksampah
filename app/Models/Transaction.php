<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

#[Fillable(['user_id', 'operator_id', 'type', 'waste_category_id', 'weight', 'amount', 'points', 'status', 'note'])]
class Transaction extends Model
{
    use HasFactory;

    /**
     * Get the student who made the transaction
     */
    public function student()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the operator who processed the transaction
     */
    public function operator()
    {
        return $this->belongsTo(User::class, 'operator_id');
    }

    /**
     * Get the waste category associated with this transaction
     */
    public function wasteCategory()
    {
        return $this->belongsTo(WasteCategory::class, 'waste_category_id');
    }

    /**
     * Scope a query to apply report filters.
     */
    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['start_date'] ?? null, function ($q, $date) {
            $q->whereDate('created_at', '>=', $date);
        });
        $query->when($filters['end_date'] ?? null, function ($q, $date) {
            $q->whereDate('created_at', '<=', $date);
        });
        $query->when($filters['waste_category_id'] ?? null, function ($q, $catId) {
            $q->where('waste_category_id', $catId);
        });
        $query->when($filters['class'] ?? null, function ($q, $class) {
            $q->whereHas('student', function ($sub) use ($class) {
                $sub->where('class', $class);
            });
        });
        $query->when($filters['type'] ?? null, function ($q, $type) {
            $q->where('type', $type);
        });
        $query->when($filters['status'] ?? null, function ($q, $status) {
            $q->where('status', $status);
        });
    }
}
