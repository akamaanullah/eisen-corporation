<?php 
$pageTitle = "Blog Posts Management | Eisen Admin";
include dirname(__DIR__) . '/admin/partials/header.php'; 
?>

<div class="blog-management-page">
    <div class="page-header-container mb-30">
        <div class="header-title-group">
            <h1 class="page-title">Blog Posts Management</h1>
            <p style="color: var(--color-text-muted); margin: 4px 0 0 0;">Manage your dynamic news, announcements, and importing guides.</p>
        </div>
        <div class="header-actions">
            <a href="<?= BASE_URL ?>/admin/blog/new" class="btn btn-primary">
                <i data-lucide="plus-circle"></i>
                <span>Write Article</span>
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-header-flex">
            <h3 class="card-title-sm">Published & Draft Articles</h3>
            <span class="badge badge-info"><?= count($posts) ?> Articles</span>
        </div>

        <div class="table-responsive">
            <table class="data-table-minimal">
                <thead>
                    <tr>
                        <th style="width: 60px;">ID</th>
                        <th style="width: 100px;">Banner</th>
                        <th>Title / Slug</th>
                        <th>Category</th>
                        <th>Author</th>
                        <th>Published Date</th>
                        <th style="width: 120px; text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($posts)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; color: var(--color-text-muted); padding: 40px;">
                                No articles found. Click "Write Article" to publish your first post!
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($posts as $post): ?>
                            <tr>
                                <td><?= $post['id'] ?></td>
                                <td>
                                    <?php 
                                    $imgUrl = $post['image'];
                                    if (empty($imgUrl)) {
                                        $imgSrc = 'https://images.unsplash.com/photo-1618843479313-40f8afb4b4d8?w=150&q=80';
                                    } elseif (strpos($imgUrl, 'http') === 0) {
                                        $imgSrc = $imgUrl;
                                    } elseif (strpos($imgUrl, '/') === 0) {
                                        $imgSrc = BASE_URL . $imgUrl;
                                    } else {
                                        $imgSrc = "https://images.unsplash.com/{$imgUrl}?w=150&q=80";
                                    }
                                    ?>
                                    <img src="<?= htmlspecialchars($imgSrc) ?>" alt="Post Image" style="width: 80px; height: 48px; object-fit: cover; border-radius: var(--radius-sm); border: 1px solid var(--color-silver-300);">
                                </td>
                                <td>
                                    <div style="font-weight: 700; color: var(--color-navy-950); font-size: 14px;"><?= htmlspecialchars($post['title']) ?></div>
                                    <div style="font-size: 11px; color: var(--color-text-muted); margin-top: 3px;">
                                        Slug: <code><?= htmlspecialchars($post['slug']) ?></code> · Read Time: <strong><?= $post['read_min'] ?> mins</strong>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-info" style="font-size: 11px;"><?= htmlspecialchars($post['category']) ?></span>
                                </td>
                                <td><strong><?= htmlspecialchars($post['author']) ?></strong></td>
                                <td><?= date('M d, Y', strtotime($post['published_date'])) ?></td>
                                <td style="text-align: right;">
                                    <a href="<?= BASE_URL ?>/admin/blog/edit/<?= $post['id'] ?>" class="btn-icon-sm" title="Edit Article">
                                        <i data-lucide="edit"></i>
                                    </a>
                                    <form method="POST" action="<?= BASE_URL ?>/admin/blog/delete/<?= $post['id'] ?>" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this article? This action cannot be undone.');">
                                        <?= $this->csrf_field() ?>
                                        <button type="submit" class="btn-icon-sm" style="color: var(--color-danger); border-color: rgba(239, 68, 68, 0.2); background: var(--color-danger-soft);" title="Delete Article">
                                            <i data-lucide="trash-2"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include dirname(__DIR__) . '/admin/partials/footer.php'; ?>
