@extends('layouts.mahasiswa')

@section('title', __('pendaftaran.form_title'))

@section('content')
    <div class="mb-8">
        <h1 class="text-2xl font-bold tracking-tight text-gray-900">{{ __('pendaftaran.form_title') }}</h1>
        <p class="mt-1 text-gray-500">{{ __('pendaftaran.form_subtitle') }}</p>
    </div>

    @if (! $tahun)
        <div class="rounded-xl border border-amber-200 bg-amber-50 p-6">
            <div class="flex items-start gap-3">
                <x-icon name="warning" class="h-6 w-6 text-amber-500" />
                <div>
                    <h2 class="text-sm font-semibold text-amber-800">{{ __('pendaftaran.no_active_year_title') }}</h2>
                    <p class="mt-1 text-sm text-amber-700">{{ __('pendaftaran.contact_committee') }}</p>
                </div>
            </div>
        </div>
    @elseif ($gelombang->isEmpty())
        <div class="rounded-xl border border-amber-200 bg-amber-50 p-6">
            <div class="flex items-start gap-3">
                <x-icon name="warning" class="h-6 w-6 text-amber-500" />
                <div>
                    <h2 class="text-sm font-semibold text-amber-800">{{ __('pendaftaran.no_open_wave_title') }}</h2>
                    <p class="mt-1 text-sm text-amber-700">{{ __('pendaftaran.contact_committee_period') }}</p>
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
                        <h2 class="text-base font-semibold text-gray-900">{{ __('pendaftaran.step1_title') }}</h2>
                    </div>

                    <div>
                        <x-ui-select name="gelombang_id" x-model="gelombangId" @change="jalurId = null" required>
                            <option value="">{{ __('pendaftaran.select_wave_placeholder') }}</option>
                            @foreach ($gelombang as $g)
                                <option value="{{ $g->id }}">{{ $g->nama }} {{ __('pendaftaran.wave_until', ['date' => $g->tanggal_selesai->format('d/m/Y')]) }}</option>
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
                            <h2 class="text-base font-semibold text-gray-900">{{ __('pendaftaran.step2_title') }}</h2>
                        </div>

                        <div>
                            <x-ui-select name="jalur_id" x-model="jalurId" @change="promoId = null" required>
                                <option value="">{{ __('pendaftaran.select_pathway_placeholder') }}</option>
                                <template x-for="j in availableJalur" :key="j.id">
                                    <option :value="j.id" x-text="j.label"></option>
                                </template>
                            </x-ui-select>
                        </div>

                        <template x-if="selectedJalur">
                            <div class="mt-3 flex items-center gap-2 rounded-lg bg-indigo-50 px-4 py-3 text-sm text-indigo-700">
                                <x-icon name="info" class="h-5 w-5 shrink-0" />
                                {{ __('pendaftaran.fee_determined_by_class') }}
                            </div>
                        </template>
                    </div>
                </template>

                {{-- Langkah 3: Data Diri (muncul setelah pilih jalur) --}}
                <template x-if="jalurId">
                    <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
                        <div class="mb-4 flex items-center gap-3">
                            <span class="flex h-7 w-7 items-center justify-center rounded-full bg-indigo-600 text-xs font-bold text-white">3</span>
                            <h2 class="text-base font-semibold text-gray-900">{{ __('pendaftaran.step3_title') }}</h2>
                        </div>

                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <x-ui-label for="nik" required>{{ __('pendaftaran.nik') }}</x-ui-label>
                                <div class="mt-2">
                                    <x-ui-input name="nik" id="nik" :value="old('nik')" required inputmode="numeric" maxlength="16" placeholder="{{ __('pendaftaran.nik_placeholder') }}" />
                                </div>
                                @error('nik')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <x-ui-label for="nisn" required>{{ __('pendaftaran.nisn') }}</x-ui-label>
                                <div class="mt-2">
                                    <x-ui-input name="nisn" id="nisn" :value="old('nisn')" required inputmode="numeric" maxlength="10" placeholder="{{ __('pendaftaran.nisn_placeholder') }}" />
                                </div>
                                @error('nisn')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <x-ui-label for="tempat_lahir" required>{{ __('pendaftaran.birth_place') }}</x-ui-label>
                                <div class="mt-2">
                                    <x-ui-input name="tempat_lahir" id="tempat_lahir" :value="old('tempat_lahir')" required placeholder="{{ __('pendaftaran.birth_place_placeholder') }}" />
                                </div>
                                @error('tempat_lahir')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <x-ui-label for="tanggal_lahir" required>{{ __('pendaftaran.birth_date') }}</x-ui-label>
                                <div class="mt-2">
                                    <x-ui-input type="date" name="tanggal_lahir" id="tanggal_lahir" :value="old('tanggal_lahir')" required />
                                </div>
                                @error('tanggal_lahir')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <x-ui-label for="jenis_kelamin" required>{{ __('pendaftaran.gender') }}</x-ui-label>
                                <div class="mt-2">
                                    <x-ui-select name="jenis_kelamin" id="jenis_kelamin" required>
                                        <option value="">{{ __('pendaftaran.select_placeholder') }}</option>
                                        @foreach ($refs['jenis_kelamin'] as $j)
                                            <option value="{{ $j['id_jenis_kelamin'] }}" @selected((string) old('jenis_kelamin') === (string) $j['id_jenis_kelamin'])>{{ $j['nama_jenis_kelamin'] }}</option>
                                        @endforeach
                                    </x-ui-select>
                                </div>
                                @error('jenis_kelamin')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <x-ui-label for="agama" required>{{ __('pendaftaran.religion') }}</x-ui-label>
                                <div class="mt-2">
                                    <x-ui-select name="agama" id="agama" required>
                                        <option value="">{{ __('pendaftaran.select_placeholder') }}</option>
                                        @foreach ($refs['agama'] as $a)
                                            <option value="{{ $a['id_agama'] }}" @selected((string) old('agama') === (string) $a['id_agama'])>{{ $a['nama_agama'] }}</option>
                                        @endforeach
                                    </x-ui-select>
                                </div>
                                @error('agama')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <x-ui-label for="kewarganegaraan" required>{{ __('pendaftaran.nationality') }}</x-ui-label>
                                <div class="mt-2">
                                    <x-ui-select name="kewarganegaraan" id="kewarganegaraan" x-model="kewarganegaraan" required>
                                        <option value="WNI" @selected(old('kewarganegaraan', 'WNI') === 'WNI')>{{ __('pendaftaran.wni') }}</option>
                                        <option value="WNA" @selected(old('kewarganegaraan') === 'WNA')>{{ __('pendaftaran.wna') }}</option>
                                    </x-ui-select>
                                </div>
                            </div>

                            <div x-show="kewarganegaraan === 'WNA'" x-cloak>
                                <x-ui-label for="negara" required>{{ __('pendaftaran.foreign_country') }}</x-ui-label>
                                <div class="mt-2">
                                    <x-ui-select name="negara" id="negara">
                                        <option value="">{{ __('pendaftaran.select_country_placeholder') }}</option>
                                        @foreach ($negaraList as $n)
                                            <option value="{{ $n->id }}" @selected(old('negara') == $n->id)>{{ $n->nama }}</option>
                                        @endforeach
                                    </x-ui-select>
                                </div>
                                @error('negara')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <div class="md:col-span-2">
                                <x-ui-label for="alamat" required>
                                    <span x-show="kewarganegaraan === 'WNA'" x-cloak>{{ __('pendaftaran.domicile_address') }}</span>
                                    <span x-show="kewarganegaraan !== 'WNA'">{{ __('pendaftaran.full_address') }}</span>
                                </x-ui-label>
                                <div class="mt-2">
                                    <textarea name="alamat" id="alamat" rows="2" required
                                              class="block w-full rounded-lg border-0 px-3 py-2.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                                              placeholder="{{ __('pendaftaran.street_placeholder') }}">{{ old('alamat') }}</textarea>
                                </div>
                                @error('alamat')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <x-ui-label for="rt">{{ __('pendaftaran.rt') }}</x-ui-label>
                                <div class="mt-2">
                                    <x-ui-input name="rt" id="rt" :value="old('rt')" inputmode="numeric" maxlength="5" placeholder="001" />
                                </div>
                            </div>

                            <div>
                                <x-ui-label for="rw">{{ __('pendaftaran.rw') }}</x-ui-label>
                                <div class="mt-2">
                                    <x-ui-input name="rw" id="rw" :value="old('rw')" inputmode="numeric" maxlength="5" placeholder="001" />
                                </div>
                            </div>

                            <div class="md:col-span-2 grid grid-cols-1 gap-6 md:grid-cols-2">

                                <div>
                                    <x-ui-label for="kelurahan" required>{{ __('pendaftaran.village') }}</x-ui-label>
                                    <div class="mt-2">
                                        <x-ui-input name="kelurahan" id="kelurahan" :value="old('kelurahan')" required placeholder="{{ __('pendaftaran.village_placeholder') }}" />
                                    </div>
                                    @error('kelurahan')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <x-ui-label for="kecamatan" required>{{ __('pendaftaran.district_label') }}</x-ui-label>
                                    <div class="mt-2">
                                        <select name="kecamatan" id="kecamatan" x-select2="{ ajax: kecamatanOptions(), placeholder: '{{ __('pendaftaran.district_search_placeholder') }}', minimumInputLength: 2 }" @change="kecamatanChanged()" required>
                                            @if ($kecamatanTerpilih)
                                                <option value="{{ $kecamatanTerpilih['id'] }}" selected>{{ $kecamatanTerpilih['text'] }}</option>
                                            @else
                                                <option value="">{{ __('pendaftaran.district_select_placeholder') }}</option>
                                            @endif
                                        </select>
                                        <p class="mt-1.5 text-xs text-gray-400">{{ __('pendaftaran.district_hint') }}</p>
                                    </div>
                                    @error('kecamatan')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                                    @error('kota')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                                    @error('provinsi')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror

                                    <input type="hidden" name="provinsi" :value="wilayah.provinsi">
                                    <input type="hidden" name="kota" :value="wilayah.kota">

                                    <template x-if="wilayah.kotaNama && wilayah.provinsiNama">
                                        <p class="mt-2 text-xs text-gray-500">
                                            {{ __('pendaftaran.city_label') }}: <span class="font-medium text-gray-700" x-text="wilayah.kotaNama"></span>
                                            · {{ __('pendaftaran.province_label') }}: <span class="font-medium text-gray-700" x-text="wilayah.provinsiNama"></span>
                                        </p>
                                    </template>
                                </div>
                            </div>

                            <div>
                                <x-ui-label for="kode_pos">{{ __('pendaftaran.postal_code') }}</x-ui-label>
                                <div class="mt-2">
                                    <x-ui-input name="kode_pos" id="kode_pos" :value="old('kode_pos')" inputmode="numeric" maxlength="5" placeholder="{{ __('pendaftaran.postal_code_placeholder') }}" />
                                </div>
                                @error('kode_pos')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                        </div>
                    </div>
                </template>

                {{-- Langkah 4: Riwayat Pendidikan & Pekerjaan --}}
                <template x-if="jalurId">
                    <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
                        <div class="mb-4 flex items-center gap-3">
                            <span class="flex h-7 w-7 items-center justify-center rounded-full bg-indigo-600 text-xs font-bold text-white">4</span>
                            <h2 class="text-base font-semibold text-gray-900">{{ __('pendaftaran.step4_title') }}</h2>
                        </div>

                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <x-ui-label for="asal_sekolah" required>{{ __('pendaftaran.school_origin') }}</x-ui-label>
                                <div class="mt-2">
                                    <x-ui-input name="asal_sekolah" id="asal_sekolah" :value="old('asal_sekolah')" required placeholder="{{ __('pendaftaran.school_placeholder') }}" />
                                </div>
                                @error('asal_sekolah')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <x-ui-label for="tahun_lulus">{{ __('pendaftaran.graduation_year') }}</x-ui-label>
                                <div class="mt-2">
                                    <x-ui-input name="tahun_lulus" id="tahun_lulus" :value="old('tahun_lulus')" inputmode="numeric" maxlength="4" placeholder="2026" />
                                </div>
                                @error('tahun_lulus')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div class="mt-5 border-t border-gray-100 pt-4">
                            <x-ui-checkbox name="sudah_bekerja" x-model="sudahBekerja" label="{{ __('pendaftaran.currently_employed') }}" />

                            <template x-if="sudahBekerja">
                                <div class="mt-4 grid grid-cols-1 gap-6 md:grid-cols-2">
                                    <div>
                                        <x-ui-label for="pekerjaan">{{ __('pendaftaran.occupation') }}</x-ui-label>
                                        <div class="mt-2">
                                            <x-ui-select name="pekerjaan" id="pekerjaan">
                                                <option value="">{{ __('pendaftaran.select_occupation_placeholder') }}</option>
                                                @foreach ($pekerjaanList as $pk)
                                                    <option value="{{ $pk->nama }}" @selected(old('pekerjaan') === $pk->nama)>{{ $pk->nama }}</option>
                                                @endforeach
                                            </x-ui-select>
                                        </div>
                                        @error('pekerjaan')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                                    </div>

                                    <div>
                                        <x-ui-label for="tempat_bekerja">{{ __('pendaftaran.workplace') }}</x-ui-label>
                                        <div class="mt-2">
                                            <x-ui-input name="tempat_bekerja" id="tempat_bekerja" :value="old('tempat_bekerja')" placeholder="{{ __('pendaftaran.workplace_placeholder') }}" />
                                        </div>
                                        @error('tempat_bekerja')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>

                {{-- Langkah 5: Pilihan Program Studi --}}
                <template x-if="jalurId">
                    <div class="space-y-6">
                        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
                            <div class="mb-4 flex items-center gap-3">
                                <span class="flex h-7 w-7 items-center justify-center rounded-full bg-indigo-600 text-xs font-bold text-white">5</span>
                                <h2 class="text-base font-semibold text-gray-900">{{ __('pendaftaran.step5_title') }}</h2>
                            </div>

                            <h3 class="text-sm font-medium text-gray-700">{{ __('pendaftaran.choice_1_main') }} <span class="font-normal text-gray-400">{{ __('pendaftaran.main_label') }}</span></h3>
                            <div class="mt-3 grid grid-cols-1 gap-6 md:grid-cols-2">
                                <div>
                                    <x-ui-label for="prodi1" required>{{ __('pendaftaran.program_study') }}</x-ui-label>
                                    <div class="mt-2">
                                        <x-ui-select name="prodi1" id="prodi1" x-model="prodi1" @change="kelas1 = ''; promoId = null" required>
                                            <option value="">{{ __('pendaftaran.select_program_placeholder') }}</option>
                                            <template x-for="p in prodiOptions" :key="p.id">
                                                <option :value="p.id" x-text="p.jenjang ? p.jenjang + ' - ' + p.nama : p.nama"></option>
                                            </template>
                                        </x-ui-select>
                                    </div>
                                </div>
                                <div>
                                    <x-ui-label for="kelas1" required>{{ __('pendaftaran.lecture_class') }}</x-ui-label>
                                    <div class="mt-2">
                                        <x-ui-select name="kelas1" id="kelas1" x-model="kelas1" @change="promoId = null" required>
                                            <option value="">{{ __('pendaftaran.select_class_placeholder') }}</option>
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
                                        {{ __('pendaftaran.registration_fee') }}
                                        <span x-show="selectedJalur" class="font-medium" x-text="'(' + selectedJalur.nama + ' · ' + namaKelas(kelas1) + ')'"></span>
                                    </span>
                                    <span class="font-semibold text-indigo-800" x-text="'Rp ' + formatRupiah(biayaAktif)"></span>
                                </div>
                            </template>

                            <div class="mt-6 border-t border-gray-100 pt-5">
                                <h3 class="text-sm font-medium text-gray-700">{{ __('pendaftaran.choice_2') }} <span class="font-normal text-gray-400">{{ __('pendaftaran.optional_label') }}</span></h3>
                                <div class="mt-3 grid grid-cols-1 gap-6 md:grid-cols-2">
                                    <div>
                                        <x-ui-label for="prodi2">{{ __('pendaftaran.program_study') }}</x-ui-label>
                                        <div class="mt-2">
                                            <x-ui-select name="prodi2" id="prodi2" x-model="prodi2" @change="kelas2 = ''; promoId = null">
                                                <option value="">{{ __('pendaftaran.leave_empty_if_none') }}</option>
                                                <template x-for="p in prodiOptions" :key="p.id">
                                                    <option :value="p.id" x-text="p.jenjang ? p.jenjang + ' - ' + p.nama : p.nama"></option>
                                                </template>
                                            </x-ui-select>
                                        </div>
                                    </div>
                                    <div>
                                        <x-ui-label for="kelas2">{{ __('pendaftaran.lecture_class') }}</x-ui-label>
                                        <div class="mt-2">
                                            <x-ui-select name="kelas2" id="kelas2" x-model="kelas2" @change="promoId = null" :required="false">
                                                <option value="">{{ __('pendaftaran.select_class_placeholder') }}</option>
                                                <template x-for="k in kelasFor(prodi2)" :key="k.id">
                                                    <option :value="k.id" x-text="k.nama"></option>
                                                </template>
                                            </x-ui-select>
                                        </div>
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
                                    <h2 class="text-base font-semibold text-gray-900">{{ __('pendaftaran.use_promo') }} <span class="font-normal text-gray-400">{{ __('pendaftaran.optional_label') }}</span></h2>
                                </div>

                                <x-ui-select name="promo_id" x-model="promoId">
                                    <option value="">{{ __('pendaftaran.no_promo') }}</option>
                                    <template x-for="p in availablePromo" :key="p.id">
                                        <option :value="p.id" x-text="p.label"></option>
                                    </template>
                                </x-ui-select>
                                @error('promo_id')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror

                                <template x-if="selectedPromo">
                                    <div class="mt-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm">
                                        <div class="flex items-center justify-between">
                                            <span class="text-emerald-700">{{ __('pendaftaran.registration_fee') }}</span>
                                            <span class="font-semibold text-gray-900" x-text="'Rp ' + formatRupiah(biayaAktif)"></span>
                                        </div>
                                        <div class="mt-2 flex items-center justify-between">
                                            <span class="text-emerald-700">{{ __('pendaftaran.promo_discount') }} <span class="font-semibold" x-text="selectedPromo.kode"></span></span>
                                            <span class="font-semibold text-emerald-600" x-text="'- Rp ' + formatRupiah(potonganPendaftaran())"></span>
                                        </div>
                                        <div class="mt-3 flex items-center justify-between border-t border-emerald-200 pt-3">
                                            <span class="font-medium text-emerald-800">{{ __('pendaftaran.total_payment') }}</span>
                                            <span class="text-base font-bold text-emerald-800" x-text="'Rp ' + formatRupiah(biayaAkhir)"></span>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </template>

                        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
                            <div class="mb-4 flex items-center gap-3">
                                <span class="flex h-7 w-7 items-center justify-center rounded-full bg-gray-200 text-xs font-bold text-gray-600">6</span>
                                <h2 class="text-base font-semibold text-gray-900">{{ __('pendaftaran.step6_title') }}</h2>
                            </div>

                            <template x-if="requiredDocuments.length > 0">
                                <div class="space-y-4">
                                    <p class="text-sm text-gray-500">{{ __('pendaftaran.upload_docs_hint') }}</p>

                                    <template x-for="doc in requiredDocuments" :key="doc.id">
                                        <div class="rounded-lg border border-gray-200 p-4">
                                            <div class="mb-2 flex items-center justify-between">
                                                <div class="flex items-center gap-2">
                                                    <x-icon name="document" class="h-4 w-4 text-gray-400" />
                                                    <span class="text-sm font-medium text-gray-900" x-text="doc.nama"></span>
                                                    <span x-show="doc.wajib" class="rounded bg-red-50 px-1.5 py-0.5 text-[11px] font-semibold text-red-600">{{ __('pendaftaran.required') }}</span>
                                                    <span x-show="!doc.wajib" class="rounded bg-gray-100 px-1.5 py-0.5 text-[11px] font-semibold text-gray-500">{{ __('pendaftaran.optional') }}</span>
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
                                <p class="text-sm text-gray-400">{{ __('pendaftaran.no_docs_needed') }}</p>
                            </template>
                        </div>

                        {{-- Syarat Khusus Jalur (muncul jika jalur terpilih punya syarat) --}}
                        <template x-if="jalurId && jalurSyarat.length > 0">
                            <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
                                <div class="mb-4 flex items-center gap-3">
                                    <span class="flex h-7 w-7 items-center justify-center rounded-full bg-indigo-50 text-indigo-600">
                                        <x-icon name="document" class="h-4 w-4" />
                                    </span>
                                    <h2 class="text-base font-semibold text-gray-900">{{ __('pendaftaran.pathway_specific_requirements') }}</h2>
                                </div>

                                <div class="space-y-4">
                                    <template x-for="s in jalurSyarat" :key="s.id">
                                        <div class="rounded-lg border border-gray-200 p-4">
                                            <div class="mb-2 flex items-center gap-2">
                                                <x-icon name="document" class="h-4 w-4 text-gray-400" />
                                                <span class="text-sm font-medium text-gray-900" x-text="s.nama"></span>
                                                <span x-show="s.wajib" class="rounded bg-red-50 px-1.5 py-0.5 text-[11px] font-semibold text-red-600">{{ __('pendaftaran.required') }}</span>
                                                <span x-show="!s.wajib" class="rounded bg-gray-100 px-1.5 py-0.5 text-[11px] font-semibold text-gray-500">{{ __('pendaftaran.optional') }}</span>
                                            </div>

                                            <template x-if="s.tipe === 'field'">
                                                <input type="text"
                                                       :name="'syarat_field[' + s.id + ']'"
                                                       :placeholder="enterPlaceholderPrefix + ' ' + s.nama"
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
                            <x-ui-button variant="primary" icon="check">{{ __('pendaftaran.submit_registration') }}</x-ui-button>
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
            sudahBekerja: @js((bool) (old('pekerjaan') || old('tempat_bekerja'))),
            dokumen: @json($dokumen),
            syarat: @json($syaratMap),
            enterPlaceholderPrefix: @js(__('pendaftaran.enter_prefix')),
            wilayah: {
                provinsi: @js($kecamatanTerpilih['provinsi_id'] ?? null),
                kota: @js($kecamatanTerpilih['kota_id'] ?? null),
                provinsiNama: @js($kecamatanTerpilih['provinsi_nama'] ?? ''),
                kotaNama: @js($kecamatanTerpilih['kota_nama'] ?? ''),
            },
            kecamatanOptions() {
                return {
                    url: '/wilayah',
                    dataType: 'json',
                    delay: 300,
                    cache: true,
                    data(params) {
                        return { level: 'kecamatan', q: params.term || '' };
                    },
                    processResults(data) {
                        return {
                            results: data.map(w => ({
                                id: w.id,
                                text: w.text,
                                kota_id: w.kota_id,
                                kota_nama: w.kota_nama,
                                provinsi_id: w.provinsi_id,
                                provinsi_nama: w.provinsi_nama,
                            })),
                        };
                    },
                };
            },
            kecamatanChanged() {
                const el = document.querySelector('select[name="kecamatan"]');
                const data = el && window.jQuery(el).data('select2') ? window.jQuery(el).select2('data')[0] : null;

                if (data && data.kota_id) {
                    this.wilayah.kota = data.kota_id;
                    this.wilayah.provinsi = data.provinsi_id;
                    this.wilayah.kotaNama = data.kota_nama;
                    this.wilayah.provinsiNama = data.provinsi_nama;
                } else {
                    this.wilayah.kota = null;
                    this.wilayah.provinsi = null;
                    this.wilayah.kotaNama = '';
                    this.wilayah.provinsiNama = '';
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
