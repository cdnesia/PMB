@extends('layouts.mahasiswa')

@section('title', 'Formulir Pendaftaran')

@section('content')
    <div class="mb-8">
        <h1 class="text-2xl font-bold tracking-tight text-gray-900">Formulir Pendaftaran</h1>
        <p class="mt-1 text-gray-500">Lengkapi langkah berikut untuk mendaftar. Anda dapat memilih maksimal 2 prodi.</p>
    </div>

    @if (! $tahun)
        <div class="rounded-xl border border-amber-200 bg-amber-50 p-6">
            <div class="flex items-start gap-3">
                <x-icon name="warning" class="h-6 w-6 text-amber-500" />
                <div>
                    <h2 class="text-sm font-semibold text-amber-800">Belum ada tahun penerimaan aktif</h2>
                    <p class="mt-1 text-sm text-amber-700">Silakan hubungi panitia untuk informasi lebih lanjut.</p>
                </div>
            </div>
        </div>
    @elseif ($gelombang->isEmpty())
        <div class="rounded-xl border border-amber-200 bg-amber-50 p-6">
            <div class="flex items-start gap-3">
                <x-icon name="warning" class="h-6 w-6 text-amber-500" />
                <div>
                    <h2 class="text-sm font-semibold text-amber-800">Belum ada gelombang yang sedang dibuka</h2>
                    <p class="mt-1 text-sm text-amber-700">Silakan hubungi panitia untuk informasi periode pendaftaran.</p>
                </div>
            </div>
        </div>
    @else
        <div x-data="pendaftaranForm()">
            <form method="POST" action="{{ route('mahasiswa.pendaftaran.store') }}" enctype="multipart/form-data" class="space-y-6">
                @csrf

                {{-- Langkah 1: Gelombang --}}
                <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
                    <div class="mb-4 flex items-center gap-3">
                        <span class="flex h-7 w-7 items-center justify-center rounded-full bg-indigo-600 text-xs font-bold text-white">1</span>
                        <h2 class="text-base font-semibold text-gray-900">Pilih Gelombang Pendaftaran</h2>
                    </div>

                    <div>
                        <x-ui-select name="gelombang_id" x-model="gelombangId" @change="jalurId = null" required>
                            <option value="">-- Pilih Gelombang --</option>
                            @foreach ($gelombang as $g)
                                <option value="{{ $g->id }}">{{ $g->nama }} — s/d {{ $g->tanggal_selesai->format('d/m/Y') }}</option>
                            @endforeach
                        </x-ui-select>
                    </div>
                    @error('gelombang_id')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Langkah 2: Jalur (difilter sesuai gelombang) --}}
                <template x-if="gelombangId">
                    <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
                        <div class="mb-4 flex items-center gap-3">
                            <span class="flex h-7 w-7 items-center justify-center rounded-full bg-indigo-600 text-xs font-bold text-white">2</span>
                            <h2 class="text-base font-semibold text-gray-900">Pilih Jalur Penerimaan</h2>
                        </div>

                        <div>
                            <x-ui-select name="jalur_id" x-model="jalurId" @change="promoId = null" required>
                                <option value="">-- Pilih Jalur --</option>
                                <template x-for="j in availableJalur" :key="j.id">
                                    <option :value="j.id" x-text="j.label"></option>
                                </template>
                            </x-ui-select>
                        </div>

                        <template x-if="selectedJalur">
                            <div class="mt-3 flex items-center gap-2 rounded-lg bg-indigo-50 px-4 py-3 text-sm text-indigo-700">
                                <x-icon name="info" class="h-5 w-5 shrink-0" />
                                Biaya pendaftaran ditentukan berdasarkan kelas perkuliahan yang Anda pilih pada langkah berikutnya.
                            </div>
                        </template>
                    </div>
                </template>

                {{-- Langkah 3: Data Diri (muncul setelah pilih jalur) --}}
                <template x-if="jalurId">
                    <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
                        <div class="mb-4 flex items-center gap-3">
                            <span class="flex h-7 w-7 items-center justify-center rounded-full bg-indigo-600 text-xs font-bold text-white">3</span>
                            <h2 class="text-base font-semibold text-gray-900">Data Diri</h2>
                        </div>

                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <x-ui-label for="nik" required>NIK</x-ui-label>
                                <div class="mt-2">
                                    <x-ui-input name="nik" id="nik" :value="old('nik')" required inputmode="numeric" maxlength="16" placeholder="16 digit NIK" />
                                </div>
                                @error('nik')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <x-ui-label for="nisn" required>NISN</x-ui-label>
                                <div class="mt-2">
                                    <x-ui-input name="nisn" id="nisn" :value="old('nisn')" required inputmode="numeric" maxlength="10" placeholder="10 digit NISN" />
                                </div>
                                @error('nisn')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <x-ui-label for="tempat_lahir" required>Tempat Lahir</x-ui-label>
                                <div class="mt-2">
                                    <x-ui-input name="tempat_lahir" id="tempat_lahir" :value="old('tempat_lahir')" required placeholder="Kota / Kabupaten" />
                                </div>
                                @error('tempat_lahir')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <x-ui-label for="tanggal_lahir" required>Tanggal Lahir</x-ui-label>
                                <div class="mt-2">
                                    <x-ui-input type="date" name="tanggal_lahir" id="tanggal_lahir" :value="old('tanggal_lahir')" required />
                                </div>
                                @error('tanggal_lahir')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <x-ui-label for="jenis_kelamin" required>Jenis Kelamin</x-ui-label>
                                <div class="mt-2">
                                    <x-ui-select name="jenis_kelamin" id="jenis_kelamin" required>
                                        <option value="">-- Pilih --</option>
                                        <option value="L" @selected(old('jenis_kelamin') === 'L')>Laki-laki</option>
                                        <option value="P" @selected(old('jenis_kelamin') === 'P')>Perempuan</option>
                                    </x-ui-select>
                                </div>
                                @error('jenis_kelamin')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <x-ui-label for="agama" required>Agama</x-ui-label>
                                <div class="mt-2">
                                    <x-ui-select name="agama" id="agama" required>
                                        <option value="">-- Pilih --</option>
                                        @foreach ($refs['agama'] as $a)
                                            <option value="{{ $a['id_agama'] }}" @selected((string) old('agama') === (string) $a['id_agama'])>{{ $a['nama_agama'] }}</option>
                                        @endforeach
                                    </x-ui-select>
                                </div>
                                @error('agama')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <x-ui-label for="kewarganegaraan" required>Kewarganegaraan</x-ui-label>
                                <div class="mt-2">
                                    <x-ui-select name="kewarganegaraan" id="kewarganegaraan" x-model="kewarganegaraan" required>
                                        <option value="WNI" @selected(old('kewarganegaraan', 'WNI') === 'WNI')>WNI (Indonesia)</option>
                                        <option value="WNA" @selected(old('kewarganegaraan') === 'WNA')>WNA (Asing)</option>
                                    </x-ui-select>
                                </div>
                            </div>

                            <div x-show="kewarganegaraan === 'WNA'" x-cloak>
                                <x-ui-label for="negara" required>Negara Asal</x-ui-label>
                                <div class="mt-2">
                                    <x-ui-select name="negara" id="negara">
                                        <option value="">-- Pilih Negara --</option>
                                        @foreach ($negaraList as $n)
                                            <option value="{{ $n->id }}" @selected(old('negara') == $n->id)>{{ $n->nama }}</option>
                                        @endforeach
                                    </x-ui-select>
                                </div>
                                @error('negara')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <div class="md:col-span-2">
                                <x-ui-label for="alamat" required>Alamat Lengkap (Jalan)</x-ui-label>
                                <div class="mt-2">
                                    <textarea name="alamat" id="alamat" rows="2" required
                                              class="block w-full rounded-lg border-0 px-3 py-2.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                                              placeholder="Nama jalan dan nomor rumah">{{ old('alamat') }}</textarea>
                                </div>
                                @error('alamat')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <x-ui-label for="rt">RT</x-ui-label>
                                <div class="mt-2">
                                    <x-ui-input name="rt" id="rt" :value="old('rt')" inputmode="numeric" maxlength="5" placeholder="001" />
                                </div>
                            </div>

                            <div>
                                <x-ui-label for="rw">RW</x-ui-label>
                                <div class="mt-2">
                                    <x-ui-input name="rw" id="rw" :value="old('rw')" inputmode="numeric" maxlength="5" placeholder="001" />
                                </div>
                            </div>

                            <div class="md:col-span-2">
                                <x-ui-label for="dusun">Dusun / Kampung</x-ui-label>
                                <div class="mt-2">
                                    <x-ui-input name="dusun" id="dusun" :value="old('dusun')" placeholder="Nama dusun/kampung (opsional)" />
                                </div>
                            </div>

                            <div>
                                <x-ui-label for="jenis_tinggal">Jenis Tinggal</x-ui-label>
                                <div class="mt-2">
                                    <x-ui-select name="jenis_tinggal" id="jenis_tinggal">
                                        <option value="">-- Pilih --</option>
                                        @foreach ($refs['jenis_tinggal'] as $r)
                                            <option value="{{ $r['id_jenis_tinggal'] }}" @selected((string) old('jenis_tinggal') === (string) $r['id_jenis_tinggal'])>{{ $r['nama_jenis_tinggal'] }}</option>
                                        @endforeach
                                    </x-ui-select>
                                </div>
                            </div>

                            <div>
                                <x-ui-label for="alat_transportasi">Alat Transportasi</x-ui-label>
                                <div class="mt-2">
                                    <x-ui-select name="alat_transportasi" id="alat_transportasi">
                                        <option value="">-- Pilih --</option>
                                        @foreach ($refs['alat_transportasi'] as $r)
                                            <option value="{{ $r['id_alat_transportasi'] }}" @selected((string) old('alat_transportasi') === (string) $r['id_alat_transportasi'])>{{ $r['nama_alat_transportasi'] }}</option>
                                        @endforeach
                                    </x-ui-select>
                                </div>
                            </div>

                            <div>
                                <x-ui-label for="pembiayaan">Pembiayaan</x-ui-label>
                                <div class="mt-2">
                                    <x-ui-select name="pembiayaan" id="pembiayaan">
                                        <option value="">-- Pilih --</option>
                                        @foreach ($refs['pembiayaan'] as $r)
                                            <option value="{{ $r['id_pembiayaan'] }}" @selected((string) old('pembiayaan') === (string) $r['id_pembiayaan'])>{{ $r['nama_pembiayaan'] }}</option>
                                        @endforeach
                                    </x-ui-select>
                                </div>
                            </div>

                            <div x-show="kewarganegaraan === 'WNI'" class="md:col-span-2 grid grid-cols-1 gap-6 md:grid-cols-2">
                                <div>
                                    <x-ui-label for="provinsi" required>Provinsi</x-ui-label>
                                    <div class="mt-2">
                                        <select name="provinsi" id="provinsi" x-select2="{ ajax: wilayahOptions(null), placeholder: 'Pilih provinsi' }" @change="wilayahChanged('provinsi')" required>
                                            <option value="">-- Pilih Provinsi --</option>
                                        </select>
                                    </div>
                                    @error('provinsi')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                                </div>

                                <div>
                                    <x-ui-label for="kota" required>Kota / Kabupaten</x-ui-label>
                                    <div class="mt-2">
                                        <select name="kota" id="kota" x-select2="{ ajax: wilayahOptions('provinsi'), placeholder: 'Pilih kota/kabupaten' }" @change="wilayahChanged('kota')" required>
                                            <option value="">-- Pilih Kota/Kabupaten --</option>
                                        </select>
                                    </div>
                                    @error('kota')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                                </div>

                                <div>
                                    <x-ui-label for="kecamatan" required>Kecamatan</x-ui-label>
                                    <div class="mt-2">
                                        <select name="kecamatan" id="kecamatan" x-select2="{ ajax: wilayahOptions('kota'), placeholder: 'Pilih kecamatan' }" @change="wilayahChanged('kecamatan')" required>
                                            <option value="">-- Pilih Kecamatan --</option>
                                        </select>
                                    </div>
                                    @error('kecamatan')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                                </div>

                                <div>
                                    <x-ui-label for="kelurahan">Kelurahan / Desa</x-ui-label>
                                    <div class="mt-2">
                                        <x-ui-input name="kelurahan" id="kelurahan" :value="old('kelurahan')" placeholder="Nama kelurahan/desa (opsional)" />
                                    </div>
                                    <p class="mt-1 text-xs text-gray-400">Opsional — isi nama kelurahan/desa tempat tinggal.</p>
                                </div>
                            </div>

                            <div>
                                <x-ui-label for="kode_pos">Kode Pos</x-ui-label>
                                <div class="mt-2">
                                    <x-ui-input name="kode_pos" id="kode_pos" :value="old('kode_pos')" inputmode="numeric" maxlength="5" placeholder="5 digit" />
                                </div>
                                @error('kode_pos')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <x-ui-label for="asal_sekolah" required>Asal Sekolah</x-ui-label>
                                <div class="mt-2">
                                    <x-ui-input name="asal_sekolah" id="asal_sekolah" :value="old('asal_sekolah')" required placeholder="Nama SMA/SMK/MA" />
                                </div>
                                @error('asal_sekolah')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <x-ui-label for="tahun_lulus">Tahun Lulus</x-ui-label>
                                <div class="mt-2">
                                    <x-ui-input name="tahun_lulus" id="tahun_lulus" :value="old('tahun_lulus')" inputmode="numeric" maxlength="4" placeholder="2026" />
                                </div>
                                @error('tahun_lulus')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <x-ui-label for="nama_ayah">Nama Ayah</x-ui-label>
                                <div class="mt-2">
                                    <x-ui-input name="nama_ayah" id="nama_ayah" :value="old('nama_ayah')" placeholder="Nama ayah" />
                                </div>
                            </div>

                            <div>
                                <x-ui-label for="nama_ibu_kandung" required>Nama Ibu Kandung</x-ui-label>
                                <div class="mt-2">
                                    <x-ui-input name="nama_ibu_kandung" id="nama_ibu_kandung" :value="old('nama_ibu_kandung')" required placeholder="Nama ibu kandung" />
                                </div>
                                @error('nama_ibu_kandung')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <x-ui-label for="nama_wali">Nama Wali</x-ui-label>
                                <div class="mt-2">
                                    <x-ui-input name="nama_wali" id="nama_wali" :value="old('nama_wali')" placeholder="Nama wali (jika ada)" />
                                </div>
                            </div>

                            <div>
                                <x-ui-label for="nik_ayah">NIK Ayah</x-ui-label>
                                <div class="mt-2">
                                    <x-ui-input name="nik_ayah" id="nik_ayah" :value="old('nik_ayah')" inputmode="numeric" maxlength="16" placeholder="16 digit NIK ayah" />
                                </div>
                                @error('nik_ayah')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <x-ui-label for="nik_ibu">NIK Ibu</x-ui-label>
                                <div class="mt-2">
                                    <x-ui-input name="nik_ibu" id="nik_ibu" :value="old('nik_ibu')" inputmode="numeric" maxlength="16" placeholder="16 digit NIK ibu" />
                                </div>
                                @error('nik_ibu')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <x-ui-label for="nik_wali">NIK Wali</x-ui-label>
                                <div class="mt-2">
                                    <x-ui-input name="nik_wali" id="nik_wali" :value="old('nik_wali')" inputmode="numeric" maxlength="16" placeholder="16 digit NIK wali" />
                                </div>
                                @error('nik_wali')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <x-ui-label for="pekerjaan_ayah">Pekerjaan Ayah</x-ui-label>
                                <div class="mt-2">
                                    <x-ui-select name="pekerjaan_ayah" id="pekerjaan_ayah">
                                        <option value="">-- Pilih --</option>
                                        @foreach ($refs['pekerjaan'] as $r)
                                            <option value="{{ $r['id_pekerjaan'] }}" @selected((string) old('pekerjaan_ayah') === (string) $r['id_pekerjaan'])>{{ $r['nama_pekerjaan'] }}</option>
                                        @endforeach
                                    </x-ui-select>
                                </div>
                            </div>

                            <div>
                                <x-ui-label for="penghasilan_ayah">Penghasilan Ayah</x-ui-label>
                                <div class="mt-2">
                                    <x-ui-select name="penghasilan_ayah" id="penghasilan_ayah">
                                        <option value="">-- Pilih --</option>
                                        @foreach ($refs['penghasilan'] as $r)
                                            <option value="{{ $r['id_penghasilan'] }}" @selected((string) old('penghasilan_ayah') === (string) $r['id_penghasilan'])>{{ $r['nama_penghasilan'] }}</option>
                                        @endforeach
                                    </x-ui-select>
                                </div>
                            </div>

                            <div>
                                <x-ui-label for="pekerjaan_ibu">Pekerjaan Ibu</x-ui-label>
                                <div class="mt-2">
                                    <x-ui-select name="pekerjaan_ibu" id="pekerjaan_ibu">
                                        <option value="">-- Pilih --</option>
                                        @foreach ($refs['pekerjaan'] as $r)
                                            <option value="{{ $r['id_pekerjaan'] }}" @selected((string) old('pekerjaan_ibu') === (string) $r['id_pekerjaan'])>{{ $r['nama_pekerjaan'] }}</option>
                                        @endforeach
                                    </x-ui-select>
                                </div>
                            </div>

                            <div>
                                <x-ui-label for="penghasilan_ibu">Penghasilan Ibu</x-ui-label>
                                <div class="mt-2">
                                    <x-ui-select name="penghasilan_ibu" id="penghasilan_ibu">
                                        <option value="">-- Pilih --</option>
                                        @foreach ($refs['penghasilan'] as $r)
                                            <option value="{{ $r['id_penghasilan'] }}" @selected((string) old('penghasilan_ibu') === (string) $r['id_penghasilan'])>{{ $r['nama_penghasilan'] }}</option>
                                        @endforeach
                                    </x-ui-select>
                                </div>
                            </div>

                            <div>
                                <x-ui-label for="pekerjaan_wali">Pekerjaan Wali</x-ui-label>
                                <div class="mt-2">
                                    <x-ui-select name="pekerjaan_wali" id="pekerjaan_wali">
                                        <option value="">-- Pilih --</option>
                                        @foreach ($refs['pekerjaan'] as $r)
                                            <option value="{{ $r['id_pekerjaan'] }}" @selected((string) old('pekerjaan_wali') === (string) $r['id_pekerjaan'])>{{ $r['nama_pekerjaan'] }}</option>
                                        @endforeach
                                    </x-ui-select>
                                </div>
                            </div>

                            <div>
                                <x-ui-label for="penghasilan_wali">Penghasilan Wali</x-ui-label>
                                <div class="mt-2">
                                    <x-ui-select name="penghasilan_wali" id="penghasilan_wali">
                                        <option value="">-- Pilih --</option>
                                        @foreach ($refs['penghasilan'] as $r)
                                            <option value="{{ $r['id_penghasilan'] }}" @selected((string) old('penghasilan_wali') === (string) $r['id_penghasilan'])>{{ $r['nama_penghasilan'] }}</option>
                                        @endforeach
                                    </x-ui-select>
                                </div>
                            </div>

                            <div>
                                <x-ui-label for="golongan_darah">Golongan Darah</x-ui-label>
                                <div class="mt-2">
                                    <x-ui-select name="golongan_darah" id="golongan_darah">
                                        <option value="">-- Pilih --</option>
                                        @foreach (['A', 'B', 'AB', 'O', '-'] as $g)
                                            <option value="{{ $g }}" @selected(old('golongan_darah') === $g)>{{ $g === '-' ? 'Tidak Tahu' : $g }}</option>
                                        @endforeach
                                    </x-ui-select>
                                </div>
                            </div>

                            <div>
                                <x-ui-label for="status_perkawinan">Status Perkawinan</x-ui-label>
                                <div class="mt-2">
                                    <x-ui-select name="status_perkawinan" id="status_perkawinan">
                                        <option value="">-- Pilih --</option>
                                        @foreach (['belum_kawin' => 'Belum Kawin', 'kawin' => 'Kawin', 'cerai_hidup' => 'Cerai Hidup', 'cerai_mati' => 'Cerai Mati'] as $val => $label)
                                            <option value="{{ $val }}" @selected(old('status_perkawinan') === $val)>{{ $label }}</option>
                                        @endforeach
                                    </x-ui-select>
                                </div>
                            </div>

                            <div>
                                <x-ui-label for="kebutuhan_khusus">Kebutuhan Khusus</x-ui-label>
                                <div class="mt-2">
                                    <x-ui-select name="kebutuhan_khusus" id="kebutuhan_khusus">
                                        <option value="">-- Tidak Ada --</option>
                                        @foreach (['Tunanetra', 'Tunarungu', 'Tunadaksa', 'Tunagrahita', 'Kesulitan Belajar', 'Lainnya'] as $k)
                                            <option value="{{ $k }}" @selected(old('kebutuhan_khusus') === $k)>{{ $k }}</option>
                                        @endforeach
                                    </x-ui-select>
                                </div>
                            </div>

                            <div class="md:col-span-2">
                                <label class="flex cursor-pointer select-none items-start gap-3 rounded-lg border border-gray-200 px-4 py-3">
                                    <input type="checkbox" name="penerima_kps" id="penerima_kps" value="1" x-model="penerimaKps"
                                           @checked(old('penerima_kps'))
                                           class="peer mt-0.5 h-5 w-5 shrink-0 rounded-md border-gray-300 text-indigo-600 focus:ring-indigo-600">
                                    <span>
                                        <span class="block text-sm font-medium text-gray-900">Penerima KPS / KIP-K</span>
                                        <span class="block text-xs text-gray-500">Centang jika Anda pemegang Kartu Perlindungan Sosial / KIP Kuliah.</span>
                                    </span>
                                </label>
                            </div>

                            <div x-show="penerimaKps" x-cloak class="md:col-span-2">
                                <x-ui-label for="nomor_kps">Nomor KPS / KIP-K</x-ui-label>
                                <div class="mt-2">
                                    <x-ui-input name="nomor_kps" id="nomor_kps" :value="old('nomor_kps')" placeholder="Nomor kartu KPS/KIP-K" />
                                </div>
                                @error('nomor_kps')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </div>
                </template>

                {{-- Langkah 4 & 5: Pilihan Prodi --}}
                <template x-if="jalurId">
                    <div class="space-y-6">
                        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
                            <div class="mb-4 flex items-center gap-3">
                                <span class="flex h-7 w-7 items-center justify-center rounded-full bg-indigo-600 text-xs font-bold text-white">4</span>
                                <h2 class="text-base font-semibold text-gray-900">Pilihan Prodi 1 & Kelas Perkuliahan</h2>
                            </div>

                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                <div>
                                    <x-ui-label for="prodi1" required>Program Studi (Pilihan 1)</x-ui-label>
                                    <div class="mt-2">
                                        <x-ui-select name="prodi1" id="prodi1" x-model="prodi1" @change="kelas1 = ''; promoId = null" required>
                                            <option value="">-- Pilih Prodi --</option>
                                            <template x-for="p in prodiOptions" :key="p.id">
                                                <option :value="p.id" x-text="p.jenjang ? p.jenjang + ' - ' + p.nama : p.nama"></option>
                                            </template>
                                        </x-ui-select>
                                    </div>
                                </div>
                                <div>
                                    <x-ui-label for="kelas1" required>Kelas Perkuliahan</x-ui-label>
                                    <div class="mt-2">
                                        <x-ui-select name="kelas1" id="kelas1" x-model="kelas1" @change="promoId = null" required>
                                            <option value="">-- Pilih Kelas --</option>
                                            <template x-for="k in kelasFor(prodi1)" :key="k.id">
                                                <option :value="k.id" x-text="k.nama"></option>
                                            </template>
                                        </x-ui-select>
                                    </div>
                                </div>
                            </div>

                            <template x-if="kelas1">
                                <div class="mt-4 flex items-center justify-between rounded-lg bg-indigo-50 px-4 py-3 text-sm">
                                    <span class="text-indigo-700">
                                        Biaya pendaftaran
                                        <span x-show="selectedJalur" class="font-medium" x-text="'(' + selectedJalur.nama + ' · ' + namaKelas(kelas1) + ')'"></span>
                                    </span>
                                    <span class="font-semibold text-indigo-800" x-text="'Rp ' + formatRupiah(biayaAktif)"></span>
                                </div>
                            </template>
                        </div>

                        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
                            <div class="mb-4 flex items-center gap-3">
                                <span class="flex h-7 w-7 items-center justify-center rounded-full bg-gray-200 text-xs font-bold text-gray-600">5</span>
                                <h2 class="text-base font-semibold text-gray-900">Pilihan Prodi 2 & Kelas <span class="font-normal text-gray-400">(opsional)</span></h2>
                            </div>

                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                <div>
                                    <x-ui-label for="prodi2">Program Studi (Pilihan 2)</x-ui-label>
                                    <div class="mt-2">
                                        <x-ui-select name="prodi2" id="prodi2" x-model="prodi2" @change="kelas2 = ''; promoId = null">
                                            <option value="">-- Kosongkan jika tidak ada --</option>
                                            <template x-for="p in prodiOptions" :key="p.id">
                                                <option :value="p.id" x-text="p.jenjang ? p.jenjang + ' - ' + p.nama : p.nama"></option>
                                            </template>
                                        </x-ui-select>
                                    </div>
                                </div>
                                <div>
                                    <x-ui-label for="kelas2">Kelas Perkuliahan</x-ui-label>
                                    <div class="mt-2">
                                        <x-ui-select name="kelas2" id="kelas2" x-model="kelas2" @change="promoId = null" :required="false">
                                            <option value="">-- Pilih Kelas --</option>
                                            <template x-for="k in kelasFor(prodi2)" :key="k.id">
                                                <option :value="k.id" x-text="k.nama"></option>
                                            </template>
                                        </x-ui-select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Promo (opsional, muncul setelah pilih jalur + prodi + kelas) --}}
                        <template x-if="jalurId && availablePromo.length > 0">
                            <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
                                <div class="mb-4 flex items-center gap-3">
                                    <span class="flex h-7 w-7 items-center justify-center rounded-full bg-emerald-600 text-xs font-bold text-white">
                                        <x-icon name="credit-card" class="h-4 w-4" />
                                    </span>
                                    <h2 class="text-base font-semibold text-gray-900">Gunakan Promo <span class="font-normal text-gray-400">(opsional)</span></h2>
                                </div>

                                <x-ui-select name="promo_id" x-model="promoId">
                                    <option value="">-- Tanpa Promo --</option>
                                    <template x-for="p in availablePromo" :key="p.id">
                                        <option :value="p.id" x-text="p.label"></option>
                                    </template>
                                </x-ui-select>
                                @error('promo_id')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror

                                <template x-if="selectedPromo">
                                    <div class="mt-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm">
                                        <div class="flex items-center justify-between">
                                            <span class="text-emerald-700">Biaya pendaftaran</span>
                                            <span class="font-semibold text-gray-900" x-text="'Rp ' + formatRupiah(biayaAktif)"></span>
                                        </div>
                                        <div class="mt-2 flex items-center justify-between">
                                            <span class="text-emerald-700">Potongan promo <span class="font-semibold" x-text="selectedPromo.kode"></span></span>
                                            <span class="font-semibold text-emerald-600" x-text="'- Rp ' + formatRupiah(potonganPendaftaran())"></span>
                                        </div>
                                        <div class="mt-3 flex items-center justify-between border-t border-emerald-200 pt-3">
                                            <span class="font-medium text-emerald-800">Total bayar</span>
                                            <span class="text-base font-bold text-emerald-800" x-text="'Rp ' + formatRupiah(biayaAkhir)"></span>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </template>

                        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
                            <div class="mb-4 flex items-center gap-3">
                                <span class="flex h-7 w-7 items-center justify-center rounded-full bg-gray-200 text-xs font-bold text-gray-600">6</span>
                                <h2 class="text-base font-semibold text-gray-900">Upload Dokumen Persyaratan</h2>
                            </div>

                            <template x-if="requiredDocuments.length > 0">
                                <div class="space-y-4">
                                    <p class="text-sm text-gray-500">Unggah dokumen berikut sesuai persyaratan jalur dan prodi pilihan Anda.</p>

                                    <template x-for="doc in requiredDocuments" :key="doc.id">
                                        <div class="rounded-lg border border-gray-200 p-4">
                                            <div class="mb-2 flex items-center justify-between">
                                                <div class="flex items-center gap-2">
                                                    <x-icon name="document" class="h-4 w-4 text-gray-400" />
                                                    <span class="text-sm font-medium text-gray-900" x-text="doc.nama"></span>
                                                    <span x-show="doc.wajib" class="rounded bg-red-50 px-1.5 py-0.5 text-[11px] font-semibold text-red-600">Wajib</span>
                                                    <span x-show="!doc.wajib" class="rounded bg-gray-100 px-1.5 py-0.5 text-[11px] font-semibold text-gray-500">Opsional</span>
                                                </div>
                                            </div>
                                            <input type="file"
                                                   :name="'dokumen[' + doc.id + ']'"
                                                   :required="doc.wajib"
                                                   accept=".jpg,.jpeg,.png,.pdf"
                                                   class="block w-full text-sm text-gray-600 file:mr-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100">
                                        </div>
                                    </template>
                                </div>
                            </template>

                            <template x-if="requiredDocuments.length === 0">
                                <p class="text-sm text-gray-400">Tidak ada dokumen yang perlu diunggah untuk pilihan ini.</p>
                            </template>
                        </div>

                        {{-- Syarat Khusus Jalur (muncul jika jalur terpilih punya syarat) --}}
                        <template x-if="jalurId && jalurSyarat.length > 0">
                            <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
                                <div class="mb-4 flex items-center gap-3">
                                    <span class="flex h-7 w-7 items-center justify-center rounded-full bg-indigo-50 text-indigo-600">
                                        <x-icon name="document" class="h-4 w-4" />
                                    </span>
                                    <h2 class="text-base font-semibold text-gray-900">Syarat Khusus Jalur</h2>
                                </div>

                                <div class="space-y-4">
                                    <template x-for="s in jalurSyarat" :key="s.id">
                                        <div class="rounded-lg border border-gray-200 p-4">
                                            <div class="mb-2 flex items-center gap-2">
                                                <x-icon name="document" class="h-4 w-4 text-gray-400" />
                                                <span class="text-sm font-medium text-gray-900" x-text="s.nama"></span>
                                                <span x-show="s.wajib" class="rounded bg-red-50 px-1.5 py-0.5 text-[11px] font-semibold text-red-600">Wajib</span>
                                                <span x-show="!s.wajib" class="rounded bg-gray-100 px-1.5 py-0.5 text-[11px] font-semibold text-gray-500">Opsional</span>
                                            </div>

                                            <template x-if="s.tipe === 'field'">
                                                <input type="text"
                                                       :name="'syarat_field[' + s.id + ']'"
                                                       :placeholder="'Masukkan ' + s.nama"
                                                       :required="s.wajib"
                                                       class="block w-full rounded-lg border-0 px-3 py-2.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                                            </template>

                                            <template x-if="s.tipe === 'file'">
                                                <input type="file"
                                                       :name="'syarat_file[' + s.id + ']'"
                                                       :required="s.wajib"
                                                       accept=".jpg,.jpeg,.png,.pdf"
                                                       class="block w-full text-sm text-gray-600 file:mr-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100">
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>

                        <div class="flex justify-end">
                            <x-ui-button variant="primary" icon="check">Simpan Pendaftaran</x-ui-button>
                        </div>
                    </div>
                </template>
            </form>
        </div>
    @endif
@endsection

@push('scripts')
<script>
    function pendaftaranForm() {
        return {
            gelombangId: null,
            jalurId: null,
            promoId: null,
            gelombangMap: @json($gelombangMap),
            matriks: @json($matriksMap),
            jalur: @json($jalurList),
            biayaKelas: @json($biayaKelas),
            promo: @json($promoList),
            prodi1: '', kelas1: '', prodi2: '', kelas2: '',
            kewarganegaraan: "{{ old('kewarganegaraan', 'WNI') }}",
            penerimaKps: {{ old('penerima_kps') ? 'true' : 'false' }},
            dokumen: @json($dokumen),
            syarat: @json($syaratMap),
            wilayah: { provinsi: null, kota: null, kecamatan: null },
            wilayahOptions(parentSource) {
                const self = this;
                return {
                    url: '/wilayah',
                    dataType: 'json',
                    delay: 200,
                    cache: true,
                    data() {
                        return { parent_id: parentSource ? self.wilayah[parentSource] : null };
                    },
                    processResults(data) {
                        return { results: data.map(w => ({ id: w.id, text: w.nama })) };
                    },
                };
            },
            wilayahChanged(field) {
                const el = document.querySelector('select[name="' + field + '"]');
                this.wilayah[field] = el ? el.value : null;

                if (field === 'provinsi') {
                    this.clearWilayah('kota');
                    this.clearWilayah('kecamatan');
                } else if (field === 'kota') {
                    this.clearWilayah('kecamatan');
                }
            },
            clearWilayah(field) {
                this.wilayah[field] = null;
                const el = document.querySelector('select[name="' + field + '"]');
                if (el && window.jQuery(el).data('select2')) {
                    window.jQuery(el).val(null).trigger('change');
                }
            },
            get availableJalur() {
                if (!this.gelombangId) return [];
                const g = this.gelombangMap.find(x => x.id == this.gelombangId);
                if (!g) return [];
                return this.jalur
                    .filter(j => g.jalur_ids.includes(String(j.id)))
                    .map(j => ({ id: j.id, label: j.nama }));
            },
            get prodiOptions() {
                if (!this.jalurId) return [];
                const m = this.matriks[this.jalurId] || {};
                return Object.keys(m).map(id => ({ id, nama: m[id].nama, jenjang: m[id].jenjang }));
            },
            kelasFor(prodiId) {
                if (!this.jalurId || !prodiId) return [];
                const m = this.matriks[this.jalurId] || {};
                return m[prodiId] ? m[prodiId].kelas : [];
            },
            get selectedJalur() {
                return this.jalur.find(j => j.id == this.jalurId);
            },
            get biayaAktif() {
                if (!this.jalurId) return 0;
                const map = this.biayaKelas[this.jalurId] || {};
                if (this.kelas1 && map[this.kelas1] != null) {
                    return Number(map[this.kelas1]);
                }
                const j = this.selectedJalur;
                return j ? Number(j.biaya_default) : 0;
            },
            namaKelas(kelasId) {
                const pool = this.kelasFor(this.prodi1).concat(this.kelasFor(this.prodi2));
                const found = pool.find(k => k.id == kelasId);
                return found ? found.nama : '';
            },
            get availablePromo() {
                if (!this.jalurId || !this.prodi1 || !this.kelas1) return [];
                return this.promo.filter(p => {
                    if (p.is_global) return true;
                    const k = p.ketentuan || [];
                    return k.some(t => t.jalur_id === this.jalurId && (
                        (t.prodi_id === this.prodi1 && t.kelas_id === this.kelas1) ||
                        (this.prodi2 && t.prodi_id === this.prodi2 && t.kelas_id === this.kelas2)
                    ));
                });
            },
            get selectedPromo() {
                return this.promo.find(p => p.id == this.promoId);
            },
            potonganPendaftaran() {
                const biaya = Number(this.biayaAktif);
                const p = this.selectedPromo;
                if (!biaya || !p) return 0;
                if (p.tipe === 'nominal') return Math.min(Number(p.nilai), biaya);
                let potongan = biaya * (Number(p.nilai) / 100);
                if (p.maks_potongan != null && Number(p.maks_potongan) >= 0) {
                    potongan = Math.min(potongan, Number(p.maks_potongan));
                }
                return Math.min(potongan, biaya);
            },
            get biayaAkhir() {
                const biaya = Number(this.biayaAktif);
                return Math.max(0, biaya - this.potonganPendaftaran());
            },
            docMatches(doc) {
                if (doc.jalur_id && doc.jalur_id !== this.jalurId) return false;
                if (doc.prodi_id) {
                    const prodiIds = [this.prodi1, this.prodi2].filter(Boolean);
                    if (!prodiIds.includes(doc.prodi_id)) return false;
                }
                return true;
            },
            get requiredDocuments() {
                return (this.dokumen || []).filter(d => this.docMatches(d));
            },
            get jalurSyarat() {
                if (!this.jalurId) return [];
                return (this.syarat || {})[this.jalurId] || [];
            },
            formatRupiah(n) {
                return new Intl.NumberFormat('id-ID').format(n || 0);
            }
        };
    }
</script>
@endpush
