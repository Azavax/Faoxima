<?php
declare(strict_types=1);

/** Return (creating if needed) the per-session CSRF token. */
function fxweb_csrf_token(): string
{
    if (empty($_SESSION['fxweb_csrf']) || !is_string($_SESSION['fxweb_csrf'])) {
        $_SESSION['fxweb_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['fxweb_csrf'];
}

/** Hidden input for forms. */
function fxweb_csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . fxweb_e(fxweb_csrf_token()) . '">';
}

/** Constant-time verification of a submitted token. */
function fxweb_csrf_check(): bool
{
    $sent = $_POST['_csrf'] ?? '';
    return is_string($sent) && $sent !== ''
        && !empty($_SESSION['fxweb_csrf'])
        && hash_equals((string) $_SESSION['fxweb_csrf'], $sent);
}
