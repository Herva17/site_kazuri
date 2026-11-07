<?php
/* =====================================================================
   STUDIO — Paramètres de la chaîne
   Fichier : ressources/views/pages/studio_settings.php
   ===================================================================== */

if (!class_exists('User')) {
    require_once dirname(__DIR__, 3) . '/Classes/UserClass.php';
}

$currentUser = User::current();
if (!$currentUser) {
    header('Location: index.php?p=account');
    exit;
}

/* ---------------------------------------------------------------------
   Helper d'accès robuste (objet User OU tableau OU session)
   --------------------------------------------------------------------- */
if (!function_exists('uVal')) {
    function uVal($user, array $methods, array $props = [], $default = '') {
        foreach ($methods as $m) {
            if (is_object($user) && method_exists($user, $m)) {
                $v = $user->$m();
                if ($v !== null && $v !== '') return $v;
            }
        }
        foreach ($props as $p) {
            if (is_object($user) && isset($user->$p) && $user->$p !== '') return $user->$p;
            if (is_array($user)  && isset($user[$p])  && $user[$p]  !== '') return $user[$p];
        }
        return $default;
    }
}

/* ---------------------------------------------------------------------
   Récupération des données utilisateur
   --------------------------------------------------------------------- */
$userId      = (int)    uVal($currentUser, ['id','getId','userId','getUserId'], ['id','user_id'], 0);
$firstName   = (string) uVal($currentUser, ['firstName','getFirstName'], ['first_name','firstName'], '');
$lastName    = (string) uVal($currentUser, ['lastName','getLastName'],   ['last_name','lastName'],  '');
$fullName    = (string) uVal($currentUser, ['fullName','getFullName'],   ['full_name','fullName'], trim($firstName . ' ' . $lastName));
$email       = (string) uVal($currentUser, ['email','getEmail'],         ['email'], '');

$channelName = (string) uVal($currentUser, ['channelName','getChannelName'], ['channel_name','channelName'], $fullName);
$description = (string) uVal($currentUser, ['bio','description','getBio','getDescription'], ['bio','description','channel_description'], '');
$country     = (string) uVal($currentUser, ['country','getCountry'], ['country'], '');
$avatarUrl   = (string) uVal($currentUser, ['avatar','getAvatar'],   ['avatar','avatar_url'], '');

/* Initiales pour l'avatar par défaut */
$initials = mb_strtoupper(
    mb_substr($firstName, 0, 1, 'UTF-8') . mb_substr($lastName, 0, 1, 'UTF-8'),
    'UTF-8'
);
if ($initials === '') {
    $initials = mb_strtoupper(mb_substr($fullName, 0, 2, 'UTF-8'), 'UTF-8');
}
if ($initials === '') $initials = 'KA';

/* ---------------------------------------------------------------------
   Traitement du formulaire
   --------------------------------------------------------------------- */
$flash = $_SESSION['studio_settings_flash'] ?? null;
unset($_SESSION['studio_settings_flash']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($pdo) && $userId > 0) {
    try {
        $newChannelName = trim($_POST['channel_name'] ?? '');
        $newDescription = trim($_POST['description']  ?? '');
        $newCountry     = trim($_POST['country']      ?? '');

        if ($newChannelName === '') {
            throw new Exception('Le nom de la chaîne est obligatoire.');
        }
        if (mb_strlen($newChannelName) > 120) {
            throw new Exception('Le nom de la chaîne est trop long (120 caractères max).');
        }
        if (mb_strlen($newDescription) > 500) {
            throw new Exception('La description est trop longue (500 caractères max).');
        }

        $st = $pdo->prepare("
            UPDATE users
            SET channel_name = :cn,
                description  = :d,
                country      = :c
            WHERE id = :id
        ");
        $st->execute([
            ':cn' => $newChannelName,
            ':d'  => $newDescription,
            ':c'  => $newCountry,
            ':id' => $userId,
        ]);

        /* --- Upload avatar (optionnel) --- */
        if (!empty($_FILES['avatar_file']['tmp_name'])
            && ($_FILES['avatar_file']['error'] ?? 1) === UPLOAD_ERR_OK) {

            $mime    = mime_content_type($_FILES['avatar_file']['tmp_name']);
            $allowed = [
                'image/jpeg' => 'jpg',
                'image/png'  => 'png',
                'image/webp' => 'webp',
                'image/gif'  => 'gif',
            ];

            if (!isset($allowed[$mime])) {
                throw new Exception('Format d\'image non supporté (jpg, png, webp, gif).');
            }
            if ($_FILES['avatar_file']['size'] > 3 * 1024 * 1024) {
                throw new Exception('Image trop lourde (3 Mo maximum).');
            }

            $ext      = $allowed[$mime];
            $filename = 'avatar_' . $userId . '_' . time() . '.' . $ext;

            // Dossier cible : public/uploads/avatars/
            $destDir = defined('ROOT')
                ? ROOT . '/public/uploads/avatars/'
                : dirname(__DIR__, 4) . '/public/uploads/avatars/';
            if (!is_dir($destDir)) @mkdir($destDir, 0755, true);

            if (!move_uploaded_file($_FILES['avatar_file']['tmp_name'], $destDir . $filename)) {
                throw new Exception('Impossible de sauvegarder l\'image.');
            }

            $avatarDbPath = 'uploads/avatars/' . $filename;
            $pdo->prepare("UPDATE users SET avatar = :a WHERE id = :id")
                ->execute([':a' => $avatarDbPath, ':id' => $userId]);

            $avatarUrl = $avatarDbPath;
        }

        $_SESSION['studio_settings_flash'] = [
            'type' => 'success',
            'msg'  => 'Modifications enregistrées avec succès.',
        ];
        header('Location: index.php?p=studio_settings');
        exit;

    } catch (Throwable $e) {
        $flash = ['type' => 'error', 'msg' => $e->getMessage()];
    }
}

/* ---------------------------------------------------------------------
   Liste des pays africains
   --------------------------------------------------------------------- */
$africanCountries = [
    'Afrique du Sud'                    => '🇿🇦 Afrique du Sud',
    'Algérie'                           => '🇩🇿 Algérie',
    'Angola'                            => '🇦🇴 Angola',
    'Bénin'                             => '🇧🇯 Bénin',
    'Botswana'                          => '🇧🇼 Botswana',
    'Burkina Faso'                      => '🇧🇫 Burkina Faso',
    'Burundi'                           => '🇧🇮 Burundi',
    'Cameroun'                          => '🇨🇲 Cameroun',
    'Cap-Vert'                          => '🇨🇻 Cap-Vert',
    'Comores'                           => '🇰🇲 Comores',
    "Côte d'Ivoire"                     => "🇨🇮 Côte d'Ivoire",
    'Djibouti'                          => '🇩🇯 Djibouti',
    'Égypte'                            => '🇪🇬 Égypte',
    'Érythrée'                          => '🇪🇷 Érythrée',
    'Eswatini'                          => '🇸🇿 Eswatini',
    'Éthiopie'                          => '🇪🇹 Éthiopie',
    'Gabon'                             => '🇬🇦 Gabon',
    'Gambie'                            => '🇬🇲 Gambie',
    'Ghana'                             => '🇬🇭 Ghana',
    'Guinée'                            => '🇬🇳 Guinée',
    'Guinée équatoriale'                => '🇬🇶 Guinée équatoriale',
    'Guinée-Bissau'                     => '🇬🇼 Guinée-Bissau',
    'Kenya'                             => '🇰🇪 Kenya',
    'Lesotho'                           => '🇱🇸 Lesotho',
    'Liberia'                           => '🇱🇷 Liberia',
    'Libye'                             => '🇱🇾 Libye',
    'Madagascar'                        => '🇲🇬 Madagascar',
    'Malawi'                            => '🇲🇼 Malawi',
    'Mali'                              => '🇲🇱 Mali',
    'Maroc'                             => '🇲🇦 Maroc',
    'Maurice'                           => '🇲🇺 Maurice',
    'Mauritanie'                        => '🇲🇷 Mauritanie',
    'Mozambique'                        => '🇲🇿 Mozambique',
    'Namibie'                           => '🇳🇦 Namibie',
    'Niger'                             => '🇳🇪 Niger',
    'Nigeria'                           => '🇳🇬 Nigeria',
    'Ouganda'                           => '🇺🇬 Ouganda',
    'République Centrafricaine'         => '🇨🇫 République Centrafricaine',
    'République du Congo'               => '🇨🇬 République du Congo',
    'République Démocratique du Congo'  => '🇨🇩 République Démocratique du Congo',
    'Rwanda'                            => '🇷🇼 Rwanda',
    'Sao Tomé-et-Principe'              => '🇸🇹 Sao Tomé-et-Principe',
    'Sénégal'                           => '🇸🇳 Sénégal',
    'Seychelles'                        => '🇸🇨 Seychelles',
    'Sierra Leone'                      => '🇸🇱 Sierra Leone',
    'Somalie'                           => '🇸🇴 Somalie',
    'Soudan'                            => '🇸🇩 Soudan',
    'Soudan du Sud'                     => '🇸🇸 Soudan du Sud',
    'Tanzanie'                          => '🇹🇿 Tanzanie',
    'Tchad'                             => '🇹🇩 Tchad',
    'Togo'                              => '🇹🇬 Togo',
    'Tunisie'                           => '🇹🇳 Tunisie',
    'Zambie'                            => '🇿🇲 Zambie',
    'Zimbabwe'                          => '🇿🇼 Zimbabwe',
];
?>

<style>
    /* Styles locaux à la page settings */
    .settings-card .dashboard-title {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .settings-flash {
        padding: 12px 16px;
        border-radius: 8px;
        margin-bottom: 16px;
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .settings-flash.success {
        background: rgba(74, 222, 128, .1);
        border: 1px solid rgba(74, 222, 128, .3);
        color: #4ade80;
    }
    .settings-flash.error {
        background: rgba(255, 75, 92, .1);
        border: 1px solid rgba(255, 75, 92, .3);
        color: #ff4b5c;
    }
    .settings-input,
    .settings-select,
    .settings-textarea {
        width: 100%;
        padding: 12px;
        background: #121212;
        border: 1px solid #444;
        border-radius: 8px;
        color: #fff;
        font-family: inherit;
        font-size: 14px;
        transition: border-color .2s;
    }
    .settings-input:focus,
    .settings-select:focus,
    .settings-textarea:focus {
        outline: none;
        border-color: var(--accent, #ff2d3a);
    }
    .settings-label {
        display: block;
        color: #aaa;
        font-size: 13px;
        margin-bottom: 8px;
        font-weight: 500;
    }
    .settings-hint {
        font-size: 11px;
        color: #666;
        margin-top: 6px;
    }
    .settings-avatar-preview {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        background: var(--accent, #ff2d3a);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 32px;
        font-weight: bold;
        color: #fff;
        margin-bottom: 12px;
        overflow: hidden;
        border: 3px solid #333;
    }
    .settings-avatar-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .settings-btn-upload {
        background: none;
        border: 1px solid #444;
        color: #fff;
        padding: 8px 16px;
        border-radius: 6px;
        cursor: pointer;
        font-size: 13px;
        display: inline-block;
        transition: .2s;
    }
    .settings-btn-upload:hover {
        background: #272727;
        border-color: #666;
    }
    .settings-btn-save {
        background: var(--accent, #ff2d3a);
        color: #fff;
        border: none;
        padding: 12px 24px;
        border-radius: 8px;
        font-weight: bold;
        cursor: pointer;
        transition: opacity .2s;
        font-size: 14px;
    }
    .settings-btn-save:hover {
        opacity: .9;
    }
    .settings-btn-save:disabled {
        opacity: .6;
        cursor: not-allowed;
    }
    .settings-layout {
        display: flex;
        gap: 32px;
        align-items: flex-start;
    }
    @media (max-width: 640px) {
        .settings-layout {
            flex-direction: column;
        }
        .settings-layout > div:first-child {
            align-self: center;
        }
    }
</style>

<div class="dashboard-card settings-card">
    <h2 class="dashboard-title">Personnalisation de la chaîne</h2>

    <?php if ($flash): ?>
        <div class="settings-flash <?= $flash['type'] === 'success' ? 'success' : 'error' ?>">
            <?php if ($flash['type'] === 'success'): ?>
                <i class="ph ph-check-circle" style="font-size:18px;"></i>
            <?php else: ?>
                <i class="ph ph-warning-circle" style="font-size:18px;"></i>
            <?php endif; ?>
            <?= htmlspecialchars($flash['msg']) ?>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" id="settingsForm">
        <div class="settings-layout">

            <!-- ===== AVATAR ===== -->
            <div style="text-align: center;">
                <div class="settings-avatar-preview" id="avatarPreview">
                    <?php if (!empty($avatarUrl)): ?>
                        <img src="<?= htmlspecialchars($avatarUrl) ?>"
                             alt="<?= htmlspecialchars($channelName) ?>"
                             onerror="this.replaceWith(document.createTextNode('<?= htmlspecialchars($initials) ?>'))">
                    <?php else: ?>
                        <?= htmlspecialchars($initials) ?>
                    <?php endif; ?>
                </div>

                <label class="settings-btn-upload" for="avatarFile">
                    Changer
                </label>
                <input type="file" name="avatar_file" id="avatarFile" accept="image/*" style="display: none;">
                <div class="settings-hint">JPG, PNG, WebP · 3 Mo max</div>
            </div>

            <!-- ===== FORMULAIRE ===== -->
            <div style="flex: 1; display: flex; flex-direction: column; gap: 16px;">

                <div>
                    <label class="settings-label" for="channelName">
                        Nom de la chaîne <span style="color:#ff4b5c;">*</span>
                    </label>
                    <input type="text"
                           name="channel_name"
                           id="channelName"
                           required
                           maxlength="120"
                           class="settings-input"
                           value="<?= htmlspecialchars($channelName) ?>">
                    <div class="settings-hint">
                        Email du compte : <?= htmlspecialchars($email) ?>
                    </div>
                </div>

                <div>
                    <label class="settings-label" for="description">
                        Description
                    </label>
                    <textarea name="description"
                              id="description"
                              rows="3"
                              maxlength="500"
                              class="settings-textarea"
                              placeholder="Créateur de contenu sur Kazuri. Passionné de cinéma et de culture africaine."><?= htmlspecialchars($description) ?></textarea>
                    <div class="settings-hint">
                        <span id="descCount"><?= mb_strlen($description) ?></span> / 500 caractères
                    </div>
                </div>

                <div>
                    <label class="settings-label" for="country">
                        Pays
                    </label>
                    <select name="country" id="country" class="settings-select">
                        <option value="">— Sélectionner un pays —</option>
                        <?php foreach ($africanCountries as $value => $label): ?>
                            <option value="<?= htmlspecialchars($value) ?>"
                                <?= $country === $value ? 'selected' : '' ?>>
                                <?= $label ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div style="margin-top: 12px;">
                    <button type="submit" class="settings-btn-save" id="saveBtn">
                        Enregistrer les modifications
                    </button>
                </div>

            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    /* --- Prévisualisation immédiate de l'avatar --- */
    const avatarFile    = document.getElementById('avatarFile');
    const avatarPreview = document.getElementById('avatarPreview');
    if (avatarFile && avatarPreview) {
        avatarFile.addEventListener('change', function () {
            const file = this.files && this.files[0];
            if (!file) return;
            if (!file.type.startsWith('image/')) {
                alert('Veuillez sélectionner une image.');
                this.value = '';
                return;
            }
            const reader = new FileReader();
            reader.onload = (e) => {
                avatarPreview.innerHTML = '';
                const img = document.createElement('img');
                img.src = e.target.result;
                img.alt = 'Aperçu';
                avatarPreview.appendChild(img);
            };
            reader.readAsDataURL(file);

            // Feedback visuel sur le bouton "Changer"
            const lbl = document.querySelector('label[for="avatarFile"]');
            if (lbl) lbl.textContent = '📷 ' + file.name;
        });
    }

    /* --- Compteur de caractères sur la description --- */
    const desc      = document.getElementById('description');
    const descCount = document.getElementById('descCount');
    if (desc && descCount) {
        desc.addEventListener('input', () => {
            descCount.textContent = desc.value.length;
        });
    }

    /* --- Anti-double-submit --- */
    const form    = document.getElementById('settingsForm');
    const saveBtn = document.getElementById('saveBtn');
    if (form && saveBtn) {
        form.addEventListener('submit', () => {
            saveBtn.disabled = true;
            saveBtn.textContent = 'Enregistrement…';
        });
    }

});
</script>