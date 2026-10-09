<?php $languageSelectId = 'language-select-' . mt_rand(); ?>
<form class="language-switcher" action="index.php" method="get">
    <input type="hidden" name="p" value="<?= htmlspecialchars((string)($page ?? 'welcome'), ENT_QUOTES, 'UTF-8') ?>">
    <?php if (isset($_GET['id_media'])): ?>
    <input type="hidden" name="id_media" value="<?= htmlspecialchars((string)$_GET['id_media'], ENT_QUOTES, 'UTF-8') ?>">
    <?php endif; ?>
    <label class="sr-only" for="<?= $languageSelectId ?>"><?= t('Choisir la langue') ?></label>
    <select id="<?= $languageSelectId ?>" name="lang" aria-label="<?= t('Choisir la langue') ?>" onchange="this.form.submit()">
        <?php foreach ($languageOptions as $code => $label): ?>
        <option value="<?= htmlspecialchars($code, ENT_QUOTES, 'UTF-8') ?>" <?= $currentLanguage === $code ? 'selected' : '' ?>><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></option>
        <?php endforeach; ?>
    </select>
</form>