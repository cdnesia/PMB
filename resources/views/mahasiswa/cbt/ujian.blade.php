@extends('layouts.mahasiswa')

@section('title', __('cbt.exam_title'))

@section('content')
    <div id="cbt-app" class="space-y-6">
        {{-- Header: timer & progres --}}
        <div class="sticky top-16 z-10 flex flex-wrap items-center justify-between gap-3 rounded-xl bg-white px-5 py-4 shadow-sm ring-1 ring-gray-200">
            <div>
                <h1 class="text-base font-semibold text-gray-900">{{ $sesi->jadwal?->nama }}</h1>
                <p class="text-xs text-gray-500">{{ __('cbt.questions_count', ['n' => $soalUrut->count()]) }} · {{ __('cbt.autosave_notice') }}</p>
            </div>
            <div class="flex items-center gap-2 rounded-lg bg-red-50 px-4 py-2 text-red-700">
                <x-icon name="calendar" class="h-4 w-4" />
                <span id="cbt-countdown" class="font-mono text-sm font-semibold">--:--:--</span>
            </div>
        </div>

        <div class="flex items-start gap-3 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-xs text-amber-800">
            <x-icon name="warning" class="mt-0.5 h-4 w-4 shrink-0 text-amber-500" />
            <span>
                {{ __('cbt.integrity_warning') }}
            </span>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-4">
            {{-- Navigasi nomor soal --}}
            <div class="order-2 lg:order-1">
                <x-ui-card>
                    <h2 class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('cbt.question_navigation') }}</h2>
                    <div id="cbt-nav" class="mt-3 grid grid-cols-5 gap-2 lg:grid-cols-4">
                        @foreach ($soalUrut as $i => $s)
                            <button type="button" data-cbt-nav="{{ $i }}"
                                class="cbt-nav-item flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 text-sm font-medium text-gray-600 transition hover:border-indigo-300">
                                {{ $i + 1 }}
                            </button>
                        @endforeach
                    </div>
                    <div class="mt-4 space-y-1.5 text-xs text-gray-500">
                        <div class="flex items-center gap-2"><span class="h-3 w-3 rounded bg-indigo-600"></span> {{ __('cbt.answered') }}</div>
                        <div class="flex items-center gap-2"><span class="h-3 w-3 rounded bg-amber-400"></span> {{ __('cbt.flagged_for_review') }}</div>
                        <div class="flex items-center gap-2"><span class="h-3 w-3 rounded border border-gray-300"></span> {{ __('cbt.not_answered') }}</div>
                    </div>
                    <button type="button" id="cbt-fullscreen" class="mt-4 w-full rounded-lg border border-gray-200 px-3 py-2 text-xs font-medium text-gray-600 hover:bg-gray-50">
                        {{ __('cbt.enable_fullscreen') }}
                    </button>
                    <button type="button" onclick="window.dispatchEvent(new CustomEvent('open-modal', {detail: 'submit-cbt'}))"
                        class="mt-2 w-full rounded-lg bg-emerald-600 px-3 py-2 text-xs font-semibold text-white hover:bg-emerald-700">
                        {{ __('cbt.submit_exam') }}
                    </button>
                </x-ui-card>
            </div>

            {{-- Panel soal --}}
            <div class="order-1 lg:col-span-3 lg:order-2">
                <x-ui-card>
                    @foreach ($soalUrut as $i => $s)
                        @php $jawabanAwal = $jawabanTersimpan->get($s->id); @endphp
                        <div class="cbt-soal-panel" data-cbt-panel="{{ $i }}" @if (! $loop->first) style="display:none" @endif>
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-400">{{ __('cbt.question_of', ['current' => $i + 1, 'total' => $soalUrut->count()]) }}</p>
                            <p class="mt-2 whitespace-pre-line text-sm font-medium text-gray-900">{{ $s->pertanyaan }}</p>

                            <div class="mt-5 space-y-2.5">
                                @foreach ($s->pilihan() as $huruf => $teks)
                                    <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-gray-200 px-4 py-3 transition hover:bg-gray-50 has-[:checked]:border-indigo-600 has-[:checked]:bg-indigo-50">
                                        <input type="radio" name="cbt-jawaban-{{ $s->id }}" value="{{ $huruf }}"
                                               class="cbt-jawaban-input mt-0.5" data-soal-id="{{ $s->id }}"
                                               @checked($jawabanAwal?->jawaban === $huruf)>
                                        <span class="text-sm text-gray-700"><strong class="mr-1.5">{{ strtoupper($huruf) }}.</strong>{{ $teks }}</span>
                                    </label>
                                @endforeach
                            </div>

                            <label class="mt-4 flex items-center gap-2 text-xs text-amber-700">
                                <input type="checkbox" class="cbt-ragu-input rounded border-gray-300" data-soal-id="{{ $s->id }}" @checked($jawabanAwal?->ragu_ragu)>
                                {{ __('cbt.mark_for_review') }}
                            </label>

                            <div class="mt-6 flex items-center justify-between border-t border-gray-100 pt-4">
                                <button type="button" data-cbt-prev class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 disabled:opacity-40" @if ($loop->first) disabled @endif>
                                    {{ __('cbt.previous') }}
                                </button>
                                <button type="button" data-cbt-next class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-40" @if ($loop->last) disabled @endif>
                                    {{ __('cbt.next') }}
                                </button>
                            </div>
                        </div>
                    @endforeach
                </x-ui-card>
            </div>
        </div>
    </div>

    <x-modal name="submit-cbt" :show="false" maxWidth="md">
        <div class="p-6">
            <h2 class="text-lg font-semibold text-gray-900">{{ __('cbt.confirm_submit_title') }}</h2>
            <p class="mt-2 text-sm text-gray-500">
                {{ __('cbt.confirm_submit_body') }}
            </p>
            <form method="POST" action="{{ route('mahasiswa.cbt.submit', $sesi) }}" class="mt-6 flex justify-end gap-3">
                @csrf
                <button type="button" class="rounded-lg px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100" onclick="window.dispatchEvent(new CustomEvent('close-modal', {detail: 'submit-cbt'}))">{{ __('cbt.cancel') }}</button>
                <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">{{ __('cbt.yes_submit') }}</button>
            </form>
        </div>
    </x-modal>

    <script id="cbt-config" type="application/json">
        {!! json_encode([
            'deadlineAt' => $sesi->deadline_at->toIso8601String(),
            'jumlahSoal' => $soalUrut->count(),
            'submitUrl' => route('mahasiswa.cbt.submit', $sesi),
            'jawabUrl' => route('mahasiswa.cbt.jawab', $sesi),
            'pelanggaranUrl' => route('mahasiswa.cbt.pelanggaran', $sesi),
            'csrfToken' => csrf_token(),
        ]) !!}
    </script>

    @vite('resources/js/cbt-exam.js')
@endsection
