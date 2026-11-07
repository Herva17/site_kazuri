<?php
// Sécurité : on s'assure que la classe User est chargée
if (!class_exists('User')) {
    require_once dirname(__DIR__, 3) . '/Classes/UserClass.php';
}

$currentUser = User::current();

$userName = $currentUser ? htmlspecialchars($currentUser->fullName()) : 'Invité';
$userEmail = $currentUser ? htmlspecialchars($currentUser->email()) : '';
$isAdmin = $currentUser ? $currentUser->isAdmin() : false;
$canPublish = $currentUser ? $currentUser->canPublish() : false;

$showInitials = false;
$initials = '';
$userAvatar = 'assets/img/av_user.jpg';

if ($currentUser) {
    $showInitials = true;
    $firstInitial = mb_substr($currentUser->firstName(), 0, 1, 'UTF-8');
    $lastInitial  = mb_substr($currentUser->lastName(), 0, 1, 'UTF-8');
    $initials = mb_strtoupper($firstInitial . $lastInitial, 'UTF-8');
}

$currentPage = $_GET['p'] ?? 'home';
?>
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Kazuri — Streaming</title>
<script src="https://unpkg.com/@phosphor-icons/web"></script>
<style>
    :root {
        --bg-base: #0f0f0f; --bg-elevated: #272727; --text-main: #f1f1f1; 
        --text-muted: #aaaaaa; --accent: #ff2d3a; --sidebar-width: 240px; --header-height: 56px;
    }
    * { box-sizing: border-box; }
    body {
        background-color: var(--bg-base); color: var(--text-main); font-family: 'Roboto', Arial, sans-serif;
        margin: 0; padding: 0; display: flex; flex-direction: column; height: 100vh; overflow: hidden;
    }
    
    /* ===== TOPBAR ===== */
    .topbar {
        height: var(--header-height); display: flex; align-items: center; justify-content: space-between;
        padding: 0 16px; position: fixed; top: 0; left: 0; right: 0; background: var(--bg-base); z-index: 100;
        border-bottom: 1px solid #222; gap: 16px;
    }
    .topbar-left { display: flex; align-items: center; gap: 12px; }
    .menu-toggle { background: none; border: none; color: #fff; font-size: 24px; cursor: pointer; padding: 8px; border-radius: 50%; transition: 0.2s; display: none; }
    .menu-toggle:hover { background: #272727; }
    .brand { display: flex; align-items: center; gap: 8px; font-size: 20px; font-weight: 700; text-decoration: none; color: #fff; }
    .brand i { color: var(--accent); font-size: 28px; }
    
    /* BARRE DE RECHERCHE */
    .search-wrapper { position: relative; flex: 1; max-width: 600px; }
    .search-form { display: flex; align-items: center; background: #121212; border: 1px solid #333; border-radius: 20px; padding: 0 4px 0 14px; transition: 0.2s; }
    .search-form:focus-within { border-color: #3ea6ff; box-shadow: 0 0 0 2px rgba(62,166,255,0.1); }
    .search-form > i { color: #888; font-size: 18px; margin-right: 8px; }
    .search-form input { background: transparent; border: none; color: #fff; padding: 10px 0; flex: 1; outline: none; font-size: 14px; }
    .search-form button { background: #222; border: none; color: #fff; width: 40px; height: 34px; border-radius: 17px; cursor: pointer; display: flex; align-items:center; justify-content:center; transition: 0.2s; font-size: 18px; }
    .search-form button:hover { background: #3ea6ff; color: #000; }
    
    .top-actions { display: flex; align-items: center; gap: 8px; }
    
    /* BOUTON CRÉER */
    .btn-create { background: #272727; color: #fff; border: none; padding: 8px 14px; border-radius: 20px; font-weight: 600; font-size: 13px; cursor: pointer; display: flex; align-items: center; gap: 6px; text-decoration: none; transition: 0.2s; }
    .btn-create:hover { background: #3a3a3a; transform: translateY(-1px); }
    .btn-create i { font-size: 18px; }
    
    /* ICONES TOPBAR */
    .icon-btn { background: none; border: none; color: #fff; font-size: 22px; cursor: pointer; padding: 8px; border-radius: 50%; transition: 0.2s; position: relative; display: flex; align-items: center; justify-content: center; }
    .icon-btn:hover { background: #272727; color: var(--accent); }
    .icon-btn:hover i { transform: scale(1.1); }
    .icon-btn i { transition: 0.2s; }
    .notif-dot { position: absolute; top: 6px; right: 6px; width: 8px; height: 8px; background: var(--accent); border-radius: 50%; animation: pulse 2s infinite; }
    @keyframes pulse { 0%,100% { opacity: 1; } 50% { opacity: 0.5; } }
    
    /* --- AVATAR ET INITIALES --- */
    .user-menu-container { position: relative; }
    .user-trigger { cursor: pointer; display: block; transition: 0.2s; }
    .user-trigger:hover { transform: scale(1.05); }
    .user-initials {
        width: 36px; height: 36px; border-radius: 50%;
        background: linear-gradient(135deg, var(--accent), #ff6b35); color: #fff; 
        display: flex; align-items: center; justify-content: center;
        font-size: 14px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px;
        border: 2px solid transparent; transition: 0.2s;
    }
    .user-trigger:hover .user-initials { border-color: #fff; }
    
    /* --- MENU DÉROULANT DU COMPTE --- */
    .user-dropdown {
        display: none; position: absolute; top: 50px; right: 0; width: 300px;
        background: #212121; border-radius: 12px; box-shadow: 0 8px 30px rgba(0,0,0,0.8);
        padding: 0; z-index: 1000; color: var(--text-main); border: 1px solid #444;
        animation: slideDown 0.2s ease-out;
    }
    @keyframes slideDown { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
    .user-dropdown.show { display: block; }
    .user-dropdown-header { display: flex; flex-direction: column; align-items: center; text-align: center; padding: 20px 16px; border-bottom: 1px solid #333; }
    .user-dropdown-avatar { width: 56px; height: 56px; border-radius: 50%; background: linear-gradient(135deg, var(--accent), #ff6b35); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: bold; margin-bottom: 10px; }
    .user-dropdown-name { font-size: 16px; font-weight: 600; margin-bottom: 4px; }
    .user-dropdown-email { font-size: 13px; color: var(--text-muted); margin-bottom: 12px; }
    .user-dropdown-link { color: #3ea6ff; text-decoration: none; font-size: 13px; font-weight: 500; display: inline-flex; align-items: center; gap: 4px; }
    .user-dropdown-link:hover { text-decoration: underline; }
    .user-dropdown-body { display: flex; flex-direction: column; padding: 8px; }
    .user-dropdown-item { display: flex; align-items: center; gap: 14px; padding: 10px 12px; border-radius: 8px; color: var(--text-main); text-decoration: none; font-size: 14px; transition: background 0.15s; }
    .user-dropdown-item:hover { background: #3f3f3f; }
    .user-dropdown-item i { font-size: 20px; color: var(--text-muted); transition: 0.2s; }
    .user-dropdown-item:hover i { color: #fff; }
    .user-dropdown-separator { height: 1px; background: #333; margin: 4px 0; }
    .user-dropdown-item.danger:hover { background: rgba(255,75,92,0.15); color: #ff4b5c; }
    .user-dropdown-item.danger:hover i { color: #ff4b5c; }
    
    /* --- NOTIFICATIONS --- */
    .notif-wrapper { position: relative; }
    .notif-dropdown {
        display: none; position: absolute; top: 50px; right: 0; width: 360px;
        background: #212121; border-radius: 12px; box-shadow: 0 8px 30px rgba(0,0,0,0.8);
        padding: 0; z-index: 1000; border: 1px solid #444; overflow: hidden;
        animation: slideDown 0.2s ease-out;
    }
    .notif-dropdown.show { display: block; }
    .notif-title { font-size: 16px; font-weight: bold; color: #fff; padding: 16px; border-bottom: 1px solid #333; display: flex; justify-content: space-between; align-items: center; }
    .notif-title i { color: var(--text-muted); cursor: pointer; transition: 0.2s; }
    .notif-title i:hover { color: #fff; }
    .notif-item { padding: 12px 16px; border-bottom: 1px solid #2a2a2a; font-size: 13px; color: #ddd; display: flex; gap: 12px; align-items: flex-start; transition: 0.2s; cursor: pointer; }
    .notif-item:hover { background: #2a2a2a; }
    .notif-item:last-child { border-bottom: none; }
    .notif-icon { width: 36px; height: 36px; flex-shrink: 0; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 18px; }
    .notif-icon.blue { background: rgba(62,166,255,0.15); color: #3ea6ff; }
    .notif-icon.red { background: rgba(255,45,58,0.15); color: var(--accent); }
    .notif-icon.green { background: rgba(74,222,128,0.15); color: #4ade80; }
    .notif-content { flex: 1; }
    .notif-content strong { color: #fff; display: block; margin-bottom: 2px; }
    .notif-content .tiny { font-size: 11px; color: #888; display: block; margin-top: 4px; }
    .notif-empty { padding: 30px; text-align: center; color: #888; font-size: 13px; }
    
    /* --- LAYOUT --- */
    .layout { display: flex; margin-top: var(--header-height); height: calc(100vh - var(--header-height)); }
    .sidebar { width: var(--sidebar-width); padding: 12px; overflow-y: auto; flex-shrink: 0; transition: 0.3s; }
    .nav-item {
        display: flex; align-items: center; gap: 16px; padding: 10px 12px; border-radius: 8px;
        color: var(--text-main); text-decoration: none; font-size: 14px; margin-bottom: 4px; transition: all 0.2s;
    }
    .nav-item:hover { background: var(--bg-elevated); transform: translateX(3px); }
    .nav-item.active { background: var(--bg-elevated); font-weight: 700; color: #fff; }
    .nav-item.active i { color: var(--accent); }
    .nav-item i { font-size: 22px; transition: 0.2s; }
    .nav-item:hover i { color: var(--accent); }
    .nav-separator { height: 1px; background: #333; margin: 12px 0; }
    .sidebar-title { font-size: 11px; font-weight: 700; color: #888; text-transform: uppercase; padding: 8px 12px; letter-spacing: 1.2px; }
    
    /* --- MAIN --- */
    .main-content { flex: 1; overflow-y: auto; padding: 24px; }
    
    /* HERO */
    .hero-banner { position: relative; width: 100%; height: 340px; border-radius: 16px; overflow: hidden; margin-bottom: 32px; background: #000; }
    .hero-banner img { width: 100%; height: 100%; object-fit: cover; opacity: 0.75; }
    .hero-content { position: absolute; bottom: 0; left: 0; padding: 40px 32px; background: linear-gradient(to top, rgba(15,15,15,1) 0%, rgba(15,15,15,0) 100%); width: 100%; box-sizing: border-box; }
    .hero-badge { background: var(--accent); color: #fff; padding: 6px 12px; border-radius: 6px; font-size: 11px; font-weight: 700; display: inline-flex; align-items: center; gap: 4px; text-transform: uppercase; letter-spacing: 1px; }
    .hero-content h1 { margin: 12px 0; font-size: 36px; text-shadow: 2px 2px 8px rgba(0,0,0,0.9); }
    .hero-content p { margin: 0 0 20px; color: #ddd; font-size: 15px; max-width: 700px; text-shadow: 1px 1px 4px rgba(0,0,0,0.9); }
    .btn-watch { background: #fff; color: #000; padding: 12px 28px; border-radius: 24px; text-decoration: none; font-weight: 700; display: inline-flex; align-items: center; gap: 8px; transition: 0.2s; font-size: 15px; }
    .btn-watch:hover { background: var(--accent); color: #fff; transform: scale(1.05); }
    .btn-watch i { font-size: 18px; }
    
    /* FILTRES */
    .filters { display: flex; gap: 12px; margin-bottom: 24px; overflow-x: auto; padding-bottom: 4px; }
    .chip { background: var(--bg-elevated); color: var(--text-main); border: 1px solid transparent; padding: 8px 18px; border-radius: 20px; cursor: pointer; white-space: nowrap; font-size: 14px; font-weight: 500; transition: 0.2s; display: flex; align-items: center; gap: 6px; }
    .chip:hover { background: #3a3a3a; }
    .chip.active { background: #fff; color: #000; font-weight: 700; }
    .chip.active i { color: var(--accent); }
    
    /* GRID DE VIDÉOS */
    .video-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 24px 16px; }
    .video-card { cursor: pointer; text-decoration: none; color: inherit; display: flex; flex-direction: column; gap: 12px; transition: 0.2s; }
    .video-card:hover { transform: translateY(-3px); }
    .thumbnail-container { position: relative; width: 100%; aspect-ratio: 16/9; border-radius: 12px; overflow: hidden; background: #222; }
    .thumbnail-container img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s; }
    .video-card:hover .thumbnail-container img { transform: scale(1.08); }
    .duration-badge { position: absolute; bottom: 8px; right: 8px; background: rgba(0,0,0,0.85); color: #fff; font-size: 12px; padding: 2px 6px; border-radius: 4px; display: flex; align-items: center; gap: 3px; }
    .duration-badge i { font-size: 11px; }
    
    /* Overlay play au survol */
    .thumbnail-overlay { position: absolute; inset: 0; background: rgba(0,0,0,0.3); display: flex; align-items: center; justify-content: center; opacity: 0; transition: 0.2s; }
    .video-card:hover .thumbnail-overlay { opacity: 1; }
    .thumbnail-overlay i { font-size: 48px; color: #fff; }
    
    .video-info { display: flex; gap: 12px; }
    .channel-avatar { width: 36px; height: 36px; border-radius: 50%; object-fit: cover; flex-shrink: 0; }
    .video-title { font-size: 15px; font-weight: 600; margin: 0 0 6px; line-height: 1.3; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; color: #fff; }
    .video-meta { font-size: 13px; color: var(--text-muted); margin: 0; display: flex; align-items: center; gap: 4px; }
    .video-meta i { font-size: 12px; }
    .video-meta-dot { margin: 0 4px; }
    
    @media (max-width: 900px) { 
        .sidebar { display: none; } 
        .menu-toggle { display: flex; }
        .search-wrapper { display: none; }
        .hero-content h1 { font-size: 24px; }
    }
</style>
</head>
<body>

    <!-- ===== TOPBAR ===== -->
    <header class="topbar">
        <div class="topbar-left">
            <button class="menu-toggle"><i class="ph ph-list"></i></button>
            <a href="index.php?p=home" class="brand">
                <i class="ph-fill ph-play-circle"></i> KAZURI
            </a>
        </div>
        
        <!-- BARRE DE RECHERCHE -->
        <div class="search-wrapper">
            <form class="search-form" action="index.php" method="GET">
                <i class="ph ph-magnifying-glass"></i>
                <input type="hidden" name="p" value="search">
                <input type="text" name="q" placeholder="Rechercher des films, séries, musique..." required>
                <button type="submit" title="Rechercher"><i class="ph ph-arrow-right"></i></button>
            </form>
        </div>
        
        <div class="top-actions">
            <?php if ($canPublish): ?>
                <a href="index.php?p=studio_upload" class="btn-create">
                    <i class="ph-bold ph-plus"></i> Créer
                </a>
            <?php endif; ?>
            
            <!-- NOTIFICATIONS -->
            <div class="notif-wrapper">
                <button class="icon-btn" onclick="toggleNotif(event)" title="Notifications">
                    <i class="ph ph-bell"></i>
                    <div class="notif-dot"></div>
                </button>
                <div class="notif-dropdown" id="notifDropdown">
                    <div class="notif-title">
                        <span><i class="ph ph-bell-ringing"></i> Notifications</span>
                        <i class="ph ph-check" title="Tout marquer comme lu"></i>
                    </div>
                    <div class="notif-item">
                        <div class="notif-icon blue"><i class="ph ph-film-script"></i></div>
                        <div class="notif-content">
                            <strong>Nouvelle série disponible</strong>
                            Une nouvelle série vient d'être ajoutée au catalogue.
                            <span class="tiny">⏱ Il y a 5 minutes</span>
                        </div>
                    </div>
                    <div class="notif-item">
                        <div class="notif-icon red"><i class="ph ph-crown"></i></div>
                        <div class="notif-content">
                            <strong>Renouvelez votre abonnement</strong>
                            Votre plan Premium arrive à échéance.
                            <span class="tiny">⏱ Il y a 2 heures</span>
                        </div>
                    </div>
                    <div class="notif-item">
                        <div class="notif-icon green"><i class="ph ph-check-circle"></i></div>
                        <div class="notif-content">
                            <strong>Vidéo approuvée</strong>
                            Votre vidéo "Richesse en enfer" est maintenant en ligne.
                            <span class="tiny">⏱ Hier</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- MENU UTILISATEUR -->
            <div class="user-menu-container">
                <div class="user-trigger" onclick="toggleUserMenu(event)">
                    <?php if ($showInitials): ?>
                        <div class="user-initials"><?= $initials ?></div>
                    <?php else: ?>
                        <img src="<?= htmlspecialchars($userAvatar) ?>" alt="Profil" class="user-avatar">
                    <?php endif; ?>
                </div>

                <div class="user-dropdown" id="userDropdown">
                    <?php if ($currentUser): ?>
                        <div class="user-dropdown-header">
                            <div class="user-dropdown-avatar"><?= $initials ?></div>
                            <div class="user-dropdown-name"><?= $userName ?></div>
                            <div class="user-dropdown-email"><?= $userEmail ?></div>
                            <a href="index.php?p=account" class="user-dropdown-link">
                                <i class="ph ph-gear"></i> Gérer mon compte
                            </a>
                        </div>
                        <div class="user-dropdown-body">
                            <a href="index.php?p=library" class="user-dropdown-item"><i class="ph ph-books"></i> Ma bibliothèque</a>
                            <a href="index.php?p=likes" class="user-dropdown-item"><i class="ph ph-heart"></i> Vidéos aimées</a>
                            <?php if ($canPublish): ?>
                                <a href="index.php?p=studio" class="user-dropdown-item"><i class="ph ph-video-camera"></i> Studio Kazuri</a>
                            <?php endif; ?>
                            <?php if ($isAdmin): ?>
                                <a href="index.php?p=admin" class="user-dropdown-item"><i class="ph ph-shield-check"></i> Administration</a>
                            <?php endif; ?>
                            <div class="user-dropdown-separator"></div>
                            <a href="#" class="user-dropdown-item"><i class="ph ph-question"></i> Aide & Support</a>
                            <a href="#" class="user-dropdown-item"><i class="ph ph-info"></i> À propos de Kazuri</a>
                            <div class="user-dropdown-separator"></div>
                            <a href="index.php?p=logout" class="user-dropdown-item danger"><i class="ph ph-sign-out"></i> Se déconnecter</a>
                        </div>
                    <?php else: ?>
                        <div class="user-dropdown-body" style="text-align: center; padding: 16px;">
                            <p style="color: #aaa; font-size: 13px; margin: 0 0 12px;">Connectez-vous pour profiter de toutes les fonctionnalités.</p>
                            <a href="index.php?p=account" class="user-dropdown-item" style="justify-content: center; background: var(--accent); color: #fff;"><i class="ph ph-sign-in"></i> Se connecter</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </header>

    <div class="layout">
        <!-- ===== SIDEBAR ===== -->
        <aside class="sidebar">
            <a href="index.php?p=home" class="nav-item <?= $currentPage === 'home' ? 'active' : '' ?>"><i class="ph-fill ph-house"></i> Accueil</a>
            <a href="index.php?p=films" class="nav-item <?= $currentPage === 'films' ? 'active' : '' ?>"><i class="ph-fill ph-film-strip"></i> Films</a>
            <a href="index.php?p=series" class="nav-item <?= $currentPage === 'series' ? 'active' : '' ?>"><i class="ph-fill ph-monitor-play"></i> Séries</a>
            <a href="index.php?p=musique" class="nav-item <?= $currentPage === 'musique' ? 'active' : '' ?>"><i class="ph-fill ph-music-notes"></i> Musique</a>
            <a href="index.php?p=reels" class="nav-item <?= $currentPage === 'reels' ? 'active' : '' ?>"><i class="ph-fill ph-device-mobile-camera"></i> Reels</a>
            
            <div class="nav-separator"></div>
            
            <div class="sidebar-title"><i class="ph ph-user-circle"></i> Vous</div>
            <a href="index.php?p=library" class="nav-item <?= $currentPage === 'library' ? 'active' : '' ?>"><i class="ph ph-books"></i> Bibliothèque</a>
            <a href="index.php?p=likes" class="nav-item <?= $currentPage === 'likes' ? 'active' : '' ?>"><i class="ph ph-heart"></i> Vidéos aimées</a>
            
            <?php if ($isAdmin): ?>
            <div class="nav-separator"></div>
            <div class="sidebar-title"><i class="ph ph-shield"></i> Admin</div>
            <a href="index.php?p=admin" class="nav-item" style="color: var(--accent);"><i class="ph-fill ph-shield-check"></i> Administration</a>
            <?php endif; ?>
        </aside>

        <main class="main-content">
            <?= $content ?>
        </main>
    </div>

    <!-- ===== JAVASCRIPT ===== -->
    <script>
        function toggleUserMenu(event) {
            event.stopPropagation();
            document.getElementById('userDropdown').classList.toggle('show');
            document.getElementById('notifDropdown').classList.remove('show');
        }

        function toggleNotif(event) {
            event.stopPropagation();
            document.getElementById('notifDropdown').classList.toggle('show');
            document.getElementById('userDropdown').classList.remove('show');
        }

        window.addEventListener('click', function(event) {
            const userDrop = document.getElementById('userDropdown');
            const notifDrop = document.getElementById('notifDropdown');
            
            if (userDrop && userDrop.classList.contains('show') && !event.target.closest('.user-menu-container')) {
                userDrop.classList.remove('show');
            }
            if (notifDrop && notifDrop.classList.contains('show') && !event.target.closest('.notif-wrapper')) {
                notifDrop.classList.remove('show');
            }
        });
    </script>

</body>
</html>