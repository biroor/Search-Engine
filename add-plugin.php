<?php
require __DIR__ . '/auth/common.php';
require_login();

$feedback = '';
$errors = [];
$defaults = [
    'id' => '',
    'name' => '',
    'description' => '',
    'version' => '1.0.0',
    'author' => '',
    'type' => 'css',
    'code' => '',
    'homepage' => '',
    'tags' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = trim($_POST['id'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $version = trim($_POST['version'] ?? '');
    $author = trim($_POST['author'] ?? '');
    $type = trim($_POST['type'] ?? '');
    $code = trim($_POST['code'] ?? '');
    $homepage = trim($_POST['homepage'] ?? '');
    $tags = trim($_POST['tags'] ?? '');

    if ($id === '') {
        $errors[] = 'Eklenti ID zorunlu.';
    } elseif (!preg_match('/^[a-z0-9\\-_.]+$/i', $id)) {
        $errors[] = 'Eklenti ID sadece harf, rakam, -, _ ve . içerebilir.';
    }
    if ($name === '') {
        $errors[] = 'Eklenti adı zorunlu.';
    }
    if ($description === '') {
        $errors[] = 'Açıklama zorunlu.';
    }
    if ($version === '') {
        $errors[] = 'Sürüm zorunlu.';
    }
    if (!in_array($type, ['css', 'js'], true)) {
        $errors[] = 'Tür sadece css veya js olabilir.';
    }
    if ($code === '') {
        $errors[] = 'Kod alanı zorunlu.';
    }
    if ($homepage !== '' && !filter_var($homepage, FILTER_VALIDATE_URL)) {
        $errors[] = 'Homepage URL geçerli değil.';
    }

    if (empty($errors)) {
        $tagList = array_filter(array_map('trim', explode(',', $tags)));
        $entry = [
            'id' => $id,
            'name' => $name,
            'description' => $description,
            'version' => $version,
            'author' => $author !== '' ? $author : (current_user()['name'] ?? ''),
            'type' => $type,
            'code' => $code,
            'homepage' => $homepage,
            'tags' => array_values($tagList),
            'created_at' => date('Y-m-d H:i:s')
        ];

        $line = json_encode($entry, JSON_UNESCAPED_UNICODE);
        file_put_contents(__DIR__ . '/plugins.txt', $line . PHP_EOL, FILE_APPEND | LOCK_EX);
        $feedback = 'Eklenti mağazaya eklendi.';

        $defaults = [
            'id' => '',
            'name' => '',
            'description' => '',
            'version' => '1.0.0',
            'author' => '',
            'type' => 'css',
            'code' => '',
            'homepage' => '',
            'tags' => ''
        ];
    } else {
        $defaults = [
            'id' => $id,
            'name' => $name,
            'description' => $description,
            'version' => $version,
            'author' => $author,
            'type' => $type,
            'code' => $code,
            'homepage' => $homepage,
            'tags' => $tags
        ];
    }
}

function escape($value)
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Eklenti Ekle - Biroor</title>
    <style>
        body {
            font-family: "Plus Jakarta Sans", "Segoe UI", sans-serif;
            margin: 0;
            min-height: 100vh;
            background: #eef2ff;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .panel {
            width: min(720px, 100%);
            background: #fff;
            border-radius: 24px;
            padding: 32px;
            box-shadow: 0 20px 45px rgba(15, 23, 42, 0.2);
            border: 1px solid rgba(148, 163, 184, 0.25);
        }
        h1 {
            margin: 0 0 12px;
            font-size: 24px;
        }
        p.subtitle {
            margin: 0 0 24px;
            color: #475569;
        }
        .alert {
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-weight: 600;
        }
        .alert-success {
            background: rgba(16, 185, 129, 0.12);
            border: 1px solid rgba(16, 185, 129, 0.4);
            color: #047857;
        }
        .alert-error {
            background: rgba(239, 68, 68, 0.12);
            border: 1px solid rgba(239, 68, 68, 0.4);
            color: #b91c1c;
        }
        label {
            display: block;
            margin-bottom: 12px;
            font-size: 14px;
            color: #475569;
        }
        input[type="text"],
        textarea,
        select {
            width: 100%;
            border-radius: 12px;
            border: 1px solid rgba(148, 163, 184, 0.6);
            padding: 10px 12px;
            font-size: 15px;
            font-family: inherit;
            resize: vertical;
        }
        textarea {
            min-height: 140px;
        }
        button {
            border: none;
            border-radius: 12px;
            padding: 12px 18px;
            font-weight: 700;
            background: linear-gradient(135deg, #2563eb, #7c3aed);
            color: #fff;
            cursor: pointer;
            font-size: 15px;
            transition: transform 0.2s ease;
        }
        button:hover {
            transform: translateY(-1px);
        }
        .grid {
            display: grid;
            gap: 12px;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        }
        .hint {
            margin-top: 24px;
            font-size: 13px;
            color: #475569;
        }
    </style>
</head>
<body>
    <div class="panel">
        <h1>Eklenti Ekle</h1>
        <p class="subtitle">Bu form ile eklenti mağazasına yeni kayıt eklersin. Kayıtlar <code>plugins.txt</code> dosyasına yazılır.</p>
        <?php if ($feedback): ?>
            <div class="alert alert-success"><?= escape($feedback) ?></div>
        <?php endif; ?>
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <?= escape(implode(' ', $errors)) ?>
            </div>
        <?php endif; ?>
        <form method="post">
            <div class="grid">
                <label>
                    Eklenti ID
                    <input type="text" name="id" value="<?= escape($defaults['id']) ?>" placeholder="tema-hero" required />
                </label>
                <label>
                    Eklenti Adı
                    <input type="text" name="name" value="<?= escape($defaults['name']) ?>" required />
                </label>
                <label>
                    Sürüm
                    <input type="text" name="version" value="<?= escape($defaults['version']) ?>" required />
                </label>
                <label>
                    Yazar
                    <input type="text" name="author" value="<?= escape($defaults['author']) ?>" placeholder="Opsiyonel" />
                </label>
            </div>
            <label>
                Açıklama
                <textarea name="description" required><?= escape($defaults['description']) ?></textarea>
            </label>
            <div class="grid">
                <label>
                    Tür
                    <select name="type" required>
                        <option value="css" <?= $defaults['type'] === 'css' ? 'selected' : '' ?>>CSS</option>
                        <option value="js" <?= $defaults['type'] === 'js' ? 'selected' : '' ?>>JavaScript</option>
                    </select>
                </label>
                <label>
                    Homepage (opsiyonel)
                    <input type="text" name="homepage" value="<?= escape($defaults['homepage']) ?>" placeholder="https://..." />
                </label>
                <label>
                    Etiketler (virgülle)
                    <input type="text" name="tags" value="<?= escape($defaults['tags']) ?>" placeholder="tema, ui" />
                </label>
            </div>
            <label>
                Eklenti Kodu
                <textarea name="code" required><?= escape($defaults['code']) ?></textarea>
            </label>
            <button type="submit">Eklentiyi Kaydet</button>
        </form>
        <p class="hint">JS eklentilerinin etkisini geri almak için sayfayı yenilemek gerekebilir.</p>
    </div>
</body>
</html>
