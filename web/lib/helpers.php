<?php
declare(strict_types=1);

/** HTML-escape. */
function fxweb_e($s): string
{
    return htmlspecialchars((string) $s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/** Redirect within the app and stop. */
function fxweb_redirect(string $to): void
{
    header('Location: ' . $to);
    exit;
}

/**
 * Base path of the storefront (the directory index.php lives in), always with
 * a trailing slash. Works whether the site is at a subdomain root or in a
 * subfolder, so links never hard-code the deploy location.
 */
function fxweb_base(): string
{
    $dir = str_replace('\\', '/', dirname((string) ($_SERVER['SCRIPT_NAME'] ?? '/')));
    $dir = rtrim($dir, '/');
    return $dir === '' ? '/' : $dir . '/';
}

/** Build a URL for a page in the app. */
function fxweb_url(string $page = 'home', array $params = []): string
{
    $params = ['page' => $page] + $params;
    return fxweb_base() . 'index.php?' . http_build_query($params);
}

/** Best-effort real client IP (host sits behind a proxy/CDN sometimes). */
function fxweb_ip(): string
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    return is_string($ip) && filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '0.0.0.0';
}

/** One-time flash message stored in the session. */
function fxweb_flash(?string $msg = null, string $type = 'info')
{
    if ($msg !== null) {
        $_SESSION['fxweb_flash'] = ['type' => $type, 'msg' => $msg];
        return null;
    }
    if (!empty($_SESSION['fxweb_flash'])) {
        $f = $_SESSION['fxweb_flash'];
        unset($_SESSION['fxweb_flash']);
        return $f;
    }
    return null;
}
