<?= $this->extend('templates/auth_layout') ?>

<?= $this->section('content') ?>
<div class="auth-container">
    <div class="auth-header">
        <h2>Daftar</h2>
        <p class="text-muted">Buat akun baru untuk mulai belajar</p>
    </div>
    
    <?php if (session()->getFlashdata('errors')) : ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach (session()->getFlashdata('errors') as $error) : ?>
                    <li><?= $error ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <form action="/signup" method="post">
        <?= csrf_field() ?>
        
        <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input type="text" class="form-control" id="username" name="username" value="<?= old('username') ?>" required>
        </div>
        
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" value="<?= old('email') ?>" required>
        </div>
        
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password" required>
            <div class="form-text">Password harus minimal 8 karakter.</div>
        </div>
        
        <div class="mb-3">
            <label for="full_name" class="form-label">Nama Lengkap</label>
            <input type="text" class="form-control" id="full_name" name="full_name" value="<?= old('full_name') ?>">
        </div>
        
        <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary">Daftar</button>
        </div>
    </form>
    
    <div class="text-center mt-4">
        <p>Sudah punya akun? <a href="/login">Masuk</a></p>
    </div>
</div>
<?= $this->endSection() ?>
