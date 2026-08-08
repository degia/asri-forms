<?php

namespace Tests\Feature;

use App\Livewire\Assets\Detail;
use App\Livewire\Assets\Edit;
use App\Livewire\Assets\Index;
use App\Models\Asset;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AssetsEditTest extends TestCase
{
    private array $createdUsers = [];

    private ?Asset $createdAsset = null;

    protected function setUp(): void
    {
        parent::setUp();

        if (DB::connection()->getDriverName() === 'sqlite') {
            $this->markTestSkipped('Migrasi proyek memakai sintaks MySQL (ENUM/MODIFY) yang tidak jalan di sqlite :memory:. Jalankan dengan DB_CONNECTION=mysql.');
        }

        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'teknisi', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'pengguna', 'guard_name' => 'web']);
    }

    protected function tearDown(): void
    {
        if ($this->createdAsset) {
            $this->createdAsset->forceDelete();
        }

        foreach ($this->createdUsers as $user) {
            $user->forceDelete();
        }

        parent::tearDown();
    }

    private function makeUser(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);
        $this->createdUsers[] = $user;

        return $user;
    }

    private function makeAsset(): Asset
    {
        $this->createdAsset = Asset::create([
            'kategori' => 'Laptop',
            'brand' => 'Dell',
            'tipe' => 'XPS 15',
            'nama_perangkat' => 'Laptop HRD-01',
            'no_serial' => 'SN-001',
            'no_asset' => 'AST-TEST-' . substr((string) Str::uuid(), 0, 8),
            'status' => 'active',
        ]);

        return $this->createdAsset;
    }

    public function test_edit_page_accessible_for_admin_and_teknisi(): void
    {
        $asset = $this->makeAsset();

        $this->actingAs($this->makeUser('admin'))
            ->get(route('assets.edit', $asset->id))
            ->assertOk();

        $this->actingAs($this->makeUser('teknisi'))
            ->get(route('assets.edit', $asset->id))
            ->assertOk();
    }

    public function test_edit_page_forbidden_for_non_admin_non_teknisi(): void
    {
        $asset = $this->makeAsset();

        $this->actingAs($this->makeUser('pengguna'))
            ->get(route('assets.edit', $asset->id))
            ->assertForbidden();

        $this->actingAs(User::factory()->create())
            ->get(route('assets.edit', $asset->id))
            ->assertForbidden();
    }

    public function test_edit_button_shown_on_index_for_admin_and_teknisi(): void
    {
        $asset = $this->makeAsset();

        foreach (['admin', 'teknisi'] as $role) {
            $this->actingAs($this->makeUser($role));

            Livewire::test(Index::class)
                ->assertSee(route('assets.edit', $asset->id));
        }
    }

    public function test_edit_button_hidden_on_index_for_pengguna(): void
    {
        $asset = $this->makeAsset();

        $this->actingAs($this->makeUser('pengguna'));

        Livewire::test(Index::class)
            ->assertDontSee(route('assets.edit', $asset->id));
    }

    public function test_edit_button_shown_on_detail_for_admin_and_teknisi(): void
    {
        $asset = $this->makeAsset();

        foreach (['admin', 'teknisi'] as $role) {
            $this->actingAs($this->makeUser($role));

            Livewire::test(Detail::class, ['id' => $asset->id])
                ->assertSee(route('assets.edit', $asset->id));
        }
    }

    public function test_edit_button_hidden_on_detail_for_pengguna(): void
    {
        $asset = $this->makeAsset();

        $this->actingAs($this->makeUser('pengguna'));

        Livewire::test(Detail::class, ['id' => $asset->id])
            ->assertDontSee(route('assets.edit', $asset->id));
    }

    public function test_asset_can_be_updated_via_edit_component(): void
    {
        $this->actingAs($this->makeUser('teknisi'));

        $asset = $this->makeAsset();

        Livewire::test(Edit::class, ['id' => $asset->id])
            ->set('brand', 'HP')
            ->set('namaPerangkat', 'Laptop HRD-02')
            ->call('update')
            ->assertHasNoErrors();

        $asset->refresh();

        $this->assertSame('HP', $asset->brand);
        $this->assertSame('Laptop HRD-02', $asset->nama_perangkat);
    }
}
