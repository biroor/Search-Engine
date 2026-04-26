<?php
require __DIR__ . '/auth/common.php';

$plugins = [];
$pluginFile = __DIR__ . '/plugins.txt';

if (is_file($pluginFile)) {
    $lines = file($pluginFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines !== false) {
        foreach ($lines as $line) {
            $decoded = json_decode($line, true);
            if (!is_array($decoded)) {
                continue;
            }
            if (empty($decoded['id']) || empty($decoded['name']) || empty($decoded['type']) || empty($decoded['code'])) {
                continue;
            }
            $plugins[$decoded['id']] = $decoded;
        }
    }
}

$plugins = array_values($plugins);

function escape($value)
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

$user = current_user();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Biroor Eklenti Mağazası</title>
    <style>
        :root {
            --page-bg: #f2f5fb;
            --surface: rgba(255, 255, 255, 0.9);
            --border: rgba(148, 163, 184, 0.4);
            --text: #0f172a;
            --muted: #64748b;
            --accent: #2563eb;
            --accent-2: #7c3aed;
            --shadow: 0 18px 40px rgba(15, 23, 42, 0.12);
            --radius: 18px;
        }
        * {
            box-sizing: border-box;
        }
        body {
            margin: 0;
            min-height: 100vh;
            font-family: "Plus Jakarta Sans", "Segoe UI", system-ui, sans-serif;
            color: var(--text);
            background: var(--page-bg);
        }
        .page {
            width: min(1100px, 92vw);
            margin: 30px auto 60px;
            display: grid;
            gap: 24px;
        }
        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            background: var(--surface);
            padding: 20px 24px;
            border-radius: var(--radius);
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
        }
        .title h1 {
            margin: 0;
            font-size: 26px;
        }
        .title p {
            margin: 6px 0 0;
            color: var(--muted);
        }
        .actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .btn {
            border: none;
            padding: 10px 16px;
            border-radius: 999px;
            font-weight: 600;
            cursor: pointer;
            background: linear-gradient(135deg, var(--accent), var(--accent-2));
            color: #fff;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-outline {
            background: #fff;
            color: var(--text);
            border: 1px solid var(--border);
        }
        .store-grid {
            display: grid;
            gap: 16px;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        }
        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 16px;
            box-shadow: var(--shadow);
            display: grid;
            gap: 10px;
        }
        .card h3 {
            margin: 0;
            font-size: 18px;
        }
        .meta {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            font-size: 12px;
            color: var(--muted);
        }
        .tag {
            padding: 2px 8px;
            border-radius: 999px;
            background: rgba(37, 99, 235, 0.12);
            color: #1e293b;
            font-weight: 600;
        }
        .description {
            margin: 0;
            color: var(--muted);
        }
        .install-btn {
            border: none;
            border-radius: 12px;
            padding: 10px 14px;
            font-weight: 700;
            background: #0ea5e9;
            color: #fff;
            cursor: pointer;
        }
        .install-btn[disabled] {
            background: #94a3b8;
            cursor: not-allowed;
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="header">
            <div class="title">
                <h1>Biroor Eklenti Mağazası</h1>
                <p>Eklentileri yükle, arama deneyimini kişiselleştir.</p>
            </div>
            <div class="actions">
                <a class="btn btn-outline" href="index.html">Ana Sayfa</a>
                <?php if ($user): ?>
                    <span class="btn btn-outline"><?= escape($user['email'] ?? '') ?></span>
                    <a class="btn" href="add-plugin.php">Eklenti Ekle</a>
                    <a class="btn btn-outline" href="/auth/logout.php">Çıkış</a>
                <?php else: ?>
                    <a class="btn" href="/auth/google-login.php">Google ile Giriş</a>
                <?php endif; ?>
            </div>
        </div>

        <?php if (empty($plugins)): ?>
            <div class="card">
                <h3>Henüz eklenti yok</h3>
                <p class="description">İlk eklentiyi eklemek için giriş yap ve “Eklenti Ekle” butonunu kullan.</p>
            </div>
        <?php else: ?>
            <div class="store-grid">
                <?php foreach ($plugins as $plugin): ?>
                    <div class="card">
                        <h3><?= escape($plugin['name'] ?? 'Eklenti') ?></h3>
                        <div class="meta">
                            <span class="tag"><?= escape($plugin['type'] ?? '') ?></span>
                            <?php if (!empty($plugin['version'])): ?>
                                <span>Sürüm <?= escape($plugin['version']) ?></span>
                            <?php endif; ?>
                            <?php if (!empty($plugin['author'])): ?>
                                <span><?= escape($plugin['author']) ?></span>
                            <?php endif; ?>
                        </div>
                        <p class="description"><?= escape($plugin['description'] ?? '') ?></p>
                        <button class="install-btn" data-plugin="<?= escape(json_encode($plugin, JSON_UNESCAPED_UNICODE)) ?>">Yükle</button>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
        const STORAGE_KEY = "biroor_plugins";

        function getStoredPlugins() {
            try {
                const parsed = JSON.parse(localStorage.getItem(STORAGE_KEY));
                return Array.isArray(parsed) ? parsed : [];
            } catch {
                return [];
            }
        }

        function saveStoredPlugins(list) {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(list));
        }

        function upsertPlugin(plugin) {
            const list = getStoredPlugins();
            const index = list.findIndex(item => item.id === plugin.id);
            const entry = { ...plugin, enabled: true };
            if (index >= 0) {
                list[index] = entry;
            } else {
                list.push(entry);
            }
            saveStoredPlugins(list);
        }

        function syncButtons() {
            const installed = getStoredPlugins().map(item => item.id);
            document.querySelectorAll(".install-btn").forEach(btn => {
                const raw = btn.getAttribute("data-plugin");
                if (!raw) return;
                try {
                    const plugin = JSON.parse(raw);
                    if (installed.includes(plugin.id)) {
                        btn.textContent = "Yüklü";
                        btn.disabled = true;
                    }
                } catch {}
            });
        }

        document.querySelectorAll(".install-btn").forEach(btn => {
            btn.addEventListener("click", () => {
                const raw = btn.getAttribute("data-plugin");
                if (!raw) return;
                try {
                    const plugin = JSON.parse(raw);
                    upsertPlugin(plugin);
                    btn.textContent = "Yüklü";
                    btn.disabled = true;
                } catch {
                    alert("Eklenti yüklenemedi.");
                }
            });
        });

        syncButtons();
    </script>
</body>
</html>
