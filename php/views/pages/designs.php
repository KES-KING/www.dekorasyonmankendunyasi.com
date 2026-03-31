<section class="hero-gradient relative flex min-h-[40vh] items-end pb-16 pt-32">
    <div class="relative z-10 mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between text-center sm:text-left">
            <div>
                <p class="text-xs uppercase tracking-[0.4em] font-bold text-yellow-600"><?= e(t('pages.designs_kicker', 'Katalog')); ?></p>
                <h1 class="mt-4 font-display text-4xl text-zinc-800 sm:text-5xl md:text-6xl"><?= e(t('pages.designs_title', 'Tasarım Galerisi')); ?></h1>
            </div>
            <p class="mt-4 max-w-xl text-sm leading-relaxed text-zinc-500 sm:mt-0">
                <?= e(t('pages.designs_intro', 'En özel ürünlerimizi aşağıdan inceleyebilirsiniz. Ürünlere ait tüm detayları ve kullanım alanlarını görebilirsiniz.')); ?>
            </p>
        </div>
    </div>
</section>

<section class="bg-white py-24 border-t border-zinc-100">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        
        <div class="flex flex-wrap justify-center gap-4 mb-16">
            <?php $activeFilter = $currentFilter ?? 'all'; ?>
            <a href="?filter=all" class="px-6 py-2.5 rounded-full text-sm font-medium transition-all shadow-sm border <?= $activeFilter === 'all' ? 'bg-zinc-900 border-zinc-900 text-white' : 'bg-white border-zinc-200 text-zinc-600 hover:border-zinc-300 hover:bg-zinc-50' ?>">Tümü</a>
            <a href="?filter=image" class="px-6 py-2.5 rounded-full text-sm font-medium transition-all shadow-sm border <?= $activeFilter === 'image' ? 'bg-zinc-900 border-zinc-900 text-white' : 'bg-white border-zinc-200 text-zinc-600 hover:border-zinc-300 hover:bg-zinc-50' ?>">Resimler</a>
            <a href="?filter=video" class="px-6 py-2.5 rounded-full text-sm font-medium transition-all shadow-sm border <?= $activeFilter === 'video' ? 'bg-zinc-900 border-zinc-900 text-white' : 'bg-white border-zinc-200 text-zinc-600 hover:border-zinc-300 hover:bg-zinc-50' ?>">Videolar</a>
        </div>

        <div class="columns-1 sm:columns-2 lg:columns-3 xl:columns-4 gap-6 space-y-6">
            
            <?php if(!empty($designs)): ?>
                <?php foreach($designs as $design): ?>
                    <?php if(!empty($design['img_url'])): ?>
                    <div class="luxury-panel break-inside-avoid relative overflow-hidden flex items-center justify-center p-2 bg-zinc-50 rounded-lg shadow-sm group">
                        <img src="<?= e((string) $design['img_url']); ?>" alt="<?= e(t('gallery.image_alt', 'Tasarım')); ?>" loading="lazy" class="w-full h-auto object-cover rounded-md transition-transform duration-700 ease-out group-hover:scale-105" />
                    </div>
                    <?php endif; ?>
                    <?php if(!empty($design['video_url'])): ?>
                    <div class="luxury-panel break-inside-avoid relative overflow-hidden p-2 bg-zinc-50 rounded-lg shadow-sm">
                        <video src="<?= e((string) $design['video_url']); ?>" controls playsinline preload="metadata" class="w-full h-auto object-cover rounded-md"></video>
                    </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php else: ?>
            <div class="col-span-full py-20 text-center text-zinc-400 break-inside-avoid">
                <svg class="mx-auto mb-4 w-12 h-12 stroke-currentColor" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                <p><?= e(t('gallery.empty_media', 'Henüz galeri içeriği yüklenmedi.')); ?></p>
            </div>
            <?php endif; ?>

        </div>

        <?php if (!empty($totalPages) && $totalPages > 1): ?>
        <div class="mt-16 flex justify-center items-center gap-4">
            <?php if ($currentPage > 1): ?>
                <a href="?filter=<?= e($activeFilter); ?>&page=<?= $currentPage - 1 ?>" class="px-6 py-2 border border-zinc-200 rounded-md text-sm font-medium text-zinc-600 hover:bg-zinc-50 transition shadow-sm">
                    &larr; Önceki
                </a>
            <?php endif; ?>
            
            <span class="text-sm font-medium text-zinc-500 min-w-24 text-center">
                <?= e(t('common.page', 'Sayfa')); ?> <?= $currentPage ?> / <?= $totalPages ?>
            </span>
            
            <?php if ($currentPage < $totalPages): ?>
                <a href="?filter=<?= e($activeFilter); ?>&page=<?= $currentPage + 1 ?>" class="px-6 py-2 border border-zinc-200 rounded-md text-sm font-medium text-zinc-600 hover:bg-zinc-50 transition shadow-sm">
                    Sonraki &rarr;
                </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>

    </div>
</section>
