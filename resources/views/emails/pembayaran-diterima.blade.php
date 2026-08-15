@extends('emails.layout')

@section('title', 'Pembayaran Diterima')

@section('content')
    {{-- Heading sukses --}}
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
        <tr>
            <td style="width:40px; vertical-align:middle;">
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="width:40px; height:40px; background-color:#ecfdf5; border-radius:9999px;">
                    <tr>
                        <td align="center" valign="middle" style="color:#059669; font-size:18px; font-weight:700;">✓</td>
                    </tr>
                </table>
            </td>
            <td style="padding-left:14px; vertical-align:middle;">
                <div style="font-size:20px; font-weight:700; color:#111827;">Pembayaran Diterima</div>
                <div style="font-size:13px; color:#6b7280; margin-top:2px;">Halo, {{ $pendaftaran->user?->name }} 👋</div>
            </td>
        </tr>
    </table>

    <div style="height:24px; font-size:0; line-height:0;">&nbsp;</div>

    <div style="font-size:14px; color:#4b5563; line-height:1.6;">
        Pembayaran biaya pendaftaran Anda telah kami <strong>terima dan konfirmasi</strong>.
        Berikut rinciannya:
    </div>

    {{-- Nomor pendaftaran (highlight) --}}
    <div style="margin-top:20px; background-color:#eef2ff; border:1px dashed #818cf8; border-radius:12px; padding:16px 20px;">
        <div style="font-size:11px; color:#6366f1; letter-spacing:0.1em; text-transform:uppercase; font-weight:700;">Nomor Pendaftaran</div>
        <div style="font-family:'Courier New', ui-monospace, SFMono-Regular, monospace; font-size:20px; font-weight:700; color:#312e81; margin-top:4px; letter-spacing:0.02em;">{{ $pendaftaran->nomor_pendaftaran }}</div>
    </div>

    {{-- Ringkasan --}}
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-top:20px; border:1px solid #e5e7eb; border-radius:12px; border-collapse:separate;">
        <tr>
            <td style="padding:14px 16px; border-bottom:1px solid #f3f4f6; width:40%;">
                <div style="font-size:11px; color:#9ca3af; text-transform:uppercase; letter-spacing:0.06em;">Jalur</div>
                <div style="font-size:14px; color:#111827; font-weight:600; margin-top:2px;">{{ $pendaftaran->jalur?->nama ?? '—' }}</div>
            </td>
            <td style="padding:14px 16px; border-bottom:1px solid #f3f4f6;">
                <div style="font-size:11px; color:#9ca3af; text-transform:uppercase; letter-spacing:0.06em;">Biaya Pendaftaran</div>
                <div style="font-size:14px; color:#111827; font-weight:600; margin-top:2px;">
                    @if ($pendaftaran->biayaPendaftaranAkhir() > 0)
                        Rp {{ number_format($pendaftaran->biayaPendaftaranAkhir(), 0, ',', '.') }}
                    @else
                        Gratis
                    @endif
                </div>
            </td>
        </tr>
        <tr>
            <td style="padding:14px 16px;" colspan="2">
                <div style="font-size:11px; color:#9ca3af; text-transform:uppercase; letter-spacing:0.06em;">Pilihan Prodi &amp; Kelas</div>
                @forelse ($pendaftaran->prodiPilihan as $p)
                    <div style="margin-top:8px; padding:10px 12px; background-color:#f9fafb; border-radius:8px; font-size:14px; color:#111827;">
                        <span style="display:inline-block; width:22px; height:22px; line-height:22px; text-align:center; background-color:#6366f1; color:#ffffff; border-radius:9999px; font-size:12px; font-weight:700; margin-right:8px;">{{ $p->urutan }}</span>
                        <strong>{{ $p->prodi?->nama ?? '—' }}</strong>
                        <span style="color:#6b7280;"> · {{ $p->kelas?->nama ?? '—' }}</span>
                    </div>
                @empty
                    <div style="margin-top:8px; font-size:13px; color:#9ca3af;">Belum ada pilihan prodi.</div>
                @endforelse
            </td>
        </tr>
    </table>

    {{-- Status pembayaran --}}
    <div style="margin-top:16px; background-color:#ecfdf5; border-radius:10px; padding:12px 16px; font-size:13px; color:#065f46;">
        <strong>Status:</strong> Pembayaran Lunas ✅
    </div>

    {{-- CTA --}}
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-top:28px;">
        <tr>
            <td align="center">
                <a href="{{ $detailUrl }}" style="display:inline-block; background-color:#4f46e5; color:#ffffff; text-decoration:none; font-size:14px; font-weight:600; padding:14px 32px; border-radius:10px;">Lihat Detail Pendaftaran</a>
            </td>
        </tr>
    </table>

    {{-- Langkah selanjutnya --}}
    <div style="margin-top:28px; border-top:1px solid #f3f4f6; padding-top:20px;">
        <div style="font-size:12px; color:#6b7280; font-weight:700; letter-spacing:0.08em; text-transform:uppercase;">Langkah Selanjutnya</div>
        <div style="margin-top:12px; font-size:13px; color:#4b5563; line-height:1.7;">
            1. Panitia akan memverifikasi berkas persyaratan Anda.<br>
            2. Hasil seleksi akan diumumkan sesuai jadwal.<br>
            3. Pantau perkembangan status melalui akun Anda.
        </div>
    </div>
@endsection
