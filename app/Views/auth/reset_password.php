<?= $this->extend('templates/auth_layout') ?>

<?= $this->section('content') ?>
<div class="auth-container">
    <div class="auth-header">
        <h2>Atur Ulang Password</h2>
        <p class="text-muted">Masukkan password baru Anda di bawah ini.</p>
    </div>

    <?php if (session()->getFlashdata('error')) : ?>
        <div class="alert alert-danger">
            <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>

    <form action="/reset-password/<?= esc($token, 'url') ?>" method="post">
        <?= csrf_field() ?>

        <div class="mb-3">
            <label for="password" class="form-label">Password Baru</label>
            <input type="password" class="form-control" id="password" name="password" required minlength="8">
        </div>

        <div class="mb-3">
            <label for="password_confirm" class="form-label">Konfirmasi Password</label>
            <input type="password" class="form-control" id="password_confirm" name="password_confirm" required minlength="8">
        </div>

        <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary">Simpan Password Baru</button>
        </div>
    </form>

    <div class="text-center mt-4">
        <p><a href="/login">Kembali ke Masuk</a></p>
    </div>
</div>
<?= $this->endSection() ?>
