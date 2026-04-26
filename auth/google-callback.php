<?php
require __DIR__ . '/common.php';

$clientId = $config['google_client_id'] ?? '';
$clientSecret = $config['google_client_secret'] ?? '';
$redirectUri = $config['google_redirect_uri'] ?? '';

if ($clientId === '' || $clientSecret === '' || $redirectUri === '') {
    http_response_code(500);
    echo 'Google OAuth ayarları eksik. config.php dosyasını doldurun.';
    exit;
}

if (!isset($_GET['state']) || ($_GET['state'] ?? '') !== ($_SESSION['oauth_state'] ?? '')) {
    http_response_code(400);
    echo 'Geçersiz oturum doğrulaması.';
    exit;
}

if (!isset($_GET['code'])) {
    http_response_code(400);
    echo 'Google doğrulama kodu alınamadı.';
    exit;
}

$tokenPayload = http_build_query([
    'code' => $_GET['code'],
    'client_id' => $clientId,
    'client_secret' => $clientSecret,
    'redirect_uri' => $redirectUri,
    'grant_type' => 'authorization_code'
]);

$tokenContext = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
        'content' => $tokenPayload,
        'timeout' => 10
    ]
]);

$tokenResponse = file_get_contents('https://oauth2.googleapis.com/token', false, $tokenContext);
if ($tokenResponse === false) {
    http_response_code(500);
    echo 'Token isteği başarısız.';
    exit;
}

$tokenData = json_decode($tokenResponse, true);
if (!is_array($tokenData) || empty($tokenData['access_token'])) {
    http_response_code(500);
    echo 'Token yanıtı geçersiz.';
    exit;
}

$accessToken = $tokenData['access_token'];

$userContext = stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => "Authorization: Bearer {$accessToken}\r\n",
        'timeout' => 10
    ]
]);

$userResponse = file_get_contents('https://www.googleapis.com/oauth2/v3/userinfo', false, $userContext);
if ($userResponse === false) {
    http_response_code(500);
    echo 'Kullanıcı bilgisi alınamadı.';
    exit;
}

$userData = json_decode($userResponse, true);
if (!is_array($userData) || empty($userData['email'])) {
    http_response_code(500);
    echo 'Kullanıcı bilgisi geçersiz.';
    exit;
}

if (!is_email_allowed($config, $userData['email'] ?? null)) {
    http_response_code(403);
    echo 'Bu hesapla giriş izni yok.';
    exit;
}

$_SESSION['user'] = [
    'email' => $userData['email'] ?? '',
    'name' => $userData['name'] ?? '',
    'picture' => $userData['picture'] ?? ''
];

unset($_SESSION['oauth_state']);

header('Location: /store.php');
exit;
