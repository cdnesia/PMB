<!DOCTYPE html>
<html lang="id" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="color-scheme" content="light">
    <title>@yield('title', 'Pemberitahuan PMB')</title>
</head>
<body style="margin:0; padding:0; background-color:#f3f4f6; font-family:'Segoe UI', -apple-system, BlinkMacSystemFont, Roboto, Helvetica, Arial, sans-serif; -webkit-font-smoothing:antialiased;">
    @php
        $institusi = [
            'nama' => \App\Models\Pengaturan::get('institusi_nama') ?? config('app.name'),
            'alamat' => \App\Models\Pengaturan::get('institusi_alamat'),
            'telepon' => \App\Models\Pengaturan::get('institusi_telepon'),
            'email' => \App\Models\Pengaturan::get('institusi_email'),
        ];
    @endphp

    {{-- Outer wrapper — background email --}}
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#f3f4f6;">
        <tr>
            <td align="center" style="padding:24px 16px;">
                {{-- Card container --}}
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:600px; width:100%;">
                    <tr>
                        <td style="border-radius:16px 16px 0 0; overflow:hidden;">
                            {{-- Header --}}
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:linear-gradient(135deg, #4338ca 0%, #6366f1 100%);">
                                <tr>
                                    <td style="padding:32px 36px;">
                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                            <tr>
                                                <td style="width:44px; vertical-align:middle;">
                                                    <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="width:44px; height:44px; background-color:rgba(255,255,255,0.15); border-radius:12px;">
                                                        <tr>
                                                            <td align="center" valign="middle" style="font-size:20px;">🎓</td>
                                                        </tr>
                                                    </table>
                                                </td>
                                                <td style="padding-left:14px; vertical-align:middle;">
                                                    <div style="font-size:18px; font-weight:700; color:#ffffff; line-height:1.2;">{{ $institusi['nama'] }}</div>
                                                    <div style="font-size:12px; color:#c7d2fe; margin-top:3px; letter-spacing:0.08em; text-transform:uppercase;">Penerimaan Mahasiswa Baru</div>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color:#ffffff; padding:36px; border-left:1px solid #e5e7eb; border-right:1px solid #e5e7eb;">
                            @yield('content')
                        </td>
                    </tr>
                    {{-- Footer --}}
                    <tr>
                        <td style="background-color:#f9fafb; padding:24px 36px; border-left:1px solid #e5e7eb; border-right:1px solid #e5e7eb; border-radius:0 0 16px 16px;">
                            <div style="font-size:12px; color:#6b7280; text-align:center; line-height:1.6;">
                                <strong style="color:#374151;">{{ $institusi['nama'] }}</strong>
                                @if ($institusi['alamat'])
                                    <br>{{ $institusi['alamat'] }}
                                @endif
                                @if ($institusi['telepon'] || $institusi['email'])
                                    <br>
                                    @if ($institusi['telepon']){{ $institusi['telepon'] }}@endif
                                    @if ($institusi['telepon'] && $institusi['email']) · @endif
                                    @if ($institusi['email']){{ $institusi['email'] }}@endif
                                @endif
                            </div>
                            <div style="margin-top:12px; font-size:11px; color:#9ca3af; text-align:center;">
                                Email ini dikirim otomatis. Mohon tidak membalas email ini.
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
