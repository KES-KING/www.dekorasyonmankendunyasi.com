<section class="hero-gradient relative flex min-h-[40vh] items-end pb-16 pt-32">
    <div class="relative z-10 mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between text-center sm:text-left">
            <div>
                <p class="text-xs uppercase tracking-[0.4em] font-bold text-yellow-600">İletişim</p>
                <h1 class="mt-4 font-display text-4xl text-zinc-800 sm:text-5xl md:text-6xl">Bize Ulaşın</h1>
            </div>
            <p class="mt-4 max-w-xl text-sm leading-relaxed text-zinc-500 sm:mt-0">
                Sipariş, detaylı bilgi ve her türlü sorunuz için iletişim formunu doldurarak bizimle irtibata geçebilirsiniz.
            </p>
        </div>
    </div>
</section>

<section class="bg-white py-24 border-t border-zinc-100">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
        
        <?php $flash = $_SESSION['contact_flash'] ?? null; unset($_SESSION['contact_flash']); ?>
        <?php if (is_array($flash)): ?>
            <div class="<?= ($flash['type'] ?? '') === 'error' ? 'bg-red-50 text-red-600 border-red-100' : 'bg-green-50 text-green-700 border-green-100'; ?> border px-6 py-4 rounded-lg mb-8 text-sm flex items-start gap-3">
                <svg width="20" height="20" fill="none" viewBox="0 0 24 24" class="flex-shrink-0 mt-0.5"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/><path d="<?= ($flash['type'] ?? '') === 'error' ? 'M12 8v4m0 4h.01' : 'M9 12l2 2 4-4'; ?>" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <?= e((string) ($flash['message'] ?? '')); ?>
            </div>
        <?php endif; ?>

        <form action="<?= e($contactUrl); ?>" method="POST" class="luxury-panel p-8 sm:p-12 shadow-sm rounded-2xl">
            <input type="hidden" name="contact_form_token" value="<?= e($data['contactFormToken'] ?? ''); ?>">
            
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-zinc-500 mb-2">Ad Soyad</label>
                    <input type="text" name="name" required class="contact-input w-full rounded-md px-4 py-3 text-sm transition" placeholder="Adınız Soyadınız">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-zinc-500 mb-2">Telefon</label>
                    <input type="tel" name="phone" required class="contact-input w-full rounded-md px-4 py-3 text-sm transition" placeholder="05XX XXX XX XX">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold uppercase tracking-wider text-zinc-500 mb-2">E-posta</label>
                    <input type="email" name="email" required class="contact-input w-full rounded-md px-4 py-3 text-sm transition" placeholder="ornek@email.com">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold uppercase tracking-wider text-zinc-500 mb-2">Konu</label>
                    <input type="text" name="subject" required class="contact-input w-full rounded-md px-4 py-3 text-sm transition" placeholder="Mesajınızın konusu">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold uppercase tracking-wider text-zinc-500 mb-2">Mesajınız</label>
                    <textarea name="message" required rows="5" class="contact-input w-full rounded-md px-4 py-3 text-sm transition resize-none" placeholder="Detaylı mesajınızı buraya yazabilirsiniz..."></textarea>
                </div>
                <div class="sm:col-span-2 mt-4 text-center">
                    <button type="submit" class="gold-button inline-flex items-center justify-center px-10 py-4 text-sm font-bold uppercase tracking-widest rounded-md w-full sm:w-auto">
                        Mesaj Gönder
                    </button>
                </div>
            </div>
        </form>
    </div>
</section>
