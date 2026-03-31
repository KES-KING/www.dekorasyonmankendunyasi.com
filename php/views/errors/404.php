<!DOCTYPE html>
<html lang="<?= e(currentLocale()); ?>">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= e(t('errors.not_found_title', '404')); ?> | <?= e(t('brand.short', 'NYSCC')); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex min-h-screen items-center justify-center bg-black text-white">
    <div class="px-6 text-center">
        <p class="text-sm uppercase tracking-[0.3em] text-zinc-400"><?= e(t('brand.name', 'NewYork Society Creation Club')); ?></p>
        <h1 class="mt-3 text-5xl font-semibold text-[#D4AF37]"><?= e(t('errors.not_found_title', '404')); ?></h1>
        <p class="mt-4 text-zinc-300"><?= e(t('errors.not_found_message', 'The page you requested does not exist.')); ?></p>
        <a href="<?= e(localizedPath(currentLocale(), '/')); ?>" class="mt-8 inline-block rounded-full border border-[#D4AF37] px-6 py-2 text-sm text-[#D4AF37] transition hover:bg-[#D4AF37] hover:text-black"><?= e(t('errors.back_home', 'Back Home')); ?></a>
    </div>
</body>
</html>
