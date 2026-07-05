<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Classroom extends Model
{
    protected $fillable = ['name'];

    /**
     * Get students in this classroom.
     */
    public function students()
    {
        return $this->hasMany(User::class, 'classroom_id');
    }

    /**
     * Get homeroom teachers for this classroom.
     */
    public function teachers()
    {
        return $this->belongsToMany(User::class, 'classroom_user', 'classroom_id', 'user_id');
    }
}
