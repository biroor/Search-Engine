<?php
$feedback = '';
$errors = [];
$defaults = [
    'title' => '',
    'url' => '',
    'description' => '',
    'keywords' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $url = trim($_POST['url'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $keywords = trim($_POST['keywords'] ?? '');

    if ($title === '') {
        $errors[] = 'Title is required.';
    }
    if ($url === '') {
        $errors[] = 'URL is required.';
    } elseif (!filter_var($url, FILTER_VALIDATE_URL)) {
        $errors[] = 'URL is not valid.';
    }
    if ($description === '') {
        $errors[] = 'Description is required.';
    }

    if (empty($errors)) {
        $keywordList = array_filter(array_map('trim', explode(',', $keywords)));
        $entry = [
            'title' => $title,
            'url' => $url,
            'description' => $description,
            'keywords' => array_values($keywordList)
        ];

        $line = json_encode($entry, JSON_UNESCAPED_UNICODE);
        file_put_contents(__DIR__ . '/veri.txt', $line . PHP_EOL, FILE_APPEND | LOCK_EX);
        $feedback = 'Your entry was added to veri.txt.';
        $title = $url = $description = $keywords = '';
    }

    $defaults = [
        'title' => $title,
        'url' => $url,
        'description' => $description,
        'keywords' => $keywords
    ];
}

function escape($value)
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Add Site to Biroor</title>
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
            width: min(520px, 100%);
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
        textarea {
            width: 100%;
            border-radius: 12px;
            border: 1px solid rgba(148, 163, 184, 0.6);
            padding: 10px 12px;
            font-size: 15px;
            font-family: inherit;
            resize: vertical;
        }
        textarea {
            min-height: 92px;
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
        .hint {
            margin-top: 24px;
            font-size: 13px;
            color: #475569;
        }
    </style>
</head>
<body>
    <div class="panel">
        <h1>Add a new website</h1>
        <p class="subtitle">The submitted entries are appended to <code>veri.txt</code> so the JavaScript loader can consume them.</p>
        <?php if ($feedback): ?>
            <div class="alert alert-success"><?= escape($feedback) ?></div>
        <?php endif; ?>
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <?= escape(implode(' ', $errors)) ?>
            </div>
        <?php endif; ?>
        <form method="post">
            <label>
                Title
                <input type="text" name="title" value="<?= escape($defaults['title']) ?>" required />
            </label>
            <label>
                URL
                <input type="text" name="url" value="<?= escape($defaults['url']) ?>" required />
            </label>
            <label>
                Description
                <textarea name="description" required><?= escape($defaults['description']) ?></textarea>
            </label>
            <label>
                Keywords (comma separated)
                <input type="text" name="keywords" value="<?= escape($defaults['keywords']) ?>" placeholder="example, favorite, link" />
            </label>
            <button type="submit">Save entry</button>
        </form>
        <p class="hint">Each submission becomes a JSON line inside <code>veri.txt</code>. The search page merges it automatically.</p>
    </div>
</body>
</html>
