<?php 
$isEdit = $post !== null;
$pageTitle = ($isEdit ? "Edit Article" : "Write Article") . " | Eisen Admin";
include dirname(__DIR__) . '/admin/partials/header.php'; 
?>

<!-- Load Quill WYSIWYG Styles and Script -->
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">
<style>
    .ql-toolbar {
        background-color: var(--color-silver-100) !important;
        border-color: var(--color-silver-300) !important;
        border-radius: var(--radius-sm) var(--radius-sm) 0 0;
    }
    .ql-container {
        border-color: var(--color-silver-300) !important;
        border-radius: 0 0 var(--radius-sm) var(--radius-sm);
        font-family: var(--font-body) !important;
        font-size: 14px !important;
    }
    .ql-editor {
        min-height: 350px;
        background-color: var(--color-white);
    }
</style>

<div class="blog-form-page">
    <div class="page-header-container mb-30">
        <div class="header-title-group">
            <h1 class="page-title"><?= $isEdit ? "Edit Article" : "Write New Article" ?></h1>
            <p style="color: var(--color-text-muted); margin: 4px 0 0 0;">
                <?= $isEdit ? "Modifying article ID: " . $post['id'] : "Draft a fresh piece for the Eisen Corporation Blog feed." ?>
            </p>
        </div>
        <div class="header-actions">
            <a href="<?= BASE_URL ?>/admin/blog" class="btn btn-outline">
                <i data-lucide="arrow-left"></i>
                <span>Back to list</span>
            </a>
        </div>
    </div>

    <div class="card">
        <form id="blogForm" action="<?= BASE_URL ?>/admin/blog/save" method="POST" enctype="multipart/form-data">
            <?= $this->csrf_field() ?>
            <input type="hidden" name="id" value="<?= $isEdit ? $post['id'] : 0 ?>">
            
            <div class="form-group">
                <label class="form-label" for="blog-title">Article Title *</label>
                <input class="form-control" type="text" id="blog-title" name="title" required value="<?= $isEdit ? htmlspecialchars($post['title']) : '' ?>" placeholder="e.g. Sourcing Hybrid SUVs from Japan Auctions">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
                <div class="form-group">
                    <label class="form-label" for="blog-slug">URL Slug (Optional)</label>
                    <input class="form-control" type="text" id="blog-slug" name="slug" value="<?= $isEdit ? htmlspecialchars($post['slug']) : '' ?>" placeholder="e.g. hybrid-suv-demand-2025 (auto-generated if empty)">
                </div>
                <div class="form-group">
                    <label class="form-label" for="blog-category">Category *</label>
                    <select class="form-control" id="blog-category" name="category" required>
                        <?php 
                        $cats = ['Japan Auctions', 'Import & Export', 'Buying Guides', 'Market & Pricing', 'Vehicle Spotlights', 'Company'];
                        $currentCat = $isEdit ? $post['category'] : 'Buying Guides';
                        foreach ($cats as $cat):
                        ?>
                            <option value="<?= $cat ?>" <?= ($currentCat === $cat) ? 'selected' : '' ?>><?= $cat ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="blog-excerpt">Excerpt / Short Description *</label>
                <textarea class="form-control" id="blog-excerpt" name="excerpt" rows="3" required placeholder="A brief 1-2 sentence description summarizing the article. This is shown on the blog feed grid cards."><?= $isEdit ? htmlspecialchars($post['excerpt']) : '' ?></textarea>
            </div>

            <!-- Quill WYSIWYG Editor container -->
            <div class="form-group">
                <label class="form-label">Article Body Content *</label>
                <div id="editor-container"></div>
                <!-- Hidden input field where content HTML is copied on submit -->
                <textarea name="content" id="hidden-content" style="display: none;"><?= $isEdit ? htmlspecialchars($post['content']) : '' ?></textarea>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label class="form-label" for="blog-author">Author</label>
                    <input class="form-control" type="text" id="blog-author" name="author" value="<?= $isEdit ? htmlspecialchars($post['author']) : 'Eisen Export Team' ?>" placeholder="e.g. Eisen Export Team">
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="blog-read_min">Read Time (Minutes)</label>
                    <input class="form-control" type="number" id="blog-read_min" name="read_min" min="1" value="<?= $isEdit ? (int)$post['read_min'] : 5 ?>">
                </div>

                <div class="form-group">
                    <label class="form-label" for="blog-date">Publish Date</label>
                    <input class="form-control" type="date" id="blog-date" name="published_date" value="<?= $isEdit ? $post['published_date'] : date('Y-m-d') ?>">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; border-top: 1px solid var(--color-silver-200); padding-top: 24px; margin-top: 10px;">
                <div class="form-group">
                    <label class="form-label">Upload Article Banner Image</label>
                    <input type="file" name="image_file" class="form-control" accept="image/*">
                    <p style="font-size: 11px; color: var(--color-text-muted); margin: 6px 0 0 0;">Best fit: 16:9 aspect ratio. Allowed extensions: jpg, jpeg, png, webp. Max 5MB.</p>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="blog-image_url">Or External Image URL / Unsplash Key</label>
                    <input class="form-control" type="text" id="blog-image_url" name="image_url" value="<?= $isEdit ? htmlspecialchars($post['image']) : '' ?>" placeholder="e.g. photo-1618843479313-40f8afb4b4d8 or http://example.com/banner.jpg">
                    <p style="font-size: 11px; color: var(--color-text-muted); margin: 6px 0 0 0;">Enter an Unsplash photo ID key or a direct image URL.</p>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 20px;">
                <a href="<?= BASE_URL ?>/admin/blog" class="btn btn-outline">Cancel</a>
                <button type="submit" class="btn btn-gold">
                    <i data-lucide="shield-check"></i>
                    <span>Publish Post</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Initialise Quill Editor
    var quill = new Quill('#editor-container', {
        theme: 'snow',
        placeholder: 'Write the complete body of the blog post. Formats like headers, bold text, links, lists, and quotes are fully supported...',
        modules: {
            toolbar: [
                [{ 'header': [1, 2, 3, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                ['link', 'blockquote', 'code-block'],
                ['clean']
            ]
        }
    });

    // 2. Pre-fill Quill Editor with existing HTML if editing
    var hiddenContent = document.getElementById('hidden-content');
    if (hiddenContent && hiddenContent.value.trim() !== '') {
        // If content contains standard tag markup, load it directly
        if (hiddenContent.value.includes('<p>') || hiddenContent.value.includes('<h2>')) {
            quill.root.innerHTML = hiddenContent.value;
        } else {
            // If it is block text (newline breaks only), convert newlines to paragraphs
            var paragraphs = hiddenContent.value.split('\n\n');
            var html = '';
            paragraphs.forEach(function(p) {
                var trimmed = p.trim();
                if (trimmed !== '') {
                    if (trimmed.indexOf('## ') === 0) {
                        html += '<h2>' + trimmed.substring(3) + '</h2>';
                    } else if (trimmed.indexOf('# ') === 0) {
                        html += '<h2>' + trimmed.substring(2) + '</h2>';
                    } else {
                        html += '<p>' + trimmed + '</p>';
                    }
                }
            });
            quill.root.innerHTML = html;
        }
    }

    // 3. Copy contents to hidden textarea on form submit
    var form = document.getElementById('blogForm');
    form.addEventListener('submit', function() {
        hiddenContent.value = quill.root.innerHTML;
    });
});
</script>

<?php include dirname(__DIR__) . '/admin/partials/footer.php'; ?>
