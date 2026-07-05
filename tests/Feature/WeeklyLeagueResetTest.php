<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WasteCategory;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class WeeklyLeagueResetTest extends TestCase
{
    use RefreshDatabase;

    private User $operator;
    private WasteCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure roles exist
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'siswa', 'guard_name' => 'web']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'operator', 'guard_name' => 'web']);

        $this->category = WasteCategory::create([
            'name' => 'Plastik (Botol/Gelas)',
            'key' => 'plastik',
            'price_per_kg' => 3000,
            'points_per_kg' => 30,
            'icon' => '🥤'
        ]);

        $this->operator = User::create([
            'name' => 'Agus Hermawan',
            'email' => 'agus@ecobank.com',
            'password' => Hash::make('password'),
            'role' => 'operator',
            'phone' => '085134567893',
            'status' => 'approved'
        ]);
        $this->operator->assignRole('operator');
    }

    /**
     * Test that storing a deposit increments both lifetime points and weekly points.
     */
    public function test_points_deposit_increments_weekly_points()
    {
        $student = User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi@ecobank.com',
            'password' => Hash::make('password'),
            'nisn' => '12345678',
            'role' => 'siswa',
            'points' => 100,
            'weekly_points' => 20,
            'league' => 'bronze',
            'status' => 'approved'
        ]);
        $student->assignRole('siswa');

        // Setor 5.0 kg of Plastik (30 points/kg = 150 points)
        $response = $this->actingAs($this->operator)->post("/operator/setor/{$student->id}", [
            'waste_category_id' => $this->category->id,
            'weight' => 5.00,
        ]);

        $response->assertStatus(302);
        
        $student->refresh();
        $this->assertEquals(250, $student->points); // 100 + 150
        $this->assertEquals(170, $student->weekly_points); // 20 + 150
    }

    /**
     * Test the weekly reset Artisan command promotion/demotion/stay logic.
     */
    public function test_weekly_reset_artisan_command_logic()
    {
        // Create 5 students in Bronze league
        // We expect top 20% (1 student) to get promoted to Silver if weekly_points > 0
        // No demotions in Bronze
        $s1 = User::create(['name' => 'Student 1', 'email' => 's1@ecobank.com', 'password' => Hash::make('password'), 'role' => 'siswa', 'league' => 'bronze', 'weekly_points' => 100, 'points' => 100, 'status' => 'approved']);
        $s2 = User::create(['name' => 'Student 2', 'email' => 's2@ecobank.com', 'password' => Hash::make('password'), 'role' => 'siswa', 'league' => 'bronze', 'weekly_points' => 80, 'points' => 80, 'status' => 'approved']);
        $s3 = User::create(['name' => 'Student 3', 'email' => 's3@ecobank.com', 'password' => Hash::make('password'), 'role' => 'siswa', 'league' => 'bronze', 'weekly_points' => 50, 'points' => 50, 'status' => 'approved']);
        $s4 = User::create(['name' => 'Student 4', 'email' => 's4@ecobank.com', 'password' => Hash::make('password'), 'role' => 'siswa', 'league' => 'bronze', 'weekly_points' => 10, 'points' => 10, 'status' => 'approved']);
        $s5 = User::create(['name' => 'Student 5', 'email' => 's5@ecobank.com', 'password' => Hash::make('password'), 'role' => 'siswa', 'league' => 'bronze', 'weekly_points' => 0, 'points' => 0, 'status' => 'approved']);

        // Create 5 students in Silver league
        // Top 20% (1 student) promoted to Gold. Bottom 20% (1 student) demoted to Bronze. Others stay.
        $v1 = User::create(['name' => 'Silver 1', 'email' => 'v1@ecobank.com', 'password' => Hash::make('password'), 'role' => 'siswa', 'league' => 'silver', 'weekly_points' => 200, 'points' => 200, 'status' => 'approved']);
        $v2 = User::create(['name' => 'Silver 2', 'email' => 'v2@ecobank.com', 'password' => Hash::make('password'), 'role' => 'siswa', 'league' => 'silver', 'weekly_points' => 150, 'points' => 150, 'status' => 'approved']);
        $v3 = User::create(['name' => 'Silver 3', 'email' => 'v3@ecobank.com', 'password' => Hash::make('password'), 'role' => 'siswa', 'league' => 'silver', 'weekly_points' => 100, 'points' => 100, 'status' => 'approved']);
        $v4 = User::create(['name' => 'Silver 4', 'email' => 'v4@ecobank.com', 'password' => Hash::make('password'), 'role' => 'siswa', 'league' => 'silver', 'weekly_points' => 50, 'points' => 50, 'status' => 'approved']);
        $v5 = User::create(['name' => 'Silver 5', 'email' => 'v5@ecobank.com', 'password' => Hash::make('password'), 'role' => 'siswa', 'league' => 'silver', 'weekly_points' => 0, 'points' => 0, 'status' => 'approved']);

        // Run the Artisan reset command
        Artisan::call('league:reset');

        // Assert Bronze outcomes
        $s1->refresh(); $s2->refresh(); $s3->refresh(); $s4->refresh(); $s5->refresh();
        $this->assertEquals('silver', $s1->league); // Top 20% (Rank 1) promoted
        $this->assertEquals('promoted', $s1->last_weekly_status);
        $this->assertEquals(1, $s1->last_weekly_rank);
        $this->assertEquals(100, $s1->last_weekly_points);
        $this->assertFalse($s1->seen_weekly_result);
        $this->assertEquals(0, $s1->weekly_points); // Reset

        $this->assertEquals('bronze', $s2->league); // Stayed
        $this->assertEquals('stayed', $s2->last_weekly_status);
        $this->assertEquals(0, $s2->weekly_points);

        // Assert Silver outcomes
        $v1->refresh(); $v2->refresh(); $v3->refresh(); $v4->refresh(); $v5->refresh();
        $this->assertEquals('gold', $v1->league); // Promoted
        $this->assertEquals('promoted', $v1->last_weekly_status);
        
        $this->assertEquals('silver', $v3->league); // Stayed
        $this->assertEquals('stayed', $v3->last_weekly_status);

        $this->assertEquals('bronze', $v5->league); // Bottom 20% demoted
        $this->assertEquals('demoted', $v5->last_weekly_status);
    }

    /**
     * Test that student dashboard displays reset results banner exactly once.
     */
    public function test_dashboard_displays_reset_results_banner_exactly_once()
    {
        $student = User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi@ecobank.com',
            'password' => Hash::make('password'),
            'nisn' => '12345678',
            'role' => 'siswa',
            'points' => 100,
            'weekly_points' => 0,
            'league' => 'silver',
            'last_weekly_points' => 150,
            'last_weekly_rank' => 1,
            'last_weekly_status' => 'promoted',
            'seen_weekly_result' => false, // Set to false to trigger banner
            'status' => 'approved'
        ]);
        $student->assignRole('siswa');

        // Fetch dashboard first time
        $response = $this->actingAs($student)->get('/siswa/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Selamat! Anda Naik Kasta!');
        $response->assertSee('Silver League');
        $response->assertSee('150 Poin');

        // Check DB that seen_weekly_result flag is cleared
        $student->refresh();
        $this->assertTrue($student->seen_weekly_result);

        // Fetch dashboard second time - banner should NOT be visible
        $secondResponse = $this->actingAs($student)->get('/siswa/dashboard');
        $secondResponse->assertStatus(200);
        $secondResponse->assertDontSee('Selamat! Anda Naik Kasta!');
    }

    /**
     * Test that student can upload avatar.
     */
    public function test_student_can_upload_avatar()
    {
        $student = User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi@ecobank.com',
            'password' => Hash::make('password'),
            'role' => 'siswa',
            'status' => 'approved'
        ]);
        $student->assignRole('siswa');

        $file = \Illuminate\Http\UploadedFile::fake()->create('avatar.jpg', 100, 'image/jpeg');

        $response = $this->actingAs($student)->post('/siswa/profil', [
            'name' => 'Budi Santoso Baru',
            'phone' => '081234567890',
            'avatar' => $file
        ]);

        $response->assertStatus(302);
        $student->refresh();
        $this->assertNotNull($student->avatar);
        $this->assertEquals('Budi Santoso Baru', $student->name);
        
        // Cleanup uploaded file from public directory
        if ($student->avatar && file_exists(public_path($student->avatar))) {
            @unlink(public_path($student->avatar));
        }
    }
}
