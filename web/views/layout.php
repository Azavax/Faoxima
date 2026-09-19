<?php
/** @var string $__content */
/** @var string $__title */
/** @var array|null $currentUser */
$flash = fxweb_flash();
$base = fxweb_base();
?><!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title><?= fxweb_e($__title) ?></title>
    <link rel="stylesheet" href="<?= fxweb_e($base) ?>assets/style.css">
</head>
<body>
<header class="site-header">
    <a class="brand" href="<?= fxweb_e(fxweb_url('home')) ?>">🛡️ فروشگاه وی‌پی‌ان</a>
    <nav class="site-nav">
        <?php if (!empty($currentUser)): ?>
            <a href="<?= fxweb_e(fxweb_url('dashboard')) ?>">حساب کاربری</a>
            <form method="post" action="<?= fxweb_e(fxweb_url('logout')) ?>" class="inline-form">
                <?= fxweb_csrf_field() ?>
                <button type="submit" class="link-btn">خروج</button>
            </form>
        <?php else: ?>
            <a href="<?= fxweb_e(fxweb_url('login')) ?>">ورود</a>
            <a class="btn-nav" href="<?= fxweb_e(fxweb_url('register')) ?>">ثبت‌نام</a>
        <?php endif; ?>
    </nav>
</header>

<main class="container">
    <?php if ($flash): ?>
        <div class="alert alert-<?= fxweb_e($flash['type']) ?>"><?= fxweb_e($flash['msg']) ?></div>
    <?php endif; ?>
    <?= $__content ?>
</main>

<footer class="site-footer">
    <span>© <?= date('Y') ?> — همه‌ی حقوق محفوظ است.</span>
</footer>
</body>
</html>
