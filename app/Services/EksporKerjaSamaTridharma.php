<?php

namespace App\Services;

use App\Enums\Dharma;
use App\Enums\StatusVerifikasi;
use App\Models\Prodi;
use App\Models\RealisasiKegiatan;
use Illuminate\Support\Collection;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Writer;

/**
 * Tabel Kerja Sama Tridharma per prodi (README §9): tiga lembar (Pendidikan, Penelitian, PkM),
 * satu baris = satu realisasi kegiatan berstatus terverifikasi milik prodi tsb.
 *
 * ASUMSI (README §9, belum dikonfirmasi GKM/UPM): rentang TS-2 s.d. TS memakai tahun kalender
 * dari tanggal mulai kegiatan.
 */
class EksporKerjaSamaTridharma
{
    public const KOLOM = [
        'No',
        'Lembaga Mitra',
        'Tingkat (Internasional/Nasional/Lokal)',
        'Judul Kegiatan Kerja Sama',
        'Manfaat bagi PS',
        'Waktu & Durasi',
        'Bukti Kerja Sama (no. dokumen + tautan berkas)',
        'Tahun Berakhir Kerja Sama',
    ];

    public const LEMBAR = [
        'pendidikan' => 'Pendidikan',
        'penelitian' => 'Penelitian',
        'pkm' => 'PkM',
    ];

    /** @return Collection<int, RealisasiKegiatan> */
    public function kegiatan(Prodi $prodi, int $tahunTs, Dharma $dharma): Collection
    {
        return RealisasiKegiatan::query()
            ->with(['kerjaSama.mitra'])
            ->where('status_verifikasi', StatusVerifikasi::Terverifikasi->value)
            ->where('dharma', $dharma->value)
            ->whereHas('prodi', fn ($q) => $q->where('prodi.id', $prodi->id))
            ->whereBetween('tanggal_mulai', [($tahunTs - 2).'-01-01', $tahunTs.'-12-31'])
            ->orderBy('tanggal_mulai')
            ->orderBy('id')
            ->get();
    }

    /** @return list<list<string|int>> baris data (tanpa header) untuk satu dharma */
    public function baris(Prodi $prodi, int $tahunTs, Dharma $dharma): array
    {
        return $this->kegiatan($prodi, $tahunTs, $dharma)
            ->values()
            ->map(function (RealisasiKegiatan $r, int $i) {
                $ks = $r->kerjaSama;

                return [
                    $i + 1,
                    $ks->mitra->pluck('nama')->implode('; '),
                    $ks->tingkat->label(),
                    $r->judul_kegiatan,
                    $r->manfaat_bagi_prodi,
                    $this->waktuDurasi($r),
                    $this->bukti($ks),
                    $ks->tanggal_berakhir->year,
                ];
            })
            ->all();
    }

    public function tulis(Prodi $prodi, int $tahunTs, string $path): void
    {
        $writer = new Writer;
        $writer->openToFile($path);
        $tebal = (new Style)->setFontBold();

        foreach (array_values(self::LEMBAR) as $i => $nama) {
            $sheet = $i === 0 ? $writer->getCurrentSheet() : $writer->addNewSheetAndMakeItCurrent();
            $sheet->setName($nama);
            $writer->addRow(Row::fromValues(self::KOLOM, $tebal));

            foreach ($this->baris($prodi, $tahunTs, Dharma::from(array_keys(self::LEMBAR)[$i])) as $baris) {
                $writer->addRow(Row::fromValues($baris));
            }
        }

        $writer->close();
    }

    private function waktuDurasi(RealisasiKegiatan $r): string
    {
        $mulai = $r->tanggal_mulai->format('d/m/Y');

        if ($r->tanggal_selesai === null) {
            return "{$mulai} (belum selesai)";
        }

        $hari = $r->tanggal_mulai->diffInDays($r->tanggal_selesai) + 1;

        return "{$mulai} – {$r->tanggal_selesai->format('d/m/Y')} ({$hari} hari)";
    }

    private function bukti($kerjaSama): string
    {
        $nomor = $kerjaSama->nomor_dokumen_unsil ?: $kerjaSama->nomor_dokumen_mitra ?: $kerjaSama->judul;
        $bagian = ["{$kerjaSama->jenis_dokumen->value} {$nomor}"];

        if (filled($kerjaSama->berkas_dokumen)) {
            $bagian[] = route('kerja-sama.berkas', [$kerjaSama, 'dokumen']);
        }

        return implode(' — ', $bagian);
    }
}
