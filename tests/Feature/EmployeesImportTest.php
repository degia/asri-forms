<?php

namespace Tests\Feature;

use App\Livewire\Admin\Employees\ImportCsv;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EmployeesImportTest extends TestCase
{
    private array $createdUsers = [];

    private array $createdEmployees = [];

    protected function setUp(): void
    {
        parent::setUp();

        if (DB::connection()->getDriverName() === 'sqlite') {
            $this->markTestSkipped('Migrasi proyek memakai sintaks MySQL (ENUM/MODIFY) yang tidak jalan di sqlite :memory:. Jalankan dengan DB_CONNECTION=mysql.');
        }

        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    }

    protected function tearDown(): void
    {
        foreach ($this->createdEmployees as $employee) {
            $employee->forceDelete();
        }

        foreach ($this->createdUsers as $user) {
            $user->forceDelete();
        }

        parent::tearDown();
    }

    private function makeUser(): User
    {
        $user = User::factory()->create([
            'email' => 'import-test-' . substr((string) Str::uuid(), 0, 8) . '@asri.co.id',
            'nik' => null,
            'status' => User::STATUS_ACTIVE,
        ]);
        $this->createdUsers[] = $user;

        return $user;
    }

    private function makeCsv(array $rows): UploadedFile
    {
        $header = ['name', 'nik', 'site', 'directorate', 'divisi', 'departement', 'sub_departement', 'position', 'no_telepon', 'email', 'status', 'date_resign'];

        $lines = [implode(',', $header)];
        foreach ($rows as $row) {
            $lines[] = implode(',', array_map(fn ($cell) => '"' . str_replace('"', '""', (string) $cell) . '"', $row));
        }

        return UploadedFile::fake()->createWithContent('employees.csv', implode("\n", $lines));
    }

    public function test_import_creates_employees_and_links_user_email(): void
    {
        $user = $this->makeUser();

        $file = $this->makeCsv([
            ['Emp Import Alpha', 'IMP-TEST-0001', '', '', '', '', '', '', '081100000001', $user->email, 'Active', ''],
            ['Emp Import Beta', '', '', '', '', '', '', '', '', '', 'Resigned', '2026-01-15'],
        ]);

        Livewire::test(ImportCsv::class)
            ->set('file', $file)
            ->call('processData')
            ->assertSet('successCount', 2)
            ->assertSet('errorCount', 0)
            ->call('confirmSendImport')
            ->assertSet('showConfirmModal', true)
            ->call('confirmImport')
            ->assertRedirect(route('admin.employees.index'));

        $alpha = Employee::where('nik', 'IMP-TEST-0001')->first();
        $this->assertNotNull($alpha);
        $this->assertEquals('Emp Import Alpha', $alpha->name);
        $this->assertEquals($user->email, $alpha->email);
        $this->assertEquals('Active', $alpha->status);
        $this->assertEquals('Connect', $alpha->akun_login);
        $this->createdEmployees[] = $alpha;

        $user->refresh();
        $this->assertEquals('IMP-TEST-0001', $user->nik);

        $beta = Employee::where('name', 'Emp Import Beta')->first();
        $this->assertNotNull($beta);
        $this->assertNotNull($beta->nik);
        $this->assertStringStartsWith('NIK-', $beta->nik);
        $this->assertEquals('Resigned', $beta->status);
        $this->assertEquals('No Access', $beta->akun_login);
        $this->assertEquals('2026-01-15', $beta->date_resign);
        $this->createdEmployees[] = $beta;
    }

    public function test_import_rejects_duplicate_nik_and_unregistered_email(): void
    {
        $user = $this->makeUser();

        Employee::create([
            'name' => 'Existing Employee',
            'nik' => 'IMP-TEST-0009',
            'status' => Employee::STATUS_ACTIVE,
        ]);
        $this->createdEmployees[] = Employee::where('nik', 'IMP-TEST-0009')->first();

        $file = $this->makeCsv([
            ['Emp Dup', 'IMP-TEST-0009', '', '', '', '', '', '', '', '', 'Active', ''],
            ['Emp Bad Email', 'IMP-TEST-0010', '', '', '', '', '', '', '', 'not-a-user@asri.co.id', 'Active', ''],
            ['Emp Ok', 'IMP-TEST-0011', '', '', '', '', '', '', '', $user->email, 'Active', ''],
        ]);

        Livewire::test(ImportCsv::class)
            ->set('file', $file)
            ->call('processData')
            ->assertSet('successCount', 1)
            ->assertSet('errorCount', 2)
            ->call('confirmSendImport')
            ->call('confirmImport')
            ->assertRedirect(route('admin.employees.index'));

        $this->assertNotNull(Employee::where('nik', 'IMP-TEST-0011')->first());
        $this->createdEmployees[] = Employee::where('nik', 'IMP-TEST-0011')->first();

        $this->assertNull(Employee::where('nik', 'IMP-TEST-0010')->first());
        $this->assertEquals(1, Employee::where('nik', 'IMP-TEST-0009')->count());
    }
}
