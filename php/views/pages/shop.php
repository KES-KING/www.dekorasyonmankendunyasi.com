<section class="mx-auto w-full max-w-7xl px-4 pb-6 pt-36 sm:px-6 lg:px-8">
    <div class="reveal-item flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-xs uppercase tracking-[0.42em] text-gold/75"><?= e(t('pages.shop_kicker', 'Store')); ?></p>
            <h1 class="mt-2 font-display text-4xl text-gold sm:text-6xl"><?= e(t('pages.shop_title', 'Shop Collection')); ?></h1>
        </div>
        <p class="max-w-2xl text-sm text-zinc-400"><?= e(t('pages.shop_description', 'Open any product to view complete detail with multiple photos and videos.')); ?></p>
    </div>
</section>

<?php
$shopSectionId = 'shop-list';
$shopKicker = t('sections.shop_kicker', 'Section 02');
$shopHeading = t('sections.shop_title', 'Shop Collection');
$shopDescription = t('sections.shop_description', 'Browse all products. Open any card for complete media detail.');
$shopShowViewAll = false;
require __DIR__ . '/../partials/shop.php';
?>
