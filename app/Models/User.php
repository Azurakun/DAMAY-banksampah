<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'nisn', 'role', 'class', 'classroom_id', 'phone', 'balance', 'points', 'weekly_points', 'last_weekly_points', 'last_weekly_rank', 'last_weekly_status', 'seen_weekly_result', 'league', 'avatar', 'status'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'seen_weekly_result' => 'boolean',
        ];
    }

    /**
     * Transactions as a Student
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'user_id');
    }

    /**
     * Transactions processed as an Operator
     */
    public function operatorTransactions()
    {
        return $this->hasMany(Transaction::class, 'operator_id');
    }

    /**
     * Get the student's classroom.
     */
    public function classroom()
    {
        return $this->belongsTo(Classroom::class, 'classroom_id');
    }

    /**
     * Get the classrooms managed by this homeroom teacher.
     */
    public function classrooms()
    {
        return $this->belongsToMany(Classroom::class, 'classroom_user', 'user_id', 'classroom_id');
    }

    /**
     * Sync classroom_id and class name string on save.
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($user) {
            if ($user->role === 'siswa') {
                if ($user->classroom_id && !$user->isDirty('class')) {
                    $classroom = Classroom::find($user->classroom_id);
                    if ($classroom) {
                        $user->class = $classroom->name;
                    }
                } elseif ($user->class && !$user->isDirty('classroom_id')) {
                    $classroom = Classroom::firstOrCreate(['name' => trim($user->class)]);
                    $user->classroom_id = $classroom->id;
                }
            }
        });

        static::saved(function ($user) {
            \Illuminate\Support\Facades\Cache::forget('leaderboard_students_v4');
        });
    }
}
