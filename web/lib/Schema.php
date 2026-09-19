<?php
declare(strict_types=1);

/**
 * Idempotent schema for the web storefront. Runs on every request but does
 * nothing once the tables exist (CREATE TABLE IF NOT EXISTS). Keeps the site
 * self-contained — no separate migration step to run on the host.
 */
function fxweb_migrate(PDO $pdo): void
{
    static $done = false;
    if ($done) {
        return;
    }

    // Web accounts — independent identity (username + password), own balance.
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS web_users (
            id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            username      VARCHAR(32)  NOT NULL,
            username_lc   VARCHAR(32)  NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            balance       BIGINT       NOT NULL DEFAULT 0,
            status        VARCHAR(16)  NOT NULL DEFAULT 'active',
            register_ip   VARCHAR(45)  NULL,
            created_at    DATETIME     NOT NULL,
            last_login_at DATETIME     NULL,
            UNIQUE KEY uq_web_users_username_lc (username_lc)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );

    // Throttling log for login/register attempts (per IP).
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS web_login_attempts (
            id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            ip         VARCHAR(45) NOT NULL,
            kind       VARCHAR(16) NOT NULL,
            created_at DATETIME    NOT NULL,
            KEY idx_ip_kind_time (ip, kind, created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );

    $done = true;
}
