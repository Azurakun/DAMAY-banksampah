<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password Akun EcoBank Anda</title>
</head>
<body style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f5f2e8; margin: 0; padding: 20px; color: #123526;">
    <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.05); border: 1px solid #e0dbcd;">
        <!-- Header -->
        <tr>
            <td style="background-color: #123526; padding: 30px; text-align: center;">
                <h1 style="color: #ffffff; margin: 0; font-size: 24px; font-weight: 700; font-style: italic; letter-spacing: 0.5px;">EcoBank</h1>
                <p style="color: #cbd5e1; margin: 5px 0 0 0; font-size: 12px; text-transform: uppercase; letter-spacing: 1px;">SMKN 2 Indramayu</p>
            </td>
        </tr>
        <!-- Content -->
        <tr>
            <td style="padding: 40px 30px;">
                <h2 style="margin-top: 0; color: #123526; font-size: 20px; font-weight: 700; border-bottom: 2px dashed #e0dbcd; padding-bottom: 15px;">Permintaan Reset Password</h2>
                <p style="font-size: 15px; line-height: 1.6; color: #475569;">
                    Halo,
                </p>
                <p style="font-size: 15px; line-height: 1.6; color: #475569;">
                    Kami menerima permintaan untuk melakukan reset password pada akun EcoBank Anda yang terdaftar dengan email <strong>{{ $email }}</strong>.
                </p>
                <p style="font-size: 15px; line-height: 1.6; color: #475569; margin-bottom: 30px;">
                    Silakan klik tombol di bawah ini untuk melanjutkan proses reset password akun Anda:
                </p>
                <!-- Button -->
                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom: 30px;">
                    <tr>
                        <td align="center">
                            <a href="{{ $resetUrl }}" target="_blank" style="background-color: #1b4e3b; color: #ffffff; padding: 14px 28px; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 15px; display: inline-block; box-shadow: 0 2px 4px rgba(27,78,59,0.3);">
                                Reset Password Saya
                            </a>
                        </td>
                    </tr>
                </table>
                <p style="font-size: 13px; line-height: 1.6; color: #64748b; background-color: #f8fafc; border-left: 4px solid #f1c232; padding: 12px; border-radius: 4px;">
                    <strong>Penting:</strong> Tautan reset password ini hanya berlaku selama <strong>60 menit</strong> sejak email ini dikirimkan.
                </p>
                <p style="font-size: 14px; line-height: 1.6; color: #475569; margin-top: 25px;">
                    Jika Anda tidak melakukan permintaan ini, silakan abaikan email ini dan password Anda akan tetap aman.
                </p>
                <p style="font-size: 14px; line-height: 1.6; color: #475569; margin-top: 25px;">
                    Salam Hangat,<br>
                    <strong>Tim EcoBank SMKN 2 Indramayu</strong>
                </p>
            </td>
        </tr>
        <!-- Footer -->
        <tr>
            <td style="background-color: #f1ede0; padding: 20px; text-align: center; font-size: 12px; color: #64748b; border-top: 1px solid #e0dbcd;">
                <p style="margin: 0;">Email ini dikirim secara otomatis oleh sistem aplikasi EcoBank.</p>
                <p style="margin: 5px 0 0 0;">&copy; {{ date('Y') }} EcoBank SMKN 2 Indramayu. Semua Hak Dilindungi.</p>
            </td>
        </tr>
    </table>
</body>
</html>
