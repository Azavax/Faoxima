<?php
declare(strict_types=1);

/**
 * Faoxima Web Storefront — bootstrap.
 * Independent browser storefront. Web accounts live in their OWN tables
 * (web_users) and are SEPARATE from the Telegram bot users. It reuses the
 * bot's database connection (config.php) and, in later phases, the bot's
 * provisioning engine (panels.php / ManagePanel) and product tables — without
 * modifying the bot itself.
 */

if (!defined('FAOXIMA_WEB')) {
    define('FAOXIMA_WEB', true);
}

// Reuse the bot's config (DB credentials + PDO). config.php has no side effect
// that processes a Telegram update, so including it here is safe.
$__fxRoot = dirname(__DIR__);
if (!defined('REFACTORED_LEGACY_ROOT')) {
    define('REFACTORED_LEGACY_ROOT', $__fxRoot);
}
require_once $__fxRoot . '/config.php';

/** @var PDO|null $pdo */
$pdo = $GLOBALS['pdo'] ?? null;
if (!($pdo instanceof PDO)) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    exit('پایگاه‌داده پیکربندی نشده است. اطلاعات اتصال را در config.php کامل کنید.');
}
$GLOBALS['fxpdo'] = $pdo;

// --- Secure session ---
if (session_status() === PHP_SESSION_NONE) {
    $secure = (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_name('fxweb');
    @session_start();
}

require_once __DIR__ . '/lib/Schema.php';
fxweb_migrate($pdo);

require_once __DIR__ . '/lib/helpers.php';
require_once __DIR__ . '/lib/Csrf.php';
require_once __DIR__ . '/lib/RateLimit.php';
require_once __DIR__ . '/lib/Captcha.php';
require_once __DIR__ . '/lib/Auth.php';
require_once __DIR__ . '/lib/View.php';
