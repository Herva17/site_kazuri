<?php
if (!class_exists('User')) {
    require_once dirname(__DIR__, 3) . '/Classes/UserClass.php';
}

$currentUser = User::current();
// Sécurité : Si pas connecté ou pas admin, on redirige
if (!$currentUser || !$currentUser->isAdmin()) {
    header('Location: index.php?p=home');
    exit;
}

$initials = mb_strtoupper(mb_substr($currentUser->firstName(), 0, 1, 'UTF-8') . mb_substr($currentUser->lastName(), 0, 1, 'UTF-8'), 'UTF-8');
$currentPage = $_GET['p'] ?? 'admin';

// Petit helper pour la classe active
$isActive = fn(string ...$pages) => in_array($currentPage, $pages, true) ? 'active' : '';
?>
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Administration — Kazuri</title>
<script src="https://unpkg.com/@phosphor-icons/web"></script>
<style>
    :root {
        --bg-base: #0f0f0f; --bg-elevated: #1e1e1e; --text-main: #f1f1f1; 
        --text-muted: #aaaaaa; --accent: #ff2d3a; --header-height: 64px; --sidebar-width: 260px;
    }
    body { background: var(--bg-base); color: var(--text-main); font-family: 'Roboto', Arial, sans-serif; margin: 0; display: flex; height: 100vh; overflow: hidden; }
    
    /* SIDEBAR ADMIN */
    .admin-sidebar { width: var(--sidebar-width); background: #111; border-right: 1px solid #333; display: flex; flex-direction: column; }
    .admin-brand { height: var(--header-height); display: flex; align-items: center; padding: 0 24px; font-size: 20px; font-weight: bold; color: #fff; text-decoration: none; border-bottom: 1px solid #333; }
    .admin-brand i { color: var(--accent); font-size: 24px; margin-right: 8px; }
    .admin-brand span { font-size: 11px; background: var(--accent); padding: 2px 6px; border-radius: 4px; margin-left: 8px; color: #fff; font-weight: bold; }
    
    .admin-nav { padding: 12px; flex: 1; overflow-y: auto; }
    .nav-section-title { font-size: 10px; text-transform: uppercase; letter-spacing: 1px; color: #666; padding: 16px 12px 8px; font-weight: 700; }
    .nav-item { display: flex; align-items: center; gap: 16px; padding: 10px 12px; color: #aaa; text-decoration: none; border-radius: 8px; margin-bottom: 4px; transition: 0.2s; font-size: 14px; }
    .nav-item:hover, .nav-item.active { background: #272727; color: #fff; }
    .nav-item.active { border-left: 3px solid var(--accent); }
    .nav-item i { font-size: 20px; }
    .nav-item .nav-badge { margin-left: auto; background: var(--accent); color: #fff; font-size: 10px; font-weight: 700; padding: 2px 6px; border-radius: 10px; }
    .nav-sep { height: 1px; background: #333; margin: 12px 0; }
    
    /* MAIN CONTENT & TOPBAR */
    .admin-main { flex: 1; display: flex; flex-direction: column; overflow: hidden; }
    .admin-topbar { 
        height: var(--header-height); background: var(--bg-base); border-bottom: 1px solid #333; 
        display: flex; align-items: center; justify-content: space-between; padding: 0 24px; position: relative; z-index: 100;
    }
    .admin-topbar h2 { margin: 0; font-size: 18px; font-weight: 500; }
    
    .topbar-actions { display: flex; align-items: center; gap: 20px; }
    
    /* NOTIFICATIONS */
    .notification-wrapper { position: relative; cursor: pointer; }
    .icon-btn { background: none; border: none; color: #aaa; font-size: 24px; cursor: pointer; transition: 0.2s; position: relative; display: flex; align-items: center; }
    .icon-btn:hover { color: #fff; }
    .notif-dot { 
        position: absolute; top: -2px; right: -2px; width: 10px; height: 10px; 
        background: var(--accent); border-radius: 50%; border: 2px solid var(--bg-base); 
    }
    .notif-dropdown {
        display: none; position: absolute; top: 40px; right: -10px; width: 320px;
        background: #1e1e1e; border: 1px solid #333; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.5);
        padding: 16px; z-index: 200;
    }
    .notif-dropdown.show { display: block; }
    .notif-title { font-size: 14px; font-weight: bold; color: #fff; margin-bottom: 12px; border-bottom: 1px solid #333; padding-bottom: 8px; }
    .notif-item { padding: 10px 0; border-bottom: 1px solid #2a2a2a; font-size: 13px; }
    .notif-item:last-child { border-bottom: none; }
    .notif-item span { color: var(--accent); font-weight: bold; }

    /* USER MENU */
    .user-wrapper { position: relative; cursor: pointer; display: flex; align-items: center; gap: 12px; padding: 6px 12px; border-radius: 8px; transition: 0.2s; border: 1px solid transparent; }
    .user-wrapper:hover { background: #1e1e1e; border-color: #333; }
    .user-avatar-circle { width: 32px; height: 32px; border-radius: 50%; background: var(--accent); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 14px; }
    .user-name { font-size: 14px; color: #fff; font-weight: 500; }
    
    .user-dropdown {
        display: none; position: absolute; top: 50px; right: 0; width: 220px;
        background: #1e1e1e; border: 1px solid #333; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.5);
        z-index: 200; overflow: hidden;
    }
    .user-dropdown.show { display: block; }
    .user-dropdown-header { padding: 16px; border-bottom: 1px solid #333; }
    .user-dropdown-name { font-weight: bold; color: #fff; font-size: 14px; margin-bottom: 4px; }
    .user-dropdown-email { font-size: 12px; color: #aaa; margin-bottom: 8px; }
    .user-dropdown-role { font-size: 11px; background: rgba(255,45,58,0.1); color: var(--accent); padding: 2px 6px; border-radius: 4px; border: 1px solid rgba(255,45,58,0.3); display: inline-block; }
    .user-dropdown-item { display: flex; align-items: center; gap: 12px; padding: 12px 16px; color: #aaa; text-decoration: none; font-size: 14px; transition: 0.2s; }
    .user-dropdown-item:hover { background: #272727; color: #fff; }
    .user-dropdown-item.danger:hover { background: rgba(255,75,92,0.1); color: #ff4b5c; }
    .user-dropdown-item i { font-size: 18px; }

    .admin-content-scroll { flex: 1; overflow-y: auto; padding: 32px; }
    
    /* CARDS & TABLES GLOBALES */
    .card { background: var(--bg-elevated); border: 1px solid #333; border-radius: 12px; padding: 24px; margin-bottom: 24px; }
    .card-title { font-size: 18px; font-weight: 600; margin: 0 0 16px; color: #fff; }
    .data-table { width: 100%; border-collapse: collapse; text-align: left; }
    .data-table th { padding: 12px; color: #aaa; font-size: 13px; border-bottom: 1px solid #333; font-weight: 500; }
    .data-table td { padding: 16px 12px; border-bottom: 1px solid #2a2a2a; font-size: 14px; }
    .data-table tr:hover { background: #252525; }
    
    /* BOUTONS */
    .btn { padding: 8px 16px; border-radius: 6px; border: none; cursor: pointer; font-weight: 600; font-size: 13px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; transition: 0.2s; }
    .btn-primary { background: var(--accent); color: #fff; }
    .btn-primary:hover { background: #e02532; }
    .btn-outline { background: transparent; border: 1px solid #444; color: #fff; }
    .btn-outline:hover { background: #333; }
    .btn-danger { background: #ff4b5c; color: #fff; }
    
    /* BADGES */
    .badge { padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; }
    .badge-success { background: rgba(74, 222, 128, 0.1); color: #4ade80; border: 1px solid rgba(74, 222, 128, 0.3); }
    .badge-warning { background: rgba(250, 204, 21, 0.1); color: #facc15; border: 1px solid rgba(250, 204, 21, 0.3); }
    .badge-danger { background: rgba(255, 75, 92, 0.1); color: #ff4b5c; border: 1px solid rgba(255, 75, 92, 0.3); }
    .badge-info { background: rgba(96, 165, 250, 0.1); color: #60a5fa; border: 1px solid rgba(96, 165, 250, 0.3); }
</style>
</head>
<body>

    <aside class="admin-sidebar">
        <a href="index.php?p=admin" class="admin-brand">
            <i class="ph-fill ph-shield-check"></i> Kazuri <span>ADMIN</span>
        </a>
        <div class="admin-nav">

            <!-- ===== SECTION : PILOTAGE ===== -->
            <div class="nav-section-title">Pilotage</div>
            <a href="index.php?p=admin" class="nav-item <?= $isActive('admin') ?>">
                <i class="ph ph-chart-pie-slice"></i> Vue d'ensemble
            </a>
            <a href="index.php?p=admin_stats" class="nav-item <?= $isActive('admin_stats') ?>">
                <i class="ph ph-chart-line-up"></i> Statistiques détaillées
            </a>

            <!-- ===== SECTION : CONTENU ===== -->
            <div class="nav-section-title">Contenu</div>
            <a href="index.php?p=admin_videos" class="nav-item <?= $isActive('admin_videos') ?>">
                <i class="ph ph-video-camera"></i> Modération Vidéos
                <span class="nav-badge">3</span>
            </a>
            <a href="index.php?p=admin_comments" class="nav-item <?= $isActive('admin_comments') ?>">
                <i class="ph ph-chat-circle-dots"></i> Commentaires
            </a>
            <a href="index.php?p=admin_reports" class="nav-item <?= $isActive('admin_reports') ?>">
                <i class="ph ph-flag"></i> Signalements
            </a>
            <a href="index.php?p=admin_categories" class="nav-item <?= $isActive('admin_categories') ?>">
                <i class="ph ph-tag"></i> Catégories & Tags
            </a>

            <!-- ===== SECTION : UTILISATEURS ===== -->
            <div class="nav-section-title">Utilisateurs</div>
            <a href="index.php?p=admin_users" class="nav-item <?= $isActive('admin_users') ?>">
                <i class="ph ph-users"></i> Utilisateurs & Rôles
            </a>
            <a href="index.php?p=admin_contributors" class="nav-item <?= $isActive('admin_contributors') ?>">
                <i class="ph ph-upload-simple"></i> Contributeurs
            </a>
            <a href="index.php?p=admin_reviewers" class="nav-item <?= $isActive('admin_reviewers', 'admin_reviewer_edit') ?>">
                <i class="ph ph-user-check"></i> Modérateurs (Reviewers)
            </a>
            <a href="index.php?p=admin_subscribers" class="nav-item <?= $isActive('admin_subscribers') ?>">
                <i class="ph ph-bell-ringing"></i> Abonnés
            </a>

            <!-- ===== SECTION : FINANCE ===== -->
            <div class="nav-section-title">Finance</div>
            <a href="index.php?p=admin_transactions" class="nav-item <?= $isActive('admin_transactions') ?>">
                <i class="ph ph-receipt"></i> Transactions
            </a>
            <a href="index.php?p=admin_payouts" class="nav-item <?= $isActive('admin_payouts') ?>">
                <i class="ph ph-currency-dollar"></i> Reversements
            </a>

            <!-- ===== SECTION : SYSTÈME ===== -->
            <div class="nav-section-title">Système</div>
            <a href="index.php?p=admin_notifications" class="nav-item <?= $isActive('admin_notifications') ?>">
                <i class="ph ph-megaphone"></i> Notifications
            </a>
            <a href="index.php?p=admin_logs" class="nav-item <?= $isActive('admin_logs') ?>">
                <i class="ph ph-scroll"></i> Journal d'activité
            </a>
            <a href="index.php?p=admin_settings" class="nav-item <?= $isActive('admin_settings') ?>">
                <i class="ph ph-gear"></i> Paramètres
            </a>

            <div class="nav-sep"></div>
            <a href="index.php?p=home" class="nav-item">
                <i class="ph ph-arrow-square-out"></i> Retour au site
            </a>
        </div>
    </aside>

    <main class="admin-main">
        <header class="admin-topbar">
            <h2>Espace Administrateur</h2>
            
            <div class="topbar-actions">
                <!-- CENTRE DE NOTIFICATIONS -->
                <div class="notification-wrapper" onclick="toggleNotif(event)">
                    <button class="icon-btn">
                        <i class="ph ph-bell"></i>
                        <div class="notif-dot"></div>
                    </button>
                    
                    <div class="notif-dropdown" id="notifDropdown">
                        <div class="notif-title">Notifications</div>
                        <div class="notif-item">
                            <span>⚠️ Modération :</span> 3 nouvelles vidéos en attente de validation.
                        </div>
                        <div class="notif-item">
                            <span>👤 Nouveau :</span> Un utilisateur a demandé à devenir contributeur.
                        </div>
                        <div class="notif-item" style="color: #aaa; font-size: 12px; text-align: center;">
                            Aucune autre notification.
                        </div>
                    </div>
                </div>

                <!-- MENU UTILISATEUR -->
                <div class="user-wrapper" onclick="toggleUser(event)">
                    <span class="user-name"><?= htmlspecialchars($currentUser->firstName()) ?></span>
                    <div class="user-avatar-circle"><?= $initials ?></div>
                    
                    <div class="user-dropdown" id="userDropdown">
                        <div class="user-dropdown-header">
                            <div class="user-dropdown-name"><?= htmlspecialchars($currentUser->fullName()) ?></div>
                            <div class="user-dropdown-email"><?= htmlspecialchars($currentUser->email()) ?></div>
                            <div class="user-dropdown-role"><?= strtoupper($currentUser->primaryRole()) ?></div>
                        </div>
                        <a href="index.php?p=account" class="user-dropdown-item">
                            <i class="ph ph-user"></i> Mon Profil
                        </a>
                        <a href="index.php?p=home" class="user-dropdown-item">
                            <i class="ph ph-globe"></i> Voir le site
                        </a>
                        <div class="nav-sep" style="margin: 0;"></div>
                        <a href="index.php?p=logout" class="user-dropdown-item danger">
                            <i class="ph ph-sign-out"></i> Déconnexion
                        </a>
                    </div>
                </div>
            </div>
        </header>
        
        <div class="admin-content-scroll">
            <?= $content ?>
        </div>
    </main>

    <!-- SCRIPT POUR LES MENUS DÉROULANTS -->
    <script>
        function toggleNotif(event) {
            event.stopPropagation();
            document.getElementById('notifDropdown').classList.toggle('show');
            document.getElementById('userDropdown').classList.remove('show');
        }

        function toggleUser(event) {
            event.stopPropagation();
            document.getElementById('userDropdown').classList.toggle('show');
            document.getElementById('notifDropdown').classList.remove('show');
        }

        window.addEventListener('click', function(event) {
            const notif = document.getElementById('notifDropdown');
            const user = document.getElementById('userDropdown');
            
            if (notif && notif.classList.contains('show') && !event.target.closest('.notification-wrapper')) {
                notif.classList.remove('show');
            }
            if (user && user.classList.contains('show') && !event.target.closest('.user-wrapper')) {
                user.classList.remove('show');
            }
        });
    </script>
</body>
</html>