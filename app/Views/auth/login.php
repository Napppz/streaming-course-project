<?= $this->extend('templates/auth_layout') ?>

<?= $this->section('content') ?>
<div class="auth-container">
    <div class="auth-header">
        <h2>Masuk</h2>
        <p class="text-muted">Silakan masuk untuk mengakses akun Anda</p>
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
    
    <form action="/login" method="post">
        <?= csrf_field() ?>
        
        <div class="mb-3">
            <label for="login" class="form-label">Username atau Email</label>
            <input type="text" class="form-control <?= (session()->getFlashdata('errors.login')) ? 'is-invalid' : '' ?>" 
                   id="login" name="login" value="<?= old('login') ?>" required>
            <?php if (session()->getFlashdata('errors.login')) : ?>
                <div class="invalid-feedback">
                    <?= session()->getFlashdata('errors.login') ?>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control <?= (session()->getFlashdata('errors.password')) ? 'is-invalid' : '' ?>" 
                   id="password" name="password" required>
            <?php if (session()->getFlashdata('errors.password')) : ?>
                <div class="invalid-feedback">
                    <?= session()->getFlashdata('errors.password') ?>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary">Masuk</button>
        </div>
    </form>
    
    <div class="text-center mt-4">
        <p>Belum punya akun? <a href="/signup">Daftar Sekarang</a></p>
    </div>
</div>
<?= $this->endSection() ?>
