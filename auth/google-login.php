<?php
require __DIR__ . '/common.php';

$clientId = $config['google_client_id'] ?? '';
$redirectUri = $config['google_redirect_uri'] ?? '';

if ($clientId === '' || $redirectUri === '') {
    http_response_code(500);
    echo 'Google OAuth ayarları eksik. config.php dosyasını doldurun.';
    exit;
}

$state = bin2hex(random_bytes(16));
$_SESSION['oauth_state'] = $state;

$params = http_build_query([
    'client_id' => $clientId,
    'redirect_uri' => $redirectUri,
    'response_type' => 'code',
    'scope' => 'openid email profile',
    'access_type' => 'online',
    'prompt' => 'select_account',
    'state' => $state
]);

$authUrl = 'https://accounts.google.com/o/oauth2/v2/auth?' . $params;
header('Location: ' . $authUrl);
exit;
