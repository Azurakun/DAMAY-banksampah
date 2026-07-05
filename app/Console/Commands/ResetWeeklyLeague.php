<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ResetWeeklyLeague extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'league:reset';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Perform the weekly reset for the gamification league ranking system (ala Duolingo).';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting weekly league reset process...');

        $leagues = ['bronze', 'silver', 'gold', 'diamond'];
        $updates = [];

        foreach ($leagues as $currentLeague) {
            // Get all students currently in this league (using state before any writes)
            $students = User::where('role', 'siswa')
                ->where('league', $currentLeague)
                ->orderBy('weekly_points', 'desc')
                ->orderBy('points', 'desc')
                ->orderBy('id', 'asc')
                ->get();

            $count = $students->count();
            if ($count === 0) {
                $this->line("No students in league: {$currentLeague}");
                continue;
            }

            // Calculate limits for promotion and demotion zones
            // Top 20% promoted, bottom 20% demoted
            $promoLimit = (int) ceil($count * 0.2);
            $demoteLimit = (int) ceil($count * 0.2);

            // Prevent overlaps in small groups
            if ($promoLimit + $demoteLimit > $count) {
                if ($count === 1) {
                    $promoLimit = 1;
                    $demoteLimit = 0;
                } else {
                    // For count >= 2, we can have 1 promo, 1 demote, and 0 stay
                    $promoLimit = 1;
                    $demoteLimit = 1;
                }
            }

            foreach ($students as $index => $student) {
                $rank = $index + 1;
                $weeklyPoints = $student->weekly_points;
                $nextLeague = $currentLeague;
                $status = 'stayed';

                // Check if student belongs to promotion zone (must have points > 0)
                if ($currentLeague !== 'diamond' && $rank <= $promoLimit && $weeklyPoints > 0) {
                    $status = 'promoted';
                    if ($currentLeague === 'bronze') {
                        $nextLeague = 'silver';
                    } elseif ($currentLeague === 'silver') {
                        $nextLeague = 'gold';
                    } elseif ($currentLeague === 'gold') {
                        $nextLeague = 'diamond';
                    }
                }
                // Check if student belongs to demotion zone
                elseif ($currentLeague !== 'bronze' && $rank > ($count - $demoteLimit)) {
                    $status = 'demoted';
                    if ($currentLeague === 'silver') {
                        $nextLeague = 'bronze';
                    } elseif ($currentLeague === 'gold') {
                        $nextLeague = 'silver';
                    } elseif ($currentLeague === 'diamond') {
                        $nextLeague = 'gold';
                    }
                }

                // Collect update details
                $updates[] = [
                    'student' => $student,
                    'data' => [
                        'league' => $nextLeague,
                        'last_weekly_points' => $weeklyPoints,
                        'last_weekly_rank' => $rank,
                        'last_weekly_status' => $status,
                        'seen_weekly_result' => false, // Will display popup/banner on next dashboard visit
                        'weekly_points' => 0, // Reset for the new week
                    ],
                    'log' => "Student: {$student->name} | Weekly Pts: {$weeklyPoints} | Rank: {$rank}/{$count} | Status: {$status} -> League: {$nextLeague}"
                ];
            }
        }

        // Apply all updates in a single transaction
        DB::transaction(function () use ($updates) {
            foreach ($updates as $update) {
                $update['student']->update($update['data']);
                $this->line($update['log']);
            }
        });

        // Clear the leaderboard cache
        Cache::forget('leaderboard_students_v4');
        $this->info('Weekly league reset process completed successfully!');
    }
}
