<?php
$productName = (string) ($product['name'] ?? t('pages.product_detail_title', 'Product Detail'));
$productPrice = formatPrice($product['price'] ?? 0);
$productMedia = is_array($product['media'] ?? null) ? $product['media'] : [];
$productDetails = trim((string) ($product['details'] ?? ''));
$whatsappRawNumber = (string) ($whatsappNumber ?? '');
$whatsappDigits = preg_replace('/\D+/', '', $whatsappRawNumber);
$whatsappLink = '';

if (is_string($whatsappDigits) && $whatsappDigits !== '') {
    $locale = currentLocale();
    $localeTurkishMap = [
        'tr' => 'Turkce',
        'en' => 'Ingilizce',
        'de' => 'Almanca',
        'ru' => 'Rusca',
    ];
    $localeLabel = $localeTurkishMap[$locale] ?? strtoupper($locale);
    $productDetailPath = localizedPath($locale, '/shop/' . (string) ($product['id'] ?? ''));

    $whatsappMessageLines = [
        'Merhaba, ' . $productName . ' urunu hakkinda bilgi almak istiyorum.',
        'Urun fiyati: ' . $productPrice,
        'Goruntulenen dil: ' . $localeLabel . ' (' . $locale . ')',
        'Urun sayfasi: ' . $productDetailPath,
    ];

    $whatsappLink = 'https://wa.me/' . $whatsappDigits . '?text=' . rawurlencode(implode("\n", $whatsappMessageLines));
}

if ($productMedia === []) {
    $productMedia = [
        ['type' => 'image', 'url' => asset('images/hero.jpg')],
    ];
}
?>
<section class="mx-auto w-full max-w-7xl px-4 pb-24 pt-36 sm:px-6 lg:px-8">
    <a href="<?= e($shopUrl); ?>" class="inline-flex items-center rounded-full border border-zinc-700 px-4 py-2 text-xs uppercase tracking-[0.16em] text-zinc-300 transition hover:border-gold/70 hover:text-gold">
        <?= e(t('common.back_to_shop', 'Back To Shop')); ?>
    </a>

    <div class="mt-8 grid grid-cols-1 gap-8 lg:grid-cols-12">
        <div class="lg:col-span-7">
            <div class="luxury-panel detail-gallery overflow-hidden rounded-2xl p-4">
                <div class="relative aspect-[4/5] overflow-hidden rounded-xl bg-zinc-950">
                    <?php foreach ($productMedia as $index => $item): ?>
                        <?php
                        $type = (string) ($item['type'] ?? 'image');
                        $url = (string) ($item['url'] ?? '');
                        if ($url === '') {
                            continue;
                        }
                        ?>
                        <?php if ($type === 'video'): ?>
                            <video
                                src="<?= e($url); ?>"
                                controls
                                playsinline
                                class="detail-media-item absolute inset-0 h-full w-full object-cover <?= $index === 0 ? 'opacity-100' : 'pointer-events-none opacity-0'; ?>"
                                data-media-index="<?= e((string) $index); ?>"
                            ></video>
                        <?php else: ?>
                            <img
                                src="<?= e($url); ?>"
                                alt="<?= e($productName); ?>"
                                class="detail-media-item absolute inset-0 h-full w-full object-cover <?= $index === 0 ? 'opacity-100' : 'pointer-events-none opacity-0'; ?>"
                                data-media-index="<?= e((string) $index); ?>"
                            />
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>

                <?php if (count($productMedia) > 1): ?>
                    <div class="mt-4 grid grid-cols-4 gap-3 sm:grid-cols-6 lg:grid-cols-5">
                        <?php foreach ($productMedia as $index => $item): ?>
                            <?php
                            $thumbType = (string) ($item['type'] ?? 'image');
                            $thumbUrl = (string) ($item['url'] ?? '');
                            if ($thumbUrl === '') {
                                continue;
                            }
                            ?>
                            <button
                                type="button"
                                class="detail-thumb relative aspect-square overflow-hidden rounded-lg border <?= $index === 0 ? 'border-gold/70' : 'border-zinc-700'; ?>"
                                data-target-index="<?= e((string) $index); ?>"
                            >
                                <?php if ($thumbType === 'video'): ?>
                                    <video src="<?= e($thumbUrl); ?>" muted playsinline class="h-full w-full object-cover"></video>
                                <?php else: ?>
                                    <img src="<?= e($thumbUrl); ?>" alt="<?= e(t('common.thumbnail_alt', 'Kucuk onizleme')); ?>" class="h-full w-full object-cover" loading="lazy" />
                                <?php endif; ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="lg:col-span-5">
            <div class="luxury-panel rounded-2xl p-8">
                <p class="text-xs uppercase tracking-[0.36em] text-gold/70"><?= e(t('pages.product_detail_label', 'Product')); ?></p>
                <h1 class="mt-4 font-display text-4xl text-zinc-100 sm:text-5xl"><?= e($productName); ?></h1>
                <p class="mt-5 text-sm uppercase tracking-[0.2em] text-gold"><?= e($productPrice); ?></p>
                <?php if ($productDetails !== ''): ?>
                    <p class="mt-5 text-sm leading-7 text-zinc-400"><?= nl2br(e($productDetails)); ?></p>
                <?php else: ?>
                    <p class="mt-5 text-sm leading-7 text-zinc-400">
                        <?= e(t('pages.product_detail_text', 'This product supports multiple photos and videos so customers can inspect every detail before purchase.')); ?>
                    </p>
                <?php endif; ?>
                <p class="mt-6 text-xs uppercase tracking-[0.2em] text-gold/80">
                    <?= e((string) max(1, count($productMedia))); ?> <?= e(t('common.media_items', 'Media Items')); ?>
                </p>

                <?php if ($whatsappLink !== ''): ?>
                    <a
                        href="<?= e($whatsappLink); ?>"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="gold-button mt-8 inline-flex rounded-full px-6 py-3 text-xs uppercase tracking-[0.18em]"
                    >
                        <?= e(t('common.buy_now', 'Buy Now')); ?>
                    </a>
                <?php else: ?>
                    <p class="mt-8 text-xs uppercase tracking-[0.16em] text-zinc-500"><?= e(t('shop.whatsapp_missing', 'WhatsApp number is not configured yet.')); ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
