<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WasteCategory;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class EcoBankTest extends TestCase
{
    use RefreshDatabase;

    private User $student;
    private User $operator;
    private WasteCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure roles exist
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'siswa', 'guard_name' => 'web']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'operator', 'guard_name' => 'web']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'walikelas', 'guard_name' => 'web']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'manajer', 'guard_name' => 'web']);

        // 1. Create default waste category
        $this->category = WasteCategory::create([
            'name' => 'Plastik (Botol/Gelas)',
            'key' => 'plastik',
            'price_per_kg' => 3000,
            'points_per_kg' => 30,
            'icon' => '🥤'
        ]);

        // 2. Create seed users
        $this->student = User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi@ecobank.com',
            'password' => Hash::make('password'),
            'nisn' => '12345678',
            'role' => 'siswa',
            'class' => 'XII RPL 1',
            'phone' => '081234567890',
            'balance' => 10000, // Initial balance Rp 10.000
            'points' => 100,
        ]);
        $this->student->assignRole('siswa');

        $this->operator = User::create([
            'name' => 'Agus Hermawan',
            'email' => 'agus@ecobank.com',
            'password' => Hash::make('password'),
            'role' => 'operator',
            'phone' => '085134567893',
            'balance' => 0,
            'points' => 0,
        ]);
        $this->operator->assignRole('operator');
    }

    /**
     * Test login functionality
     */
    public function test_user_can_login_with_correct_credentials()
    {
        $response = $this->post('/login', [
            'email' => 'budi@ecobank.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/siswa/dashboard');
        $this->assertAuthenticatedAs($this->student);
    }

    /**
     * Test student dashboard loads correctly and queries balance/points
     */
    public function test_student_dashboard_displays_correct_balances()
    {
        $response = $this->actingAs($this->student)->get('/siswa/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Budi');
        $response->assertSee('10.000');
        $response->assertSee('100 Poin');
    }

    /**
     * Test operator auto-suggest search queries correctly
     */
    public function test_operator_can_search_students_by_nisn_or_name()
    {
        // Act as operator
        $response = $this->actingAs($this->operator)->get('/operator/search?query=Budi');

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'name' => 'Budi Santoso',
            'nisn' => '12345678',
            'class' => 'XII RPL 1'
        ]);
    }

    /**
     * Test trash deposit (setor) works properly, updates student balance & points,
     * and redirects to confirmation print receipt.
     */
    public function test_operator_can_record_trash_deposit_and_student_balance_increases()
    {
        // Student starting balance: 10,000 | points: 100
        // Setor 5.0 kg of Plastik (Price per kg = 3000, Points per kg = 30)
        // Calculated reward: 5.0 * 3000 = 15,000 | Points: 5.0 * 30 = 150
        // Expected ending student balance: 10,000 + 15,000 = 25,000 | Points: 100 + 150 = 250

        $response = $this->actingAs($this->operator)->post("/operator/setor/{$this->student->id}", [
            'waste_category_id' => $this->category->id,
            'weight' => 5.00,
            'note' => 'Setoran botol timbulan plastik Budi'
        ]);

        // Assert redirect to confirm page
        $transaction = Transaction::where('user_id', $this->student->id)->first();
        $this->assertNotNull($transaction);
        $response->assertRedirect("/operator/konfirmasi/{$transaction->id}");

        // Assert database updates
        $this->student->refresh();
        $this->assertEquals(25000, $this->student->balance);
        $this->assertEquals(250, $this->student->points);
        $this->assertEquals(15000, $transaction->amount);
        $this->assertEquals(150, $transaction->points);
        $this->assertEquals(5.00, $transaction->weight);
        $this->assertEquals('Berhasil', $transaction->status);
    }

    /**
     * Test cash withdrawal request (tarik) works properly, creates a pending transaction,
     * and shows in operator's dashboard queue.
     */
    public function test_student_can_request_cash_withdrawal_and_wait_approval()
    {
        // Student requests to withdraw Rp 5.000
        $response = $this->actingAs($this->student)->post('/siswa/tarik', [
            'amount' => 5000,
            'note' => 'Tarik uang saku Budi'
        ]);

        $response->assertRedirect('/siswa/dashboard');

        // Transaction created with status 'Menunggu'
        $transaction = Transaction::where('user_id', $this->student->id)->where('type', 'tarik')->first();
        $this->assertNotNull($transaction);
        $this->assertEquals('Menunggu', $transaction->status);
        $this->assertEquals(5000, $transaction->amount);

        // Verify that operator dashboard sees it in the queue
        $operatorResponse = $this->actingAs($this->operator)->get('/operator/dashboard');
        $operatorResponse->assertStatus(200);
        $operatorResponse->assertSee('Tarik uang saku Budi');
        $operatorResponse->assertSee('Rp 5.000');
    }

    /**
     * Test operator cash out approval successfully deducts student's balance
     */
    public function test_operator_can_approve_cash_withdrawal_and_balance_is_deducted()
    {
        // 1. Create a pending withdrawal request
        $transaction = Transaction::create([
            'user_id' => $this->student->id,
            'operator_id' => $this->operator->id,
            'type' => 'tarik',
            'amount' => 5000,
            'points' => 0,
            'status' => 'Menunggu',
            'note' => 'Budi cash out'
        ]);

        // 2. Act as Operator and approve
        $response = $this->actingAs($this->operator)->post("/operator/tarik/{$transaction->id}/approve");

        $response->assertStatus(302); // Redirect back

        // 3. Verify balance is deducted: 10,000 - 5,000 = 5,000
        $this->student->refresh();
        $transaction->refresh();

        $this->assertEquals(5000, $this->student->balance);
        $this->assertEquals('Berhasil', $transaction->status);
        $this->assertEquals($this->operator->id, $transaction->operator_id);
    }

    /**
     * Test operator cash out rejection leaves student balance unchanged and updates status to 'Batal'
     */
    public function test_operator_can_cancel_cash_withdrawal_and_balance_remains_unchanged()
    {
        // 1. Create a pending withdrawal request
        $transaction = Transaction::create([
            'user_id' => $this->student->id,
            'operator_id' => $this->operator->id,
            'type' => 'tarik',
            'amount' => 5000,
            'points' => 0,
            'status' => 'Menunggu',
            'note' => 'Budi cash out'
        ]);

        // 2. Act as Operator and reject
        $response = $this->actingAs($this->operator)->post("/operator/tarik/{$transaction->id}/cancel");

        $response->assertStatus(302); // Redirect back

        // 3. Verify balance remains unchanged: 10,000
        $this->student->refresh();
        $transaction->refresh();

        $this->assertEquals(10000, $this->student->balance);
        $this->assertEquals('Batal', $transaction->status);
        $this->assertEquals('Pengajuan ditolak oleh operator', $transaction->note);
    }

    /**
     * Test student can view their profile and update details
     */
    public function test_student_can_view_profile_and_update_details()
    {
        $response = $this->actingAs($this->student)->get('/siswa/profil');

        $response->assertStatus(200);
        $response->assertSee($this->student->nisn);

        $updateResponse = $this->actingAs($this->student)->post('/siswa/profil', [
            'name' => 'Budi Santoso Edit',
            'phone' => '081122334455',
        ]);

        $updateResponse->assertRedirect('/siswa/profil');

        $this->student->refresh();
        $this->assertEquals('Budi Santoso Edit', $this->student->name);
        $this->assertEquals('081122334455', $this->student->phone);
    }

    /**
     * Test operator can view the student registration page
     */
    public function test_operator_can_view_registration_page()
    {
        $response = $this->actingAs($this->operator)->get('/operator/register-siswa');

        $response->assertStatus(200);
        $response->assertSee('Registrasi Nasabah Baru');
        $response->assertSee('Import Massal via CSV');
    }

    /**
     * Test operator can register a single student manually
     */
    public function test_operator_can_register_single_student_manually()
    {
        $response = $this->actingAs($this->operator)->post('/operator/register-siswa/single', [
            'name' => 'Mega Utami',
            'email' => 'mega@ecobank.com',
            'nisn' => '98761234',
            'class' => 'XI RPL 2',
            'phone' => '089988776655',
            'password' => 'megapassword'
        ]);

        $response->assertStatus(302); // Redirect back
        $response->assertSessionHas('success');

        // Check user database contains the newly registered student
        $newStudent = User::where('nisn', '98761234')->first();
        $this->assertNotNull($newStudent);
        $this->assertEquals('Mega Utami', $newStudent->name);
        $this->assertEquals('mega@ecobank.com', $newStudent->email);
        $this->assertEquals('XI RPL 2', $newStudent->class);
        $this->assertEquals('siswa', $newStudent->role);
        $this->assertTrue(Hash::check('megapassword', $newStudent->password));
    }

    /**
     * Test operator can bulk import students successfully from a CSV file
     */
    public function test_operator_can_bulk_import_students_successfully()
    {
        $csvHeader = "Nama,Email,NISN,Kelas,Telepon\n";
        $csvContent = "Citra Lestari,citra@ecobank.com,99887766,XII RPL 1,087766554433\n";
        $csvContent .= "Doni Setiawan,doni@ecobank.com,55667788,XII RPL 1,\n";

        $file = \Illuminate\Http\UploadedFile::fake()->createWithContent('siswa.csv', $csvHeader . $csvContent);

        $response = $this->actingAs($this->operator)->post('/operator/register-siswa/bulk', [
            'file' => $file
        ]);

        $response->assertStatus(302); // Redirect back
        $response->assertSessionHas('success');
        $response->assertSessionHas('bulk_success_count', 2);
        $response->assertSessionHas('bulk_skip_count', 0);

        // Assert database holds the bulk-registered students
        $citra = User::where('nisn', '99887766')->first();
        $this->assertNotNull($citra);
        $this->assertEquals('Citra Lestari', $citra->name);
        $this->assertEquals('citra@ecobank.com', $citra->email);
        $this->assertEquals('087766554433', $citra->phone);
        $this->assertEquals('siswa', $citra->role);

        $doni = User::where('nisn', '55667788')->first();
        $this->assertNotNull($doni);
        $this->assertEquals('Doni Setiawan', $doni->name);
        $this->assertEquals('doni@ecobank.com', $doni->email);
        $this->assertNull($doni->phone);
        $this->assertEquals('siswa', $doni->role);
    }
}
