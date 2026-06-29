<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
</head>
<body style="margin:0;padding:0;background-color:#f8f9fa;font-family:Arial,Helvetica,sans-serif;color:#333;">
    <div style="max-width:480px;margin:2rem auto;background:#fff;border-radius:10px;padding:2rem;box-shadow:0 0.5rem 1rem rgba(0,0,0,0.1);">
        <h2 style="color:#4e73df;margin-top:0;">Reset Password</h2>
        <p>Halo <?= esc($name) ?>,</p>
        <p>Kami menerima permintaan untuk mengatur ulang password akun Anda. Klik tombol di bawah ini untuk membuat password baru.</p>
        <p style="text-align:center;margin:2rem 0;">
            <a href="<?= esc($resetUrl, 'attr') ?>" style="display:inline-block;background-color:#4e73df;color:#fff;text-decoration:none;padding:0.75rem 1.5rem;border-radius:5px;">Atur Ulang Password</a>
        </p>
        <p>Atau salin dan tempel tautan berikut ke browser Anda:</p>
        <p style="word-break:break-all;color:#4e73df;"><?= esc($resetUrl) ?></p>
        <p style="color:#888;font-size:0.9rem;">Tautan ini akan kedaluwarsa dalam 60 menit. Jika Anda tidak meminta reset password, abaikan email ini.</p>
    </div>
</body>
</html>
