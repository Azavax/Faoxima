<?php
declare(strict_types=1);

/**
 * Lightweight captcha to blunt automated mass registration. Uses GD for an
 * image challenge when available (the bot already requires the gd extension);
 * otherwise falls back to a plain math question rendered as text.
 */

/** Create a fresh challenge, store its answer in the session, return meta. */
function fxweb_captcha_new(): array
{
    if (function_exists('imagecreatetruecolor')) {
        $code = '';
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // no easily-confused chars
        for ($i = 0; $i < 5; $i++) {
            $code .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        }
        $_SESSION['fxweb_captcha'] = ['type' => 'image', 'answer' => $code, 'ts' => time()];
        return ['type' => 'image'];
    }

    $a = random_int(2, 9);
    $b = random_int(2, 9);
    $_SESSION['fxweb_captcha'] = ['type' => 'math', 'answer' => (string) ($a + $b), 'ts' => time()];
    return ['type' => 'math', 'question' => "{$a} + {$b}"];
}

/** Verify a submitted captcha answer (case-insensitive). One-shot: consumed. */
function fxweb_captcha_check(?string $input): bool
{
    $c = $_SESSION['fxweb_captcha'] ?? null;
    unset($_SESSION['fxweb_captcha']); // single use, even on failure
    if (!is_array($c) || !isset($c['answer'])) {
        return false;
    }
    if ((int) ($c['ts'] ?? 0) < time() - 600) {
        return false; // expired after 10 minutes
    }
    $input = strtoupper(trim((string) $input));
    return $input !== '' && hash_equals(strtoupper((string) $c['answer']), $input);
}

/** Render the current image challenge as a PNG (called by captcha.php). */
function fxweb_captcha_render_png(): void
{
    $c = $_SESSION['fxweb_captcha'] ?? null;
    $code = (is_array($c) && ($c['type'] ?? '') === 'image') ? (string) $c['answer'] : 'XXXXX';

    $w = 150;
    $h = 50;
    $img = imagecreatetruecolor($w, $h);
    $bg = imagecolorallocate($img, 245, 246, 250);
    imagefilledrectangle($img, 0, 0, $w, $h, $bg);

    // noise dots + lines
    for ($i = 0; $i < 400; $i++) {
        $c1 = imagecolorallocate($img, random_int(180, 230), random_int(180, 230), random_int(180, 230));
        imagesetpixel($img, random_int(0, $w), random_int(0, $h), $c1);
    }
    for ($i = 0; $i < 5; $i++) {
        $c2 = imagecolorallocate($img, random_int(120, 200), random_int(120, 200), random_int(120, 200));
        imageline($img, random_int(0, $w), random_int(0, $h), random_int(0, $w), random_int(0, $h), $c2);
    }

    $len = strlen($code);
    for ($i = 0; $i < $len; $i++) {
        $col = imagecolorallocate($img, random_int(20, 90), random_int(20, 90), random_int(60, 130));
        $x = 12 + $i * 26 + random_int(-2, 2);
        $y = random_int(12, 24);
        imagestring($img, 5, $x, $y, $code[$i], $col);
    }

    header('Content-Type: image/png');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    imagepng($img);
    imagedestroy($img);
}
