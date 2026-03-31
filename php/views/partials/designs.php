<?php
$designSectionId = $designSectionId ?? 'designs';
$designsKicker = $designsKicker ?? t('sections.designs_kicker', 'Section 01');
$designsHeading = $designsHeading ?? t('sections.designs_title', 'Designs Gallery');
$designsDescription = $designsDescription ?? t('sections.designs_description', 'Explore all signature designs and open each piece for full detail and media.');
$designsShowViewAll = isset($designsShowViewAll) ? (bool) $designsShowViewAll : false;
$designsViewAllUrl = $designsViewAllUrl ?? $designsUrl;
?>
<section id="<?= e($designSectionId); ?>" class="reveal-section relative mx-auto w-full max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
    <div class="mb-10 flex items-end justify-between gap-4 reveal-item">
        <div>
            <p class="text-xs uppercase tracking-[0.45em] text-gold/75"><?= e($designsKicker); ?></p>
            <h2 class="mt-2 font-display text-4xl text-gold sm:text-5xl"><?= e($designsHeading); ?></h2>
        </div>
        <div class="flex max-w-md flex-col items-end gap-3 text-right">
            <p class="text-sm text-zinc-400"><?= e($designsDescription); ?></p>
            <?php if ($designsShowViewAll): ?>
                <a href="<?= e($designsViewAllUrl); ?>" class="rounded-full border border-gold/60 px-5 py-2 text-[11px] uppercase tracking-[0.16em] text-gold transition hover:bg-gold hover:text-black">
                    <?= e(t('common.view_all_designs', 'View All Designs')); ?>
                </a>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($dbError !== null): ?>
        <div class="mb-8 rounded-xl border border-red-400/40 bg-red-900/20 px-4 py-3 text-sm text-red-200">
            <?= e(t('common.db_error', 'Database connection issue')); ?>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-3">
        <?php if (count($designs) === 0): ?>
            <div class="luxury-panel col-span-full rounded-2xl p-10 text-center reveal-item">
                <p class="text-lg text-zinc-200"><?= e(t('gallery.empty_title', 'No designs found yet.')); ?></p>
                <p class="mt-2 text-sm text-zinc-400"><?= e(t('gallery.empty_text', 'Insert rows into the designs table to render this gallery.')); ?></p>
            </div>
        <?php else: ?>
            <?php foreach ($designs as $designItem): ?>
                <?php
                $title = (string) ($designItem['title'] ?? t('gallery.default_title', 'Untitled Design'));
                $mediaCount = (int) ($designItem['media_count'] ?? 0);
                $detailPath = (string) ($designItem['detail_path'] ?? '/designs');
                $detailUrl = localizedPath(currentLocale(), $detailPath);
                $cover = is_array($designItem['cover_media'] ?? null) ? $designItem['cover_media'] : ['type' => 'image', 'url' => asset('images/hero.jpg')];
                $coverType = (string) ($cover['type'] ?? 'image');
                $coverUrl = (string) ($cover['url'] ?? asset('images/hero.jpg'));
                ?>
                <a
                    href="<?= e($detailUrl); ?>"
                    class="reveal-item luxury-panel group overflow-hidden rounded-2xl p-3 shadow-gold transition-transform duration-300 hover:-translate-y-1"
                >
                    <div class="relative aspect-[4/5] overflow-hidden rounded-xl">
                        <?php if ($coverType === 'video'): ?>
                            <video
                                src="<?= e($coverUrl); ?>"
                                class="h-full w-full object-cover transition duration-700 group-hover:scale-105"
                                muted
                                loop
                                autoplay
                                playsinline
                                preload="metadata"
                            ></video>
                        <?php else: ?>
                            <img
                                src="<?= e($coverUrl); ?>"
                                alt="<?= e($title); ?>"
                                class="h-full w-full object-cover transition duration-700 group-hover:scale-105"
                                loading="lazy"
                            />
                        <?php endif; ?>
                        <div class="absolute inset-0 bg-gradient-to-t from-black/75 via-black/25 to-transparent"></div>
                        <div class="absolute bottom-4 left-4 right-4 flex items-end justify-between gap-3">
                            <div>
                                <p class="font-display text-2xl leading-tight text-zinc-100"><?= e($title); ?></p>
                                <p class="mt-2 text-[11px] uppercase tracking-[0.18em] text-gold/85">
                                    <?= e((string) max(1, $mediaCount)); ?> <?= e(t('common.media_items', 'Media Items')); ?>
                                </p>
                            </div>
                            <span class="rounded-full border border-zinc-200/30 px-3 py-1 text-[10px] uppercase tracking-[0.16em] text-zinc-100/90">
                                <?= e(t('common.view_detail', 'View Detail')); ?>
                            </span>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>
