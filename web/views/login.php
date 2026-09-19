<?php
/** @var string|null $error */
/** @var string $username */
?>
<div class="card auth-card">
    <h2>ورود</h2>

    <?php if (!empty($error)): ?>
        <div class="alert alert-error"><?= fxweb_e($error) ?></div>
    <?php endif; ?>

    <form method="post" action="<?= fxweb_e(fxweb_url('login')) ?>" autocomplete="off" novalidate>
        <?= fxweb_csrf_field() ?>

        <label for="username">نام کاربری</label>
        <input type="text" id="username" name="username" value="<?= fxweb_e($username) ?>"
               dir="ltr" maxlength="32" required>

        <label for="password">رمز عبور</label>
        <input type="password" id="password" name="password" dir="ltr" maxlength="128" required>

        <button type="submit" class="btn btn-primary btn-block">ورود</button>
    </form>

    <p class="auth-alt">حساب ندارید؟ <a href="<?= fxweb_e(fxweb_url('register')) ?>">ثبت‌نام کنید</a></p>
</div>
