<?php
declare(strict_types=1);

$adminBaseUrl = basePath() . '/site-admin';
$adminSubPath = normalizePath(substr($relativePath, strlen('/site-admin')) ?: '/');
$adminSubPath = normalizeRoutePath($adminSubPath);

$flash = $_SESSION['admin_flash'] ?? null;
unset($_SESSION['admin_flash']);

$csrfToken = static function (): string {
    if (!isset($_SESSION['admin_csrf_token']) || !is_string($_SESSION['admin_csrf_token']) || $_SESSION['admin_csrf_token'] === '') {
        $_SESSION['admin_csrf_token'] = bin2hex(random_bytes(32));
    }

    return (string) $_SESSION['admin_csrf_token'];
};

$setFlash = static function (string $type, string $message): void {
    $_SESSION['admin_flash'] = [
        'type' => $type,
        'message' => $message,
    ];
};

$redirectTo = static function (string $path) use ($adminBaseUrl): never {
    $target = str_starts_with($path, '/') ? basePath() . $path : $adminBaseUrl . '/' . ltrim($path, '/');
    header('Location: ' . $target, true, 302);
    exit;
};

$tableExists = static function (PDO $pdo, string $table): bool {
    static $cache = [];

    if (array_key_exists($table, $cache)) {
        return $cache[$table];
    }

    try {
        $stmt = $pdo->prepare(
            'SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = :table_name LIMIT 1'
        );
        $stmt->execute(['table_name' => $table]);
        $cache[$table] = $stmt->fetchColumn() !== false;
    } catch (Throwable) {
        $query = 'SHOW TABLES LIKE ' . $pdo->quote($table);
        $stmt = $pdo->query($query);
        $cache[$table] = $stmt !== false && $stmt->fetchColumn() !== false;
    }

    return $cache[$table];
};

$columnExists = static function (PDO $pdo, string $table, string $column): bool {
    static $cache = [];
    $key = $table . '.' . $column;

    if (array_key_exists($key, $cache)) {
        return $cache[$key];
    }

    try {
        $stmt = $pdo->prepare(
            'SELECT 1 FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = :table_name AND column_name = :column_name LIMIT 1'
        );
        $stmt->execute([
            'table_name' => $table,
            'column_name' => $column,
        ]);
        $cache[$key] = $stmt->fetchColumn() !== false;
    } catch (Throwable) {
        $query = sprintf('SHOW COLUMNS FROM `%s` LIKE %s', str_replace('`', '``', $table), $pdo->quote($column));
        $stmt = $pdo->query($query);
        $cache[$key] = $stmt !== false && $stmt->fetchColumn() !== false;
    }

    return $cache[$key];
};

$storeMediaUpload = static function (array $file, string $bucket): array {
    $errorCode = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);

    if ($errorCode === UPLOAD_ERR_NO_FILE) {
        throw new RuntimeException('Yuklenecek dosya secilmedi.');
    }

    if ($errorCode !== UPLOAD_ERR_OK) {
        $errorMap = [
            UPLOAD_ERR_INI_SIZE => 'Dosya PHP upload limitini asti.',
            UPLOAD_ERR_FORM_SIZE => 'Dosya form limitini asti.',
            UPLOAD_ERR_PARTIAL => 'Dosya kismen yuklendi. Tekrar deneyin.',
            UPLOAD_ERR_NO_TMP_DIR => 'Gecici klasor bulunamadi.',
            UPLOAD_ERR_CANT_WRITE => 'Dosya diske yazilamadi.',
            UPLOAD_ERR_EXTENSION => 'Dosya yukleme bir PHP eklentisi tarafindan durduruldu.',
        ];
        throw new RuntimeException($errorMap[$errorCode] ?? 'Dosya yukleme basarisiz oldu.');
    }

    $tmpPath = (string) ($file['tmp_name'] ?? '');
    $fileSize = (int) ($file['size'] ?? 0);
    $originalName = (string) ($file['name'] ?? '');

    if ($tmpPath === '' || !is_uploaded_file($tmpPath)) {
        throw new RuntimeException('Gecerli bir dosya yuklemesi algilanamadi.');
    }

    if ($fileSize <= 0) {
        throw new RuntimeException('Bos dosya yuklenemez.');
    }

    $maxUploadBytes = 100 * 1024 * 1024;
    if ($fileSize > $maxUploadBytes) {
        throw new RuntimeException('Dosya boyutu 100MB limitini asti.');
    }

    $allowedByMime = [
        'image/jpeg' => ['ext' => 'jpg', 'type' => 'image'],
        'image/png' => ['ext' => 'png', 'type' => 'image'],
        'image/webp' => ['ext' => 'webp', 'type' => 'image'],
        'image/gif' => ['ext' => 'gif', 'type' => 'image'],
        'image/avif' => ['ext' => 'avif', 'type' => 'image'],
        'video/mp4' => ['ext' => 'mp4', 'type' => 'video'],
        'video/webm' => ['ext' => 'webm', 'type' => 'video'],
        'video/ogg' => ['ext' => 'ogg', 'type' => 'video'],
        'video/quicktime' => ['ext' => 'mov', 'type' => 'video'],
    ];
    $allowedByExtension = [
        'jpg' => 'image',
        'jpeg' => 'image',
        'png' => 'image',
        'webp' => 'image',
        'gif' => 'image',
        'avif' => 'image',
        'mp4' => 'video',
        'webm' => 'video',
        'ogg' => 'video',
        'mov' => 'video',
    ];

    $mimeType = '';
    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo !== false) {
            $detected = finfo_file($finfo, $tmpPath);
            finfo_close($finfo);
            if (is_string($detected)) {
                $mimeType = strtolower(trim($detected));
            }
        }
    }

    $mediaType = '';
    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

    if ($mimeType !== '' && isset($allowedByMime[$mimeType])) {
        $extension = $allowedByMime[$mimeType]['ext'];
        $mediaType = $allowedByMime[$mimeType]['type'];
    } elseif ($extension !== '' && isset($allowedByExtension[$extension])) {
        $mediaType = $allowedByExtension[$extension];
    } else {
        throw new RuntimeException('Yalnizca gorsel veya video dosyalari yuklenebilir.');
    }

    $safeBucket = in_array($bucket, ['designs', 'products'], true) ? $bucket : 'misc';
    $uploadDir = dirname(__DIR__, 2) . '/public/uploads/' . $safeBucket;

    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
        throw new RuntimeException('Yukleme klasoru olusturulamadi.');
    }

    if (!is_writable($uploadDir)) {
        throw new RuntimeException('Yukleme klasorune yazma yetkisi yok.');
    }

    $filename = date('YmdHis') . '-' . bin2hex(random_bytes(8)) . '.' . $extension;
    $destinationPath = $uploadDir . '/' . $filename;

    if (!move_uploaded_file($tmpPath, $destinationPath)) {
        throw new RuntimeException('Dosya sunucuya tasinamadi.');
    }

    return [
        'url' => '/uploads/' . $safeBucket . '/' . $filename,
        'type' => $mediaType,
    ];
};

$adminAuthenticated = isset($_SESSION['admin_user_id']) && is_numeric($_SESSION['admin_user_id']);
$adminUser = [
    'id' => $_SESSION['admin_user_id'] ?? null,
    'username' => $_SESSION['admin_username'] ?? 'admin',
];

$tabFromPath = trim($adminSubPath, '/');
if ($tabFromPath === '') {
    $tabFromPath = $adminAuthenticated ? 'dashboard' : 'login';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = trim((string) ($_POST['action'] ?? ''));
    $actionTabMap = [
        'save_settings'          => 'settings',
        'update_contact_status'  => 'messages',
        'delete_contact_message' => 'messages',
    ];
    $postedCsrf = (string) ($_POST['_csrf'] ?? '');
    $sessionCsrf = (string) ($_SESSION['admin_csrf_token'] ?? '');

    if ($action === '' || $sessionCsrf === '' || !hash_equals($sessionCsrf, $postedCsrf)) {
        $setFlash('error', 'Gecersiz oturum dogrulamasi. Lutfen tekrar deneyin.');
        $redirectTo('/site-admin' . (isset($actionTabMap[$action]) ? '/' . $actionTabMap[$action] : ''));
    }

    if ($action === 'login') {
        $login = trim((string) ($_POST['login'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        try {
            $pdo = databaseConnection();
            $stmt = $pdo->prepare('SELECT id, username, email, password_hash, is_active FROM admin_users WHERE username = :username_login OR email = :email_login LIMIT 1');
            $stmt->execute([
                'username_login' => $login,
                'email_login' => $login,
            ]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            $valid = is_array($user)
                && (int) ($user['is_active'] ?? 0) === 1
                && password_verify($password, (string) ($user['password_hash'] ?? ''));

            if (!$valid) {
                $setFlash('error', 'Giris bilgileri hatali.');
                $redirectTo('/site-admin');
            }

            $_SESSION['admin_user_id'] = (int) $user['id'];
            $_SESSION['admin_username'] = (string) $user['username'];
            session_regenerate_id(true);

            $updateStmt = $pdo->prepare('UPDATE admin_users SET last_login_at = NOW() WHERE id = :id');
            $updateStmt->execute(['id' => (int) $user['id']]);

            $setFlash('success', 'Yonetim paneline hos geldiniz.');
            $redirectTo('/site-admin/dashboard');
        } catch (Throwable $exception) {
            $setFlash('error', 'Login sirasinda bir hata olustu. DB tablolarini kontrol edin.');
            $redirectTo('/site-admin');
        }
    }

    if ($action === 'logout') {
        unset($_SESSION['admin_user_id'], $_SESSION['admin_username']);
        session_regenerate_id(true);
        $setFlash('success', 'Cikis yapildi.');
        $redirectTo('/site-admin');
    }

    if (!$adminAuthenticated) {
        $setFlash('error', 'Once giris yapmalisiniz.');
        $redirectTo('/site-admin');
    }

    try {
        $pdo = databaseConnection();

        if ($action === 'save_settings') {
            $allowedKeys = [
                'brand_name',
                'brand_short',
                'brand_subline',
                'hero_kicker',
                'hero_description',
                'hero_cta_shop',
                'hero_cta_designs',
                'hero_image',
                'footer_tagline',
                'footer_manifesto_title',
                'footer_manifesto_text',
                'instagram_url',
                'instagram_reels_url',
                'instagram_shop_url',
                'facebook_url',
                'youtube_url',
                'tiktok_url',
                'whatsapp_number',
                'phone',
                'viber',
                'telegram',
                'email_address',
                'business_location',
                'bank_account_1',
                'bank_account_2',
                'bank_account_3',
                'bank_account_4',
                'gallery_image',
                'gallery_video',
            ];

            $stmt = $pdo->prepare('INSERT INTO site_settings (setting_key, setting_value) VALUES (:key, :value) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)');

            foreach ($allowedKeys as $key) {
                $value = trim((string) ($_POST[$key] ?? ''));
                $stmt->execute([
                    'key' => $key,
                    'value' => $value,
                ]);
            }

            $setFlash('success', 'Site ayarlari kaydedildi.');
            $redirectTo('/site-admin/settings');
        }

        if ($action === 'create_design') {
            $imgUrl = '';
            $videoUrl = '';

            if (isset($_FILES['img_file']) && $_FILES['img_file']['error'] === 0) {
                $ext = pathinfo($_FILES['img_file']['name'], PATHINFO_EXTENSION);
                $filename = 'img_' . uniqid() . '.' . $ext;
                $target = __DIR__ . '/../../public/uploads/' . $filename;
                if (move_uploaded_file($_FILES['img_file']['tmp_name'], $target)) {
                    $imgUrl = '/uploads/' . $filename;
                }
            }

            if (isset($_FILES['video_file']) && $_FILES['video_file']['error'] === 0) {
                $ext = pathinfo($_FILES['video_file']['name'], PATHINFO_EXTENSION);
                $filename = 'vid_' . uniqid() . '.' . $ext;
                $target = __DIR__ . '/../../public/uploads/' . $filename;
                if (move_uploaded_file($_FILES['video_file']['tmp_name'], $target)) {
                    $videoUrl = '/uploads/' . $filename;
                }
            }

            if ($imgUrl === '' && $videoUrl === '') {
                throw new RuntimeException('Lutfen gecerli bir resim veya video secin.');
            }

            if ($columnExists($pdo, 'designs', 'details')) {
                $stmt = $pdo->prepare('INSERT INTO designs (title, details, img_url, video_url) VALUES (\'\', \'\', :img_url, :video_url)');
                $stmt->execute(['img_url' => $imgUrl, 'video_url' => $videoUrl]);
            } else {
                $stmt = $pdo->prepare('INSERT INTO designs (title, img_url, video_url) VALUES (\'\', :img_url, :video_url)');
                $stmt->execute(['img_url' => $imgUrl, 'video_url' => $videoUrl]);
            }

            $setFlash('success', 'Design eklendi.');
            $redirectTo('/site-admin/designs');
        }

        if ($action === 'update_design') {
            $id = (int) ($_POST['id'] ?? 0);
            $title = trim((string) ($_POST['title'] ?? ''));
            $details = trim((string) ($_POST['details'] ?? ''));
            $imgUrl = trim((string) ($_POST['img_url'] ?? ''));
            $videoUrl = trim((string) ($_POST['video_url'] ?? ''));

            if ($id <= 0 || $title === '' || $imgUrl === '' || $videoUrl === '') {
                throw new RuntimeException('Design guncelleme bilgileri gecersiz.');
            }

            if ($columnExists($pdo, 'designs', 'details')) {
                $stmt = $pdo->prepare('UPDATE designs SET title = :title, details = :details, img_url = :img_url, video_url = :video_url WHERE id = :id');
                $stmt->execute([
                    'id' => $id,
                    'title' => $title,
                    'details' => $details,
                    'img_url' => $imgUrl,
                    'video_url' => $videoUrl,
                ]);
            } else {
                $stmt = $pdo->prepare('UPDATE designs SET title = :title, img_url = :img_url, video_url = :video_url WHERE id = :id');
                $stmt->execute([
                    'id' => $id,
                    'title' => $title,
                    'img_url' => $imgUrl,
                    'video_url' => $videoUrl,
                ]);
            }

            $setFlash('success', 'Design guncellendi.');
            $redirectTo('/site-admin/designs');
        }

        if ($action === 'delete_design') {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id <= 0) {
                throw new RuntimeException('Design silme istegi gecersiz.');
            }

            $stmt = $pdo->prepare('DELETE FROM designs WHERE id = :id');
            $stmt->execute(['id' => $id]);

            $setFlash('success', 'Design silindi.');
            $redirectTo('/site-admin/designs');
        }

        if ($action === 'update_contact_status') {
            if (!$tableExists($pdo, 'contact_messages')) {
                throw new RuntimeException('contact_messages tablosu bulunamadi.');
            }

            $id = (int) ($_POST['id'] ?? 0);
            $status = trim((string) ($_POST['status'] ?? ''));
            $allowedStatus = ['new', 'read', 'replied'];

            if ($id <= 0 || !in_array($status, $allowedStatus, true)) {
                throw new RuntimeException('Mesaj durumu guncelleme bilgileri gecersiz.');
            }

            $stmt = $pdo->prepare('UPDATE contact_messages SET status = :status WHERE id = :id');
            $stmt->execute([
                'id' => $id,
                'status' => $status,
            ]);

            $setFlash('success', 'Mesaj durumu guncellendi.');
            $redirectTo('/site-admin/messages');
        }

        if ($action === 'delete_contact_message') {
            if (!$tableExists($pdo, 'contact_messages')) {
                throw new RuntimeException('contact_messages tablosu bulunamadi.');
            }

            $id = (int) ($_POST['id'] ?? 0);
            if ($id <= 0) {
                throw new RuntimeException('Mesaj silme istegi gecersiz.');
            }

            $stmt = $pdo->prepare('DELETE FROM contact_messages WHERE id = :id');
            $stmt->execute(['id' => $id]);

            $setFlash('success', 'Mesaj silindi.');
            $redirectTo('/site-admin/messages');
        }

        $setFlash('error', 'Gecersiz admin aksiyonu.');
        $redirectTo('/site-admin/dashboard');
    } catch (Throwable $exception) {
        $targetTab = $actionTabMap[$action] ?? ($tabFromPath === 'login' ? 'dashboard' : $tabFromPath);
        $setFlash('error', 'Islem basarisiz. Girilen bilgileri ve veritabani tablolarini kontrol edin.');
        $redirectTo('/site-admin/' . $targetTab);
    }
}

$csrf = $csrfToken();

/* ──────────────── LOGIN PAGE ──────────────── */
if (!$adminAuthenticated) {
    ?>
    <!DOCTYPE html>
    <html lang="tr">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Admin Girişi · NYSCC</title>
        <script src="<?= e(asset('js/tailwind-config.js')); ?>"></script>
        <script src="https://cdn.tailwindcss.com"></script>
        <link rel="stylesheet" href="<?= e(asset('css/admin.css')); ?>" />
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    </head>
    <body class="admin-login-bg min-h-screen flex items-center justify-center" style="font-family:'Inter',sans-serif;">
        <div class="admin-login-card w-full max-w-md mx-4">
            <div class="admin-login-logo">
                <div class="admin-login-icon">
                    <svg width="28" height="28" fill="none" viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" stroke="#D4AF37" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>
                <div>
                    <h1 class="admin-brand-name">NYSCC Admin</h1>
                    <p class="admin-brand-sub">NewYork Society Creation Club</p>
                </div>
            </div>

            <?php if (is_array($flash)): ?>
                <div class="admin-flash <?= ($flash['type'] ?? '') === 'error' ? 'admin-flash-error' : 'admin-flash-success'; ?>">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" class="flex-shrink-0">
                        <?php if (($flash['type'] ?? '') === 'error'): ?>
                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.8"/><path d="M12 8v4m0 4h.01" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        <?php else: ?>
                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.8"/><path d="M9 12l2 2 4-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        <?php endif; ?>
                    </svg>
                    <?= e((string) ($flash['message'] ?? '')); ?>
                </div>
            <?php endif; ?>

            <form method="post" action="<?= e($adminBaseUrl); ?>" class="admin-login-form">
                <input type="hidden" name="action" value="login" />
                <input type="hidden" name="_csrf" value="<?= e($csrf); ?>" />

                <div class="admin-field">
                    <label class="admin-label">Kullanıcı Adı / E-posta</label>
                    <div class="admin-input-wrap">
                        <svg class="admin-input-icon" width="16" height="16" fill="none" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><circle cx="12" cy="7" r="4" stroke="currentColor" stroke-width="1.8"/></svg>
                        <input type="text" name="login" required class="admin-input" placeholder="kullanici@email.com" />
                    </div>
                </div>

                <div class="admin-field">
                    <label class="admin-label">Şifre</label>
                    <div class="admin-input-wrap">
                        <svg class="admin-input-icon" width="16" height="16" fill="none" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" stroke="currentColor" stroke-width="1.8"/><path d="M7 11V7a5 5 0 0 1 10 0v4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                        <input type="password" name="password" required class="admin-input" placeholder="••••••••" />
                    </div>
                </div>

                <button type="submit" class="admin-login-btn">
                    Giriş Yap
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
            </form>
        </div>
    </body>
    </html>
    <?php
    return;
}

/* ──────────────── AUTHENTICATED PANEL ──────────────── */
$allowedTabs = ['dashboard', 'settings', 'messages', 'designs'];
$activeTab = in_array($tabFromPath, $allowedTabs, true) ? $tabFromPath : 'dashboard';

$settings = [];
$designs = [];
$products = [];
$contactMessages = [];
$contactTableAvailable = false;
$contactStatusCounts = [
    'new' => 0,
    'read' => 0,
    'replied' => 0,
    'total' => 0,
];
$contactStatusLabels = [
    'new' => 'Yeni',
    'read' => 'Okundu',
    'replied' => 'Yanitlandi',
];

try {
    $pdo = databaseConnection();

    $settingsStmt = $pdo->query('SELECT setting_key, setting_value FROM site_settings');
    if ($settingsStmt !== false) {
        foreach ($settingsStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $key = (string) ($row['setting_key'] ?? '');
            if ($key !== '') {
                $settings[$key] = (string) ($row['setting_value'] ?? '');
            }
        }
    }

    if ($tableExists($pdo, 'designs')) {
        $stmt = $pdo->query('SELECT * FROM designs ORDER BY id DESC');
        if ($stmt) {
            $designs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }

    $contactTableAvailable = $tableExists($pdo, 'contact_messages');
    if ($contactTableAvailable) {
        $contactCountStmt = $pdo->query('SELECT status, COUNT(*) AS total_count FROM contact_messages GROUP BY status');
        if ($contactCountStmt !== false) {
            foreach ($contactCountStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $status = (string) ($row['status'] ?? '');
                $count = (int) ($row['total_count'] ?? 0);
                if (array_key_exists($status, $contactStatusCounts)) {
                    $contactStatusCounts[$status] = $count;
                }
            }
        }
        $contactStatusCounts['total'] = $contactStatusCounts['new'] + $contactStatusCounts['read'] + $contactStatusCounts['replied'];

        $messageStmt = $pdo->query('SELECT id, name, email, phone, subject, message, locale, status, ip_address, user_agent, created_at FROM contact_messages ORDER BY id DESC LIMIT 500');
        if ($messageStmt !== false) {
            $contactMessages = $messageStmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
} catch (Throwable $exception) {
    $setFlash('error', 'Panel verileri yuklenemedi. Tablolari olusturdugunuzdan emin olun.');
    $redirectTo('/site-admin');
}


$adminNav = [
    'dashboard' => [
        'label' => 'Dashboard',
        'icon'  => '<path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><polyline points="9 22 9 12 15 12 15 22" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>',
    ],
    'messages' => [
        'label' => 'İletişim Mesajları',
        'icon'  => '<path d="M4 5h16a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><polyline points="22,7 12,13 2,7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>',
    ],
    'settings' => [
        'label' => 'Site Ayarları',
        'icon'  => '<circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.8"/><path d="M19.07 4.93a10 10 0 0 0-14.14 0M4.93 19.07a10 10 0 0 0 14.14 0M12 2v2m0 16v2m10-10h-2M4 12H2m15.66-7.66L16.24 5.76m-8.48 12.48L6.34 19.66m12.32 0L16.24 18.24M7.76 5.76L6.34 4.34" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>',
    ],
    'designs' => [
        'label' => 'Tasarım Galerisi',
        'icon'  => '<rect x="3" y="3" width="18" height="18" rx="2" stroke="currentColor" stroke-width="1.8"/><circle cx="8.5" cy="8.5" r="1.5" stroke="currentColor" stroke-width="1.8"/><polyline points="21 15 16 10 5 21" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>',
    ],
];
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Panel · NYSCC</title>
    <script src="<?= e(asset('js/tailwind-config.js')); ?>"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?= e(asset('css/admin.css')); ?>" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
</head>
<body class="admin-panel-body">

    <!-- ═══════ SIDEBAR ═══════ -->
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="admin-sidebar-header">
            <div class="admin-sidebar-logo">
                <svg width="22" height="22" fill="none" viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" stroke="#D4AF37" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </div>
            <div>
                <p class="admin-sidebar-brand">NYSCC</p>
                <p class="admin-sidebar-subbrand">Admin Panel</p>
            </div>
        </div>

        <nav class="admin-sidebar-nav">
            <p class="admin-nav-group-label">Ana Menü</p>
            <?php foreach ($adminNav as $tabKey => $tabInfo): ?>
                <a href="<?= e($adminBaseUrl . '/' . $tabKey); ?>"
                   class="admin-nav-item <?= $activeTab === $tabKey ? 'admin-nav-item-active' : ''; ?>">
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24">
                        <?= $tabInfo['icon']; ?>
                    </svg>
                    <span><?= e($tabInfo['label']); ?></span>
                    <?php if ($activeTab === $tabKey): ?>
                        <span class="admin-nav-indicator"></span>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </nav>

        <div class="admin-sidebar-footer">
            <div class="admin-sidebar-stats">
                <div class="admin-stat-item">
                    <span class="admin-stat-value"><?= e((string) count($products)); ?></span>
                    <span class="admin-stat-label">Ürün</span>
                </div>
                <div class="admin-stat-divider"></div>
                <div class="admin-stat-item">
                    <span class="admin-stat-value"><?= e((string) count($designs)); ?></span>
                    <span class="admin-stat-label">Tasarım</span>
                </div>
                <div class="admin-stat-divider"></div>
                <div class="admin-stat-item">
                    <span class="admin-stat-value"><?= e((string) $contactStatusCounts['new']); ?></span>
                    <span class="admin-stat-label">Yeni Mesaj</span>
                </div>
            </div>
        </div>
    </aside>

    <!-- ═══════ MAIN WRAPPER ═══════ -->
    <div class="admin-main-wrapper">

        <!-- ═══════ TOP NAVBAR ═══════ -->
        <header class="admin-topbar">
            <div class="admin-topbar-left">
                <button class="admin-sidebar-toggle" id="sidebarToggle" aria-label="Toggle sidebar">
                    <svg width="20" height="20" fill="none" viewBox="0 0 24 24"><path d="M3 12h18M3 6h18M3 18h18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                </button>
                <div class="admin-breadcrumb">
                    <span class="admin-breadcrumb-root">Admin</span>
                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24"><path d="M9 18l6-6-6-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <span class="admin-breadcrumb-current"><?= e($adminNav[$activeTab]['label'] ?? 'Dashboard'); ?></span>
                </div>
            </div>

            <div class="admin-topbar-right">
                <div class="admin-topbar-time" id="adminClock"></div>

                <div class="admin-user-badge">
                    <div class="admin-user-avatar">
                        <?= strtoupper(substr((string) $adminUser['username'], 0, 1)); ?>
                    </div>
                    <div class="admin-user-info">
                        <span class="admin-user-name"><?= e((string) $adminUser['username']); ?></span>
                        <span class="admin-user-role">Süper Admin</span>
                    </div>
                </div>

                <form method="post" action="<?= e($adminBaseUrl); ?>">
                    <input type="hidden" name="action" value="logout" />
                    <input type="hidden" name="_csrf" value="<?= e($csrf); ?>" />
                    <button type="submit" class="admin-logout-btn">
                        <svg width="15" height="15" fill="none" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        Çıkış
                    </button>
                </form>
            </div>
        </header>

        <!-- ═══════ CONTENT AREA ═══════ -->
        <main class="admin-content">

            <?php if (is_array($flash)): ?>
                <div class="admin-flash-bar <?= ($flash['type'] ?? '') === 'error' ? 'admin-flash-bar-error' : 'admin-flash-bar-success'; ?>">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" class="flex-shrink-0">
                        <?php if (($flash['type'] ?? '') === 'error'): ?>
                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.8"/><path d="M12 8v4m0 4h.01" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        <?php else: ?>
                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.8"/><path d="M9 12l2 2 4-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        <?php endif; ?>
                    </svg>
                    <span><?= e((string) ($flash['message'] ?? '')); ?></span>
                    <button class="admin-flash-close" onclick="this.parentElement.remove()">
                        <svg width="14" height="14" fill="none" viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                    </button>
                </div>
            <?php endif; ?>

            <!-- ════ DASHBOARD ════ -->
            <?php if ($activeTab === 'dashboard'): ?>
                <div class="admin-page-header">
                    <h2 class="admin-page-title">Dashboard</h2>
                    <p class="admin-page-desc">Sitenizin genel durumuna hızlı bir bakış.</p>
                </div>

                <div class="admin-stats-grid">
                    <div class="admin-stat-card">
                        <div class="admin-stat-card-icon admin-stat-card-icon--gold">
                            <svg width="22" height="22" fill="none" viewBox="0 0 24 24"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><line x1="3" y1="6" x2="21" y2="6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><path d="M16 10a4 4 0 0 1-8 0" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </div>
                        <div class="admin-stat-card-body">
                            <p class="admin-stat-card-label">Toplam Ürün</p>
                            <p class="admin-stat-card-value"><?= e((string) count($products)); ?></p>
                        </div>
                        <a href="<?= e($adminBaseUrl . '/products'); ?>" class="admin-stat-card-link">Yönet →</a>
                    </div>

                    <div class="admin-stat-card">
                        <div class="admin-stat-card-icon admin-stat-card-icon--blue">
                            <svg width="22" height="22" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.8"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.8"/><path d="M12 2v3m0 14v3M2 12h3m14 0h3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                        </div>
                        <div class="admin-stat-card-body">
                            <p class="admin-stat-card-label">Toplam Tasarım</p>
                            <p class="admin-stat-card-value"><?= e((string) count($designs)); ?></p>
                        </div>
                        <a href="<?= e($adminBaseUrl . '/designs'); ?>" class="admin-stat-card-link">Yönet →</a>
                    </div>

                    <div class="admin-stat-card">
                        <div class="admin-stat-card-icon admin-stat-card-icon--green">
                            <svg width="22" height="22" fill="none" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><circle cx="12" cy="7" r="4" stroke="currentColor" stroke-width="1.8"/></svg>
                        </div>
                        <div class="admin-stat-card-body">
                            <p class="admin-stat-card-label">Aktif Kullanıcı</p>
                            <p class="admin-stat-card-value"><?= e((string) $adminUser['username']); ?></p>
                        </div>
                        <span class="admin-stat-card-link" style="color:var(--admin-green);">Çevrimiçi</span>
                    </div>

                    <div class="admin-stat-card">
                        <div class="admin-stat-card-icon admin-stat-card-icon--gold">
                            <svg width="22" height="22" fill="none" viewBox="0 0 24 24"><path d="M4 5h16a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><polyline points="22,7 12,13 2,7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </div>
                        <div class="admin-stat-card-body">
                            <p class="admin-stat-card-label">Yeni Mesaj</p>
                            <p class="admin-stat-card-value"><?= e((string) $contactStatusCounts['new']); ?></p>
                        </div>
                        <a href="<?= e($adminBaseUrl . '/messages'); ?>" class="admin-stat-card-link">Mesajlar →</a>
                    </div>
                </div>

                <div class="admin-dashboard-grid">
                    <div class="admin-card">
                        <div class="admin-card-header">
                            <h3 class="admin-card-title">Son Ürünler</h3>
                            <a href="<?= e($adminBaseUrl . '/products'); ?>" class="admin-card-action">Tümünü Gör</a>
                        </div>
                        <div class="admin-card-body">
                            <?php if (empty($products)): ?>
                                <p class="admin-empty-msg">Henüz ürün eklenmedi.</p>
                            <?php else: ?>
                                <table class="admin-table">
                                    <thead><tr><th>Ürün Adı</th><th>Fiyat</th><th>İşlem</th></tr></thead>
                                    <tbody>
                                    <?php foreach (array_slice($products, 0, 5) as $p): ?>
                                        <tr>
                                            <td><?= e((string) ($p['name'] ?? '')); ?></td>
                                            <td class="admin-table-price">$<?= e(number_format((float) ($p['price'] ?? 0), 2)); ?></td>
                                            <td><a href="<?= e($adminBaseUrl . '/products'); ?>" class="admin-table-link">Düzenle</a></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="admin-card">
                        <div class="admin-card-header">
                            <h3 class="admin-card-title">Son Tasarımlar</h3>
                            <a href="<?= e($adminBaseUrl . '/designs'); ?>" class="admin-card-action">Tümünü Gör</a>
                        </div>
                        <div class="admin-card-body">
                            <?php if (empty($designs)): ?>
                                <p class="admin-empty-msg">Henüz tasarım eklenmedi.</p>
                            <?php else: ?>
                                <table class="admin-table">
                                    <thead><tr><th>Başlık</th><th>İşlem</th></tr></thead>
                                    <tbody>
                                    <?php foreach (array_slice($designs, 0, 5) as $d): ?>
                                        <tr>
                                            <td><?= e((string) ($d['title'] ?? '')); ?></td>
                                            <td><a href="<?= e($adminBaseUrl . '/designs'); ?>" class="admin-table-link">Düzenle</a></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="admin-card">
                        <div class="admin-card-header">
                            <h3 class="admin-card-title">Son Iletisim Mesajları</h3>
                            <a href="<?= e($adminBaseUrl . '/messages'); ?>" class="admin-card-action">Mesajları Gör</a>
                        </div>
                        <div class="admin-card-body">
                            <?php if (!$contactTableAvailable): ?>
                                <p class="admin-empty-msg">`contact_messages` tablosu bulunamadi.</p>
                            <?php elseif (empty($contactMessages)): ?>
                                <p class="admin-empty-msg">Henüz iletişim mesajı yok.</p>
                            <?php else: ?>
                                <table class="admin-table">
                                    <thead><tr><th>Gönderen</th><th>Konu</th><th>Durum</th></tr></thead>
                                    <tbody>
                                    <?php foreach (array_slice($contactMessages, 0, 5) as $message): ?>
                                        <?php
                                        $status = (string) ($message['status'] ?? 'new');
                                        $statusLabel = $contactStatusLabels[$status] ?? 'Yeni';
                                        ?>
                                        <tr>
                                            <td><?= e((string) ($message['name'] ?? '')); ?></td>
                                            <td><?= e((string) ($message['subject'] ?? '')); ?></td>
                                            <td>
                                                <span class="admin-status-badge admin-status-badge--<?= e($status); ?>">
                                                    <?= e($statusLabel); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            <?php endif; ?>

            <!-- ════ DESIGNS ════ -->
            <?php if ($activeTab === 'designs'): ?>
                <div class="admin-page-header">
                    <h2 class="admin-page-title">Tasarım Galerisi Yönetimi</h2>
                    <p class="admin-page-desc">Galeride görünecek resim ve videoları buradan yönetebilirsiniz.</p>
                </div>

                <!-- Add Design Form -->
                <div class="admin-card admin-card--add">
                    <div class="admin-card-header">
                        <h3 class="admin-card-title">Yeni Tasarım Ekle</h3>
                    </div>
                    <div class="admin-card-body">
                        <form method="post" action="<?= e($adminBaseUrl); ?>" class="admin-add-form" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="create_design" />
                            <input type="hidden" name="_csrf" value="<?= e($csrf); ?>" />
                            <div class="admin-form-grid admin-form-grid--3">
                                <div class="admin-field">
                                    <label class="admin-label">Resim Yükle (*isteğe bağlı)</label>
                                    <input type="file" name="img_file" accept=".jpg,.jpeg,.png,.webp,.gif,.heic" class="admin-input" style="padding-top:6px;" />
                                </div>
                                <div class="admin-field">
                                    <label class="admin-label">Video Yükle (*isteğe bağlı)</label>
                                    <input type="file" name="video_file" accept=".mp4,.mov,.webm" class="admin-input" style="padding-top:6px;" />
                                </div>
                                <div class="admin-field admin-field--action">
                                    <label class="admin-label">&nbsp;</label>
                                    <button type="submit" class="admin-btn admin-btn--gold admin-btn--full">Yükle</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Designs Grid -->
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h3 class="admin-card-title">Tüm Tasarımlar <span class="admin-count-badge"><?= count($designs); ?></span></h3>
                    </div>
                    <div class="admin-card-body">
                        <div class="admin-item-cards" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 16px;">
                            <?php foreach ($designs as $design): ?>
                                <div class="admin-item-card" style="margin-bottom:0;">
                                    <div style="height: 200px;
; overflow: hidden; background:#f4f4f5; display:flex; align-items:center; justify-content:center;">
                                        <?php if(!empty($design['img_url'])): ?>
                                            <img src="<?= e(str_replace('/uploads/designs/', '/uploads/', $design['img_url'])); ?>" loading="lazy" style="width:100%; height:100%; object-fit:cover;" />
                                        <?php elseif(!empty($design['video_url'])): ?>
                                            <video src="<?= e(str_replace('/uploads/designs/', '/uploads/', $design['video_url'])); ?>" style="width:100%; height:100%; object-fit:cover;" controls></video>
                                        <?php endif; ?>
                                    </div>
                                    <div class="admin-item-card-header" style="padding: 12px; border-top: 1px solid #e4e4e7;">
                                        <span class="admin-item-card-id">#<?= e((string)$design['id']); ?></span>
                                        <form method="post" action="<?= e($adminBaseUrl); ?>" style="margin:0;">
                                            <input type="hidden" name="action" value="delete_design" />
                                            <input type="hidden" name="id" value="<?= e((string)$design['id']); ?>" />
                                            <input type="hidden" name="_csrf" value="<?= e($csrf); ?>" />
                                            <button type="submit" class="admin-btn admin-btn--sm admin-btn--red" onclick="return confirm('Silinsin mi?')">Sil</button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            <!-- ════ MESSAGES ════ -->
            <?php if ($activeTab === 'messages'): ?>
                <div class="admin-page-header">
                    <h2 class="admin-page-title">Iletisim Mesajları</h2>
                    <p class="admin-page-desc">Ziyaretçilerden gelen iletişim taleplerini yönetin.</p>
                </div>

                <?php if (!$contactTableAvailable): ?>
                    <div class="admin-card">
                        <div class="admin-card-body">
                            <p class="admin-empty-msg">`contact_messages` tablosu bulunamadi. Lutfen `schema.sql` dosyasını tekrar çalıştırın.</p>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="admin-stats-grid admin-stats-grid--messages">
                        <div class="admin-stat-card">
                            <div class="admin-stat-card-icon admin-stat-card-icon--gold">
                                <svg width="22" height="22" fill="none" viewBox="0 0 24 24"><path d="M4 5h16a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><polyline points="22,7 12,13 2,7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </div>
                            <div class="admin-stat-card-body">
                                <p class="admin-stat-card-label">Toplam Mesaj</p>
                                <p class="admin-stat-card-value"><?= e((string) $contactStatusCounts['total']); ?></p>
                            </div>
                        </div>
                        <div class="admin-stat-card">
                            <div class="admin-stat-card-icon admin-stat-card-icon--gold">
                                <svg width="22" height="22" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.8"/><path d="M12 8v4m0 4h.01" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                            </div>
                            <div class="admin-stat-card-body">
                                <p class="admin-stat-card-label">Yeni</p>
                                <p class="admin-stat-card-value"><?= e((string) $contactStatusCounts['new']); ?></p>
                            </div>
                        </div>
                        <div class="admin-stat-card">
                            <div class="admin-stat-card-icon admin-stat-card-icon--blue">
                                <svg width="22" height="22" fill="none" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </div>
                            <div class="admin-stat-card-body">
                                <p class="admin-stat-card-label">Okundu / Yanitlandi</p>
                                <p class="admin-stat-card-value"><?= e((string) ($contactStatusCounts['read'] + $contactStatusCounts['replied'])); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="admin-card">
                        <div class="admin-card-header">
                            <h3 class="admin-card-title">Tüm Mesajlar <span class="admin-count-badge"><?= count($contactMessages); ?></span></h3>
                            <div class="admin-card-actions">
                                <input type="text" class="admin-search-input" placeholder="Mesaj ara..." oninput="filterTable(this,'messageTable')" />
                            </div>
                        </div>
                        <div class="admin-card-body admin-card-body--table">
                            <?php if (empty($contactMessages)): ?>
                                <div class="admin-empty-state">
                                    <svg width="40" height="40" fill="none" viewBox="0 0 24 24" opacity=".3"><path d="M4 5h16a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2z" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/><polyline points="22,7 12,13 2,7" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    <p>Henüz iletişim mesajı gelmedi.</p>
                                </div>
                            <?php else: ?>
                                <table class="admin-table admin-table--full" id="messageTable">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Gönderen</th>
                                            <th>Konu / Mesaj</th>
                                            <th>Dil</th>
                                            <th>Durum</th>
                                            <th>Tarih</th>
                                            <th>İşlemler</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($contactMessages as $message): ?>
                                        <?php
                                        $status = (string) ($message['status'] ?? 'new');
                                        if (!array_key_exists($status, $contactStatusLabels)) {
                                            $status = 'new';
                                        }
                                        $statusLabel = $contactStatusLabels[$status];
                                        $createdAtRaw = (string) ($message['created_at'] ?? '');
                                        $createdAtTimestamp = $createdAtRaw !== '' ? strtotime($createdAtRaw) : false;
                                        $createdAtLabel = $createdAtTimestamp !== false ? date('d.m.Y H:i', $createdAtTimestamp) : '-';
                                        $messagePreview = trim((string) preg_replace('/\s+/', ' ', (string) ($message['message'] ?? '')));
                                        if (function_exists('mb_strlen') && function_exists('mb_substr')) {
                                            if (mb_strlen($messagePreview) > 180) {
                                                $messagePreview = mb_substr($messagePreview, 0, 177) . '...';
                                            }
                                        } elseif (strlen($messagePreview) > 180) {
                                            $messagePreview = substr($messagePreview, 0, 177) . '...';
                                        }
                                        ?>
                                        <tr class="admin-table-row">
                                            <td class="admin-table-id"><?= e((string) ($message['id'] ?? '')); ?></td>
                                            <td>
                                                <p class="admin-message-sender"><?= e((string) ($message['name'] ?? '')); ?></p>
                                                <a href="mailto:<?= e((string) ($message['email'] ?? '')); ?>" class="admin-table-link"><?= e((string) ($message['email'] ?? '')); ?></a>
                                                <?php if (trim((string) ($message['phone'] ?? '')) !== ''): ?>
                                                    <p class="admin-message-meta"><?= e((string) ($message['phone'] ?? '')); ?></p>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <p class="admin-message-subject"><?= e((string) ($message['subject'] ?? '')); ?></p>
                                                <p class="admin-message-preview"><?= e($messagePreview); ?></p>
                                            </td>
                                            <td>
                                                <span class="admin-message-meta"><?= e(strtoupper((string) ($message['locale'] ?? ''))); ?></span>
                                            </td>
                                            <td>
                                                <form method="post" action="<?= e($adminBaseUrl); ?>" class="admin-message-status-form">
                                                    <input type="hidden" name="id" value="<?= e((string) ($message['id'] ?? '')); ?>" />
                                                    <input type="hidden" name="_csrf" value="<?= e($csrf); ?>" />
                                                    <div class="admin-row-actions">
                                                        <select name="status" class="admin-input admin-input--inline admin-select" aria-label="Mesaj durumu">
                                                            <?php foreach ($contactStatusLabels as $statusValue => $statusText): ?>
                                                                <option value="<?= e($statusValue); ?>" <?= $status === $statusValue ? 'selected' : ''; ?>>
                                                                    <?= e($statusText); ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                        <button name="action" value="update_contact_status" class="admin-btn admin-btn--sm admin-btn--green">
                                                            Kaydet
                                                        </button>
                                                    </div>
                                                </form>
                                            </td>
                                            <td><span class="admin-message-meta"><?= e($createdAtLabel); ?></span></td>
                                            <td>
                                                <form method="post" action="<?= e($adminBaseUrl); ?>" onsubmit="return confirm('Bu mesajı silmek istediğinize emin misiniz?')">
                                                    <input type="hidden" name="id" value="<?= e((string) ($message['id'] ?? '')); ?>" />
                                                    <input type="hidden" name="_csrf" value="<?= e($csrf); ?>" />
                                                    <button name="action" value="delete_contact_message" class="admin-btn admin-btn--sm admin-btn--red">
                                                        Sil
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <!-- ════ SETTINGS ════ -->
            <?php if ($activeTab === 'settings'): ?>
                <div class="admin-page-header">
                    <h2 class="admin-page-title">Site Ayarları</h2>
                    <p class="admin-page-desc">Ana sayfa ve marka içeriklerini buradan yönetin.</p>
                </div>

                <form method="post" action="<?= e($adminBaseUrl); ?>">
                    <input type="hidden" name="action" value="save_settings" />
                    <input type="hidden" name="_csrf" value="<?= e($csrf); ?>" />

                    <?php
                    $settingGroups = [
                        'Marka Bilgileri' => [
                            'brand_name'  => 'Marka Adı',
                            'brand_short' => 'Kısa Ad',
                            'brand_subline' => 'Marka Alt Başlığı',
                        ],
                        'Tasarım Galerisi (Tekil Seçim)' => [
                            'gallery_image' => 'Galeri Görsel URL',
                            'gallery_video' => 'Galeri Video URL',
                        ],
                        'Hero Bölümü' => [
                            'hero_kicker'      => 'Hero Kicker',
                            'hero_description' => 'Hero Açıklaması',
                            'hero_cta_shop'    => 'CTA - İletişim',
                            'hero_cta_designs' => 'CTA - Katalog',
                            'hero_image'       => 'Hero Görsel URL',
                        ],
                        'Footer' => [
                            'footer_tagline'          => 'Footer Slogan',
                            'footer_manifesto_title'  => 'Manifesto Başlığı',
                            'footer_manifesto_text'   => 'Manifesto Metni',
                        ],
                        'İletişim & Sosyal Medya' => [
                            'phone'               => 'Telefon',
                            'viber'               => 'Viber',
                            'telegram'            => 'Telegram',
                            'whatsapp_number'     => 'WhatsApp Numarası',
                            'email_address'       => 'E-posta Adresi',
                            'business_location'   => 'İşletme Konumu',
                            'instagram_url'       => 'Instagram URL',
                            'facebook_url'        => 'Facebook URL',
                            'youtube_url'         => 'YouTube URL',
                            'tiktok_url'          => 'TikTok URL',
                        ],
                        'Banka Bilgileri' => [
                            'bank_account_1' => 'Banka 1',
                            'bank_account_2' => 'Banka 2',
                            'bank_account_3' => 'Banka 3',
                            'bank_account_4' => 'Banka 4',
                        ],
                    ];
                    $textareaFields = ['hero_description', 'footer_manifesto_text', 'business_location'];
                    ?>

                    <?php foreach ($settingGroups as $groupLabel => $fields): ?>
                        <div class="admin-card admin-settings-card">
                            <div class="admin-card-header">
                                <h3 class="admin-card-title"><?= e($groupLabel); ?></h3>
                            </div>
                            <div class="admin-card-body">
                                <div class="admin-form-grid admin-form-grid--2">
                                    <?php foreach ($fields as $fieldKey => $fieldLabel): ?>
                                        <div class="admin-field <?= in_array($fieldKey, $textareaFields, true) ? 'admin-field--full' : ''; ?>">
                                            <label class="admin-label"><?= e($fieldLabel); ?></label>
                                            <?php if (in_array($fieldKey, $textareaFields, true)): ?>
                                                <textarea name="<?= e($fieldKey); ?>" rows="3" class="admin-input admin-input--textarea"><?= e((string) ($settings[$fieldKey] ?? '')); ?></textarea>
                                            <?php else: ?>
                                                <input type="text" name="<?= e($fieldKey); ?>" value="<?= e((string) ($settings[$fieldKey] ?? '')); ?>" class="admin-input" />
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <div class="admin-settings-footer">
                        <button type="submit" class="admin-btn admin-btn--gold admin-btn--lg">
                            <svg width="16" height="16" fill="none" viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><polyline points="17 21 17 13 7 13 7 21" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><polyline points="7 3 7 8 15 8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            Tüm Ayarları Kaydet
                        </button>
                    </div>
                </form>
            <?php endif; ?>

        </main>
    </div>

    <script>
        // Clock
        const clockEl = document.getElementById('adminClock');
        function updateClock() {
            if (!clockEl) return;
            const now = new Date();
            clockEl.textContent = now.toLocaleTimeString('tr-TR', { hour: '2-digit', minute: '2-digit' });
        }
        updateClock();
        setInterval(updateClock, 1000);

        // Sidebar toggle (mobile)
        const sidebar = document.getElementById('adminSidebar');
        const toggleBtn = document.getElementById('sidebarToggle');
        if (toggleBtn && sidebar) {
            toggleBtn.addEventListener('click', () => {
                sidebar.classList.toggle('admin-sidebar--open');
            });
        }

        // Live table filter (for message tables etc.)
        function filterTable(input, tableId) {
            const filter = input.value.toLowerCase();
            const rows = document.querySelectorAll('#' + tableId + ' tbody tr');
            rows.forEach(row => {
                row.style.display = row.textContent.toLowerCase().includes(filter) ? '' : 'none';
            });
        }

        // Live card filter (for product / design card lists)
        function filterCards(input, containerId) {
            const filter = input.value.toLowerCase();
            const cards = document.querySelectorAll('#' + containerId + ' .admin-item-card');
            cards.forEach(card => {
                const search = (card.getAttribute('data-search') || '') + ' ' + card.textContent.toLowerCase();
                card.style.display = search.includes(filter) ? '' : 'none';
            });
        }

        function inferMediaTypeFromFileName(fileName) {
            const lower = fileName.toLowerCase();
            if (/\.(mp4|webm|ogg|mov)$/i.test(lower)) {
                return 'video';
            }
            return 'image';
        }

        function attachMediaDropzones() {
            const dropzones = document.querySelectorAll('[data-media-dropzone]');
            dropzones.forEach(zone => {
                if (zone.dataset.ready === '1') {
                    return;
                }
                zone.dataset.ready = '1';

                const fileInput = zone.querySelector('.admin-media-file-input');
                const fileNameEl = zone.querySelector('.admin-media-file-name');
                const defaultName = fileNameEl ? (fileNameEl.getAttribute('data-default-text') || 'Dosya secilmedi') : 'Dosya secilmedi';
                const form = zone.closest('form');
                const mediaTypeSelect = form ? form.querySelector('select[name="media_type"]') : null;

                if (!fileInput || !fileNameEl) {
                    return;
                }

                const refreshDropzoneState = () => {
                    const file = fileInput.files && fileInput.files.length > 0 ? fileInput.files[0] : null;
                    if (!file) {
                        zone.classList.remove('has-file');
                        fileNameEl.textContent = defaultName;
                        return;
                    }

                    zone.classList.add('has-file');
                    fileNameEl.textContent = file.name;

                    if (mediaTypeSelect) {
                        mediaTypeSelect.value = inferMediaTypeFromFileName(file.name);
                    }
                };

                fileInput.addEventListener('change', refreshDropzoneState);

                ['dragenter', 'dragover'].forEach(eventName => {
                    zone.addEventListener(eventName, event => {
                        event.preventDefault();
                        event.stopPropagation();
                        zone.classList.add('is-dragover');
                    });
                });

                ['dragleave', 'dragend'].forEach(eventName => {
                    zone.addEventListener(eventName, event => {
                        event.preventDefault();
                        event.stopPropagation();
                        zone.classList.remove('is-dragover');
                    });
                });

                zone.addEventListener('drop', event => {
                    event.preventDefault();
                    event.stopPropagation();
                    zone.classList.remove('is-dragover');

                    const droppedFiles = event.dataTransfer && event.dataTransfer.files ? event.dataTransfer.files : null;
                    if (!droppedFiles || droppedFiles.length === 0) {
                        return;
                    }

                    try {
                        if (typeof DataTransfer !== 'undefined') {
                            const dataTransfer = new DataTransfer();
                            dataTransfer.items.add(droppedFiles[0]);
                            fileInput.files = dataTransfer.files;
                        } else {
                            fileInput.files = droppedFiles;
                        }
                    } catch (error) {
                        return;
                    }

                    fileInput.dispatchEvent(new Event('change', { bubbles: true }));
                });

                refreshDropzoneState();
            });
        }

        attachMediaDropzones();
    </script>
</body>
</html>
