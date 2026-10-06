<?php

namespace Database\Seeders;

use App\Models\BentukKerjaSama;
use Illuminate\Database\Seeder;

class BentukKerjaSamaSeeder extends Seeder
{
    /** Sumber: docs/rujukan.md §A.2 (Permendikbud 14/2014) + tambahan FKIP. */
    private const DATA = [
        ['AK-PT-01', 'Pendidikan', 'akademik', 'antar_pt', 'pendidikan', '7 huruf a, 8 ay. 1'],
        ['AK-PT-02', 'Penelitian', 'akademik', 'antar_pt', 'penelitian', '7 huruf a, 8 ay. 2'],
        ['AK-PT-03', 'Pengabdian kepada masyarakat', 'akademik', 'antar_pt', 'pkm', '7 huruf a, 8 ay. 3'],
        ['AK-PT-04', 'Penjaminan mutu internal', 'akademik', 'antar_pt', 'pendidikan', '7 huruf b, 9'],
        ['AK-PT-05', 'Program kembaran', 'akademik', 'antar_pt', 'pendidikan', '7 huruf c, 10'],
        ['AK-PT-06', 'Gelar bersama', 'akademik', 'antar_pt', 'pendidikan', '7 huruf d, 11'],
        ['AK-PT-07', 'Gelar ganda', 'akademik', 'antar_pt', 'pendidikan', '7 huruf e, 12'],
        ['AK-PT-08', 'Pengalihan/pemerolehan angka kredit (transfer kredit)', 'akademik', 'antar_pt', 'pendidikan', '7 huruf f, 13'],
        ['AK-PT-09', 'Penugasan dosen senior sebagai pembina', 'akademik', 'antar_pt', 'pendidikan', '7 huruf g, 14'],
        ['AK-PT-10', 'Pertukaran dosen', 'akademik', 'antar_pt', 'pendidikan', '7 huruf h, 15'],
        ['AK-PT-11', 'Pertukaran mahasiswa', 'akademik', 'antar_pt', 'pendidikan', '7 huruf h, 16'],
        ['AK-PT-12', 'Pemanfaatan bersama sumber daya', 'akademik', 'antar_pt', null, '7 huruf i, 17'],
        ['AK-PT-13', 'Pengembangan pusat kajian Indonesia dan budaya lokal', 'akademik', 'antar_pt', 'penelitian', '7 huruf j, 18'],
        ['AK-PT-14', 'Penerbitan berkala ilmiah', 'akademik', 'antar_pt', 'penelitian', '7 huruf k, 19'],
        ['AK-PT-15', 'Pemagangan', 'akademik', 'antar_pt', 'pendidikan', '7 huruf l, 20'],
        ['AK-PT-16', 'Penyelenggaraan seminar bersama', 'akademik', 'antar_pt', null, '7 huruf m, 21'],
        ['AK-PT-99', 'Bentuk lain (akademik, antar-PT)', 'akademik', 'antar_pt', null, '7 huruf n, 22'],
        ['AK-DU-01', 'Pengembangan sumber daya manusia', 'akademik', 'dudi_pihak_lain', 'pendidikan', '23 huruf a, 24'],
        ['AK-DU-02', 'Penelitian', 'akademik', 'dudi_pihak_lain', 'penelitian', '23 huruf b, 25'],
        ['AK-DU-03', 'Pengabdian kepada masyarakat', 'akademik', 'dudi_pihak_lain', 'pkm', '23 huruf b, 25'],
        ['AK-DU-04', 'Pemerolehan angka kredit/satuan sejenis', 'akademik', 'dudi_pihak_lain', 'pendidikan', '23 huruf c, 26'],
        ['AK-DU-05', 'Pemanfaatan bersama sumber daya', 'akademik', 'dudi_pihak_lain', null, '23 huruf d, 27'],
        ['AK-DU-06', 'Penerbitan terbitan/jurnal berkala ilmiah', 'akademik', 'dudi_pihak_lain', 'penelitian', '23 huruf e, 28'],
        ['AK-DU-07', 'Penyelenggaraan seminar bersama', 'akademik', 'dudi_pihak_lain', null, '23 huruf f, 29'],
        ['AK-DU-08', 'Layanan keahlian praktis oleh dosen tamu dari dunia usaha', 'akademik', 'dudi_pihak_lain', 'pendidikan', '23 huruf g, 30'],
        ['AK-DU-09', 'Beasiswa / bantuan biaya pendidikan', 'akademik', 'dudi_pihak_lain', 'pendidikan', '23 huruf h, 31'],
        ['AK-DU-99', 'Bentuk lain (akademik, dunia usaha/pihak lain)', 'akademik', 'dudi_pihak_lain', null, '23 huruf i, 32'],
        ['NA-PT-01', 'Pendayagunaan aset', 'non_akademik', 'antar_pt', null, '33 huruf a, 34'],
        ['NA-PT-02', 'Penggalangan dana', 'non_akademik', 'antar_pt', null, '33 huruf b'],
        ['NA-PT-03', 'Jasa dan royalti HKI', 'non_akademik', 'antar_pt', null, '33 huruf c'],
        ['NA-PT-99', 'Bentuk lain (non-akademik, antar-PT)', 'non_akademik', 'antar_pt', null, '33 huruf d'],
        ['NA-DU-01', 'Pendayagunaan aset', 'non_akademik', 'dudi_pihak_lain', null, '38 huruf a'],
        ['NA-DU-02', 'Penggalangan dana', 'non_akademik', 'dudi_pihak_lain', null, '38 huruf b, 40'],
        ['NA-DU-03', 'Jasa dan royalti penggunaan HKI', 'non_akademik', 'dudi_pihak_lain', null, '38 huruf c, 41'],
        ['NA-DU-04', 'Pengembangan SDM (pelatihan, internship, bursa kerja)', 'non_akademik', 'dudi_pihak_lain', null, '38 huruf d, 42'],
        ['NA-DU-05', 'Pengurangan tarif', 'non_akademik', 'dudi_pihak_lain', null, '38 huruf e, 43'],
        ['NA-DU-06', 'Koordinator kegiatan (event organizer)', 'non_akademik', 'dudi_pihak_lain', null, '38 huruf f, 44'],
        ['NA-DU-07', 'Pemberdayaan masyarakat', 'non_akademik', 'dudi_pihak_lain', 'pkm', '38 huruf g, 45'],
        ['NA-DU-99', 'Bentuk lain (non-akademik, dunia usaha/pihak lain)', 'non_akademik', 'dudi_pihak_lain', null, '38 huruf h, 46'],
        ['FKIP-01', 'PLP/Magang Kependidikan di sekolah mitra', 'akademik', 'dudi_pihak_lain', 'pendidikan', 'Tambahan FKIP (bukan dari Permendikbud)'],
        ['FKIP-02', 'Sekolah mitra penelitian tindakan kelas', 'akademik', 'dudi_pihak_lain', 'penelitian', 'Tambahan FKIP (bukan dari Permendikbud)'],
    ];

    public function run(): void
    {
        foreach (self::DATA as [$kode, $nama, $bidang, $ruang, $dharma, $pasal]) {
            BentukKerjaSama::updateOrCreate(
                ['kode' => $kode],
                ['nama' => $nama, 'bidang' => $bidang, 'ruang' => $ruang, 'dharma_default' => $dharma, 'pasal_rujukan' => $pasal],
            );
        }
    }
}
