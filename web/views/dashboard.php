<?php
/** @var array $user */
$balance = number_format((float) ($user['balance'] ?? 0));
?>
<div class="card">
    <h2>سلام، <?= fxweb_e($user['username']) ?> 👋</h2>
    <div class="wallet">
        <span class="wallet-label">موجودی کیف پول</span>
        <span class="wallet-amount"><?= fxweb_e($balance) ?> <small>تومان</small></span>
    </div>
</div>

<div class="grid-cards">
    <div class="card muted">
        <h3>🛒 خرید سرویس</h3>
        <p>به‌زودی: انتخاب پلن و خرید مستقیم از سایت.</p>
        <button class="btn btn-ghost" disabled>به‌زودی</button>
    </div>
    <div class="card muted">
        <h3>📶 سرویس‌های من</h3>
        <p>به‌زودی: مشاهده‌ی اشتراک‌ها، مصرف و تمدید.</p>
        <button class="btn btn-ghost" disabled>به‌زودی</button>
    </div>
    <div class="card muted">
        <h3>💳 افزایش موجودی</h3>
        <p>به‌زودی: شارژ کیف پول از طریق درگاه‌ها.</p>
        <button class="btn btn-ghost" disabled>به‌زودی</button>
    </div>
</div>

<p class="hint">فاز بعدی: اتصال ویترین محصولات و پرداخت به همین حساب.</p>
