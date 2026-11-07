<?php
$currentPage = $_GET['p'] ?? 'studio';
if (!class_exists('User')) {
    require_once dirname(__DIR__, 3) . '/Classes/UserClass.php';
}

$currentUser = User::current();
if (!$currentUser) {
    header('Location: index.php?p=account');
    exit;
}

$userName = htmlspecialchars($currentUser->fullName());
$initials = mb_strtoupper(mb_substr($currentUser->firstName(), 0, 1, 'UTF-8') . mb_substr($currentUser->lastName(), 0, 1, 'UTF-8'), 'UTF-8');
?>
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Kazuri Studio</title>
<script src="https://unpkg.com/@phosphor-icons/web"></script>
<style>
    :root {
        --bg-base: #0f0f0f; 
        --bg-elevated: #212121; 
        --text-main: #f1f1f1; 
        --text-muted: #aaaaaa; 
        --accent: #ff2d3a; 
        --header-height: 64px; 
        --sidebar-width: 240px;
    }
    
    /* RESET TOTAL */
    * { box-sizing: border-box; }
    body { 
        background: var(--bg-base); 
        color: var(--text-main); 
        font-family: 'Roboto', Arial, sans-serif; 
        margin: 0; 
        padding: 0; 
        display: flex; 
        height: 100vh; 
        overflow: hidden; 
    }
    
    /* SIDEBAR STUDIO */
    .studio-sidebar { 
        width: var(--sidebar-width); 
        background: var(--bg-base); 
        border-right: 1px solid #333; 
        display: flex; 
        flex-direction: column; 
        flex-shrink: 0;
    }
    .studio-brand { 
        height: var(--header-height); 
        display: flex; 
        align-items: center; 
        padding: 0 24px; 
        font-size: 20px; 
        font-weight: bold; 
        color: #fff; 
        text-decoration: none; 
        border-bottom: 1px solid #333; 
    }
    .studio-brand i { 
        color: var(--accent); 
        font-size: 24px; 
        margin-right: 8px; 
    }
    .studio-brand span { 
        font-size: 11px; 
        background: #333; 
        padding: 2px 6px; 
        border-radius: 4px; 
        margin-left: 8px; 
        color: #aaa; 
        font-weight: normal;
    }
    
    .studio-nav { 
        padding: 12px; 
        flex: 1; 
        overflow-y: auto;
    }
    .studio-nav-item { 
        display: flex; 
        align-items: center; 
        gap: 16px; 
        padding: 12px; 
        color: #aaa; 
        text-decoration: none; 
        border-radius: 8px; 
        margin-bottom: 4px; 
        transition: 0.2s; 
        font-size: 14px;
    }
    .studio-nav-item:hover, .studio-nav-item.active { 
        background: var(--bg-elevated); 
        color: #fff; 
    }
    .studio-nav-item i { 
        font-size: 22px; 
    }
    .nav-sep {
        height: 1px;
        background: #333;
        margin: 12px 0;
    }
    
    /* MAIN CONTENT */
    .studio-main { 
        flex: 1; 
        display: flex; 
        flex-direction: column; 
        overflow: hidden; 
    }
    .studio-topbar { 
        height: var(--header-height); 
        background: var(--bg-base); 
        border-bottom: 1px solid #333; 
        display: flex; 
        align-items: center; 
        justify-content: space-between;
        padding: 0 24px; 
        gap: 16px; 
    }
    .topbar-left {
        display: flex;
        align-items: center;
        gap: 16px;
    }
    .btn-create { 
        background: #fff; 
        color: #000; 
        padding: 8px 16px; 
        border-radius: 20px; 
        font-weight: 600; 
        text-decoration: none; 
        display: inline-flex; 
        align-items: center; 
        gap: 8px; 
        font-size: 14px; 
    }
    .user-avatar-circle { 
        width: 36px; 
        height: 36px; 
        border-radius: 50%; 
        background: var(--accent); 
        color: #fff; 
        display: flex; 
        align-items: center; 
        justify-content: center; 
        font-weight: bold; 
        font-size: 14px; 
        margin-left: auto;
    }
    
    .studio-content-scroll { 
        flex: 1; 
        overflow-y: auto; 
        padding: 32px; 
    }
    
    /* CARDS & TABLES */
    .dashboard-card { 
        background: #1e1e1e; 
        border: 1px solid #333; 
        border-radius: 12px; 
        padding: 24px; 
        margin-bottom: 24px; 
    }
    .dashboard-title { 
        font-size: 20px; 
        font-weight: 600; 
        margin: 0 0 16px; 
        color: #fff; 
    }
    
    .data-table { 
        width: 100%; 
        border-collapse: collapse; 
        text-align: left; 
    }
    .data-table th { 
        padding: 12px; 
        color: #aaa; 
        font-size: 13px; 
        border-bottom: 1px solid #333; 
        font-weight: 500; 
    }
    .data-table td { 
        padding: 16px 12px; 
        border-bottom: 1px solid #2a2a2a; 
        font-size: 14px; 
    }
    .data-table tr:hover { 
        background: #252525; 
    }
    
    .video-thumb-sm { 
        width: 100px; 
        aspect-ratio: 16/9; 
        border-radius: 6px; 
        object-fit: cover; 
        background: #000; 
    }
</style>
</head>
<body>

    <!-- SIDEBAR STUDIO (UNIQUE À CETTE PAGE) -->
      <aside class="studio-sidebar">
        <a href="index.php?p=studio" class="studio-brand">
            <i class="ph-fill ph-play-circle"></i> Kazuri <span>Studio</span>
        </a>
        <div class="studio-nav">
            <a href="index.php?p=studio" class="studio-nav-item <?= $currentPage === 'studio' ? 'active' : '' ?>"><i class="ph ph-chart-pie-slice"></i> Tableau de bord</a>
            <a href="index.php?p=studio_contents" class="studio-nav-item <?= $currentPage === 'studio_contents' ? 'active' : '' ?>"><i class="ph ph-video-camera"></i> Contenus</a>
            <a href="index.php?p=studio_stats" class="studio-nav-item <?= $currentPage === 'studio_stats' ? 'active' : '' ?>"><i class="ph ph-chart-line-up"></i> Statistiques</a>
            <a href="index.php?p=studio_comments" class="studio-nav-item <?= $currentPage === 'studio_comments' ? 'active' : '' ?>"><i class="ph ph-chat-circle-text"></i> Commentaires</a>
            <a href="index.php?p=studio_monetization" class="studio-nav-item <?= $currentPage === 'studio_monetization' ? 'active' : '' ?>"><i class="ph ph-currency-dollar"></i> Monétisation</a>
            <div class="nav-sep"></div>
            <a href="index.php?p=studio_settings" class="studio-nav-item <?= $currentPage === 'studio_settings' ? 'active' : '' ?>"><i class="ph ph-gear"></i> Paramètres</a>
            <a href="index.php?p=home" class="studio-nav-item"><i class="ph ph-arrow-square-out"></i> Quitter le Studio</a>
            <a href="index.php?p=logout" class="studio-nav-item" style="color: #ff4b5c;"><i class="ph ph-sign-out"></i> Déconnexion</a>
        </div>
    </aside>

    <!-- CONTENU PRINCIPAL -->
    <main class="studio-main">
        <header class="studio-topbar">
            <div class="topbar-left">
                <a href="#" class="btn-create"><i class="ph ph-plus"></i> Créer</a>
            </div>
            <div class="user-avatar-circle" title="<?= $userName ?>"><?= $initials ?></div>
        </header>
        <div class="studio-content-scroll">
            <!-- ✅ INJECTION DU CONTENU DE studio.php -->
            <?= $content ?>
        </div>
    </main>

</body>
</html>