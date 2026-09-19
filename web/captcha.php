<?php
declare(strict_types=1);

/**
 * Renders the current session's captcha image as PNG. The challenge itself is
 * created by the page (fxweb_captcha_new) before the form is shown; this file
 * only draws whatever is stored in the session.
 */
require __DIR__ . '/bootstrap.php';

if (($_SESSION['fxweb_captcha']['type'] ?? '') !== 'image') {
    // No image challenge pending (e.g. GD unavailable → math fallback shown as text).
    http_response_code(204);
    exit;
}

fxweb_captcha_render_png();
