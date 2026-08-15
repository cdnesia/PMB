@extends('emails.layout')

@section('title', 'Selamat Datang di PMB')

@section('content')
    {{-- Heading --}}
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
        <tr>
            <td style="width:40px; vertical-align:middle;">
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="width:40px; height:40px; background-color:#eef2ff; border-radius:9999px;">
                    <tr>
                        <td align="center" valign="middle" style="font-size:20px;">👋</td>
                    </tr>
                </table>
            </td>
            <td style="padding-left:14px; vertical-align:middle;">
                <div style="font-size:20px; font-weight:700; color:#111827;">Selamat Datang, {{ $user->name }}</div>
                <div style="font-size:13px; color:#6b7280; margin-top:2px;">Akun Anda berhasil dibuat</div>
            </td>
        </tr>
    </table>

    <div style="height:24px; font-size:0; line-height:0;">&nbsp;</div>

    <div style="font-size:14px; color:#4b5563; line-height:1.6;">
        Terima kasih telah mendaftar akun di sistem <strong>Penerimaan Mahasiswa Baru</strong>.
        Akun Anda sudah aktif dan siap digunakan untuk melakukan pendaftaran.
    </div>

    {{-- Info akun --}}
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-top:20px; border:1px solid #e5e7eb; border-radius:12px; border-collapse:separate;">
        <tr>
            <td style="padding:14px 16px; border-bottom:1px solid #f3f4f6; width:40%;">
                <div style="font-size:11px; color:#9ca3af; text-transform:uppercase; letter-spacing:0.06em;">Nama</div>
                <div style="font-size:14px; color:#111827; font-weight:600; margin-top:2px;">{{ $user->name }}</div>
            </td>
            <td style="padding:14px 16px; border-bottom:1px solid #f3f4f6;">
                <div style="font-size:11px; color:#9ca3af; text-transform:uppercase; letter-spacing:0.06em;">Email</div>
                <div style="font-size:14px; color:#111827; font-weight:600; margin-top:2px;">{{ $user->email }}</div>
            </td>
        </tr>
        <tr>
            <td style="padding:14px 16px;" colspan="2">
                <div style="font-size:11px; color:#9ca3af; text-transform:uppercase; letter-spacing:0.06em;">Nomor WhatsApp</div>
                <div style="font-size:14px; color:#111827; font-weight:600; margin-top:2px;">{{ $user->phone ?? '—' }}</div>
            </td>
        </tr>
    </table>

    {{-- CTA --}}
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-top:28px;">
        <tr>
            <td align="center">
                <a href="{{ $pendaftaranUrl }}" style="display:inline-block; background-color:#4f46e5; color:#ffffff; text-decoration:none; font-size:14px; font-weight:600; padding:14px 32px; border-radius:10px;">Mulai Pendaftaran</a>
            </td>
        </tr>
    </table>

    {{-- Langkah pendaftaran --}}
    <div style="margin-top:28px; border-top:1px solid #f3f4f6; padding-top:20px;">
        <div style="font-size:12px; color:#6b7280; font-weight:700; letter-spacing:0.08em; text-transform:uppercase;">Alur Pendaftaran</div>
        <div style="margin-top:12px; font-size:13px; color:#4b5563; line-height:1.7;">
            1. Isi formulir pendaftaran (pilih gelombang, jalur, dan prodi).<br>
            2. Lengkapi data diri dan unggah dokumen persyaratan.<br>
            3. Selesaikan pembayaran biaya pendaftaran.<br>
            4. Pantau status seleksi melalui akun Anda.
        </div>
    </div>

    <div style="margin-top:20px; font-size:13px; color:#9ca3af; text-align:center;">
        Butuh bantuan? Hubungi panitia melalui kontak di bawah email ini.
    </div>
@endsection
