<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="mt-4">Courses</h1>
        <a href="<?= site_url('admin/course/create') ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add New Course
        </a>
    </div>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="<?= site_url('admin') ?>">Dashboard</a></li>
        <li class="breadcrumb-item active">Courses</li>
    </ol>
    
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            All Courses
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="coursesTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Thumbnail</th>
                            <th>Title</th>
                            <th>Status</th>
                            <th>Access</th>
                            <th>Level</th>
                            <th>Rating</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($courses as $course): ?>
                        <tr>
                            <td><?= $course['id'] ?></td>
                            <td>
                                <?php if (!empty($course['thumbnail'])): ?>
                                    <img src="<?= base_url($course['thumbnail']) ?>" alt="<?= $course['title'] ?>" width="80" class="img-thumbnail">
                                <?php else: ?>
                                    <div class="bg-light text-center p-2" style="width: 80px; height: 45px;">No Image</div>
                                <?php endif; ?>
                            </td>
                            <td><?= $course['title'] ?></td>
                            <td>
                                <?php if ($course['status'] === 'published'): ?>
                                    <span class="badge bg-success">Published</span>
                                <?php elseif ($course['status'] === 'private'): ?>
                                    <span class="badge bg-warning">Private</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Draft</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                    $isPremium = !empty($course['is_premium']);
                                    $priceAmount = (int) ($course['price_amount'] ?? 0);
                                    $isPurchasable = !empty($course['is_purchasable']);
                                ?>

                                <?php if ($isPremium && $priceAmount > 0 && $isPurchasable): ?>
                                    <span class="badge bg-warning text-dark">Premium</span>
                                    <div class="small text-muted mt-1">
                                        <?= esc(strtoupper($course['price_currency'] ?? 'IDR')) ?> <?= number_format($priceAmount) ?>
                                    </div>
                                    <div class="small text-muted">Purchasable</div>
                                <?php else: ?>
                                    <span class="badge bg-info text-dark">Free</span>
                                    <div class="small text-muted mt-1">No premium access</div>
                                <?php endif; ?>
                            </td>
                            <td><?= ucfirst($course['level']) ?></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <span class="me-2"><?= number_format($course['rating'], 1) ?>/5</span>
                                    <div>
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <?php if ($i <= round($course['rating'])): ?>
                                                <i class="fas fa-star text-warning"></i>
                                            <?php else: ?>
                                                <i class="far fa-star text-warning"></i>
                                            <?php endif; ?>
                                        <?php endfor; ?>
                                    </div>
                                    <a href="<?= site_url('admin/course/' . $course['id'] . '/reviews') ?>" class="ms-2 btn btn-sm btn-outline-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </div>
                            </td>
                            <td><?= date('M d, Y', strtotime($course['created_at'])) ?></td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="<?= site_url('admin/course/' . $course['id'] . '/edit') ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="<?= site_url('admin/course/' . $course['id'] . '/modules') ?>" class="btn btn-sm btn-info">
                                        <i class="fas fa-folder"></i> Modules
                                    </a>
                                    <a href="<?= site_url('admin/course/' . $course['id'] . '/delete') ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this course?')">
                                        <i class="fas fa-trash"></i> Delete
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const table = new simpleDatatables.DataTable("#coursesTable");
    });
</script>
<?= $this->endSection() ?>
