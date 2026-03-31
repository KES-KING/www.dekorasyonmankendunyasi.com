<?php
declare(strict_types=1);

function supportedLocales(): array
{
    return ['tr', 'en', 'de', 'ru'];
}

function defaultLocale(): string
{
    return 'en';
}

function localeNativeName(string $locale): string
{
    $map = [
        'tr' => 'Turkce',
        'en' => 'English',
        'de' => 'Deutsch',
        'ru' => 'Russkiy',
    ];

    return $map[$locale] ?? strtoupper($locale);
}

function isSupportedLocale(string $locale): bool
{
    return in_array(strtolower($locale), supportedLocales(), true);
}

function normalizePath(string $path): string
{
    $normalized = '/' . ltrim($path, '/');

    return rtrim($normalized, '/') ?: '/';
}

function appRelativePath(string $requestPath): string
{
    $normalized = normalizePath($requestPath);
    $base = basePath();

    if ($base !== '' && ($normalized === $base || str_starts_with($normalized, $base . '/'))) {
        $normalized = substr($normalized, strlen($base));
        if ($normalized === '') {
            return '/';
        }
    }

    // Support requests like /public/tr when server root is one level above public.
    if ($normalized === '/public') {
        return '/';
    }
    if (str_starts_with($normalized, '/public/')) {
        $normalized = substr($normalized, 7);
        if ($normalized === '') {
            return '/';
        }
    }

    return normalizePath($normalized);
}

function stripLocaleFromPath(string $path): array
{
    $normalized = normalizePath($path);
    $segments = explode('/', trim($normalized, '/'));
    $firstSegment = strtolower($segments[0] ?? '');

    if ($firstSegment !== '' && isSupportedLocale($firstSegment)) {
        $remaining = array_slice($segments, 1);
        $remainingPath = '/' . implode('/', $remaining);

        return [$firstSegment, normalizeRoutePath($remainingPath)];
    }

    return [null, $normalized];
}

function normalizeRoutePath(string $path): string
{
    $normalized = normalizePath($path);

    if ($normalized === '/index.php' || $normalized === '/public' || $normalized === '/public/index.php') {
        return '/';
    }

    return $normalized;
}

function localizedPath(string $locale, string $path = '/'): string
{
    $activeLocale = isSupportedLocale($locale) ? strtolower($locale) : defaultLocale();
    $routePath = normalizeRoutePath($path);
    $suffix = $routePath === '/' ? '' : $routePath;

    return basePath() . '/' . $activeLocale . $suffix;
}

function detectPreferredLocale(?string $acceptLanguage): string
{
    if ($acceptLanguage === null || trim($acceptLanguage) === '') {
        return defaultLocale();
    }

    $weightedLocales = [];

    foreach (explode(',', $acceptLanguage) as $part) {
        $item = trim($part);
        if ($item === '') {
            continue;
        }

        $pieces = explode(';', $item);
        $locale = strtolower(trim($pieces[0]));

        if ($locale === '' || $locale === '*') {
            continue;
        }

        $quality = 1.0;
        if (isset($pieces[1]) && str_starts_with(trim($pieces[1]), 'q=')) {
            $qualityValue = (float) substr(trim($pieces[1]), 2);
            if ($qualityValue >= 0 && $qualityValue <= 1) {
                $quality = $qualityValue;
            }
        }

        $weightedLocales[] = [$locale, $quality];
    }

    usort(
        $weightedLocales,
        static fn (array $a, array $b): int => $b[1] <=> $a[1]
    );

    foreach ($weightedLocales as [$locale]) {
        if (isSupportedLocale($locale)) {
            return $locale;
        }

        $short = substr($locale, 0, 2);
        if (isSupportedLocale($short)) {
            return $short;
        }
    }

    return defaultLocale();
}

function loadTranslations(string $locale): array
{
    static $cache = [];

    $locale = isSupportedLocale($locale) ? strtolower($locale) : defaultLocale();

    if (isset($cache[$locale])) {
        return $cache[$locale];
    }

    $filePath = dirname(__DIR__) . '/lang/' . $locale . '.json';
    if (!is_file($filePath)) {
        $cache[$locale] = [];

        return $cache[$locale];
    }

    $content = file_get_contents($filePath);
    if ($content === false) {
        $cache[$locale] = [];

        return $cache[$locale];
    }

    $decoded = json_decode($content, true);
    $cache[$locale] = is_array($decoded) ? $decoded : [];

    return $cache[$locale];
}

function setLocaleContext(string $locale, array $messages, array $fallbackMessages = []): void
{
    $GLOBALS['app_locale'] = isSupportedLocale($locale) ? strtolower($locale) : defaultLocale();
    $GLOBALS['app_messages'] = $messages;
    $GLOBALS['app_fallback_messages'] = $fallbackMessages;
}

function currentLocale(): string
{
    $locale = (string) ($GLOBALS['app_locale'] ?? defaultLocale());

    return isSupportedLocale($locale) ? $locale : defaultLocale();
}

function arrayDotGet(array $source, string $key, mixed $default = null): mixed
{
    $segments = explode('.', $key);
    $value = $source;

    foreach ($segments as $segment) {
        if (!is_array($value) || !array_key_exists($segment, $value)) {
            return $default;
        }

        $value = $value[$segment];
    }

    return $value;
}

function t(string $key, ?string $default = null): string
{
    $messages = $GLOBALS['app_messages'] ?? [];
    $fallbackMessages = $GLOBALS['app_fallback_messages'] ?? [];

    $value = arrayDotGet($messages, $key);

    if ($value === null) {
        $value = arrayDotGet($fallbackMessages, $key, $default ?? $key);
    }

    if (!is_scalar($value)) {
        return $default ?? $key;
    }

    return (string) $value;
}
