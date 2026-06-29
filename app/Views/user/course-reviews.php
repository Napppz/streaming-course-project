<?= $this->extend('layouts/user_layout') ?>

<?= $this->section('content') ?>
<div class="container mx-auto px-4 py-8">
    <!-- Breadcrumbs -->
    <nav class="mb-6 text-sm" aria-label="Breadcrumb">
        <ol class="list-none p-0 inline-flex space-x-2">
            <li class="flex items-center">
                <a href="<?= site_url('user/courses') ?>" class="text-muted-foreground hover:text-primary">Kursus</a>
            </li>
            <li class="flex items-center">
                <span class="text-muted-foreground mx-2">/</span>
                <a href="<?= site_url('user/view-course/' . $course['id']) ?>" class="text-muted-foreground hover:text-primary"><?= esc($course['title']) ?></a>
            </li>
            <li class="flex items-center">
                <span class="text-muted-foreground mx-2">/</span>
                <span class="text-foreground">Ulasan</span>
            </li>
        </ol>
    </nav>

    <div class="flex flex-col md:flex-row items-start justify-between mb-6">
        <h1 class="text-3xl font-bold text-foreground mb-2 md:mb-0">Ulasan Kursus: <?= esc($course['title']) ?></h1>
        <?php if ($isEnrolled): ?>
            <?php if (!$userReview): ?>
                <a href="<?= site_url('course/' . $course['id'] . '/reviews/create') ?>" class="px-4 py-2 bg-primary text-primary-foreground rounded-md hover:bg-primary/90 text-sm font-medium inline-flex items-center">
                    <i class="fas fa-plus mr-2"></i> Tulis Ulasan
                </a>
            <?php else: ?>
                <div class="flex items-center space-x-2">
                    <a href="<?= site_url('course/' . $course['id'] . '/reviews/edit') ?>" class="px-3 py-1.5 border border-muted-foreground text-muted-foreground hover:bg-muted rounded-md text-xs font-medium inline-flex items-center">
                        <i class="fas fa-pencil-alt mr-1.5"></i> Edit Ulasan
                    </a>
                    <a href="<?= site_url('course/' . $course['id'] . '/reviews/delete') ?>" class="px-3 py-1.5 border border-red-500 text-red-500 hover:bg-red-50 rounded-md text-xs font-medium inline-flex items-center" onclick="return confirm('Apakah Anda yakin ingin menghapus ulasan ini?')">
                        <i class="fas fa-trash mr-1.5"></i> Hapus
                    </a>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Kolom Kiri: Ringkasan Rating -->
        <div class="lg:col-span-1">
            <div class="bg-background border rounded-lg p-6 sticky top-8">
                <h3 class="text-xl font-semibold text-foreground mb-4">Ringkasan Rating</h3>
                <div class="text-center mb-6">
                    <div class="text-5xl font-bold text-primary mb-1"><?= number_format($averageRating ?? 0, 1) ?></div>
                    <div class="flex justify-center items-center text-yellow-400 mb-1">
                        <?php for ($i = 1; $i <= 5; $i++): ?><i class="fas fa-star <?= $i <= round($averageRating ?? 0) ? '' : 'text-gray-300' ?> mr-0.5"></i><?php endfor; ?>
                    </div>
                    <p class="text-sm text-muted-foreground">Berdasarkan <?= count($reviews) ?> ulasan</p>
                </div>

                <?php if (!empty($ratingDistribution)): ?>
                    <div class="space-y-1.5">
                        <?php 
                        $totalReviewsForDist = count($reviews);
                        for ($i = 5; $i >= 1; $i--):
                            $count = $ratingDistribution[$i] ?? 0;
                            $percentage = $totalReviewsForDist > 0 ? ($count / $totalReviewsForDist) * 100 : 0;
                        ?>
                            <div class="flex items-center text-sm">
                                <span class="w-8 text-muted-foreground"><?= $i ?> <i class="fas fa-star text-yellow-400 text-xs"></i></span>
                                <div class="flex-grow bg-muted rounded-full h-2 mx-2">
                                    <div class="bg-yellow-400 h-2 rounded-full" style="width: <?= $percentage ?>%"></div>
                                </div>
                                <span class="w-8 text-right text-muted-foreground"><?= $count ?></span>
                            </div>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Kolom Kanan: Daftar Ulasan -->
        <div class="lg:col-span-2">
            <?php if (!empty($reviews)): ?>
                <div class="space-y-6">
                    <?php foreach ($reviews as $review): ?>
                        <div class="bg-background border rounded-lg p-5 flex space-x-4">
                            <div class="flex-shrink-0">
                                <?php
                                    $profilePicture = trim((string) ($review['profile_picture'] ?? ''));
                                    $profilePicturePath = $profilePicture !== '' ? FCPATH . 'avatar/' . $profilePicture : '';
                                ?>
                                <?php if ($profilePicture !== '' && is_file($profilePicturePath)): ?>
                                    <img src="<?= base_url('avatar/' . $profilePicture) ?>" class="rounded-full h-10 w-10 object-cover" alt="<?= esc($review['full_name']) ?>">
                                <?php else: ?>
                                    <div class="rounded-full h-10 w-10 bg-muted flex items-center justify-center text-muted-foreground">
                                        <i class="fas fa-user text-lg"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="flex-grow">
                                <div class="flex items-center justify-between mb-1">
                                    <h5 class="font-semibold text-foreground text-sm"><?= esc($review['full_name']) ?></h5>
                                    <p class="text-xs text-muted-foreground"><?= date('d M Y', strtotime($review['created_at'])) ?></p>
                                </div>
                                <div class="flex items-center text-yellow-400 mb-2">
                                    <?php for ($i = 1; $i <= 5; $i++): ?><i class="fas fa-star <?= $i <= $review['rating'] ? '' : 'text-gray-300' ?> text-sm mr-0.5"></i><?php endfor; ?>
                                </div>
                                <p class="text-sm text-foreground leading-relaxed"><?= nl2br(esc($review['review'])) ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="bg-background border rounded-lg p-12 text-center">
                    <i class="fas fa-comment-slash fa-3x text-muted-foreground mb-4"></i>
                    <h3 class="text-xl font-semibold text-foreground mb-2">Belum Ada Ulasan</h3>
                    <p class="text-muted-foreground mb-4">Jadilah yang pertama memberikan ulasan untuk kursus ini.</p>
                    <?php if ($isEnrolled && !$userReview): ?>
                         <a href="<?= site_url('course/' . $course['id'] . '/reviews/create') ?>" class="px-4 py-2 bg-primary text-primary-foreground rounded-md hover:bg-primary/90 text-sm font-medium inline-flex items-center">
                            <i class="fas fa-plus mr-2"></i> Tulis Ulasan Sekarang
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
