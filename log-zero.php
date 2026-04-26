<?php
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed'], JSON_UNESCAPED_UNICODE);
    exit;
}

$query = trim((string) ($_POST['query'] ?? ''));
if ($query === '') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Empty query'], JSON_UNESCAPED_UNICODE);
    exit;
}

$query = preg_replace('/\s+/', ' ', $query);
if (function_exists('mb_substr')) {
    $query = mb_substr($query, 0, 200, 'UTF-8');
} else {
    $query = substr($query, 0, 200);
}

$line = date('Y-m-d H:i:s') . ' | ' . $query;
file_put_contents(__DIR__ . '/x.txt', $line . PHP_EOL, FILE_APPEND | LOCK_EX);

echo json_encode(['ok' => true], JSON_UNESCAPED_UNICODE);
