<?php
/** @var string|null $error */
/** @var string $username */
$captcha = $_SESSION['fxweb_captcha'] ?? null;
?>
<div class="card auth-card">
    <h2>ثبت‌نام</h2>

    <?php if (!empty($error)): ?>
        <div class="alert alert-error"><?= fxweb_e($error) ?></div>
    <?php endif; ?>

    <form method="post" action="<?= fxweb_e(fxweb_url('register')) ?>" autocomplete="off" novalidate>
        <?= fxweb_csrf_field() ?>

        <label for="username">نام کاربری</label>
        <input type="text" id="username" name="username" value="<?= fxweb_e($username) ?>"
               dir="ltr" maxlength="32" required
               placeholder="حروف انگلیسی، عدد و _">

        <label for="password">رمز عبور</label>
        <input type="password" id="password" name="password" dir="ltr" minlength="8" maxlength="128" required
               placeholder="حداقل ۸ کاراکتر">

        <label for="password2">تکرار رمز عبور</label>
        <input type="password" id="password2" name="password2" dir="ltr" minlength="8" maxlength="128" required>

        <label for="captcha">کد امنیتی</label>
        <?php if (is_array($captcha) && ($captcha['type'] ?? '') === 'image'): ?>
            <div class="captcha-row">
                <img class="captcha-img" src="<?= fxweb_e(fxweb_base()) ?>captcha.php?ts=<?= time() ?>" alt="کد امنیتی" width="150" height="50">
                <input type="text" id="captcha" name="captcha" dir="ltr" maxlength="6" required
                       inputmode="latin" autocomplete="off" placeholder="کد داخل تصویر">
            </div>
        <?php else: ?>
            <div class="captcha-row">
                <span class="captcha-math">حاصل: <?= fxweb_e($captcha['question'] ?? '') ?> = ؟</span>
                <input type="text" id="captcha" name="captcha" dir="ltr" maxlength="4" required
                       inputmode="numeric" autocomplete="off" placeholder="پاسخ">
            </div>
        <?php endif; ?>

        <button type="submit" class="btn btn-primary btn-block">ساخت حساب</button>
    </form>

    <p class="auth-alt">حساب دارید؟ <a href="<?= fxweb_e(fxweb_url('login')) ?>">وارد شوید</a></p>
</div>
