<header class="site-header fixed top-0 z-40 w-full transition-all">
    <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-6 lg:px-8">
        <a href="<?= e($localeHomeUrl); ?>" class="group flex items-center gap-3">
            <svg width="32" height="32" fill="none" viewBox="0 0 24 24" class="text-yellow-600"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
            <div>
                <p class="font-display text-xl uppercase tracking-[0.16em] text-zinc-800 sm:text-2xl"><?= e($settings['brand_short'] ?? 'DMD'); ?></p>
                <p class="hidden text-[10px] uppercase tracking-[0.25em] text-zinc-500 sm:block"><?= e($settings['brand_subline'] ?? 'Dekorasyon ve Manken'); ?></p>
            </div>
        </a>

        <div class="flex items-center gap-4">
            <nav class="hidden items-center gap-6 text-xs uppercase tracking-[0.15em] text-zinc-600 sm:flex sm:text-sm font-medium">
                <a href="<?= e($localeHomeUrl); ?>" class="transition <?= $activePage === 'home' ? 'text-yellow-600' : 'hover:text-yellow-600'; ?>"><?= e(t('nav.home', 'Ana Sayfa')); ?></a>
                <a href="<?= e($designsUrl); ?>" class="transition <?= $activePage === 'designs' ? 'text-yellow-600' : 'hover:text-yellow-600'; ?>"><?= e(t('nav.designs', 'Tasarım Galerisi')); ?></a>
                <a href="<?= e($contactUrl); ?>" class="transition <?= $activePage === 'contact' ? 'text-yellow-600' : 'hover:text-yellow-600'; ?>"><?= e(t('nav.contact', 'İletişim')); ?></a>
            </nav>

            <div class="language-pill flex items-center gap-2 rounded-full px-3 py-1 border text-[11px] uppercase tracking-[0.18em]">
                <?php foreach ($languageSwitcher as $language): ?>
                    <a
                        href="<?= e($language['url']); ?>"
                        class="transition <?= $language['active'] ? 'text-yellow-600 font-bold' : 'hover:text-yellow-600'; ?>"
                        hreflang="<?= e($language['code']); ?>"
                    >
                        <?= e(strtoupper($language['code'])); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="border-t border-black/5 px-4 py-3 bg-white/50 sm:hidden backdrop-blur-md">
        <nav class="flex items-center justify-center gap-6 text-[11px] uppercase tracking-[0.18em] text-zinc-600 font-medium">
            <a href="<?= e($localeHomeUrl); ?>" class="transition <?= $activePage === 'home' ? 'text-yellow-600' : 'hover:text-yellow-600'; ?>"><?= e(t('nav.home', 'Ana Sayfa')); ?></a>
            <a href="<?= e($designsUrl); ?>" class="transition <?= $activePage === 'designs' ? 'text-yellow-600' : 'hover:text-yellow-600'; ?>"><?= e(t('nav.designs', 'Galeri')); ?></a>
            <a href="<?= e($contactUrl); ?>" class="transition <?= $activePage === 'contact' ? 'text-yellow-600' : 'hover:text-yellow-600'; ?>"><?= e(t('nav.contact', 'İletişim')); ?></a>
        </nav>
    </div>
</header>
