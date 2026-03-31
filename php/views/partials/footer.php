<footer class="site-footer">
    <div class="mx-auto grid max-w-7xl grid-cols-1 gap-10 px-4 py-14 sm:px-6 lg:grid-cols-4 lg:px-8">
        <div class="lg:col-span-1">
            <svg width="40" height="40" fill="none" viewBox="0 0 24 24" class="text-yellow-600"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
            <p class="mt-4 text-xs uppercase tracking-[0.2em] text-yellow-600 font-bold"><?= e($settings['brand_name'] ?? t('brand.name', 'Dekorasyon Manken Dünyası')); ?></p>
            <p class="mt-2 text-sm text-zinc-500"><?= e($settings['footer_tagline'] ?? t('footer.tagline', 'Sizin İçin Tasarlandı')); ?></p>
        </div>

        <div>
            <h3 class="footer-title text-xs uppercase tracking-[0.24em] font-semibold"><?= e(t('footer.links_title', 'Hızlı Linkler')); ?></h3>
            <ul class="mt-4 space-y-2 text-sm">
                <li><a href="<?= e($localeHomeUrl); ?>" class="footer-link">Ana Sayfa</a></li>
                <li><a href="<?= e($designsUrl); ?>" class="footer-link">Tasarım Galerisi</a></li>
                <li><a href="<?= e($contactUrl); ?>" class="footer-link">İletişim</a></li>
            </ul>
        </div>

        <div>
            <h3 class="footer-title text-xs uppercase tracking-[0.24em] font-semibold"><?= e($settings['footer_manifesto_title'] ?? t('footer.manifesto_title', 'Hakkımızda')); ?></h3>
            <p class="mt-4 text-sm leading-6 text-zinc-500">
                <?= e($settings['footer_manifesto_text'] ?? t('footer.manifesto_text', 'Mekanlarınıza değer katmak için en özel manken ve dekorasyon ürünlerini sunuyoruz.')); ?>
            </p>
        </div>

        <div>
            <h3 class="footer-title text-xs uppercase tracking-[0.24em] font-semibold"><?= e(t('footer.contact_title', 'Tüm İletişim')); ?></h3>
            <ul class="mt-4 space-y-2 text-sm">
                <?php if (!empty($settings['instagram_url'])): ?>
                    <li><a href="<?= e($settings['instagram_url']); ?>" target="_blank" rel="noopener noreferrer" class="footer-link">Instagram</a></li>
                <?php endif; ?>
                <?php if (!empty($settings['facebook_url'])): ?>
                    <li><a href="<?= e($settings['facebook_url']); ?>" target="_blank" rel="noopener noreferrer" class="footer-link">Facebook</a></li>
                <?php endif; ?>
                <?php if (!empty($settings['youtube_url'])): ?>
                    <li><a href="<?= e($settings['youtube_url']); ?>" target="_blank" rel="noopener noreferrer" class="footer-link">YouTube</a></li>
                <?php endif; ?>
                <?php if (!empty($settings['tiktok_url'])): ?>
                    <li><a href="<?= e($settings['tiktok_url']); ?>" target="_blank" rel="noopener noreferrer" class="footer-link">TikTok</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>

    <div class="border-t border-black/5">
        <div class="mx-auto flex max-w-7xl flex-col items-start justify-between gap-2 px-4 py-5 text-xs text-zinc-400 sm:flex-row sm:items-center sm:px-6 lg:px-8">
            <p>&copy; <?= e((string) $currentYear); ?> <?= e($settings['brand_name'] ?? t('brand.name', 'Dekorasyon Manken Dünyası')); ?>. <?= e(t('footer.rights', 'Tüm hakları saklıdır.')); ?></p>
            <p><?= e(currentLocale()); ?> | <?= e($settings['footer_tagline'] ?? t('footer.tagline', 'Sizin İçin Tasarlandı')); ?></p>
        </div>
    </div>
</footer>
