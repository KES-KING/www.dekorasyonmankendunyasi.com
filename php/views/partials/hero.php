<section id="home" class="hero-section relative grain-overlay flex min-h-screen items-center justify-center overflow-hidden pt-20">
    <img
        src="<?= e($heroImage); ?>"
        alt="<?= e(t('hero.image_alt', 'Hero arka plan gorseli')); ?>"
        class="absolute inset-0 h-full w-full object-cover opacity-40"
    />
    <div class="hero-vignette absolute inset-0"></div>
    <div class="hero-aurora absolute inset-0"></div>

    <div class="hero-orb hero-orb--one"></div>
    <div class="hero-orb hero-orb--two"></div>
    <div class="hero-orb hero-orb--three"></div>

    <div class="relative z-10 mx-auto max-w-5xl px-6 text-center reveal-section">
        <img
            src="<?= e($brandLogo); ?>"
            alt="<?= e($brandName); ?>"
            class="hero-logo mx-auto reveal-item"
        />
        <p class="reveal-item mt-6 text-xs uppercase tracking-[0.5em] text-gold/80 sm:text-sm"><?= e($heroKicker); ?></p>
        <h1 class="logo-shimmer reveal-item mt-5 font-display text-4xl uppercase tracking-[0.1em] sm:text-6xl md:text-7xl lg:text-8xl">
            <?= e($brandName); ?>
        </h1>
        <p class="hero-copy reveal-item mx-auto mt-6 max-w-3xl text-sm text-zinc-100/90 sm:text-base md:text-lg">
            <?= e($heroDescription); ?>
        </p>
        <div class="hero-cta reveal-item mt-10 flex flex-col items-center justify-center gap-4 sm:flex-row">
            <a href="<?= e($shopUrl); ?>" class="gold-button rounded-full px-7 py-3 text-sm uppercase tracking-[0.18em]"><?= e($heroCtaShop); ?></a>
            <a href="<?= e($designsUrl); ?>" class="rounded-full border border-zinc-300/35 px-7 py-3 text-sm uppercase tracking-[0.18em] text-zinc-100 transition hover:border-gold/70 hover:text-gold"><?= e($heroCtaDesigns); ?></a>
        </div>
    </div>
</section>
