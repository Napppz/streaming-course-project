<?= $this->extend('templates/auth_layout') ?>

<?= $this->section('content') ?>
<div class="auth-container">
    <div class="auth-header">
        <h2>Lupa Password</h2>
        <p class="text-muted">Masukkan email Anda dan kami akan mengirimkan tautan untuk mengatur ulang password.</p>
    </div>

    <?php if (session()->getFlashdata('error')) : ?>
        <div class="alert alert-danger">
            <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('success')) : ?>
        <div class="alert alert-success">
            <?= session()->getFlashdata('success') ?>
        </div>
    <?php endif; ?>

    <form action="/forgot-password" method="post">
        <?= csrf_field() ?>

        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" value="<?= old('email') ?>" required>
        </div>

        <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary">Kirim Tautan Reset</button>
        </div>
    </form>

    <div class="text-center mt-4">
        <p><a href="/login">Kembali ke Masuk</a></p>
    </div>
</div>
<?= $this->endSection() ?>
