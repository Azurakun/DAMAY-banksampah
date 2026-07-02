<?php

namespace Tests\Feature;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRegistrationApprovalTest extends TestCase
{
    use RefreshDatabase;

    private User $teacher;
    private User $manager;

    protected function setUp(): void
    {
        parent::setUp();

        // Prepare Spatie Roles
        Role::firstOrCreate(['name' => 'siswa', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'operator', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'walikelas', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'manajer', 'guard_name' => 'web']);

        // Create Homeroom Teacher
        $this->teacher = User::create([
            'name' => 'Dra. Sri Wahyuni',
            'email' => 'sri@ecobank.com',
            'password' => bcrypt('password'),
            'role' => 'walikelas',
            'class' => 'XII RPL 1',
            'balance' => 0,
            'points' => 0
        ]);
        $this->teacher->assignRole('walikelas');

        // Create Manager
        $this->manager = User::create([
            'name' => 'Haji Mulyono',
            'email' => 'mulyono@ecobank.com',
            'password' => bcrypt('password'),
            'role' => 'manajer',
            'balance' => 0,
            'points' => 0
        ]);
        $this->manager->assignRole('manajer');
    }

    /** @test */
    public function test_siswa_dapat_mendaftar_secara_mandiri_dan_berstatus_pending()
    {
        $response = $this->post('/register', [
            'name' => 'Rian Hidayat',
            'email' => 'rian@ecobank.com',
            'nisn' => '11223344',
            'class' => 'XII RPL 1',
            'phone' => '081234567890',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'email' => 'rian@ecobank.com',
            'status' => 'pending',
            'role' => 'siswa',
        ]);
    }

    /** @test */
    public function test_siswa_berstatus_pending_tidak_bisa_login()
    {
        $student = User::create([
            'name' => 'Rian Hidayat',
            'email' => 'rian@ecobank.com',
            'password' => bcrypt('password'),
            'role' => 'siswa',
            'class' => 'XII RPL 1',
            'status' => 'pending',
            'balance' => 0,
            'points' => 0
        ]);
        $student->assignRole('siswa');

        $response = $this->post('/login', [
            'email' => 'rian@ecobank.com',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /** @test */
    public function test_siswa_berstatus_rejected_tidak_bisa_login()
    {
        $student = User::create([
            'name' => 'Rian Hidayat',
            'email' => 'rian@ecobank.com',
            'password' => bcrypt('password'),
            'role' => 'siswa',
            'class' => 'XII RPL 1',
            'status' => 'rejected',
            'balance' => 0,
            'points' => 0
        ]);
        $student->assignRole('siswa');

        $response = $this->post('/login', [
            'email' => 'rian@ecobank.com',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /** @test */
    public function test_wali_kelas_bisa_melihat_dan_menyetujui_pendaftar_baru_secara_bulk()
    {
        $student1 = User::create([
            'name' => 'Rian Hidayat',
            'email' => 'rian@ecobank.com',
            'password' => bcrypt('password'),
            'role' => 'siswa',
            'class' => 'XII RPL 1',
            'status' => 'pending',
            'balance' => 0,
            'points' => 0
        ]);
        $student1->assignRole('siswa');

        $student2 = User::create([
            'name' => 'Dewi Lestari',
            'email' => 'dewi@ecobank.com',
            'password' => bcrypt('password'),
            'role' => 'siswa',
            'class' => 'XII RPL 1',
            'status' => 'pending',
            'balance' => 0,
            'points' => 0
        ]);
        $student2->assignRole('siswa');

        // Login as Wali Kelas
        $response = $this->actingAs($this->teacher)->get('/walikelas/pendaftar');
        $response->assertStatus(200);
        $response->assertSee('Rian Hidayat');
        $response->assertSee('Dewi Lestari');

        // Approve both students in bulk
        $approveResponse = $this->actingAs($this->teacher)->post('/walikelas/pendaftar/approve', [
            'ids' => [$student1->id, $student2->id]
        ]);

        $approveResponse->assertRedirect('/walikelas/pendaftar');
        $approveResponse->assertSessionHas('success');

        $this->assertEquals('approved', $student1->refresh()->status);
        $this->assertEquals('approved', $student2->refresh()->status);
    }

    /** @test */
    public function test_wali_kelas_bisa_menolak_pendaftar_baru_secara_bulk()
    {
        $student = User::create([
            'name' => 'Rian Hidayat',
            'email' => 'rian@ecobank.com',
            'password' => bcrypt('password'),
            'role' => 'siswa',
            'class' => 'XII RPL 1',
            'status' => 'pending',
            'balance' => 0,
            'points' => 0
        ]);
        $student->assignRole('siswa');

        $rejectResponse = $this->actingAs($this->teacher)->post('/walikelas/pendaftar/reject', [
            'ids' => [$student->id]
        ]);

        $rejectResponse->assertRedirect('/walikelas/pendaftar');
        $rejectResponse->assertSessionHas('success');

        $this->assertEquals('rejected', $student->refresh()->status);
    }

    /** @test */
    public function test_manajer_dapat_mendaftarkan_operator_dan_wali_kelas_baru()
    {
        // 1. Register Operator
        $response1 = $this->actingAs($this->manager)->post('/manajer/staff/register', [
            'name' => 'Agus Operator',
            'email' => 'agus_op@ecobank.com',
            'password' => 'password',
            'role' => 'operator',
        ]);

        $response1->assertRedirect('/manajer/users');
        
        $newOp = User::where('email', 'agus_op@ecobank.com')->first();
        $this->assertNotNull($newOp);
        $this->assertEquals('operator', $newOp->role);
        $this->assertEquals('approved', $newOp->status);
        $this->assertTrue($newOp->hasRole('operator'));

        // 2. Register Wali Kelas
        $response2 = $this->actingAs($this->manager)->post('/manajer/staff/register', [
            'name' => 'Sri Walikelas',
            'email' => 'sri_wk@ecobank.com',
            'password' => 'password',
            'role' => 'walikelas',
            'class' => 'XI RPL 1'
        ]);

        $response2->assertRedirect('/manajer/users');
        
        $newWk = User::where('email', 'sri_wk@ecobank.com')->first();
        $this->assertNotNull($newWk);
        $this->assertEquals('walikelas', $newWk->role);
        $this->assertEquals('XI RPL 1', $newWk->class);
        $this->assertEquals('approved', $newWk->status);
        $this->assertTrue($newWk->hasRole('walikelas'));
    }

    /** @test */
    public function test_manajer_bisa_mengakses_dan_mengubah_profil()
    {
        $response = $this->actingAs($this->manager)->get('/manajer/profil');
        $response->assertStatus(200);
        $response->assertSee('Profil Manajer');
        $response->assertSee($this->manager->email);

        $updateResponse = $this->actingAs($this->manager)->post('/manajer/profil', [
            'name' => 'Haji Mulyono Edit',
            'phone' => '089999111122',
            'password' => 'newpassword',
            'password_confirmation' => 'newpassword'
        ]);

        $updateResponse->assertRedirect('/manajer/profil');
        $updateResponse->assertSessionHas('success');

        $this->manager->refresh();
        $this->assertEquals('Haji Mulyono Edit', $this->manager->name);
        $this->assertEquals('089999111122', $this->manager->phone);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('newpassword', $this->manager->password));
    }

    /** @test */
    public function test_dasbor_manajer_menampilkan_metrik_poin_global_dan_top_students()
    {
        $student = User::create([
            'name' => 'Active Student',
            'email' => 'active_stu@ecobank.com',
            'password' => bcrypt('password'),
            'role' => 'siswa',
            'class' => 'XII RPL 1',
            'status' => 'approved',
            'balance' => 1000,
            'points' => 999
        ]);
        $student->assignRole('siswa');

        $response = $this->actingAs($this->manager)->get('/manajer/dashboard');
        $response->assertStatus(200);
        
        // Assert we see global points
        $response->assertSee('999');
        $response->assertSee('Total Poin');
        
        // Assert we see the top student
        $response->assertSee('Active Student');
    }

    /** @test */
    public function test_manajer_bisa_melihat_dan_memfilter_daftar_pengguna()
    {
        // 1. Create a dummy operator and dummy pending student
        $op = User::create([
            'name' => 'Adit Operator',
            'email' => 'adit@ecobank.com',
            'password' => bcrypt('password'),
            'role' => 'operator',
            'status' => 'approved',
            'balance' => 0,
            'points' => 0
        ]);
        $op->assignRole('operator');

        $pendingStudent = User::create([
            'name' => 'Cantika Siswa',
            'email' => 'cantika@ecobank.com',
            'password' => bcrypt('password'),
            'role' => 'siswa',
            'class' => 'XII RPL 1',
            'status' => 'pending',
            'balance' => 0,
            'points' => 0
        ]);
        $pendingStudent->assignRole('siswa');

        // 2. Open User Directory page
        $response = $this->actingAs($this->manager)->get('/manajer/users');
        $response->assertStatus(200);
        $response->assertSee('Adit Operator');
        $response->assertSee('Cantika Siswa');

        // 3. Search for 'Cantika'
        $searchResponse = $this->actingAs($this->manager)->get('/manajer/users?search=Cantika');
        $searchResponse->assertStatus(200);
        $searchResponse->assertSee('Cantika Siswa');
        $searchResponse->assertDontSee('Adit Operator');

        // 4. Filter by role 'operator'
        $roleResponse = $this->actingAs($this->manager)->get('/manajer/users?role=operator');
        $roleResponse->assertStatus(200);
        $roleResponse->assertSee('Adit Operator');
        $roleResponse->assertDontSee('Cantika Siswa');

        // 5. Filter by status 'pending'
        $statusResponse = $this->actingAs($this->manager)->get('/manajer/users?status=pending');
        $statusResponse->assertStatus(200);
        $statusResponse->assertSee('Cantika Siswa');
        $statusResponse->assertDontSee('Adit Operator');
    }

    /** @test */
    public function test_manajer_dapat_menghapus_akun_pengguna_lain()
    {
        $otherUser = User::create([
            'name' => 'Delete Me',
            'email' => 'delete_me@ecobank.com',
            'password' => bcrypt('password'),
            'role' => 'operator',
            'status' => 'approved',
            'balance' => 0,
            'points' => 0
        ]);
        $otherUser->assignRole('operator');

        $response = $this->actingAs($this->manager)->delete("/manajer/users/{$otherUser->id}");
        $response->assertRedirect('/manajer/users');
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('users', [
            'id' => $otherUser->id
        ]);
    }

    /** @test */
    public function test_manajer_tidak_dapat_menghapus_akun_sendiri()
    {
        $response = $this->actingAs($this->manager)->delete("/manajer/users/{$this->manager->id}");
        $response->assertRedirect('/manajer/users');
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('users', [
            'id' => $this->manager->id
        ]);
    }
}
