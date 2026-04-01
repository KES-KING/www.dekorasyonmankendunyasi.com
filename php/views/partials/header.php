<header class="site-header fixed top-0 z-40 w-full transition-all">
    <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-2 sm:px-6 lg:px-8">
        <a href="<?= e($localeHomeUrl); ?>" class="group flex shrink-0 items-center">
            <img
                src="<?= e(asset('logo.png')); ?>"
                alt="<?= e($settings['brand_name'] ?? t('brand.name', 'Dekorasyon Manken Dünyası')); ?>"
                class="h-14 w-auto object-contain drop-shadow-[0_6px_18px_rgba(0,0,0,0.55)] sm:h-16"
            />
        </a>

        <div class="flex items-center gap-4">
            <nav class="hidden items-center gap-6 text-xs uppercase tracking-[0.15em] text-zinc-200 sm:flex sm:text-sm font-medium">
                <a href="<?= e($localeHomeUrl); ?>" class="transition <?= $activePage === 'home' ? 'text-amber-300' : 'hover:text-amber-300'; ?>"><?= e(t('nav.home', 'Ana Sayfa')); ?></a>
                <a href="<?= e($designsUrl); ?>" class="transition <?= $activePage === 'designs' ? 'text-amber-300' : 'hover:text-amber-300'; ?>"><?= e(t('nav.designs', 'Tasarım Galerisi')); ?></a>
                <a href="<?= e($contactUrl); ?>" class="transition <?= $activePage === 'contact' ? 'text-amber-300' : 'hover:text-amber-300'; ?>"><?= e(t('nav.contact', 'İletişim')); ?></a>
            </nav>

            <div class="language-pill rounded-full border px-2 py-1">
                <label for="locale-switcher" class="sr-only"><?= e(t('nav.language', 'Dil')); ?></label>
                <select
                    id="locale-switcher"
                    aria-label="<?= e(t('nav.language', 'Dil')); ?>"
                    class="min-w-[10.5rem] bg-transparent px-2 py-1 text-[11px] font-semibold text-zinc-100 outline-none"
                    onchange="if (this.value) { window.location.href = this.value; }"
                >
                    <?php foreach ($languageSwitcher as $language): ?>
                        <option value="<?= e($language['url']); ?>" <?= $language['active'] ? 'selected' : ''; ?>>
                            <?= e(localeFlagEmoji((string) $language['code']) . ' ' . (string) $language['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <div class="border-t border-white/10 px-4 py-3 bg-slate-950/55 sm:hidden backdrop-blur-md">
        <nav class="flex items-center justify-center gap-6 text-[11px] uppercase tracking-[0.18em] text-zinc-200 font-medium">
            <a href="<?= e($localeHomeUrl); ?>" class="transition <?= $activePage === 'home' ? 'text-amber-300' : 'hover:text-amber-300'; ?>"><?= e(t('nav.home', 'Ana Sayfa')); ?></a>
            <a href="<?= e($designsUrl); ?>" class="transition <?= $activePage === 'designs' ? 'text-amber-300' : 'hover:text-amber-300'; ?>"><?= e(t('nav.designs', 'Galeri')); ?></a>
            <a href="<?= e($contactUrl); ?>" class="transition <?= $activePage === 'contact' ? 'text-amber-300' : 'hover:text-amber-300'; ?>"><?= e(t('nav.contact', 'İletişim')); ?></a>
        </nav>
    </div>
</header>
