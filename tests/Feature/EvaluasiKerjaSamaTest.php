<?php

use App\Enums\Peran;
use App\Filament\Resources\KerjaSama\Pages\EditKerjaSama;
use App\Filament\Resources\KerjaSama\Pages\ViewKerjaSama;
use App\Filament\Resources\KerjaSama\RelationManagers\EvaluasiRelationManager;
use App\Models\EvaluasiKerjaSama;
use App\Models\KerjaSama;
use App\Models\Prodi;
use App\Models\User;
use Database\Seeders\RolSeeder;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->seed(RolSeeder::class);
    Filament::setCurrentPanel('admin');
});

function evaluator(Peran $peran, ?Prodi $prodi = null): User
{
    return User::factory()->create(['prodi_id' => $prodi?->id])->assignRole($peran->value);
}

it('admin_prodi mengevaluasi kerja sama prodinya dan penilai tercatat', function () {
    $prodi = Prodi::factory()->create();
    $ks = KerjaSama::factory()->create();
    $ks->prodi()->attach($prodi->id, ['penginisiasi' => false]);
    $user = evaluator(Peran::AdminProdi, $prodi);
    actingAs($user);

    Livewire::test(EvaluasiRelationManager::class, ['ownerRecord' => $ks, 'pageClass' => EditKerjaSama::class])
        ->callAction(TestAction::make('create')->table(), [
            'tahun_ts' => 2025, 'skor_keefektifan' => 3, 'analisis' => 'Berkontribusi nyata', 'rekomendasi' => 'perpanjang',
        ])
        ->assertHasNoFormErrors();

    $e = EvaluasiKerjaSama::first();

    expect($e->kerja_sama_id)->toBe($ks->id)
        ->and($e->dinilai_oleh)->toBe($user->id)
        ->and($e->rekomendasi->value)->toBe('perpanjang')
        ->and($e->skor_keefektifan)->toBe(3);
});

it('skor di luar 1–4 dan analisis kosong ditolak', function () {
    $ks = KerjaSama::factory()->create();
    actingAs(evaluator(Peran::AdminFakultas));

    Livewire::test(EvaluasiRelationManager::class, ['ownerRecord' => $ks, 'pageClass' => EditKerjaSama::class])
        ->callAction(TestAction::make('create')->table(), [
            'tahun_ts' => 2025, 'skor_keefektifan' => 5, 'analisis' => '', 'rekomendasi' => 'lanjutkan',
        ])
        ->assertHasFormErrors(['skor_keefektifan', 'analisis' => 'required']);

    expect(EvaluasiKerjaSama::count())->toBe(0);
});

it('admin_prodi tidak bisa mengevaluasi kerja sama prodi lain dan pimpinan hanya melihat', function () {
    $milikLain = KerjaSama::factory()->create();
    $milikLain->prodi()->attach(Prodi::factory()->create()->id, ['penginisiasi' => false]);
    $e = EvaluasiKerjaSama::factory()->create(['kerja_sama_id' => $milikLain->id]);

    $adminProdi = evaluator(Peran::AdminProdi, Prodi::factory()->create());
    $pimpinan = evaluator(Peran::Pimpinan);

    expect($adminProdi->can('update', $e))->toBeFalse()
        ->and($adminProdi->can('view', $e))->toBeTrue()
        ->and($pimpinan->can('update', $e))->toBeFalse()
        ->and($pimpinan->can('create', EvaluasiKerjaSama::class))->toBeFalse()
        ->and(evaluator(Peran::SuperAdmin)->can('delete', $e))->toBeFalse();

    actingAs($pimpinan);
    Livewire::test(EvaluasiRelationManager::class, ['ownerRecord' => $milikLain, 'pageClass' => ViewKerjaSama::class])
        ->assertCanSeeTableRecords([$e])
        ->assertActionHidden(TestAction::make('create')->table());
});
