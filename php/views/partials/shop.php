<?php
$shopSectionId = $shopSectionId ?? 'shop';
$shopKicker = $shopKicker ?? t('sections.shop_kicker', 'Section 02');
$shopHeading = $shopHeading ?? t('sections.shop_title', 'Shop Collection');
$shopDescription = $shopDescription ?? t('sections.shop_description', 'Discover products with dedicated detail pages and full media sets.');
$shopShowViewAll = isset($shopShowViewAll) ? (bool) $shopShowViewAll : false;
$shopViewAllUrl = $shopViewAllUrl ?? $shopUrl;
?>
<section id="<?= e($shopSectionId); ?>" class="reveal-section mx-auto w-full max-w-7xl px-4 pb-24 pt-8 sm:px-6 lg:px-8">
    <div class="mb-10 flex items-end justify-between gap-4 reveal-item">
        <div>
            <p class="text-xs uppercase tracking-[0.45em] text-gold/75"><?= e($shopKicker); ?></p>
            <h2 class="mt-2 font-display text-4xl text-gold sm:text-5xl"><?= e($shopHeading); ?></h2>
        </div>
        <div class="flex max-w-md flex-col items-end gap-3 text-right">
            <p class="text-sm text-zinc-400"><?= e($shopDescription); ?></p>
            <?php if ($shopShowViewAll): ?>
                <a href="<?= e($shopViewAllUrl); ?>" class="rounded-full border border-gold/60 px-5 py-2 text-[11px] uppercase tracking-[0.16em] text-gold transition hover:bg-gold hover:text-black">
                    <?= e(t('common.view_all_products', 'View All Products')); ?>
                </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        <?php if (count($products) === 0): ?>
            <div class="luxury-panel col-span-full rounded-2xl p-10 text-center reveal-item">
                <p class="text-lg text-zinc-200"><?= e(t('shop.empty_title', 'No products available yet.')); ?></p>
                <p class="mt-2 text-sm text-zinc-400"><?= e(t('shop.empty_text', 'Insert rows into the products table to populate your store.')); ?></p>
            </div>
        <?php else: ?>
            <?php foreach ($products as $productItem): ?>
                <?php
                $productName = (string) ($productItem['name'] ?? t('shop.default_name', 'Unnamed Product'));
                $productPrice = formatPrice($productItem['price'] ?? 0);
                $mediaCount = (int) ($productItem['media_count'] ?? 0);
                $detailPath = (string) ($productItem['detail_path'] ?? '/shop');
                $detailUrl = localizedPath(currentLocale(), $detailPath);
                $cover = is_array($productItem['cover_media'] ?? null) ? $productItem['cover_media'] : ['type' => 'image', 'url' => asset('images/hero.jpg')];
                $coverType = (string) ($cover['type'] ?? 'image');
                $coverUrl = (string) ($cover['url'] ?? asset('images/hero.jpg'));
                ?>
                <a
                    href="<?= e($detailUrl); ?>"
                    class="reveal-item luxury-panel group overflow-hidden rounded-2xl p-3 shadow-gold transition-transform duration-300 hover:-translate-y-1"
                >
                    <div class="relative aspect-[3/4] overflow-hidden rounded-xl">
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
                                alt="<?= e($productName); ?>"
                                class="h-full w-full object-cover transition duration-700 group-hover:scale-105"
                                loading="lazy"
                            />
                        <?php endif; ?>
                    </div>
                    <div class="px-1 pb-2 pt-4">
                        <h3 class="font-display text-2xl leading-tight text-zinc-100"><?= e($productName); ?></h3>
                        <p class="mt-2 text-sm uppercase tracking-[0.18em] text-gold"><?= e($productPrice); ?></p>
                        <p class="mt-2 text-[11px] uppercase tracking-[0.15em] text-zinc-400">
                            <?= e((string) max(1, $mediaCount)); ?> <?= e(t('common.media_items', 'Media Items')); ?>
                        </p>
                    </div>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>
