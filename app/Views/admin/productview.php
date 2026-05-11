<?php
/**
 * @var array $products
 */
?>
<!-- 1. Include Shared Header (CSS/Theme) -->
<?= $this->include('theme/header') ?>

<!-- 2. Include Shared Sidebar -->
<?= $this->include('theme/sidebar') ?>

<!-- Vue App Mount Point -->
<main class="main-content">
    <div id="admin-products-app"></div>
</main>

<!-- Load built assets -->
<?php
$distPath = FCPATH . 'public/dist/assets/';
$jsFile = '';
$cssFile = '';

if (is_dir($distPath)) {
    $files = scandir($distPath);
    foreach ($files as $file) {
        if (str_starts_with($file, 'admin-products') && str_ends_with($file, '.js')) {
            $jsFile = base_url('public/dist/assets/' . $file);
        }
        if (str_starts_with($file, 'admin-products') && str_ends_with($file, '.css')) {
            $cssFile = base_url('public/dist/assets/' . $file);
        }
    }
}
?>

<?php if ($cssFile): ?>
    <link rel="stylesheet" href="<?= $cssFile ?>">
<?php endif; ?>

<?php if ($jsFile): ?>
    <script type="module" src="<?= $jsFile ?>"></script>
<?php endif; ?>

<?= $this->include('theme/footer') ?>
