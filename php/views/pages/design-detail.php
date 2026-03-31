<?php
$designTitle = (string) ($design['title'] ?? t('pages.design_detail_title', 'Design Detail'));
$designMedia = is_array($design['media'] ?? null) ? $design['media'] : [];
$designDetails = trim((string) ($design['details'] ?? ''));
$designId = (int) ($design['id'] ?? 0);

$designPath = $designId > 0
    ? localizedPath(currentLocale(), '/designs/' . $designId)
    : localizedPath(currentLocale(), '/designs');

$requestScheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (string) ($_SERVER['SERVER_PORT'] ?? '') === '443'
    ? 'https'
    : 'http';
$requestHost = trim((string) ($_SERVER['HTTP_HOST'] ?? ''));
$designAbsoluteUrl = $requestHost !== ''
    ? $requestScheme . '://' . $requestHost . $designPath
    : $designPath;

$whatsappRawNumber = (string) ($whatsappNumber ?? '');
$whatsappDigits = (string) preg_replace('/\D+/', '', $whatsappRawNumber);
$whatsappLink = '';

if ($whatsappDigits !== '') {
    $whatsappMessageLines = [
        t('design.whatsapp_message_intro', 'Merhaba, bu tasarım hakkında bilgi almak istiyorum.'),
        t('design.whatsapp_message_link', 'Tasarım linki') . ': ' . $designAbsoluteUrl,
    ];
    $whatsappLink = 'https://wa.me/' . $whatsappDigits . '?text=' . rawurlencode(implode("\n", $whatsappMessageLines));
}

if ($designMedia === []) {
    $designMedia = [
        ['type' => 'image', 'url' => asset('images/hero.jpg')],
    ];
}
?>
<section class="bg-white border-t border-zinc-100 pb-24 pt-36">
    <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
        <a href="<?= e($designsUrl); ?>" class="inline-flex items-center rounded-full border border-zinc-300 px-4 py-2 text-xs uppercase tracking-[0.16em] text-zinc-600 transition hover:border-gold/70 hover:text-gold">
            <?= e(t('common.back_to_gallery', 'Back To Gallery')); ?>
        </a>

        <div class="mt-8 grid grid-cols-1 gap-8 lg:grid-cols-12">
            <div class="lg:col-span-7">
                <div class="luxury-panel detail-gallery overflow-hidden rounded-2xl p-4">
                    <div class="relative aspect-[4/5] overflow-hidden rounded-xl bg-zinc-100">
                        <?php foreach ($designMedia as $index => $item): ?>
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
                                    alt="<?= e($designTitle); ?>"
                                    class="detail-media-item absolute inset-0 h-full w-full object-cover <?= $index === 0 ? 'opacity-100' : 'pointer-events-none opacity-0'; ?>"
                                    data-media-index="<?= e((string) $index); ?>"
                                />
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>

                    <?php if (count($designMedia) > 1): ?>
                        <div class="mt-4 grid grid-cols-4 gap-3 sm:grid-cols-6 lg:grid-cols-5">
                            <?php foreach ($designMedia as $index => $item): ?>
                                <?php
                                $thumbType = (string) ($item['type'] ?? 'image');
                                $thumbUrl = (string) ($item['url'] ?? '');
                                if ($thumbUrl === '') {
                                    continue;
                                }
                                ?>
                                <button
                                    type="button"
                                    class="detail-thumb relative aspect-square overflow-hidden rounded-lg border <?= $index === 0 ? 'border-gold/70' : 'border-zinc-300'; ?>"
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
                    <p class="text-xs uppercase tracking-[0.36em] text-amber-700"><?= e(t('pages.design_detail_label', 'Design')); ?></p>
                    <h1 class="mt-4 font-display text-4xl text-zinc-900 sm:text-5xl"><?= e($designTitle); ?></h1>
                    <?php if ($designDetails !== ''): ?>
                        <p class="mt-5 text-sm leading-7 text-zinc-700"><?= nl2br(e($designDetails)); ?></p>
                    <?php else: ?>
                        <p class="mt-5 text-sm leading-7 text-zinc-700">
                            <?= e(t('pages.design_detail_text', 'This design includes a curated sequence of photos and videos. Use the media selector to move through every visual angle.')); ?>
                        </p>
                    <?php endif; ?>
                    <p class="mt-6 text-xs uppercase tracking-[0.2em] text-amber-700">
                        <?= e((string) max(1, count($designMedia))); ?> <?= e(t('common.media_items', 'Media Items')); ?>
                    </p>
                    <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                        <?php if ($whatsappLink !== ''): ?>
                            <a
                                href="<?= e($whatsappLink); ?>"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="gold-button inline-flex items-center justify-center rounded-full px-6 py-3 text-xs uppercase tracking-[0.16em]"
                            >
                                <?= e(t('design.whatsapp_button', 'WhatsApp\'a Git')); ?>
                            </a>
                        <?php else: ?>
                            <p class="text-xs uppercase tracking-[0.16em] text-zinc-500"><?= e(t('shop.whatsapp_missing', 'WhatsApp number is not configured yet.')); ?></p>
                        <?php endif; ?>

                        <button
                            type="button"
                            class="js-copy-design-link inline-flex items-center justify-center rounded-full border border-zinc-300 px-6 py-3 text-xs uppercase tracking-[0.16em] text-zinc-700 transition hover:border-gold/70 hover:text-gold"
                            data-copy-link="<?= e($designAbsoluteUrl); ?>"
                            data-default-label="<?= e(t('design.copy_link_button', 'Linki Kopyala')); ?>"
                            data-copied-label="<?= e(t('design.copy_link_done', 'Kopyalandı')); ?>"
                            data-error-label="<?= e(t('design.copy_link_error', 'Kopyalanamadı')); ?>"
                        >
                            <?= e(t('design.copy_link_button', 'Linki Kopyala')); ?>
                        </button>
                    </div>
                    <p class="mt-4 text-xs text-zinc-500 break-all"><?= e($designAbsoluteUrl); ?></p>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    (function () {
        const copyButton = document.querySelector('.js-copy-design-link');
        if (!copyButton) {
            return;
        }

        const defaultLabel = copyButton.getAttribute('data-default-label') || 'Linki Kopyala';
        const copiedLabel = copyButton.getAttribute('data-copied-label') || 'Kopyalandı';
        const errorLabel = copyButton.getAttribute('data-error-label') || 'Kopyalanamadı';
        const copyLink = copyButton.getAttribute('data-copy-link') || '';
        let resetTimeoutId = null;

        copyButton.addEventListener('click', async () => {
            if (copyLink === '') {
                return;
            }

            let label = copiedLabel;
            try {
                await navigator.clipboard.writeText(copyLink);
            } catch (error) {
                label = errorLabel;
            }

            copyButton.textContent = label;
            copyButton.classList.add('animate-pulse');

            if (resetTimeoutId !== null) {
                window.clearTimeout(resetTimeoutId);
            }

            resetTimeoutId = window.setTimeout(() => {
                copyButton.textContent = defaultLabel;
                copyButton.classList.remove('animate-pulse');
            }, 1600);
        });
    })();
</script>
