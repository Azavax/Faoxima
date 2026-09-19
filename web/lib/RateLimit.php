<?php
declare(strict_types=1);

/**
 * Very small IP-based throttle backed by web_login_attempts. Used to slow
 * brute-force logins and mass registration. Not a substitute for the captcha —
 * a second layer next to it.
 */

/** Record one attempt of a given kind ("login" | "register") for this IP. */
function fxweb_rl_hit(PDO $pdo, string $kind): void
{
    $st = $pdo->prepare("INSERT INTO web_login_attempts (ip, kind, created_at) VALUES (:ip, :k, NOW())");
    $st->execute([':ip' => fxweb_ip(), ':k' => $kind]);
}

/** Count attempts of a kind from this IP within the last $seconds. */
function fxweb_rl_count(PDO $pdo, string $kind, int $seconds): int
{
    // $seconds is always an internal integer constant (never user input), so
    // it is cast and inlined — MySQL does not accept a placeholder inside
    // INTERVAL ... SECOND.
    $seconds = max(1, (int) $seconds);
    $st = $pdo->prepare(
        "SELECT COUNT(*) FROM web_login_attempts
         WHERE ip = :ip AND kind = :k AND created_at >= (NOW() - INTERVAL {$seconds} SECOND)"
    );
    $st->bindValue(':ip', fxweb_ip());
    $st->bindValue(':k', $kind);
    $st->execute();
    return (int) $st->fetchColumn();
}

/** True when the IP is over the limit for this kind. */
function fxweb_rl_blocked(PDO $pdo, string $kind, int $max, int $seconds): bool
{
    return fxweb_rl_count($pdo, $kind, $seconds) >= $max;
}

/** Occasionally purge rows older than a day so the table stays small. */
function fxweb_rl_gc(PDO $pdo): void
{
    if (random_int(1, 50) === 1) {
        @$pdo->exec("DELETE FROM web_login_attempts WHERE created_at < (NOW() - INTERVAL 1 DAY)");
    }
}
