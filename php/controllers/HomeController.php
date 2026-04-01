<?php
declare(strict_types=1);

final class HomeController
{
    public function index(): array
    {
        $data = $this->loadBasePayload();
        $data['activePage'] = 'home';

        return [
            'title' => 'Dekorasyon Manken Dünyası',
            'view' => 'home',
            'data' => $data,
        ];
    }

    public function designsIndex(): array
    {
        $data = $this->loadBasePayload();
        $data['activePage'] = 'designs';

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $limit = 30;
        $offset = ($page - 1) * $limit;

        $filter = strtolower(trim((string) ($_GET['filter'] ?? 'all')));
        $data['currentFilter'] = $filter;

        try {
            $pdo = databaseConnection();
            if ($this->tableExists($pdo, 'designs')) {

                $whereSQL = '1=1';
                if ($filter === 'image') {
                    $whereSQL = "img_url IS NOT NULL AND img_url != ''";
                } elseif ($filter === 'video') {
                    $whereSQL = "video_url IS NOT NULL AND video_url != ''";
                }

                $countStmt = $pdo->query("SELECT COUNT(*) FROM designs WHERE $whereSQL");
                $total = (int) $countStmt->fetchColumn();
                $data['totalPages'] = (int) ceil($total / $limit);
                $data['currentPage'] = $page;

                $stmt = $pdo->prepare("SELECT id, img_url, video_url FROM designs WHERE $whereSQL ORDER BY id DESC LIMIT :limit OFFSET :offset");
                $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
                $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
                $stmt->execute();
                $designRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $data['designs'] = array_map(
                    function (array $row): array {
                        $designId = (int) ($row['id'] ?? 0);
                        $row['img_url'] = mediaUrl($row['img_url'] ?? '');
                        $row['video_url'] = mediaUrl($row['video_url'] ?? '');
                        $row['detail_url'] = $designId > 0
                            ? localizedPath(currentLocale(), '/designs/' . $designId)
                            : localizedPath(currentLocale(), '/designs');

                        return $row;
                    },
                    $designRows
                );
            } else {
                $data['designs'] = [];
                $data['totalPages'] = 1;
                $data['currentPage'] = 1;
            }
        } catch (Throwable) {
            $data['designs'] = [];
        }

        return [
            'title' => t('pages.designs_title', 'Tasarım Galerisi') . ' | Dekorasyon Manken Dünyası',
            'view' => 'designs',
            'data' => $data,
        ];
    }

    public function designDetail(array $params): array
    {
        $designId = (int) ($params['id'] ?? 0);
        if ($designId < 1) {
            return ['status' => 404];
        }

        $data = $this->loadBasePayload();
        $data['activePage'] = 'designs';

        try {
            $pdo = databaseConnection();
            if (!$this->tableExists($pdo, 'designs')) {
                return ['status' => 404];
            }

            try {
                $stmt = $pdo->prepare('SELECT id, title, details, img_url, video_url FROM designs WHERE id = :id LIMIT 1');
                $stmt->execute(['id' => $designId]);
            } catch (Throwable) {
                $stmt = $pdo->prepare('SELECT id, img_url, video_url FROM designs WHERE id = :id LIMIT 1');
                $stmt->execute(['id' => $designId]);
            }

            $designRow = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!is_array($designRow)) {
                return ['status' => 404];
            }

            $designTitle = trim((string) ($designRow['title'] ?? ''));
            if ($designTitle === '') {
                $designTitle = '#' . $designId;
            }

            $designDetails = trim((string) ($designRow['details'] ?? ''));
            $designMedia = [];

            $imageUrl = mediaUrl((string) ($designRow['img_url'] ?? ''));
            if ($imageUrl !== '') {
                $designMedia[] = [
                    'type' => 'image',
                    'url' => $imageUrl,
                ];
            }

            $videoUrl = mediaUrl((string) ($designRow['video_url'] ?? ''));
            if ($videoUrl !== '') {
                $designMedia[] = [
                    'type' => 'video',
                    'url' => $videoUrl,
                ];
            }

            $data['design'] = [
                'id' => $designId,
                'title' => $designTitle,
                'details' => $designDetails,
                'media' => $designMedia,
            ];
        } catch (Throwable) {
            return ['status' => 404];
        }

        return [
            'title' => (string) ($data['design']['title'] ?? t('pages.design_detail_title', 'Tasarım Detayı')) . ' | Dekorasyon Manken Dünyası',
            'view' => 'design-detail',
            'data' => $data,
        ];
    }

    public function contactIndex(): array
    {
        $data = $this->loadBasePayload();
        $data['activePage'] = 'contact';

        return [
            'title' => t('pages.contact_title', 'İletişim') . ' | Dekorasyon Manken Dünyası',
            'view' => 'contact',
            'data' => $data,
        ];
    }

    public function submitContact(): array
    {
        $defaultRedirect = localizedPath(currentLocale(), '/contact') . '#contact';
        $returnTo = $this->normalizeContactReturnPath((string) ($_POST['return_to'] ?? ''), $defaultRedirect);

        $token = trim((string) ($_POST['contact_form_token'] ?? ''));
        if (!$this->consumeContactFormToken($token)) {
            $this->setContactFlash('error', t('contact.flash_invalid_token', 'Form süresi dolmuş. Lütfen tekrar deneyin.'));

            return [
                'redirect' => $returnTo,
                'status' => 303,
            ];
        }

        $name = trim((string) ($_POST['name'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $phone = trim((string) ($_POST['phone'] ?? ''));
        $subject = trim((string) ($_POST['subject'] ?? ''));
        $message = trim((string) ($_POST['message'] ?? ''));

        $oldInput = [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'subject' => $subject,
            'message' => $message,
        ];

        if ($name === '' || $email === '' || $subject === '' || $message === '') {
            $this->setContactFlash('error', t('contact.flash_required', 'Lütfen tüm zorunlu alanları doldurun.'), $oldInput);

            return [
                'redirect' => $returnTo,
                'status' => 303,
            ];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->setContactFlash('error', t('contact.flash_email', 'Geçerli bir e-posta adresi girin.'), $oldInput);

            return [
                'redirect' => $returnTo,
                'status' => 303,
            ];
        }

        try {
            $pdo = databaseConnection();

            if (!$this->tableExists($pdo, 'contact_messages')) {
                throw new RuntimeException('contact_messages table is missing');
            }

            $stmt = $pdo->prepare(
                'INSERT INTO contact_messages (name, email, phone, subject, message, locale, ip_address, user_agent, idempotency_key) VALUES (:name, :email, :phone, :subject, :message, :locale, :ip_address, :user_agent, :idempotency_key)'
            );
            $stmt->execute([
                'name' => mb_substr($name, 0, 150),
                'email' => mb_substr($email, 0, 190),
                'phone' => $phone === '' ? null : mb_substr($phone, 0, 60),
                'subject' => mb_substr($subject, 0, 180),
                'message' => mb_substr($message, 0, 4000),
                'locale' => currentLocale(),
                'ip_address' => mb_substr($this->clientIpAddress(), 0, 45),
                'user_agent' => mb_substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255),
                'idempotency_key' => $token,
            ]);

            $this->setContactFlash('success', t('contact.flash_success', 'Mesajınız başarıyla gönderildi.'));
        } catch (Throwable) {
            $this->setContactFlash('error', t('contact.flash_error', 'Mesaj gönderilemedi. Lütfen daha sonra tekrar deneyin.'), $oldInput);
        }

        return [
            'redirect' => $returnTo,
            'status' => 303,
        ];
    }

    private function loadBasePayload(): array
    {
        $data = [
            'brandName' => 'Dekorasyon Manken Dünyası',
            'brandShort' => 'DMD',
            'brandLogo' => asset('logo.png'),
            'siteSettings' => [],
            'dbError' => null,
            'activePage' => 'home',
            'contactFormToken' => $this->createContactFormToken(),
        ];

        try {
            $pdo = databaseConnection();
            $data['siteSettings'] = $this->fetchSiteSettings($pdo);
        } catch (Throwable) {
            $data['dbError'] = t('common.db_error', 'Database connection issue');
        }

        return $data;
    }

    private function fetchSiteSettings(PDO $pdo): array
    {
        if (!$this->tableExists($pdo, 'site_settings')) {
            return [];
        }

        $settings = [];
        $stmt = $pdo->query('SELECT setting_key, setting_value FROM site_settings');

        if ($stmt === false) {
            return $settings;
        }

        foreach ($stmt->fetchAll() as $row) {
            $key = (string) ($row['setting_key'] ?? '');
            if ($key === '') {
                continue;
            }

            $settings[$key] = $row['setting_value'] ?? '';
        }

        return $settings;
    }

    private function tableExists(PDO $pdo, string $table): bool
    {
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
    }

    private function setContactFlash(string $type, string $message, array $oldInput = []): void
    {
        $_SESSION['contact_flash'] = [
            'type' => $type,
            'message' => $message,
            'old' => $oldInput,
        ];
    }

    private function createContactFormToken(): string
    {
        if (!isset($_SESSION['contact_form_tokens']) || !is_array($_SESSION['contact_form_tokens'])) {
            $_SESSION['contact_form_tokens'] = [];
        }

        $this->pruneContactFormTokens();

        $token = bin2hex(random_bytes(24));
        $_SESSION['contact_form_tokens'][$token] = time();

        return $token;
    }

    private function consumeContactFormToken(string $token): bool
    {
        if ($token === '' || !isset($_SESSION['contact_form_tokens']) || !is_array($_SESSION['contact_form_tokens'])) {
            return false;
        }

        $this->pruneContactFormTokens();

        if (!array_key_exists($token, $_SESSION['contact_form_tokens'])) {
            return false;
        }

        unset($_SESSION['contact_form_tokens'][$token]);

        return true;
    }

    private function pruneContactFormTokens(): void
    {
        $now = time();
        $tokens = $_SESSION['contact_form_tokens'] ?? [];

        if (!is_array($tokens)) {
            $_SESSION['contact_form_tokens'] = [];

            return;
        }

        foreach ($tokens as $token => $createdAt) {
            if (!is_int($createdAt) || ($now - $createdAt) > 7200) {
                unset($tokens[$token]);
            }
        }

        if (count($tokens) > 50) {
            $tokens = array_slice($tokens, -50, null, true);
        }

        $_SESSION['contact_form_tokens'] = $tokens;
    }

    private function normalizeContactReturnPath(string $input, string $fallback): string
    {
        $value = trim($input);
        if ($value === '') {
            return $fallback;
        }

        $parts = parse_url($value);
        if ($parts === false) {
            return $fallback;
        }

        if (isset($parts['scheme']) || isset($parts['host'])) {
            return $fallback;
        }

        $path = (string) ($parts['path'] ?? '');
        if ($path === '' || !str_starts_with($path, '/')) {
            return $fallback;
        }

        $query = isset($parts['query']) ? '?' . $parts['query'] : '';
        $fragment = isset($parts['fragment']) ? '#' . $parts['fragment'] : '';

        return $path . $query . $fragment;
    }

    private function clientIpAddress(): string
    {
        $forwardedFor = (string) ($_SERVER['HTTP_X_FORWARDED_FOR'] ?? '');
        if ($forwardedFor !== '') {
            $parts = explode(',', $forwardedFor);
            $first = trim((string) ($parts[0] ?? ''));
            if ($first !== '') {
                return $first;
            }
        }

        $realIp = trim((string) ($_SERVER['HTTP_X_REAL_IP'] ?? ''));
        if ($realIp !== '') {
            return $realIp;
        }

        return trim((string) ($_SERVER['REMOTE_ADDR'] ?? ''));
    }
}
