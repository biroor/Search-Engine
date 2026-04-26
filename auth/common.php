<?php
session_start();

$config = require __DIR__ . '/../config.php';

function is_logged_in(): bool
{
    return !empty($_SESSION['user']);
}

function require_login(): void
{
    if (!is_logged_in()) {
        header('Location: /auth/google-login.php');
        exit;
    }
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function is_email_allowed(array $config, ?string $email): bool
{
    $allowed = $config['allowed_emails'] ?? [];
    if (empty($allowed)) {
        return true;
    }
    if ($email === null) {
        return false;
    }
    return in_array($email, $allowed, true);
}
