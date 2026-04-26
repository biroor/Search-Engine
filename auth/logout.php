<?php
require __DIR__ . '/common.php';

$_SESSION = [];
if (session_id() !== '') {
    session_destroy();
}

header('Location: /store.php');
exit;
