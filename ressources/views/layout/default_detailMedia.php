<?php
if (!class_exists('User')) {
    require_once dirname(__DIR__, 3) . '/Classes/UserClass.php';
}
$currentUser = User::current();
$userName = $currentUser ? htmlspecialchars($currentUser->fullName()) : 'Invité';
$isAdmin = $currentUser ? $currentUser->isAdmin() : false;
$canPublish = $currentUser ? $currentUser->canPublish() : false;
$initials = '';
if ($currentUser) {
    $initials = mb_strtoupper(
        mb_substr($currentUser->firstName(), 0, 1, 'UTF-8') . 
        mb_substr($currentUser->lastName(), 0, 1, 'UTF-8'), 
        'UTF-8'
    );
}
?>
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Regarder — Kazuri</title>
<script src="https://unpkg.com/@phosphor-icons/web"></script>
<style>
    :root {
        --bg-base: #0f0f0f; --bg-elevated: #272727; --text-main: #f1f1f1; 
        --text-muted: #aaaaaa; --accent: #ff2d3a; --header-height: 56px;
    }
    * { box-sizing: border-box; }
    body {
        background-color: var(--bg-base); color: var(--text-main); 
        font-family: 'Roboto', Arial, sans-serif;
        margin: 0; padding: 0; min-height: 100vh;
    }
    .topbar {
        height: var(--header-height); display: flex; align-items: center; justify-content: space-between;
        padding: 0 16px; background: var(--bg-base); border-bottom: 1px solid #222;
        position: sticky; top: 0; z-index: 100;
    }
    .brand { display: flex; align-items: center; gap: 8px; font-size: 20px; font-weight: 700; text-decoration: none; color: #fff; }
    .brand i { color: var(--accent); font-size: 28px; }
    .top-actions { display: flex; align-items: center; gap: 12px; }
    .btn-back { color: #fff; text-decoration: none; padding: 8px 16px; border-radius: 8px; background: #272727; font-size: 14px; font-weight: 500; display: inline-flex; align-items: center; gap: 6px; }
    .btn-back:hover { background: #3a3a3a; }
    .user-initials {
        width: 32px; height: 32px; border-radius: 50%; background: var(--accent);
        color: #fff; display: flex; align-items: center; justify-content: center;
        font-size: 14px; font-weight: 600; text-transform: uppercase;
    }
    .container {
        max-width: 1400px; margin: 0 auto; padding: 24px;
    }
</style>
</head>
<body>

    <header class="topbar">
        <a href="index.php?p=home" class="brand"><i class="ph-fill ph-play-circle"></i> KAZURI</a>
        <div class="top-actions">
            <a href="index.php?p=home" class="btn-back"><i class="ph ph-arrow-left"></i> Accueil</a>
            <?php if ($currentUser): ?>
                <a href="index.php?p=account" style="text-decoration: none;">
                    <div class="user-initials" title="<?= $userName ?>"><?= $initials ?></div>
                </a>
            <?php else: ?>
                <a href="index.php?p=account" class="btn-back">Connexion</a>
            <?php endif; ?>
        </div>
    </header>

    <div class="container">
        <?= $content ?>
    </div>

</body>
</html>