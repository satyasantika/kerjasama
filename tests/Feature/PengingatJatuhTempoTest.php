<?php

use App\Enums\Peran;
use App\Models\KerjaSama;
use App\Models\Mitra;
use App\Models\PengingatTerkirim;
use App\Models\Prodi;
use App\Models\User;
use App\Notifications\PengingatJatuhTempo;
use Database\Seeders\RolSeeder;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schedule;

beforeEach(function () {
    $this->seed(RolSeeder::class);
    $this->prodi = Prodi::factory()->create();
    $this->fakultas = User::factory()->create()->assignRole(Peran::AdminFakultas->value);
    $this->kaprodi = User::factory()->create(['prodi_id' => $this->prodi->id])->assignRole(Peran::Pimpinan->value);
    $this->kaprodiLain = User::factory()->create(['prodi_id' => Prodi::factory()->create()->id])->assignRole(Peran::Pimpinan->value);
    $this->dekan = User::factory()->create()->assignRole(Peran::Pimpinan->value);   // tanpa prodi
    $this->adminProdi = User::factory()->create(['prodi_id' => $this->prodi->id])->assignRole(Peran::AdminProdi->value);
    Notification::fake();
});

function ksBerakhirDalam(int $hari, ?Prodi $prodi = null, array $extra = []): KerjaSama
{
    $ks = KerjaSama::factory()->berlaku(today()->subYear()->toDateString(), today()->addDays($hari)->toDateString())->create($extra);
    $ks->mitra()->attach(Mitra::factory()->create()->id);

    if ($prodi) {
        $ks->prodi()->attach($prodi->id, ['penginisiasi' => false]);
    }

    return $ks;
}

it('mengirim ke admin fakultas dan kaprodi terkait saja', function () {
    $ks = ksBerakhirDalam(25, $this->prodi);

    $this->artisan('kerjasama:kirim-pengingat')->assertSuccessful();

    Notification::assertSentTo([$this->fakultas, $this->kaprodi], PengingatJatuhTempo::class, fn ($n) => $n->kerjaSama->is($ks) && $n->ambangHari === 30);
    Notification::assertNotSentTo([$this->kaprodiLain, $this->dekan, $this->adminProdi], PengingatJatuhTempo::class);
});

it('tidak mengirim ganda untuk ambang yang sama', function () {
    ksBerakhirDalam(25, $this->prodi);

    $this->artisan('kerjasama:kirim-pengingat');
    $this->artisan('kerjasama:kirim-pengingat');

    Notification::assertSentToTimes($this->fakultas, PengingatJatuhTempo::class, 1);
    expect(PengingatTerkirim::pluck('ambang_hari')->sort()->values()->all())->toBe([30, 90, 180]);
});

it('mengirim bertahap 180, 90, lalu 30 hari ketika waktu berjalan', function () {
    $ks = ksBerakhirDalam(170, $this->prodi);

    $this->artisan('kerjasama:kirim-pengingat');
    Notification::assertSentToTimes($this->fakultas, PengingatJatuhTempo::class, 1);

    $this->travel(85)->days();
    $this->artisan('kerjasama:kirim-pengingat');
    Notification::assertSentToTimes($this->fakultas, PengingatJatuhTempo::class, 2);

    $this->travel(5)->days();   // sisa 80, masih di ambang 90 yang sudah terkirim
    $this->artisan('kerjasama:kirim-pengingat');
    Notification::assertSentToTimes($this->fakultas, PengingatJatuhTempo::class, 2);

    $this->travel(55)->days();  // sisa 25 → ambang 30
    $this->artisan('kerjasama:kirim-pengingat');
    Notification::assertSentToTimes($this->fakultas, PengingatJatuhTempo::class, 3);

    expect(PengingatTerkirim::where('kerja_sama_id', $ks->id)->count())->toBe(3);
});

it('kerja sama baru dengan sisa pendek hanya memicu ambang terkecil', function () {
    ksBerakhirDalam(20, $this->prodi);

    $this->artisan('kerjasama:kirim-pengingat');

    Notification::assertSentToTimes($this->fakultas, PengingatJatuhTempo::class, 1);
    Notification::assertSentTo($this->fakultas, PengingatJatuhTempo::class, fn ($n) => $n->ambangHari === 30);

    $this->travel(-1)->days();
    $this->artisan('kerjasama:kirim-pengingat');
    Notification::assertSentToTimes($this->fakultas, PengingatJatuhTempo::class, 1);
});

it('tidak mengirim untuk kerja sama jauh, kedaluwarsa, belum berlaku, atau dibatalkan', function () {
    ksBerakhirDalam(400, $this->prodi);
    ksBerakhirDalam(-1, $this->prodi);
    ksBerakhirDalam(20, $this->prodi, ['status_manual' => 'dibatalkan']);
    KerjaSama::factory()->berlaku(today()->addDay()->toDateString(), today()->addDays(20)->toDateString())->create();

    $this->artisan('kerjasama:kirim-pengingat')->assertSuccessful();

    Notification::assertNothingSent();
    expect(PengingatTerkirim::count())->toBe(0);
});

it('mengirim hanya ke admin fakultas bila tidak ada prodi terkait', function () {
    ksBerakhirDalam(25);

    $this->artisan('kerjasama:kirim-pengingat');

    Notification::assertSentTo($this->fakultas, PengingatJatuhTempo::class);
    Notification::assertNotSentTo([$this->kaprodi, $this->dekan], PengingatJatuhTempo::class);
});

it('notifikasi memakai kanal database dan surel serta isi yang benar', function () {
    $ks = ksBerakhirDalam(25, $this->prodi, ['judul' => 'MoU Contoh']);
    $n = new PengingatJatuhTempo($ks->load('mitra'), 30);

    expect($n->via($this->fakultas))->toBe(['database', 'mail']);

    $mail = $n->toMail($this->fakultas);
    $db = $n->toDatabase($this->fakultas);

    expect($mail->subject)->toContain('30 hari')->toContain('MoU Contoh')
        ->and($db['title'])->toContain('30 hari')
        ->and($db['body'])->toContain('MoU Contoh');
});

it('terjadwal harian lewat scheduler', function () {
    $acara = collect(app(\Illuminate\Console\Scheduling\Schedule::class)->events())
        ->first(fn ($e) => str_contains($e->command, 'kerjasama:kirim-pengingat'));

    expect($acara)->not->toBeNull()->and($acara->expression)->toBe('0 7 * * *');
});
