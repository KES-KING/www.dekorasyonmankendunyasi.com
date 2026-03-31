<?php
declare(strict_types=1);

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function formatPrice(mixed $price): string
{
    if (is_numeric($price)) {
        return '$' . number_format((float) $price, 2);
    }

    return (string) $price;
}

function basePath(): string
{
    static $basePath = null;

    if ($basePath !== null) {
        return $basePath;
    }

    $scriptName = (string) ($_SERVER['SCRIPT_NAME'] ?? '');
    if ($scriptName === '' || !str_starts_with($scriptName, '/')) {
        $basePath = '';

        return $basePath;
    }
    if (is_file($scriptName)) {
        $basePath = '';

        return $basePath;
    }

    $directory = str_replace('\\', '/', dirname($scriptName));

    if ($directory === '/' || $directory === '.' || $directory === '\\') {
        $basePath = '';

        return $basePath;
    }

    $basePath = rtrim($directory, '/');

    return $basePath;
}

function asset(string $path): string
{
    return basePath() . '/' . ltrim($path, '/');
}

function mediaUrl(mixed $value): string
{
    $url = trim((string) $value);
    if ($url === '') {
        return '';
    }

    if (preg_match('#^(?:https?:)?//#i', $url) === 1 || str_starts_with($url, 'data:') || str_starts_with($url, 'blob:')) {
        return $url;
    }

    $url = str_replace('\\', '/', $url);

    if (str_starts_with($url, '/public/')) {
        $url = substr($url, 7);
    }

    if (str_starts_with($url, 'public/')) {
        $url = substr($url, 6);
    }

    if (str_starts_with($url, '/')) {
        return basePath() . $url;
    }

    return asset($url);
}

function siteSetting(array $settings, string $key, mixed $default = null): mixed
{
    if (!array_key_exists($key, $settings)) {
        return $default;
    }

    $value = $settings[$key];

    if ($value === null || $value === '') {
        return $default;
    }

    return $value;
}
