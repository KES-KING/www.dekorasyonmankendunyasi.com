<?php
$contactSectionId = $contactSectionId ?? 'contact';
$contactTitle = $contactTitle ?? t('contact.title', 'Contact');
$contactDescription = $contactDescription ?? t('contact.description', 'For collaborations, custom orders and business inquiries, send us a message.');
$contactReturnTo = $contactReturnTo ?? (localizedPath(currentLocale(), '/contact') . '#contact');
$contactAction = localizedPath(currentLocale(), '/contact');
$contactFlashType = is_array($contactFlash ?? null) ? (string) ($contactFlash['type'] ?? '') : '';
$contactFlashMessage = is_array($contactFlash ?? null) ? (string) ($contactFlash['message'] ?? '') : '';
$contactOld = is_array($contactFlash['old'] ?? null) ? $contactFlash['old'] : [];
?>
<section id="<?= e($contactSectionId); ?>" class="reveal-section mx-auto w-full max-w-7xl px-4 pb-24 pt-8 sm:px-6 lg:px-8">
    <div class="mb-10 reveal-item">
        <p class="text-xs uppercase tracking-[0.45em] text-gold/75"><?= e(t('contact.kicker', 'Section 03')); ?></p>
        <h2 class="mt-2 font-display text-4xl text-gold sm:text-5xl"><?= e($contactTitle); ?></h2>
        <p class="mt-4 max-w-2xl text-sm text-zinc-400"><?= e($contactDescription); ?></p>
    </div>

    <div class="grid grid-cols-1 gap-8 lg:grid-cols-12">
        <div class="lg:col-span-8">
            <form method="post" action="<?= e($contactAction); ?>" class="luxury-panel reveal-item rounded-2xl p-6 sm:p-8">
                <input type="hidden" name="contact_form_token" value="<?= e($contactFormToken); ?>" />
                <input type="hidden" name="return_to" value="<?= e($contactReturnTo); ?>" />

                <?php if ($contactFlashMessage !== ''): ?>
                    <div class="mb-5 rounded-xl border px-4 py-3 text-sm <?= $contactFlashType === 'success' ? 'border-emerald-500/40 bg-emerald-900/20 text-emerald-200' : 'border-red-500/40 bg-red-900/20 text-red-200'; ?>">
                        <?= e($contactFlashMessage); ?>
                    </div>
                <?php endif; ?>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <label class="block text-sm text-zinc-300">
                        <span class="mb-1 block text-xs uppercase tracking-[0.16em] text-zinc-500"><?= e(t('contact.name', 'Name')); ?> *</span>
                        <input type="text" name="name" value="<?= e((string) ($contactOld['name'] ?? '')); ?>" required class="contact-input w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-zinc-100" />
                    </label>
                    <label class="block text-sm text-zinc-300">
                        <span class="mb-1 block text-xs uppercase tracking-[0.16em] text-zinc-500"><?= e(t('contact.email', 'Email')); ?> *</span>
                        <input type="email" name="email" value="<?= e((string) ($contactOld['email'] ?? '')); ?>" required class="contact-input w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-zinc-100" />
                    </label>
                    <label class="block text-sm text-zinc-300">
                        <span class="mb-1 block text-xs uppercase tracking-[0.16em] text-zinc-500"><?= e(t('contact.phone', 'Phone')); ?></span>
                        <input type="text" name="phone" value="<?= e((string) ($contactOld['phone'] ?? '')); ?>" class="contact-input w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-zinc-100" />
                    </label>
                    <label class="block text-sm text-zinc-300">
                        <span class="mb-1 block text-xs uppercase tracking-[0.16em] text-zinc-500"><?= e(t('contact.subject', 'Subject')); ?> *</span>
                        <input type="text" name="subject" value="<?= e((string) ($contactOld['subject'] ?? '')); ?>" required class="contact-input w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-zinc-100" />
                    </label>
                    <label class="block text-sm text-zinc-300 sm:col-span-2">
                        <span class="mb-1 block text-xs uppercase tracking-[0.16em] text-zinc-500"><?= e(t('contact.message', 'Message')); ?> *</span>
                        <textarea name="message" rows="6" required class="contact-input w-full rounded-lg border border-zinc-700 bg-zinc-950 px-3 py-2 text-sm text-zinc-100"><?= e((string) ($contactOld['message'] ?? '')); ?></textarea>
                    </label>
                </div>

                <div class="mt-6">
                    <button class="gold-button rounded-full px-7 py-3 text-xs uppercase tracking-[0.18em]">
                        <?= e(t('contact.submit', 'Send Message')); ?>
                    </button>
                </div>
            </form>
        </div>

        <aside class="lg:col-span-4">
            <div class="luxury-panel reveal-item rounded-2xl p-6 sm:p-8">
                <p class="text-xs uppercase tracking-[0.2em] text-gold/80"><?= e(t('contact.info_title', 'Direct')); ?></p>
                <p class="mt-4 text-sm leading-7 text-zinc-400">
                    <?= e(t('contact.info_text', 'We usually answer within 24-48 hours. Please include your project details for a faster response.')); ?>
                </p>
                <ul class="mt-5 space-y-3 text-sm text-zinc-300">
                    <li>Email: <a href="mailto:info@newyorksocietycc.com" class="text-gold hover:underline">info@newyorksocietycc.com</a></li>
                    <li>Email: <a href="mailto:sales@newyorksocietycc.com" class="text-gold hover:underline">sales@newyorksocietycc.com</a></li>
                    <li>Instagram: <a href="https://instagram.com/" target="_blank" rel="noopener noreferrer" class="text-gold hover:underline">@newyorksocietycc</a></li>
                </ul>
            </div>
        </aside>
    </div>
</section>
