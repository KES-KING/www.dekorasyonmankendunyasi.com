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
            $normalizeExternalUrl = static function (string $url): string {
                $value = trim($url);
                if ($value === '') {
                    return '';
                }

                if (preg_match('#^(?:https?:)?//#i', $value) === 1) {
                    return str_starts_with($value, '//') ? 'https:' . $value : $value;
                }

                return 'https://' . ltrim($value, '/');
            };

            $phone = trim((string) ($settings['phone'] ?? ''));
            $whatsappRaw = trim((string) ($settings['whatsapp_number'] ?? ''));
            $whatsappDigits = (string) preg_replace('/\D+/', '', $whatsappRaw);
            $viberRaw = trim((string) ($settings['viber'] ?? ''));
            $viberDigits = (string) preg_replace('/\D+/', '', $viberRaw);
            $telegramRaw = trim((string) ($settings['telegram'] ?? ''));
            $telegramLink = '';
            if ($telegramRaw !== '') {
                if (preg_match('#^https?://#i', $telegramRaw) === 1) {
                    $telegramLink = $telegramRaw;
                } else {
                    $telegramHandle = ltrim($telegramRaw, '@/');
                    if ($telegramHandle !== '') {
                        $telegramLink = 'https://t.me/' . rawurlencode($telegramHandle);
                    }
                }
            }

            $emailAddress = trim((string) ($settings['email_address'] ?? ''));
            $businessLocation = trim((string) ($settings['business_location'] ?? ''));
            $locationMapLink = '';
            $locationEmbedUrl = '';
            if ($businessLocation !== '') {
                $mapQuery = rawurlencode($businessLocation);
                $locationMapLink = 'https://www.google.com/maps/search/?api=1&query=' . $mapQuery;
                $locationEmbedUrl = 'https://www.google.com/maps?q=' . $mapQuery . '&output=embed';
            }

            $contacts = [
                ['icon' => 'M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z', 'label' => 'Telefon', 'value' => $phone, 'link' => $phone === '' ? '' : 'tel:' . $phone, 'new_tab' => false],
                ['icon' => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z', 'label' => 'WhatsApp', 'value' => $whatsappRaw, 'link' => $whatsappDigits === '' ? '' : 'https://wa.me/' . $whatsappDigits, 'new_tab' => true],
                ['icon' => 'M16 8l2-3.5L21 8h-5z M3 16l2 3.5 3-3.5H3z M12 2l4 7H8l4-7z M12 22l-4-7h8l-4 7z', 'label' => 'Viber', 'value' => $viberRaw, 'link' => $viberDigits === '' ? '' : 'viber://chat?number=' . $viberDigits, 'new_tab' => true],
                ['icon' => 'M2 12l20-9-4 18-6-4-3 5v-6l10-8-12 9-5-2z', 'label' => 'Telegram', 'value' => $telegramRaw, 'link' => $telegramLink, 'new_tab' => true],
                ['icon' => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z', 'label' => 'E-posta', 'value' => $emailAddress, 'link' => $emailAddress === '' ? '' : 'mailto:' . $emailAddress, 'new_tab' => false],
                ['icon' => 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z M15 11a3 3 0 11-6 0 3 3 0 016 0z', 'label' => 'Konum', 'value' => $businessLocation, 'link' => $locationMapLink, 'new_tab' => true],
            ];
            foreach ($contacts as $contact):
                if (empty($contact['value']) || empty($contact['link'])) continue;
            ?>
            <a
                href="<?= e((string) $contact['link']); ?>"
                class="contact-card group"
                <?= !empty($contact['new_tab']) ? 'target="_blank" rel="noopener noreferrer"' : ''; ?>
            >
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

        <?php if ($locationEmbedUrl !== ''): ?>
            <div class="mt-10 overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm">
                <div class="border-b border-zinc-100 bg-zinc-50/70 px-6 py-5">
                    <p class="text-xs uppercase tracking-[0.18em] text-zinc-500">Konum</p>
                    <p class="mt-2 text-sm font-medium text-zinc-700"><?= e($businessLocation); ?></p>
                    <a href="<?= e($locationMapLink); ?>" target="_blank" rel="noopener noreferrer" class="mt-3 inline-flex text-xs font-semibold uppercase tracking-[0.14em] text-yellow-700 transition hover:text-yellow-600">
                        Haritada Aç
                    </a>
                </div>
                <div class="h-[320px] w-full sm:h-[360px]">
                    <iframe
                        src="<?= e($locationEmbedUrl); ?>"
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                        class="h-full w-full border-0"
                        title="İşletme Konumu"
                    ></iframe>
                </div>
            </div>
        <?php endif; ?>

        <div class="mt-12 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <?php
            $socials = [
                [
                    'name' => 'Instagram',
                    'url' => $normalizeExternalUrl((string) ($settings['instagram_url'] ?? '')),
                    'icon' => 'M7.5 2h9A5.5 5.5 0 0 1 22 7.5v9a5.5 5.5 0 0 1-5.5 5.5h-9A5.5 5.5 0 0 1 2 16.5v-9A5.5 5.5 0 0 1 7.5 2Zm0 1.8A3.7 3.7 0 0 0 3.8 7.5v9A3.7 3.7 0 0 0 7.5 20.2h9a3.7 3.7 0 0 0 3.7-3.7v-9a3.7 3.7 0 0 0-3.7-3.7Zm4.5 3.2a4.5 4.5 0 1 1 0 9 4.5 4.5 0 0 1 0-9Zm0 1.8a2.7 2.7 0 1 0 0 5.4 2.7 2.7 0 0 0 0-5.4Zm5.1-2.4a1.1 1.1 0 1 1 0 2.2 1.1 1.1 0 0 1 0-2.2Z',
                    'cardClass' => 'hover:border-pink-300 hover:bg-pink-50/60',
                    'iconClass' => 'group-hover:bg-pink-100 group-hover:text-pink-600',
                ],
                [
                    'name' => 'Facebook',
                    'url' => $normalizeExternalUrl((string) ($settings['facebook_url'] ?? '')),
                    'icon' => 'M14 22v-8h2.6l.4-3.2H14V8.9c0-.9.3-1.6 1.6-1.6h1.7V4.5c-.3 0-1.3-.1-2.5-.1-2.5 0-4.2 1.5-4.2 4.2v2.3H8V14h2.6v8H14Z',
                    'cardClass' => 'hover:border-blue-300 hover:bg-blue-50/70',
                    'iconClass' => 'group-hover:bg-blue-100 group-hover:text-blue-600',
                ],
                [
                    'name' => 'YouTube',
                    'url' => $normalizeExternalUrl((string) ($settings['youtube_url'] ?? '')),
                    'icon' => 'M23.5 7.2a3.1 3.1 0 0 0-2.2-2.2C19.4 4.5 12 4.5 12 4.5s-7.4 0-9.3.5A3.1 3.1 0 0 0 .5 7.2 32 32 0 0 0 0 12a32 32 0 0 0 .5 4.8 3.1 3.1 0 0 0 2.2 2.2c1.9.5 9.3.5 9.3.5s7.4 0 9.3-.5a3.1 3.1 0 0 0 2.2-2.2A32 32 0 0 0 24 12a32 32 0 0 0-.5-4.8ZM9.6 15.5v-7l6.1 3.5-6.1 3.5Z',
                    'cardClass' => 'hover:border-red-300 hover:bg-red-50/70',
                    'iconClass' => 'group-hover:bg-red-100 group-hover:text-red-600',
                ],
                [
                    'name' => 'TikTok',
                    'url' => $normalizeExternalUrl((string) ($settings['tiktok_url'] ?? '')),
                    'icon' => 'M14.5 3c.2 1.8 1.2 3.2 2.9 3.8.8.3 1.7.4 2.6.3v2.7a8.8 8.8 0 0 1-2.8-.5v5.4a5.9 5.9 0 1 1-5.1-5.8v2.8a3.1 3.1 0 1 0 2.4 3V3h0Z',
                    'cardClass' => 'hover:border-zinc-400 hover:bg-zinc-100',
                    'iconClass' => 'group-hover:bg-zinc-900 group-hover:text-white',
                ],
            ];
            $hasSocial = false;
            foreach ($socials as $social):
                if (empty($social['url'])) continue;
                $hasSocial = true;
            ?>
            <a
                href="<?= e((string) $social['url']); ?>"
                target="_blank"
                rel="noopener noreferrer"
                class="group flex items-center gap-3 rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-zinc-700 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md <?= $social['cardClass']; ?>"
                aria-label="<?= e($social['name']); ?>"
            >
                <span class="flex h-10 w-10 items-center justify-center rounded-full bg-zinc-100 text-zinc-600 transition <?= $social['iconClass']; ?>">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="<?= e($social['icon']); ?>"></path>
                    </svg>
                </span>
                <span class="text-sm font-semibold"><?= e($social['name']); ?></span>
            </a>
            <?php endforeach; ?>
            <?php if (!$hasSocial): ?>
                <p class="col-span-full text-center text-sm text-zinc-500">Henüz sosyal medya bağlantısı eklenmemiş.</p>
            <?php endif; ?>
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
            <?php
            $bankNames = [
                1 => 'Finans Bankası',
                2 => 'Garanti Bankası',
                3 => 'Akbank',
                4 => 'Denizbank',
            ];
            for($i=1; $i<=4; $i++):
                $bankKey = "bank_account_$i";
                if(empty($settings[$bankKey])) continue;
                $bankName = $bankNames[$i] ?? ('Banka ' . $i);
            ?>
            <div class="bank-card text-center">
                <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-yellow-50 text-yellow-600">
                    <svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>
                </div>
                <h3 class="bank-card-title"><?= e($bankName); ?></h3>
                <p class="bank-card-value text-sm sm:text-base"><?= e($settings[$bankKey]); ?></p>
            </div>
            <?php endfor; ?>
        </div>
    </div>
</section>
