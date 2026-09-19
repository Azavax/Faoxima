<?php
declare(strict_types=1);

/**
 * Authentication for the independent web storefront (username + password).
 * Separate from the Telegram bot users. Passwords are hashed with the PHP
 * default algorithm (bcrypt/argon2). Sessions are native PHP sessions.
 */

const FXWEB_USERNAME_MIN = 3;
const FXWEB_USERNAME_MAX = 32;
const FXWEB_PASSWORD_MIN = 8;
const FXWEB_PASSWORD_MAX = 128;

/** Validate a username; returns an error string or null if OK. */
function fxweb_validate_username(string $u): ?string
{
    $len = strlen($u);
    if ($len < FXWEB_USERNAME_MIN || $len > FXWEB_USERNAME_MAX) {
        return 'نام کاربری باید بین ۳ تا ۳۲ کاراکتر باشد.';
    }
    if (!preg_match('/^[A-Za-z0-9_]+$/', $u)) {
        return 'نام کاربری فقط می‌تواند شامل حروف انگلیسی، عدد و آندرلاین ( _ ) باشد.';
    }
    return null;
}

/** Validate a password; returns an error string or null if OK. */
function fxweb_validate_password(string $p): ?string
{
    $len = strlen($p);
    if ($len < FXWEB_PASSWORD_MIN || $len > FXWEB_PASSWORD_MAX) {
        return 'رمز عبور باید حداقل ۸ کاراکتر باشد.';
    }
    return null;
}

/** True if a username is already taken (case-insensitive). */
function fxweb_username_exists(PDO $pdo, string $username): bool
{
    $st = $pdo->prepare("SELECT 1 FROM web_users WHERE username_lc = :lc LIMIT 1");
    $st->execute([':lc' => mb_strtolower($username, 'UTF-8')]);
    return (bool) $st->fetchColumn();
}

/**
 * Create a new web account. Returns [true, userId] on success or
 * [false, errorMessage] on failure.
 */
function fxweb_register(PDO $pdo, string $username, string $password)
{
    if ($err = fxweb_validate_username($username)) {
        return [false, $err];
    }
    if ($err = fxweb_validate_password($password)) {
        return [false, $err];
    }
    if (fxweb_username_exists($pdo, $username)) {
        return [false, 'این نام کاربری قبلاً گرفته شده است.'];
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    try {
        $st = $pdo->prepare(
            "INSERT INTO web_users (username, username_lc, password_hash, balance, status, register_ip, created_at)
             VALUES (:u, :lc, :h, 0, 'active', :ip, NOW())"
        );
        $st->execute([
            ':u'  => $username,
            ':lc' => mb_strtolower($username, 'UTF-8'),
            ':h'  => $hash,
            ':ip' => fxweb_ip(),
        ]);
        return [true, (int) $pdo->lastInsertId()];
    } catch (\PDOException $e) {
        // Unique key race → duplicate username.
        if ($e->getCode() === '23000') {
            return [false, 'این نام کاربری قبلاً گرفته شده است.'];
        }
        error_log('[fxweb register] ' . $e->getMessage());
        return [false, 'خطای داخلی هنگام ثبت‌نام. بعداً دوباره تلاش کنید.'];
    }
}

/**
 * Verify credentials and start a logged-in session. Returns [true, null] or
 * [false, errorMessage].
 */
function fxweb_login(PDO $pdo, string $username, string $password)
{
    $st = $pdo->prepare("SELECT * FROM web_users WHERE username_lc = :lc LIMIT 1");
    $st->execute([':lc' => mb_strtolower($username, 'UTF-8')]);
    $user = $st->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($password, (string) $user['password_hash'])) {
        return [false, 'نام کاربری یا رمز عبور اشتباه است.'];
    }
    if (($user['status'] ?? 'active') !== 'active') {
        return [false, 'این حساب غیرفعال شده است.'];
    }

    // Rehash if the algorithm/cost changed.
    if (password_needs_rehash((string) $user['password_hash'], PASSWORD_DEFAULT)) {
        $new = password_hash($password, PASSWORD_DEFAULT);
        $up = $pdo->prepare("UPDATE web_users SET password_hash = :h WHERE id = :id");
        $up->execute([':h' => $new, ':id' => $user['id']]);
    }

    // Prevent session fixation.
    session_regenerate_id(true);
    $_SESSION['web_uid'] = (int) $user['id'];
    $pdo->prepare("UPDATE web_users SET last_login_at = NOW() WHERE id = :id")
        ->execute([':id' => $user['id']]);

    return [true, null];
}

/** Log out the current web user. */
function fxweb_logout(): void
{
    unset($_SESSION['web_uid']);
    session_regenerate_id(true);
}

/** Current logged-in web user row, or null. */
function fxweb_current_user(PDO $pdo): ?array
{
    $uid = $_SESSION['web_uid'] ?? null;
    if (!$uid) {
        return null;
    }
    $st = $pdo->prepare("SELECT * FROM web_users WHERE id = :id LIMIT 1");
    $st->execute([':id' => (int) $uid]);
    $user = $st->fetch(PDO::FETCH_ASSOC);
    if (!$user || ($user['status'] ?? '') !== 'active') {
        unset($_SESSION['web_uid']);
        return null;
    }
    return $user;
}

/** Require a logged-in user or redirect to the login page. */
function fxweb_require_login(PDO $pdo): array
{
    $u = fxweb_current_user($pdo);
    if (!$u) {
        fxweb_redirect(fxweb_url('login'));
    }
    return $u;
}
