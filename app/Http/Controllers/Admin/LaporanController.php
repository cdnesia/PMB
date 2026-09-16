<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Jalur;
use App\Models\Pendaftaran;
use App\Models\PendaftaranProdi;
use App\Models\Prodi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LaporanController extends Controller
{
    public function index(): View
    {
        // Ringkasan utama
        $ringkasan = [
            'total_pendaftar' => Pendaftaran::count(),
            'lunas' => Pendaftaran::where('status_pembayaran', 'lunas')->count(),
            'belum_bayar' => Pendaftaran::where('status_pembayaran', 'belum_bayar')->count(),
            'terverifikasi' => Pendaftaran::where('status', 'terverifikasi')->count(),
            'lolos' => Pendaftaran::where('status', 'lolos')->count(),
            'mahasiswa_baru' => Pendaftaran::where('status', 'mahasiswa_baru')->count(),
        ];

        // Rekap per jalur
        $perJalur = Jalur::withCount('kuota')
            ->orderBy('urutan')
            ->get()
            ->map(function ($j) {
                $pendaftar = Pendaftaran::where('jalur_id', $j->id);
                $j->pendaftar = (clone $pendaftar)->count();
                $j->lunas = (clone $pendaftar)->where('status_pembayaran', 'lunas')->count();
                $j->lolos = (clone $pendaftar)->where('status', 'lolos')->count();
                return $j;
            });

        // Rekap per prodi. Hanya pilihan 1 yang dihitung, karena hanya pilihan
        // itu yang mengurangi kuota prodi (lihat PendaftaranController::store).
        $perProdi = Prodi::orderBy('jenjang')->orderBy('nama')
            ->get()
            ->map(function ($p) {
                $pilihan = PendaftaranProdi::where('prodi_id', $p->id)->where('urutan', 1);
                $p->pendaftar = (clone $pilihan)->distinct('pendaftaran_id')->count('pendaftaran_id');
                $p->lolos = (clone $pilihan)->where('status', 'lolos')->count();
                return $p;
            });

        // Rekap per status
        $statusOrder = ['draft', 'menunggu_pembayaran', 'lunas', 'terverifikasi', 'lolos', 'cadangan', 'tidak_lolos', 'daftar_ulang', 'mahasiswa_baru', 'ditolak'];
        $perStatus = Pendaftaran::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $statusRekap = collect($statusOrder)->map(fn ($s) => [
            'status' => $s,
            'total' => $perStatus->get($s, 0),
        ])->values();

        return view('admin.laporan.index', compact('ringkasan', 'perJalur', 'perProdi', 'statusRekap'));
    }

    /**
     * Export rekap pendaftar lengkap ke file Excel (.xlsx):
     * identitas, alamat lengkap dengan kode wilayah, pendidikan asal,
     * jalur/gelombang, kode referral (jika ada), prodi yang dipilih, status
     * & nominal biaya pendaftaran serta biaya semester (daftar ulang) secara
     * terpisah, hingga tautan ke halaman detail & berkas pendaftar.
     */
    public function export(): StreamedResponse
    {
        $filename = 'laporan-pendaftar-'.now()->format('Y-m-d-His').'.xlsx';

        $columns = [
            'No', 'Nomor Pendaftaran', 'Nama', 'Email', 'No HP',
            'NIK', 'NISN', 'Tempat Lahir', 'Tanggal Lahir', 'Jenis Kelamin',
            'Alamat',
            'Provinsi', 'Kode Provinsi',
            'Kota/Kabupaten', 'Kode Kota/Kabupaten',
            'Kecamatan', 'Kode Kecamatan',
            'Kelurahan', 'Kode Kelurahan',
            'Kode Pos',
            'Asal Sekolah', 'Tahun Lulus',
            'Jalur', 'Gelombang',
            'Kode Referral',
            'Prodi Pilihan 1', 'Prodi Pilihan 2',
            'Status Pendaftaran',
            'Biaya Pendaftaran', 'Pembayaran Pendaftaran',
            'Biaya Semester', 'Pembayaran Semester',
            'Link Berkas',
        ];

        $linkBerkasColumn = Coordinate::stringFromColumnIndex(count($columns));
        $pembayaranPendaftaranColumn = Coordinate::stringFromColumnIndex(array_search('Pembayaran Pendaftaran', $columns, true) + 1);
        $pembayaranSemesterColumn = Coordinate::stringFromColumnIndex(array_search('Pembayaran Semester', $columns, true) + 1);

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Rekap Pendaftar');

        $sheet->fromArray($columns, null, 'A1');
        $sheet->getStyle('A1:'.$sheet->getHighestColumn().'1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '8B1A1A']],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->freezePane('A2');

        $no = 0;
        $rowNumber = 2;

        Pendaftaran::with(['user', 'pendaftar', 'jalur', 'gelombang', 'referrer', 'prodiPilihan.prodi', 'pembayaran', 'daftarUlang'])
            ->orderBy('nomor_pendaftaran')
            ->chunk(200, function ($rows) use ($sheet, $linkBerkasColumn, &$no, &$rowNumber) {
                foreach ($rows as $p) {
                    $no++;
                    $pendaftar = $p->pendaftar;

                    $namaProdi = fn ($pp) => ($pp->prodi?->jenjang ? $pp->prodi->jenjang.' - ' : '').($pp->prodi?->nama ?? '-');
                    $prodiPilihan1 = $p->prodiPilihan->firstWhere('urutan', 1);
                    $prodiPilihan2 = $p->prodiPilihan->firstWhere('urutan', 2);

                    $sheet->fromArray([
                        $no,
                        $p->nomor_pendaftaran,
                        $p->user->name ?? '',
                        $p->user->email ?? '',
                        $p->user->phone ?? '',
                        $pendaftar->nik ?? '',
                        $pendaftar->nisn ?? '',
                        $pendaftar->tempat_lahir ?? '',
                        $pendaftar?->tanggal_lahir?->format('d-m-Y') ?? '',
                        $pendaftar?->jenis_kelamin === 'L' ? 'Laki-laki' : ($pendaftar?->jenis_kelamin === 'P' ? 'Perempuan' : ''),
                        $pendaftar->alamat ?? '',
                        $pendaftar->provinsi ?? '',
                        $pendaftar->provinsi_kode ?? '',
                        $pendaftar->kota ?? '',
                        $pendaftar->kota_kode ?? '',
                        $pendaftar->kecamatan ?? '',
                        $pendaftar->kecamatan_kode ?? '',
                        $pendaftar->kelurahan ?? '',
                        $pendaftar->kelurahan_kode ?? '',
                        $pendaftar->kode_pos ?? '',
                        $pendaftar->asal_sekolah ?? '',
                        $pendaftar->tahun_lulus ?? '',
                        $p->jalur->nama ?? '',
                        $p->gelombang->nama ?? '',
                        $p->referrer->kode ?? '',
                        $prodiPilihan1 ? $namaProdi($prodiPilihan1) : '',
                        $prodiPilihan2 ? $namaProdi($prodiPilihan2) : '',
                        str_replace('_', ' ', $p->status),
                        str_replace('_', ' ', $p->status_pembayaran),
                        $p->pembayaran ? (float) $p->pembayaran->nominal : 0,
                        $p->daftarUlang ? str_replace('_', ' ', $p->daftarUlang->status) : 'belum daftar ulang',
                        $p->daftarUlang ? (float) $p->daftarUlang->nominal : 0,
                    ], null, 'A'.$rowNumber, true);

                    $berkasUrl = route('admin.pendaftar.show', $p);
                    $cell = $sheet->getCell($linkBerkasColumn.$rowNumber);
                    $cell->setValueExplicit('Lihat Berkas', DataType::TYPE_STRING);
                    $cell->getHyperlink()->setUrl($berkasUrl);
                    $sheet->getStyle($linkBerkasColumn.$rowNumber)->getFont()->setUnderline(true)->getColor()->setRGB('2563EB');

                    $rowNumber++;
                }
            });

        if ($rowNumber > 2) {
            $lastDataRow = $rowNumber - 1;
            $sheet->getStyle("{$pembayaranPendaftaranColumn}2:{$pembayaranPendaftaranColumn}{$lastDataRow}")
                ->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("{$pembayaranSemesterColumn}2:{$pembayaranSemesterColumn}{$lastDataRow}")
                ->getNumberFormat()->setFormatCode('#,##0');
        }

        for ($i = 1; $i <= Coordinate::columnIndexFromString($sheet->getHighestColumn()); $i++) {
            $col = Coordinate::stringFromColumnIndex($i);
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $callback = function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        };

        return new StreamedResponse($callback, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Daftar mahasiswa yang lolos, beserta prodi mana yang meluluskannya.
     * Diambil dari status per pilihan prodi (bukan status pendaftaran secara
     * umum), karena status "lolos" ditentukan per pilihan (lihat
     * PendaftarController::update, field prodi_status).
     */
    public function lolos(Request $request): View
    {
        $lolos = PendaftaranProdi::with(['pendaftaran.user', 'pendaftaran.jalur', 'prodi', 'kelas'])
            ->where('status', 'lolos')
            ->when($request->filled('prodi_id'), fn ($q) => $q->where('prodi_id', $request->prodi_id))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = $request->q;
                $q->whereHas('pendaftaran', function ($pq) use ($term) {
                    $pq->where('nomor_pendaftaran', 'like', "%{$term}%")
                        ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$term}%")->orWhere('email', 'like', "%{$term}%"));
                });
            })
            ->latest('updated_at')
            ->paginate(20)
            ->withQueryString();

        $prodiList = Prodi::orderBy('jenjang')->orderBy('nama')->get();

        return view('admin.laporan.lolos', compact('lolos', 'prodiList'));
    }
}
