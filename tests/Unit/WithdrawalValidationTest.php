<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;

class WithdrawalValidationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Setup: buat user siswa dan operator dummy.
     */
    protected function createSiswa(int $balance = 50000): User
    {
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'siswa', 'guard_name' => 'web']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'operator', 'guard_name' => 'web']);

        $user = User::create([
            'name'     => 'Siswa Test',
            'email'    => 'siswa_test@ecobank.com',
            'password' => bcrypt('password'),
            'nisn'     => '99999999',
            'role'     => 'siswa',
            'class'    => 'XII RPL 1',
            'balance'  => $balance,
            'points'   => 0,
        ]);
        $user->assignRole('siswa');
        return $user;
    }

    protected function createOperator(): User
    {
        $user = User::create([
            'name'     => 'Operator Test',
            'email'    => 'operator_test@ecobank.com',
            'password' => bcrypt('password'),
            'role'     => 'operator',
            'balance'  => 0,
            'points'   => 0,
        ]);
        $user->assignRole('operator');
        return $user;
    }

    /** @test */
    public function test_siswa_tidak_bisa_tarik_lebih_dari_saldo(): void
    {
        $siswa    = $this->createSiswa(balance: 30000);
        $operator = $this->createOperator();

        // Penarikan dengan nominal melebihi saldo harus ditolak
        $response = $this->actingAs($siswa)->post(route('siswa.withdraw.post'), [
            'amount' => 50000, // lebih dari saldo 30.000
            'note'   => 'Test lebih dari saldo',
        ]);

        // Harus redirect kembali dengan error validasi
        $response->assertSessionHasErrors('amount');
    }

    /** @test */
    public function test_saldo_siswa_tidak_berubah_saat_pengajuan_tarik_menunggu(): void
    {
        $siswa    = $this->createSiswa(balance: 50000);
        $operator = $this->createOperator();

        $this->actingAs($siswa)->post(route('siswa.withdraw.post'), [
            'amount' => 20000,
            'note'   => 'Test tarik saldo',
        ]);

        // Saldo tidak berkurang saat masih status 'Menunggu'
        $this->assertDatabaseHas('users', [
            'id'      => $siswa->id,
            'balance' => 50000, // saldo tetap, belum dikurangi
        ]);

        // Transaksi dengan status 'Menunggu' harus terbuat
        $this->assertDatabaseHas('transactions', [
            'user_id' => $siswa->id,
            'type'    => 'tarik',
            'amount'  => 20000,
            'status'  => 'Menunggu',
        ]);
    }

    /** @test */
    public function test_saldo_berkurang_hanya_saat_operator_approve(): void
    {
        $siswa    = $this->createSiswa(balance: 50000);
        $operator = $this->createOperator();

        // Siswa mengajukan tarik
        $this->actingAs($siswa)->post(route('siswa.withdraw.post'), [
            'amount' => 20000,
            'note'   => 'Test approve',
        ]);

        $transaction = Transaction::where('user_id', $siswa->id)
            ->where('type', 'tarik')
            ->firstOrFail();

        // Operator melakukan approval
        $this->actingAs($operator)->post(route('operator.withdraw.approve', $transaction->id));

        // Saldo berkurang setelah diapprove
        $siswa->refresh();
        $this->assertEquals(30000, $siswa->balance); // 50.000 - 20.000 = 30.000
    }

    /** @test */
    public function test_saldo_tidak_bisa_negatif_saat_approve_jika_saldo_kurang(): void
    {
        $siswa    = $this->createSiswa(balance: 10000);
        $operator = $this->createOperator();

        // Buat transaksi tarik secara langsung dengan nominal melebihi saldo (edge case)
        $transaction = Transaction::create([
            'user_id'    => $siswa->id,
            'operator_id' => $operator->id,
            'type'       => 'tarik',
            'amount'     => 50000,
            'points'     => 0,
            'status'     => 'Menunggu',
            'note'       => 'Simulasi saldo tidak cukup',
        ]);

        // Operator mencoba approve, tapi saldo tidak cukup
        $this->actingAs($operator)->post(route('operator.withdraw.approve', $transaction->id));

        // Transaksi harus dibatalkan (Batal) bukan Berhasil
        $this->assertDatabaseHas('transactions', [
            'id'     => $transaction->id,
            'status' => 'Batal',
        ]);

        // Saldo tetap tidak berubah / tidak jadi negatif
        $siswa->refresh();
        $this->assertEquals(10000, $siswa->balance);
    }

    /** @test */
    public function test_penarikan_minimum_adalah_5000(): void
    {
        $siswa    = $this->createSiswa(balance: 50000);
        $operator = $this->createOperator();

        $response = $this->actingAs($siswa)->post(route('siswa.withdraw.post'), [
            'amount' => 4000, // di bawah minimum 5.000
            'note'   => 'Test minimum',
        ]);

        $response->assertSessionHasErrors('amount');
    }
}
