<?= $this->include('theme/header') ?>
<?= $this->include('theme/sidebar') ?>

<main class="main-content">
    <?php if (session()->getFlashdata('msg')): ?><div class="alert glass-panel">✅ <?= esc(session()->getFlashdata('msg')) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="alert glass-panel">⚠️ <?= esc($error) ?></div><?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?><div class="alert glass-panel">⚠️ <?= esc(session()->getFlashdata('error')) ?></div><?php endif; ?>
    <?php $errors = session()->getFlashdata('errors') ?? []; ?>

    <div class="card glass-panel">
        <h2 style="font-size:2rem;">Categories 🏷️</h2>
        <form method="get" class="premium-form" style="margin-bottom:10px;">
            <div class="form-group"><label>Search</label><input type="text" name="q" value="<?= esc($filters['q'] ?? '') ?>" placeholder="Category name"></div>
            <button type="submit" class="btn-primary">Filter</button>
        </form>
        <form action="<?= site_url('admin/categories/store') ?>" method="post" class="premium-form">
            <?= csrf_field() ?>
            <div class="form-group"><label>Name</label><input type="text" name="name" value="<?= old('name') ?>" required><?php if(isset($errors['name'])): ?><small style="color:#fca5a5;"><?= esc($errors['name']) ?></small><?php endif; ?></div>
            <div class="form-group"><label>Description</label><input type="text" name="description"></div>
            <div class="form-group"><label>Active</label><select name="is_active"><option value="1">Yes</option><option value="0">No</option></select></div>
            <button type="submit" class="btn-primary">Add Category</button>
        </form>
        <div class="table-responsive glass-panel">
            <table class="premium-table">
                <thead><tr><th>NAME</th><th>DESCRIPTION</th><th>STATUS</th></tr></thead>
                <tbody>
                <?php if (!empty($categories)): foreach ($categories as $c): ?>
                    <tr><td><?= esc($c['name']) ?></td><td><?= esc($c['description'] ?? '-') ?></td><td><?= (int)($c['is_active'] ?? 1) === 1 ? 'Active' : 'Inactive' ?></td></tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="3" style="text-align:center;padding:30px;">No categories yet.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if (isset($pager)): ?><div style="margin-top:12px;"><?= $pager->links() ?></div><?php endif; ?>
    </div>
</main>

<?= $this->include('theme/footer') ?>
