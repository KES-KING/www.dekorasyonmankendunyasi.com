<section class="hero-gradient relative flex min-h-screen items-center justify-center overflow-hidden py-32">
    <div class="relative z-10 mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 text-center">
        <p class="mb-4 text-xs font-bold uppercase tracking-[0.2em] text-yellow-600 sm:text-sm">
            <?= e($settings['hero_kicker'] ?? 'Estetik | Dekorasyon | Tasarım'); ?>
        </p>

        <h1 class="mx-auto max-w-4xl font-display text-4xl leading-tight text-zinc-800 sm:text-6xl md:text-7xl">
            <?= e($settings['brand_name'] ?? 'Dekorasyon Manken Dünyası'); ?>
        </h1>

        <p class="mx-auto mt-6 max-w-2xl text-base text-zinc-600 sm:text-lg">
            <?= e($settings['hero_description'] ?? 'Mekanlarınızı canlandıran dekoratif mankenler ve estetik tasarımlar.'); ?>
        </p>

        <div class="mt-10 flex flex-col items-center justify-center gap-4 sm:flex-row">
            <a href="<?= e($designsUrl); ?>" class="gold-button inline-flex flex-1 flex-shrink-0 items-center justify-center whitespace-nowrap rounded-none px-8 py-4 text-xs uppercase tracking-[0.16em] sm:flex-none">
                Kataloğu İncele
            </a>
            <a href="<?= e($contactUrl); ?>" class="inline-flex flex-1 flex-shrink-0 items-center justify-center whitespace-nowrap rounded-none border border-zinc-300 bg-white px-8 py-4 text-xs uppercase tracking-[0.16em] text-zinc-600 transition hover:bg-zinc-50 hover:text-yellow-600 sm:flex-none">
                İletişime Geç
            </a>
        </div>
    </div>
</section>

<!-- Business Info Section -->
<section class="bg-white py-24 border-y border-zinc-100">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="font-display text-3xl text-zinc-800 sm:text-4xl">İletişim & Sosyal Medya</h2>
            <p class="mt-4 text-zinc-500">Bize her zaman ulaşabilirsiniz.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php
            $contacts = [
                ['icon' => 'M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z', 'label' => 'Telefon', 'value' => $settings['phone'] ?? '', 'link' => 'tel:' . ($settings['phone'] ?? '')],
                ['icon' => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z', 'label' => 'WhatsApp', 'value' => $settings['whatsapp_number'] ?? '', 'link' => 'https://wa.me/' . preg_replace('/[^0-9]/', '', $settings['whatsapp_number'] ?? '')],
                ['icon' => 'M16 8l2-3.5L21 8h-5z M3 16l2 3.5 3-3.5H3z M12 2l4 7H8l4-7z M12 22l-4-7h8l-4 7z', 'label' => 'Viber', 'value' => $settings['viber'] ?? '', 'link' => 'viber://chat?number=' . preg_replace('/[^0-9]/', '', $settings['viber'] ?? '')],
                ['icon' => 'M2 12l20-9-4 18-6-4-3 5v-6l10-8-12 9-5-2z', 'label' => 'Telegram', 'value' => $settings['telegram'] ?? '', 'link' => 'https://t.me/' . ($settings['telegram'] ?? '')],
                ['icon' => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z', 'label' => 'E-posta', 'value' => $settings['email_address'] ?? '', 'link' => 'mailto:' . ($settings['email_address'] ?? '')],
                ['icon' => 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z M15 11a3 3 0 11-6 0 3 3 0 016 0z', 'label' => 'Konum', 'value' => $settings['business_location'] ?? '', 'link' => '#'],
            ];
            foreach ($contacts as $contact):
                if (empty($contact['value'])) continue;
            ?>
            <a href="<?= e($contact['link']); ?>" class="contact-card group">
                <div class="contact-icon">
                    <svg width="20" height="20" fill="currentColor" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="<?= e($contact['icon']); ?>" /></svg>
                </div>
                <div>
                    <h3 class="text-xs uppercase tracking-wider text-zinc-500 mb-1"><?= e($contact['label']); ?></h3>
                    <p class="text-zinc-800 font-medium whitespace-pre-wrap"><?= e($contact['value']); ?></p>
                </div>
            </a>
            <?php endforeach; ?>
        </div>

        <div class="mt-12 flex flex-wrap justify-center gap-6">
            <?php
            $socials = [
                ['name' => 'Instagram', 'url' => $settings['instagram_url'] ?? '', 'color' => 'hover:text-pink-600'],
                ['name' => 'Facebook', 'url' => $settings['facebook_url'] ?? '', 'color' => 'hover:text-blue-600'],
                ['name' => 'YouTube', 'url' => $settings['youtube_url'] ?? '', 'color' => 'hover:text-red-600'],
                ['name' => 'TikTok', 'url' => $settings['tiktok_url'] ?? '', 'color' => 'hover:text-black'],
            ];
            foreach ($socials as $social):
                if (empty($social['url'])) continue;
            ?>
            <a href="<?= e($social['url']); ?>" target="_blank" rel="noopener noreferrer" class="px-6 py-3 rounded-full border border-zinc-200 text-sm font-medium text-zinc-600 transition <?= $social['color']; ?> hover:border-currentColor hover:bg-zinc-50">
                <?= e($social['name']); ?>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Banks Info Section -->
<section class="bg-[#faf9f6]/50 py-24">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="font-display text-3xl text-zinc-800 sm:text-4xl">Banka Hesaplarımız</h2>
            <p class="mt-4 text-zinc-500">Ödemelerinizi güvenle aşağıdaki hesaplarımıza yapabilirsiniz.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php for($i=1; $i<=4; $i++): $bankKey = "bank_account_$i"; if(empty($settings[$bankKey])) continue; ?>
            <div class="bank-card text-center">
                <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-yellow-50 text-yellow-600">
                    <svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>
                </div>
                <h3 class="bank-card-title">Banka <?= $i; ?></h3>
                <p class="bank-card-value text-sm sm:text-base"><?= e($settings[$bankKey]); ?></p>
            </div>
            <?php endfor; ?>
        </div>
    </div>
</section>
