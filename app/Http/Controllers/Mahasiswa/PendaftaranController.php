<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Agama;
use App\Models\DokumenPendaftar;
use App\Models\DokumenPersyaratan;
use App\Models\Gelombang;
use App\Models\Jalur;
use App\Models\JalurKelas;
use App\Models\JenisKelamin;
use App\Models\Kuota;
use App\Models\Pendaftar;
use App\Models\Pendaftaran;
use App\Models\PendaftaranProdi;
use App\Models\PendaftaranSyarat;
use App\Models\ProdiKelasJalur;
use App\Models\Promo;
use App\Models\SyaratJalur;
use App\Models\TahunPenerimaan;
use App\Models\Wilayah;
use App\Services\PendaftaranNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PendaftaranController extends Controller
{
    public function index(): View
    {
        $pendaftaran = Pendaftaran::with(['tahun', 'gelombang', 'jalur', 'prodiPilihan.prodi', 'prodiPilihan.kelas'])
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        return view('mahasiswa.pendaftaran.index', compact('pendaftaran'));
    }

    public function create(): View|RedirectResponse
    {
        // Satu akun hanya boleh memiliki satu pendaftaran — arahkan ke detail bila sudah ada.
        $existing = Pendaftaran::where('user_id', Auth::id())->latest()->first();

        if ($existing) {
            return redirect()
                ->route('mahasiswa.pendaftaran.show', $existing)
                ->with('info', 'Anda sudah pernah mendaftar. Berikut detail pendaftaran Anda.');
        }

        $tahun = TahunPenerimaan::where('status', 'aktif')->first();

        $today = now()->toDateString();
        $gelombang = Gelombang::with('jalur')
            ->where('is_active', true)
            ->where('tanggal_mulai', '<=', $today)
            ->where('tanggal_selesai', '>=', $today)
            ->orderBy('tanggal_mulai')
            ->get();

        $jalur = Jalur::where('is_active', true)->orderBy('urutan')->get();

        // Daftar jalur untuk kebutuhan client-side (nama + biaya default jalur)
        $jalurList = $jalur->map(fn ($j) => [
            'id' => $j->id,
            'nama' => $j->nama,
            'biaya_default' => (float) $j->biaya_pendaftaran,
        ])->values()->all();

        // Biaya pendaftaran per kombinasi jalur + kelas (override biaya default jalur).
        $biayaKelas = JalurKelas::all()
            ->groupBy('jalur_id')
            ->map(fn ($rows) => $rows->mapWithKeys(fn ($r) => [$r->kelas_id => (float) $r->biaya_pendaftaran])->all())
            ->all();

        // Matriks jalur yang tersedia per gelombang (untuk filter di client)
        $gelombangMap = $gelombang->map(fn ($g) => [
            'id' => $g->id,
            'nama' => $g->nama,
            'tanggal_selesai' => $g->tanggal_selesai?->format('d/m/Y'),
            'jalur_ids' => $g->jalur->pluck('id')->all(),
        ])->values()->all();

        $matriks = ProdiKelasJalur::with(['prodi', 'kelas'])
            ->whereHas('prodi', fn ($q) => $q->where('is_active', true))
            ->whereHas('kelas', fn ($q) => $q->where('is_active', true))
            ->whereHas('jalur', fn ($q) => $q->where('is_active', true))
            ->get()
            ->sortBy(fn ($r) => $r->prodi->jenjang.$r->prodi->nama);

        $matriksMap = $matriks->groupBy('jalur_id')->map(function ($rows) {
            return $rows->groupBy('prodi_id')->map(function ($prodiRows) {
                return [
                    'nama' => $prodiRows->first()->prodi->nama,
                    'jenjang' => $prodiRows->first()->prodi->jenjang,
                    'kelas' => $prodiRows->map(fn ($r) => [
                        'id' => $r->kelas_id,
                        'nama' => $r->kelas->nama,
                    ])->values()->all(),
                ];
            })->all();
        })->all();

        // Persyaratan dokumen (flat, dengan scope jalur/prodi untuk dicocokkan di client)
        $dokumen = DokumenPersyaratan::where('is_active', true)
            ->get()
            ->map(fn ($d) => $this->docShape($d))
            ->values();

        // Syarat khusus per jalur (field / file)
        $syaratMap = SyaratJalur::where('is_active', true)
            ->get()
            ->groupBy('jalur_id')
            ->map(fn ($group) => $group->map(fn ($s) => [
                'id' => $s->id,
                'tipe' => $s->tipe,
                'kode' => $s->kode,
                'nama' => $s->nama,
                'wajib' => (bool) $s->wajib,
            ])->values()->all())
            ->all();

        // Promo aktif yang berlaku untuk biaya pendaftaran (beserta ketentuan jalur+prodi+kelas)
        $promoList = Promo::with('ketentuan')
            ->where('is_active', true)
            ->whereIn('jenis', ['pendaftaran', 'semua'])
            ->where(function ($q) use ($today) {
                $q->whereNull('tanggal_mulai')->orWhere('tanggal_mulai', '<=', $today);
            })
            ->where(function ($q) use ($today) {
                $q->whereNull('tanggal_selesai')->orWhere('tanggal_selesai', '>=', $today);
            })
            ->orderBy('kode')
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'kode' => $p->kode,
                'nama' => $p->nama,
                'tipe' => $p->tipe,
                'nilai' => (float) $p->nilai,
                'maks_potongan' => $p->maks_potongan !== null ? (float) $p->maks_potongan : null,
                'is_global' => (bool) $p->is_global,
                'ketentuan' => $p->ketentuan->map(fn ($k) => [
                    'jalur_id' => $k->jalur_id,
                    'prodi_id' => $k->prodi_id,
                    'kelas_id' => $k->kelas_id,
                ])->values()->all(),
                'label' => $p->kode.' — '.$p->nama.' ('.$p->labelPotongan().')',
            ])->values()->all();

        // Daftar negara (untuk pendaftar WNA) — tidak termasuk Indonesia.
        $negaraList = Wilayah::where('level', Wilayah::LEVEL_NEGARA)
            ->where('kode', '!=', '000000')
            ->orderBy('kode')
            ->get();

        // Kamus referensi NEO Feeder untuk biodata (dengan fallback kosong bila API gagal).
        $refs = $this->neoReferences();

        return view('mahasiswa.pendaftaran.create', compact(
            'tahun',
            'gelombang',
            'gelombangMap',
            'jalur',
            'jalurList',
            'biayaKelas',
            'matriksMap',
            'dokumen',
            'syaratMap',
            'promoList',
            'negaraList',
            'refs'
        ));
    }

    /**
     * Ambil kamus referensi biodata dari database lokal.
     * Data referensi (contoh: agama) diinkronkan dari NEO Feeder oleh admin,
     * sehingga halaman pendaftaran tidak perlu memanggil NEO Feeder secara langsung.
     */
    private function neoReferences(): array
    {
        return [
            'agama' => Agama::orderBy('kode')
                ->get()
                ->map(fn (Agama $a) => [
                    'id_agama' => $a->kode,
                    'nama_agama' => $a->nama,
                ])
                ->all(),
            'jenis_kelamin' => JenisKelamin::orderBy('kode')
                ->get()
                ->map(fn (JenisKelamin $j) => [
                    'id_jenis_kelamin' => $j->kode,
                    'nama_jenis_kelamin' => $j->nama,
                ])
                ->all(),
        ];
    }

    private function docShape(DokumenPersyaratan $d): array
    {
        return [
            'id' => $d->id,
            'nama' => $d->nama,
            'wajib' => (bool) $d->wajib,
            'jalur_id' => $d->jalur_id,
            'prodi_id' => $d->prodi_id,
        ];
    }

    /**
     * Resolve nama dari kode referensi NEO Feeder (mis. id_agama → nama_agama).
     */
    private function refName(array $rows, string $idKey, string $namaKey, mixed $code): ?string
    {
        if ($code === null || $code === '') {
            return null;
        }

        foreach ($rows as $row) {
            if ((string) ($row[$idKey] ?? '') === (string) $code) {
                return $row[$namaKey] ?? null;
            }
        }

        return null;
    }

    public function store(Request $request): RedirectResponse
    {
        // Satu akun pendaftar hanya boleh memiliki satu pendaftaran.
        if (Pendaftaran::where('user_id', Auth::id())->exists()) {
            return back()->withErrors(['gelombang_id' => 'Anda sudah pernah mendaftar. Satu akun hanya dapat mendaftar satu kali.']);
        }

        $request->validate([
            'gelombang_id' => 'required|exists:gelombang,id',
            'jalur_id' => 'required|exists:jalur,id',
            'promo_id' => 'nullable|exists:promo,id',
            'prodi1' => 'required|exists:prodi,id',
            'kelas1' => 'required|exists:kelas_perkuliahan,id',
            'prodi2' => 'nullable|exists:prodi,id',
            'kelas2' => 'nullable|required_with:prodi2|exists:kelas_perkuliahan,id',
            'nik' => 'required|digits:16|unique:pendaftar,nik',
            'nisn' => 'required|digits:10|unique:pendaftar,nisn',
            'tempat_lahir' => 'required|string|max:100',
            'tanggal_lahir' => 'required|date|before:today',
            'jenis_kelamin' => 'required|in:L,P',
            'agama' => 'required|integer',
            'kewarganegaraan' => 'required|in:WNI,WNA',
            'negara' => 'nullable|required_if:kewarganegaraan,WNA|exists:wilayah,id',
            'alamat' => 'required|string|max:500',
            'rt' => 'nullable|string|max:5',
            'rw' => 'nullable|string|max:5',
            'dusun' => 'nullable|string|max:100',
            'provinsi' => 'required|exists:wilayah,id',
            'kota' => 'required|exists:wilayah,id',
            'kecamatan' => 'required|exists:wilayah,id',
            'kelurahan' => 'required|string|max:100',
            'kode_pos' => 'nullable|digits:5',
            'asal_sekolah' => 'required|string|max:150',
            'tahun_lulus' => 'nullable|digits:4',
        ]);

        // Pastikan "negara" yang dipilih benar-benar level negara (untuk WNA).
        if ($request->kewarganegaraan === 'WNA') {
            $negaraWilayah = Wilayah::find($request->negara);
            if (! $negaraWilayah || $negaraWilayah->level !== Wilayah::LEVEL_NEGARA) {
                return back()->withErrors(['negara' => 'Negara yang dipilih tidak valid.'])->withInput();
            }
        }

        $tahun = TahunPenerimaan::where('status', 'aktif')->first();
        if (! $tahun) {
            return back()->withErrors(['gelombang_id' => 'Belum ada tahun penerimaan aktif. Hubungi panitia.']);
        }

        $gelombang = Gelombang::with('jalur')->find($request->gelombang_id);

        // Validasi gelombang masih dibuka (aktif + dalam rentang tanggal)
        $today = now()->toDateString();
        if (! $gelombang
            || ! $gelombang->is_active
            || $gelombang->tanggal_mulai->toDateString() > $today
            || $gelombang->tanggal_selesai->toDateString() < $today) {
            return back()->withErrors(['gelombang_id' => 'Gelombang yang dipilih sudah ditutup atau belum dibuka.'])->withInput();
        }

        $jalurId = $request->input('jalur_id');

        // Pastikan jalur yang dipilih tersedia di gelombang ini
        if (! $gelombang->jalur->contains('id', $jalurId)) {
            return back()->withErrors(['jalur_id' => 'Jalur ini tidak tersedia pada gelombang yang dipilih.'])->withInput();
        }

        // Validasi promo (opsional) — harus aktif, dalam periode, berlaku untuk biaya
        // pendaftaran, dan cocok dengan kombinasi jalur + prodi + kelas yang dipilih.
        $promo = null;
        if ($request->filled('promo_id')) {
            $promo = Promo::with('ketentuan')->find($request->input('promo_id'));

            $cocok = false;
            if ($promo) {
                $cocok = $promo->is_global
                    || $promo->ketentuan->contains(fn ($k) => $k->jalur_id === $jalurId
                        && ($k->prodi_id === $request->input('prodi1') && $k->kelas_id === $request->input('kelas1')
                            || ($request->filled('prodi2') && $k->prodi_id === $request->input('prodi2') && $k->kelas_id === $request->input('kelas2'))));
            }

            if (! $promo || ! $promo->isBerlaku() || ! $promo->berlakuUntuk('pendaftaran') || ! $cocok) {
                return back()->withErrors(['promo_id' => 'Promo yang dipilih tidak valid, sudah tidak berlaku, atau tidak tersedia untuk pilihan jalur/prodi/kelas Anda.'])->withInput();
            }
        }

        $pilihan = [
            ['urutan' => 1, 'prodi_id' => $request->input('prodi1'), 'kelas_id' => $request->input('kelas1')],
        ];

        if ($request->filled('prodi2')) {
            $pilihan[] = ['urutan' => 2, 'prodi_id' => $request->input('prodi2'), 'kelas_id' => $request->input('kelas2')];
        }

        if (count($pilihan) === 2 && $pilihan[0]['prodi_id'] === $pilihan[1]['prodi_id']) {
            return back()->withErrors(['prodi2' => 'Prodi pilihan 2 tidak boleh sama dengan pilihan 1.'])->withInput();
        }

        foreach ($pilihan as $p) {
            $exists = ProdiKelasJalur::where('jalur_id', $jalurId)
                ->where('prodi_id', $p['prodi_id'])
                ->where('kelas_id', $p['kelas_id'])
                ->exists();

            if (! $exists) {
                return back()->withErrors(['kelas1' => 'Kombinasi jalur, prodi, dan kelas perkuliahan tidak tersedia.'])->withInput();
            }
        }

        // Kumpulkan dokumen yang wajib diunggah berdasarkan jalur + prodi pilihan
        $prodiIds = array_column($pilihan, 'prodi_id');
        $requiredDocs = $this->resolveRequiredDocuments($jalurId, $prodiIds);

        // Syarat khusus jalur terpilih
        $syarat = SyaratJalur::where('jalur_id', $jalurId)->where('is_active', true)->get();

        // Validasi file yang diunggah
        $request->validate([
            'dokumen' => 'nullable|array',
            'dokumen.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'syarat_field' => 'nullable|array',
            'syarat_file' => 'nullable|array',
            'syarat_file.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ], [
            'dokumen.*.mimes' => 'Format file harus JPG, PNG, atau PDF.',
            'dokumen.*.max' => 'Ukuran file maksimal 2 MB.',
            'syarat_file.*.mimes' => 'Format file harus JPG, PNG, atau PDF.',
            'syarat_file.*.max' => 'Ukuran file maksimal 2 MB.',
        ]);

        $uploads = $request->file('dokumen', []);
        foreach ($requiredDocs as $doc) {
            if ($doc->wajib && empty($uploads[$doc->id])) {
                return back()
                    ->withErrors(['dokumen.'.$doc->id => 'Dokumen "'.$doc->nama.'" wajib diunggah.'])
                    ->withInput();
            }
        }

        // Validasi syarat khusus (wajib field/file terisi)
        $syaratFieldInput = $request->input('syarat_field', []);
        $syaratFileInput = $request->file('syarat_file', []);
        foreach ($syarat as $s) {
            if (! $s->wajib) {
                continue;
            }
            if ($s->tipe === 'field' && empty(trim($syaratFieldInput[$s->id] ?? ''))) {
                return back()->withErrors(['syarat_field.'.$s->id => 'Syarat "'.$s->nama.'" wajib diisi.'])->withInput();
            }
            if ($s->tipe === 'file' && empty($syaratFileInput[$s->id])) {
                return back()->withErrors(['syarat_file.'.$s->id => 'Berkas "'.$s->nama.'" wajib diunggah.'])->withInput();
            }
        }

        // Kamus referensi NEO Feeder (di-cache) untuk resolve kode → nama.
        $refs = $this->neoReferences();

        try {
            $pendaftaran = DB::transaction(function () use ($request, $tahun, $gelombang, $jalurId, $promo, $pilihan, $requiredDocs, $uploads, $syarat, $syaratFieldInput, $syaratFileInput, $refs) {
                $pendaftaran = Pendaftaran::create([
                    'user_id' => Auth::id(),
                    'tahun_id' => $tahun->id,
                    'jalur_id' => $jalurId,
                    'gelombang_id' => $gelombang->id,
                    'promo_id' => $promo?->id,
                    'referrer_id' => Auth::user()->referrer_id,
                    'status' => 'draft',
                ]);

                // Simpan biodata pendaftar
                $wProv = Wilayah::find($request->provinsi);
                $wKota = Wilayah::find($request->kota);
                $wKec = Wilayah::find($request->kecamatan);
                $wNegara = $request->kewarganegaraan === 'WNA' ? Wilayah::find($request->negara) : null;

                Pendaftar::create([
                    'pendaftaran_id' => $pendaftaran->id,
                    'user_id' => Auth::id(),
                    'nik' => $request->nik,
                    'nisn' => $request->nisn,
                    'tempat_lahir' => $request->tempat_lahir,
                    'tanggal_lahir' => $request->tanggal_lahir,
                    'jenis_kelamin' => $request->jenis_kelamin,
                    'agama' => $this->refName($refs['agama'], 'id_agama', 'nama_agama', $request->agama),
                    'agama_kode' => $request->agama,
                    'kewarganegaraan' => $request->kewarganegaraan,
                    'negara' => $wNegara?->nama,
                    'negara_kode' => $wNegara?->kode,
                    'alamat' => $request->alamat,
                    'rt' => $request->rt,
                    'rw' => $request->rw,
                    'dusun' => $request->dusun,
                    'provinsi' => $wProv?->nama,
                    'provinsi_kode' => $wProv?->kode,
                    'kota' => $wKota?->nama,
                    'kota_kode' => $wKota?->kode,
                    'kecamatan' => $wKec?->nama,
                    'kecamatan_kode' => $wKec?->kode,
                    'kelurahan' => $request->kelurahan,
                    'kelurahan_kode' => null,
                    'kode_pos' => $request->kode_pos,
                    'asal_sekolah' => $request->asal_sekolah,
                    'tahun_lulus' => $request->tahun_lulus,
                ]);

                foreach ($pilihan as $p) {
                    $this->claimKuota($tahun->id, $jalurId, $p);

                    PendaftaranProdi::create([
                        'pendaftaran_id' => $pendaftaran->id,
                        'urutan' => $p['urutan'],
                        'prodi_id' => $p['prodi_id'],
                        'kelas_id' => $p['kelas_id'],
                    ]);
                }

                $this->saveDokumen($pendaftaran, $requiredDocs, $uploads);
                $this->saveSyarat($pendaftaran, $syarat, $syaratFieldInput, $syaratFileInput);

                // Nomor pendaftaran berurutan dari kolom auto-increment no_urut.
                $pendaftaran->refresh();
                $pendaftaran->update([
                    'nomor_pendaftaran' => sprintf('PMB-%s-%05d', $tahun->kode, $pendaftaran->no_urut),
                ]);

                return $pendaftaran;
            });
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        // Kirim notifikasi konfirmasi ke email pendaftar (tidak memblokir alur).
        app(PendaftaranNotificationService::class)->sendPendaftaranDiterima($pendaftaran);

        return redirect()
            ->route('mahasiswa.pendaftaran.show', $pendaftaran)
            ->with('success', 'Pendaftaran berhasil disimpan. Nomor pendaftaran Anda: '.$pendaftaran->nomor_pendaftaran);
    }

    /**
     * Resolusi dokumen yang berlaku untuk kombinasi jalur + prodi terpilih.
     * Suatu dokumen berlaku jika:
     *   - jalur_id kosong ATAU sama dengan jalur terpilih, DAN
     *   - prodi_id kosong ATAU termasuk dalam prodi terpilih.
     *
     * @return Collection<int, DokumenPersyaratan>
     */
    private function resolveRequiredDocuments(string $jalurId, array $prodiIds)
    {
        return DokumenPersyaratan::where('is_active', true)
            ->where(function ($q) use ($jalurId) {
                $q->whereNull('jalur_id')->orWhere('jalur_id', $jalurId);
            })
            ->where(function ($q) use ($prodiIds) {
                $q->whereNull('prodi_id')->orWhereIn('prodi_id', $prodiIds);
            })
            ->get();
    }

    private function saveDokumen(Pendaftaran $pendaftaran, $requiredDocs, array $uploads): void
    {
        foreach ($requiredDocs as $doc) {
            $file = $uploads[$doc->id] ?? null;

            DokumenPendaftar::create([
                'pendaftaran_id' => $pendaftaran->id,
                'dokumen_persyaratan_id' => $doc->id,
                'nama' => $doc->nama,
                'file_path' => $file ? $file->store('dokumen', 'public') : null,
                'file_name' => $file ? $file->getClientOriginalName() : null,
                'file_size' => $file ? $file->getSize() : null,
                'status' => $file ? 'menunggu' : 'belum_diunggah',
            ]);
        }
    }

    private function saveSyarat(Pendaftaran $pendaftaran, $syarat, array $fieldInput, array $fileInput): void
    {
        foreach ($syarat as $s) {
            $file = $fileInput[$s->id] ?? null;

            PendaftaranSyarat::create([
                'pendaftaran_id' => $pendaftaran->id,
                'syarat_jalur_id' => $s->id,
                'nilai' => $s->tipe === 'field' ? ($fieldInput[$s->id] ?? null) : null,
                'file_path' => $s->tipe === 'file' && $file ? $file->store('syarat', 'public') : null,
                'file_name' => $s->tipe === 'file' && $file ? $file->getClientOriginalName() : null,
                'file_size' => $s->tipe === 'file' && $file ? $file->getSize() : null,
            ]);
        }
    }

    public function show(Pendaftaran $pendaftaran): View
    {
        abort_unless($pendaftaran->user_id === Auth::id(), 403);

        $pendaftaran->load(['tahun', 'gelombang', 'jalur', 'promo', 'prodiPilihan.prodi', 'prodiPilihan.kelas', 'dokumen', 'pendaftar', 'daftarUlang', 'syaratJawaban.syarat', 'cbtSesi']);

        return view('mahasiswa.pendaftaran.show', compact('pendaftaran'));
    }

    /**
     * Mahasiswa yang dinyatakan lolos melakukan daftar ulang dengan
     * mengunggah bukti pembayaran uang kuliah (SPP).
     */
    public function daftarUlang(Request $request, Pendaftaran $pendaftaran): RedirectResponse
    {
        abort_unless($pendaftaran->user_id === Auth::id(), 403);

        if (! $pendaftaran->isLolos()) {
            return back()->withErrors(['daftar_ulang' => 'Anda belum dinyatakan lolos seleksi.'])->withInput();
        }

        $request->validate([
            'nominal' => 'required|numeric|min:0|max:999999999',
            'bukti_bayar' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ], [
            'bukti_bayar.required' => 'Bukti pembayaran wajib diunggah.',
            'bukti_bayar.mimes' => 'Format bukti harus JPG, PNG, atau PDF.',
            'bukti_bayar.max' => 'Ukuran file maksimal 2 MB.',
        ]);

        $file = $request->file('bukti_bayar');

        $daftarUlang = $pendaftaran->daftarUlang()->updateOrCreate(
            ['pendaftaran_id' => $pendaftaran->id],
            [
                'nominal' => $request->nominal,
                'status' => 'menunggu_verifikasi',
                'bukti_bayar' => $file->store('daftar_ulang', 'public'),
                'file_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'tanggal_bayar' => now()->toDateString(),
                'catatan' => null,
            ]
        );

        // Majukan status pendaftaran ke "daftar ulang"
        $pendaftaran->update(['status' => 'daftar_ulang']);

        return redirect()
            ->route('mahasiswa.pendaftaran.show', $pendaftaran)
            ->with('success', 'Bukti pembayaran daftar ulang berhasil dikirim. Menunggu verifikasi panitia.');
    }

    private function claimKuota(string $tahunId, string $jalurId, array $pilihan): void
    {
        $kuota = Kuota::where('tahun_id', $tahunId)
            ->where('jalur_id', $jalurId)
            ->where('prodi_id', $pilihan['prodi_id'])
            ->where(function ($q) use ($pilihan) {
                $q->where('kelas_id', $pilihan['kelas_id'])->orWhereNull('kelas_id');
            })
            ->where('is_active', true)
            ->orderByRaw('kelas_id IS NULL ASC')
            ->first();

        if (! $kuota) {
            return; // tidak ada kuota dikonfigurasi → dianggap tidak dibatasi
        }

        $updated = Kuota::whereKey($kuota->id)
            ->whereRaw('terpakai < jumlah')
            ->update(['terpakai' => DB::raw('terpakai + 1')]);

        if (! $updated) {
            throw ValidationException::withMessages([
                'prodi1' => 'Kuota untuk pilihan '.($pilihan['urutan']).' sudah penuh.',
            ]);
        }
    }
}
