<?php
$designTitle = (string) ($design['title'] ?? t('pages.design_detail_title', 'Design Detail'));
$designMedia = is_array($design['media'] ?? null) ? $design['media'] : [];
$designDetails = trim((string) ($design['details'] ?? ''));
if ($designMedia === []) {
    $designMedia = [
        ['type' => 'image', 'url' => asset('images/hero.jpg')],
    ];
}
?>
<section class="mx-auto w-full max-w-7xl px-4 pb-24 pt-36 sm:px-6 lg:px-8">
    <a href="<?= e($designsUrl); ?>" class="inline-flex items-center rounded-full border border-zinc-700 px-4 py-2 text-xs uppercase tracking-[0.16em] text-zinc-300 transition hover:border-gold/70 hover:text-gold">
        <?= e(t('common.back_to_gallery', 'Back To Gallery')); ?>
    </a>

    <div class="mt-8 grid grid-cols-1 gap-8 lg:grid-cols-12">
        <div class="lg:col-span-7">
            <div class="luxury-panel detail-gallery overflow-hidden rounded-2xl p-4">
                <div class="relative aspect-[4/5] overflow-hidden rounded-xl bg-zinc-950">
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
                <p class="text-xs uppercase tracking-[0.36em] text-gold/70"><?= e(t('pages.design_detail_label', 'Design')); ?></p>
                <h1 class="mt-4 font-display text-4xl text-zinc-100 sm:text-5xl"><?= e($designTitle); ?></h1>
                <?php if ($designDetails !== ''): ?>
                    <p class="mt-5 text-sm leading-7 text-zinc-400"><?= nl2br(e($designDetails)); ?></p>
                <?php else: ?>
                    <p class="mt-5 text-sm leading-7 text-zinc-400">
                        <?= e(t('pages.design_detail_text', 'This design includes a curated sequence of photos and videos. Use the media selector to move through every visual angle.')); ?>
                    </p>
                <?php endif; ?>
                <p class="mt-6 text-xs uppercase tracking-[0.2em] text-gold/80">
                    <?= e((string) max(1, count($designMedia))); ?> <?= e(t('common.media_items', 'Media Items')); ?>
                </p>
            </div>
        </div>
    </div>
</section>
