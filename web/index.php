<?php
declare(strict_types=1);

/**
 * Front controller for the web storefront. Routing is query-based
 * (index.php?page=...) so it works identically whether the site is deployed at
 * a subdomain root or inside a subfolder — no rewrite rules required.
 *
 * Phase 1 pages: home, register, login, logout, dashboard.
 * (Phases 2+: store, checkout, payment, services.)
 */

require __DIR__ . '/bootstrap.php';

/** @var PDO $pdo */
$pdo = $GLOBALS['fxpdo'];
fxweb_rl_gc($pdo);

$page   = isset($_GET['page']) ? (string) $_GET['page'] : 'home';
$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

switch ($page) {

    // ---------------------------------------------------------------- home
    case 'home':
        $user = fxweb_current_user($pdo);
        if ($user) {
            fxweb_redirect(fxweb_url('dashboard'));
        }
        fxweb_view('home', [], 'فروشگاه وی‌پی‌ان');
        break;

    // ------------------------------------------------------------ register
    case 'register':
        if (fxweb_current_user($pdo)) {
            fxweb_redirect(fxweb_url('dashboard'));
        }

        $error = null;
        $username = '';

        if ($method === 'POST') {
            $username = trim((string) ($_POST['username'] ?? ''));
            $password = (string) ($_POST['password'] ?? '');
            $password2 = (string) ($_POST['password2'] ?? '');
            $captcha = (string) ($_POST['captcha'] ?? '');

            if (!fxweb_csrf_check()) {
                $error = 'نشست شما منقضی شده است. صفحه را دوباره باز کنید.';
            } elseif (fxweb_rl_blocked($pdo, 'register', 8, 3600)) {
                $error = 'تعداد تلاش‌های ثبت‌نام از این آی‌پی زیاد است. کمی بعد دوباره تلاش کنید.';
            } elseif (!fxweb_captcha_check($captcha)) {
                $error = 'کد امنیتی (کپچا) اشتباه است.';
            } elseif ($password !== $password2) {
                $error = 'رمز عبور و تکرار آن یکسان نیستند.';
            } else {
                [$ok, $res] = fxweb_register($pdo, $username, $password);
                fxweb_rl_hit($pdo, 'register');
                if ($ok) {
                    session_regenerate_id(true);
                    $_SESSION['web_uid'] = (int) $res;
                    fxweb_flash('حساب شما با موفقیت ساخته شد. خوش آمدید!', 'success');
                    fxweb_redirect(fxweb_url('dashboard'));
                } else {
                    $error = (string) $res;
                }
            }
        }

        fxweb_captcha_new();
        fxweb_view('register', ['error' => $error, 'username' => $username], 'ثبت‌نام');
        break;

    // --------------------------------------------------------------- login
    case 'login':
        if (fxweb_current_user($pdo)) {
            fxweb_redirect(fxweb_url('dashboard'));
        }

        $error = null;
        $username = '';

        if ($method === 'POST') {
            $username = trim((string) ($_POST['username'] ?? ''));
            $password = (string) ($_POST['password'] ?? '');

            if (!fxweb_csrf_check()) {
                $error = 'نشست شما منقضی شده است. صفحه را دوباره باز کنید.';
            } elseif (fxweb_rl_blocked($pdo, 'login', 15, 900)) {
                $error = 'تلاش‌های ناموفق زیاد است. چند دقیقه صبر کنید و دوباره تلاش کنید.';
            } else {
                [$ok, $err] = fxweb_login($pdo, $username, $password);
                if ($ok) {
                    fxweb_redirect(fxweb_url('dashboard'));
                } else {
                    fxweb_rl_hit($pdo, 'login');
                    $error = (string) $err;
                }
            }
        }

        fxweb_view('login', ['error' => $error, 'username' => $username], 'ورود');
        break;

    // -------------------------------------------------------------- logout
    case 'logout':
        // Only act on POST + CSRF to prevent logout-CSRF.
        if ($method === 'POST' && fxweb_csrf_check()) {
            fxweb_logout();
        }
        fxweb_redirect(fxweb_url('home'));
        break;

    // ----------------------------------------------------------- dashboard
    case 'dashboard':
        $user = fxweb_require_login($pdo);
        fxweb_view('dashboard', ['user' => $user], 'حساب کاربری');
        break;

    // ------------------------------------------------------------- default
    default:
        http_response_code(404);
        fxweb_view('home', [], 'یافت نشد');
        break;
}
