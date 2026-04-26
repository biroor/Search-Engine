<?php
header('Content-Type: application/json; charset=utf-8');

$entries = [];

$brandFile = __DIR__ . '/marka.txt';
if (is_file($brandFile)) {
    $lines = file($brandFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines !== false) {
        foreach ($lines as $line) {
            $decoded = json_decode($line, true);
            if (!is_array($decoded)) {
                continue;
            }
            if (empty($decoded['title']) || empty($decoded['url'])) {
                continue;
            }
            $entries[] = [
                'title' => (string) $decoded['title'],
                'url' => (string) $decoded['url'],
                'description' => isset($decoded['description']) ? (string) $decoded['description'] : '',
                'keywords' => isset($decoded['keywords']) && is_array($decoded['keywords']) ? array_values($decoded['keywords']) : [],
                'icon' => isset($decoded['icon']) ? (string) $decoded['icon'] : '',
                'type' => 'brand',
                'badge' => isset($decoded['badge']) && $decoded['badge'] !== '' ? (string) $decoded['badge'] : 'Resmi Site'
            ];
        }
    }
}

$veriFile = __DIR__ . '/veri.txt';

if (is_file($veriFile)) {
    $lines = file($veriFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines !== false) {
        foreach ($lines as $line) {
            $decoded = json_decode($line, true);
            if (!is_array($decoded)) {
                continue;
            }
            if (empty($decoded['title']) || empty($decoded['url'])) {
                continue;
            }
            $entries[] = [
                'title' => (string) $decoded['title'],
                'url' => (string) $decoded['url'],
                'description' => isset($decoded['description']) ? (string) $decoded['description'] : '',
                'keywords' => isset($decoded['keywords']) && is_array($decoded['keywords']) ? array_values($decoded['keywords']) : [],
                'icon' => isset($decoded['icon']) ? (string) $decoded['icon'] : ''
            ];
        }
    }
}

echo json_encode($entries, JSON_UNESCAPED_UNICODE);
