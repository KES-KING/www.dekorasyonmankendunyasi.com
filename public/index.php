<?php
declare(strict_types=1);

if (PHP_SAPI === 'cli-server') {
    $requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $filePath = __DIR__ . $requestPath;

    if ($requestPath !== '/' && is_file($filePath)) {
        return false;
    }
}

require_once __DIR__ . '/../php/bootstrap.php';
require_once __DIR__ . '/../php/routes/web.php';

$router = registerRoutes();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$relativePath = appRelativePath($requestPath);

$pathSegments = array_values(array_filter(explode('/', trim($relativePath, '/')), static fn (string $segment): bool => $segment !== ''));
if (count($pathSegments) >= 2 && in_array($pathSegments[0], supportedLocales(), true) && $pathSegments[1] === 'site-admin') {
    $adminSuffix = implode('/', array_slice($pathSegments, 2));
    $target = basePath() . '/site-admin' . ($adminSuffix !== '' ? '/' . $adminSuffix : '');
    $queryString = $_SERVER['QUERY_STRING'] ?? '';

    if ($queryString !== '') {
        $target .= '?' . $queryString;
    }

    header('Location: ' . $target, true, 302);
    exit;
}

if ($relativePath === '/site-admin' || str_starts_with($relativePath, '/site-admin/')) {
    require __DIR__ . '/../php/admin/panel.php';
    exit;
}

[$pathLocale, $routePath] = stripLocaleFromPath($relativePath);
$routePath = normalizeRoutePath($routePath);

if ($pathLocale === null) {
    $detectedLocale = detectPreferredLocale($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? null);
    $redirectPath = localizedPath($detectedLocale, $relativePath);
    $queryString = $_SERVER['QUERY_STRING'] ?? '';

    if ($queryString !== '') {
        $redirectPath .= '?' . $queryString;
    }

    header('Vary: Accept-Language');
    header('Location: ' . $redirectPath, true, 302);
    exit;
}

$messages = loadTranslations($pathLocale);
$fallbackMessages = loadTranslations(defaultLocale());
setLocaleContext($pathLocale, $messages, $fallbackMessages);

$routeResult = $router->dispatch($method, $routePath);

if ($routeResult === null) {
    http_response_code(404);
    require __DIR__ . '/../php/views/errors/404.php';
    exit;
}

if (isset($routeResult['redirect']) && is_string($routeResult['redirect']) && $routeResult['redirect'] !== '') {
    $redirectStatus = (int) ($routeResult['status'] ?? 302);
    if ($redirectStatus < 300 || $redirectStatus > 399) {
        $redirectStatus = 302;
    }

    header('Location: ' . $routeResult['redirect'], true, $redirectStatus);
    exit;
}

$responseStatus = (int) ($routeResult['status'] ?? 200);
if ($responseStatus >= 400) {
    http_response_code($responseStatus);
    require __DIR__ . '/../php/views/errors/404.php';
    exit;
}

$data = $routeResult['data'] ?? [];
$siteSettings = is_array($data['siteSettings'] ?? null) ? $data['siteSettings'] : [];
$settings = $siteSettings;

$brandName = (string) siteSetting($siteSettings, 'brand_name', t('brand.name', $data['brandName'] ?? 'NewYork Society Creation Club'));
$brandShort = (string) siteSetting($siteSettings, 'brand_short', t('brand.short', $data['brandShort'] ?? 'NYSCC'));
$brandSubline = (string) siteSetting($siteSettings, 'brand_subline', t('brand.subline', 'Creation Club'));
$pageTitle = (string) ($routeResult['title'] ?? t('meta.title', $brandName));

$brandLogo = $data['brandLogo'] ?? asset('nyscc_logo_transparan.png');
$designs = $data['designs'] ?? [];
$totalPages = (int) ($data['totalPages'] ?? 1);
$currentPage = (int) ($data['currentPage'] ?? 1);
$currentFilter = (string) ($data['currentFilter'] ?? 'all');
$products = $data['products'] ?? [];
$dbError = $data['dbError'] ?? null;
$footerTagline = (string) siteSetting($siteSettings, 'footer_tagline', t('footer.tagline', 'Crafted for Instagram Luxury'));
$footerManifestoTitle = (string) siteSetting($siteSettings, 'footer_manifesto_title', t('footer.manifesto_title', 'Manifesto'));
$footerManifestoText = (string) siteSetting($siteSettings, 'footer_manifesto_text', t('footer.manifesto_text', 'We build refined visual narratives where fashion, identity, and digital craft move as one.'));
$whatsappNumber = (string) siteSetting($siteSettings, 'whatsapp_number', env('WHATSAPP_NUMBER', ''));

$viewName = (string) ($routeResult['view'] ?? 'home');
$allowedViews = ['home', 'designs', 'shop', 'contact', 'design-detail', 'product-detail'];
if (!in_array($viewName, $allowedViews, true)) {
    http_response_code(404);
    require __DIR__ . '/../php/views/errors/404.php';
    exit;
}

$viewPath = __DIR__ . '/../php/views/pages/' . $viewName . '.php';
if (!is_file($viewPath)) {
    http_response_code(404);
    require __DIR__ . '/../php/views/errors/404.php';
    exit;
}

$localeHomeUrl = localizedPath(currentLocale(), '/');
$designsUrl = localizedPath(currentLocale(), '/designs');
$shopUrl = localizedPath(currentLocale(), '/shop');
$contactUrl = localizedPath(currentLocale(), '/contact');
$currentYear = (int) date('Y');
$activePage = (string) ($data['activePage'] ?? 'home');
$design = is_array($data['design'] ?? null) ? $data['design'] : null;
$product = is_array($data['product'] ?? null) ? $data['product'] : null;
$contactFormToken = (string) ($data['contactFormToken'] ?? '');
$contactFlash = is_array($_SESSION['contact_flash'] ?? null) ? $_SESSION['contact_flash'] : null;
unset($_SESSION['contact_flash']);

$footerLinks = [
    ['label' => t('footer.link_home', 'Home'), 'url' => $localeHomeUrl],
    ['label' => t('footer.link_designs', 'Designs'), 'url' => $designsUrl],
    ['label' => t('footer.link_shop', 'Shop'), 'url' => $shopUrl],
    ['label' => t('footer.link_contact', 'Contact'), 'url' => $contactUrl],
];

$instagramLinks = [
    [
        'label' => t('footer.social_instagram', 'Instagram'),
        'url' => (string) siteSetting($siteSettings, 'instagram_url', env('INSTAGRAM_URL', 'https://instagram.com/')),
    ],
    [
        'label' => t('footer.social_reels', 'Instagram Reels'),
        'url' => (string) siteSetting($siteSettings, 'instagram_reels_url', env('INSTAGRAM_REELS_URL', 'https://instagram.com/reels')),
    ],
    [
        'label' => t('footer.social_shop', 'Instagram Shop'),
        'url' => (string) siteSetting($siteSettings, 'instagram_shop_url', env('INSTAGRAM_SHOP_URL', 'https://instagram.com/shop')),
    ],
];

$languageSwitcher = [];
$queryString = $_SERVER['QUERY_STRING'] ?? '';

foreach (supportedLocales() as $localeCode) {
    $url = localizedPath($localeCode, $routePath);
    if ($queryString !== '') {
        $url .= '?' . $queryString;
    }

    $languageSwitcher[] = [
        'code' => $localeCode,
        'name' => localeNativeName($localeCode),
        'url' => $url,
        'active' => $localeCode === currentLocale(),
    ];
}
?>
<!DOCTYPE html>
<html lang="<?= e(currentLocale()); ?>">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= e($pageTitle); ?></title>
    <meta name="theme-color" content="#000000" />

    <link rel="manifest" href="<?= e(asset('site.webmanifest')); ?>" />
    <link rel="icon" type="image/png" sizes="32x32" href="<?= e(asset('favicon-32x32.png')); ?>" />
    <link rel="icon" type="image/png" sizes="16x16" href="<?= e(asset('favicon-16x16.png')); ?>" />
    <link rel="shortcut icon" href="<?= e(asset('favicon.ico')); ?>" />
    <link rel="apple-touch-icon" sizes="180x180" href="<?= e(asset('apple-touch-icon.png')); ?>" />

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Manrope:wght@400;500;600&display=swap" rel="stylesheet" />

    <script src="<?= e(asset('js/tailwind-config.js')); ?>"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        (function () {
            try {
                var alreadySeen = window.localStorage.getItem('nyscc_intro_loader_seen') === '1';
                if (!alreadySeen) {
                    document.documentElement.classList.add('show-intro-loader');
                } else {
                    document.documentElement.classList.add('intro-loader-seen');
                }
            } catch (error) {
                document.documentElement.classList.add('show-intro-loader');
            }
        })();
    </script>

    <link rel="stylesheet" href="<?= e(asset('css/main.css')); ?>" />
</head>
<body class="font-body bg-black text-zinc-100 antialiased">
    <div id="introLoader" class="intro-loader" aria-hidden="true">
        <div class="intro-loader__inner">
            <img src="<?= e($brandLogo); ?>" alt="<?= e($brandName); ?>" class="intro-loader__logo" />
            <p class="intro-loader__brand"><?= e($brandName); ?></p>
            <div class="intro-loader__line" aria-hidden="true"><span></span></div>
        </div>
    </div>

    <?php require __DIR__ . '/../php/views/partials/header.php'; ?>

    <main>
        <?php require $viewPath; ?>
    </main>

    <?php require __DIR__ . '/../php/views/partials/footer.php'; ?>

    <script defer src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
    <script defer src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>
    <script defer src="<?= e(asset('js/main.js')); ?>"></script>
</body>
</html>
