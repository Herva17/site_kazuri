<?php
// Sécurité : on s'assure que la classe User est chargée
if (!class_exists('User')) {
    require_once dirname(__DIR__, 3) . '/Classes/UserClass.php';
}

$currentUser = User::current();

$userName = $currentUser ? htmlspecialchars($currentUser->fullName()) : 'Invité';
$userEmail = $currentUser ? htmlspecialchars($currentUser->email()) : '';
$isAdmin = $currentUser ? $currentUser->isAdmin() : false;

$showInitials = false;
$initials = '';
$userAvatar = 'assets/img/av_user.jpg';

if ($currentUser) {
    $showInitials = true;
    // Première lettre du prénom + première lettre du nom
    $firstInitial = mb_substr($currentUser->firstName(), 0, 1, 'UTF-8');
    $lastInitial  = mb_substr($currentUser->lastName(), 0, 1, 'UTF-8');
    $initials = mb_strtoupper($firstInitial . $lastInitial, 'UTF-8');
}
?>
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Accueil — Kazuri</title>
<script src="https://unpkg.com/@phosphor-icons/web"></script>
<style>
    :root {
        --bg-base: #0f0f0f; --bg-elevated: #272727; --text-main: #f1f1f1; 
        --text-muted: #aaaaaa; --accent: #ff2d3a; --sidebar-width: 240px; --header-height: 56px;
    }
    body {
        background-color: var(--bg-base); color: var(--text-main); font-family: 'Roboto', Arial, sans-serif;
        margin: 0; padding: 0; display: flex; flex-direction: column; height: 100vh; overflow: hidden;
    }
    .topbar {
        height: var(--header-height); display: flex; align-items: center; justify-content: space-between;
        padding: 0 16px; position: fixed; top: 0; left: 0; right: 0; background: var(--bg-base); z-index: 100;
    }
    .brand { display: flex; align-items: center; gap: 8px; font-size: 20px; font-weight: 700; text-decoration: none; color: #fff; }
    .brand i { color: var(--accent); font-size: 28px; }
    .search-bar {
        display: flex; align-items: center; background: #121212; border: 1px solid #333;
        border-radius: 20px; padding: 0 4px 0 16px; width: 40%; max-width: 600px;
    }
    .search-bar input { background: transparent; border: none; color: #fff; padding: 10px 0; flex: 1; outline: none; }
    .search-bar button { background: #222; border: none; color: #fff; width: 40px; height: 34px; border-radius: 17px; cursor: pointer; display: flex; align-items:center; justify-content:center;}
    .top-actions { display: flex; align-items: center; gap: 16px; }
    
    /* --- AVATAR ET INITIALES --- */
    .user-menu-container { position: relative; }
    .user-trigger { cursor: pointer; display: block; }
    .user-avatar { 
        width: 32px; height: 32px; border-radius: 50%; object-fit: cover; 
        border: 1px solid #444; 
    }
    .user-initials {
        width: 32px; height: 32px; border-radius: 50%;
        background-color: var(--accent); color: #fff; display: flex; align-items: center; justify-content: center;
        font-size: 14px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;
    }
    
    /* --- MENU DÉROULANT DU COMPTE --- */
    .user-dropdown {
        display: none; position: absolute; top: 45px; right: 0; width: 260px;
        background: #282828; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.6);
        padding: 16px; z-index: 1000; color: var(--text-main); border: 1px solid #444;
    }
    .user-dropdown.show { display: block; }
    .user-dropdown-header { display: flex; flex-direction: column; align-items: center; text-align: center; padding-bottom: 16px; border-bottom: 1px solid #444; }
    .user-dropdown-name { font-size: 16px; font-weight: 600; margin-bottom: 4px; }
    .user-dropdown-email { font-size: 13px; color: var(--text-muted); margin-bottom: 12px; }
    .user-dropdown-link { color: #3ea6ff; text-decoration: none; font-size: 14px; font-weight: 500; }
    .user-dropdown-link:hover { text-decoration: underline; }
    .user-dropdown-body { display: flex; flex-direction: column; margin-top: 8px; }
    .user-dropdown-item { display: flex; align-items: center; gap: 12px; padding: 10px; border-radius: 8px; color: var(--text-main); text-decoration: none; font-size: 14px; transition: background 0.2s; }
    .user-dropdown-item:hover { background: #3f3f3f; }
    .user-dropdown-item i { font-size: 20px; color: var(--text-muted); }
    .user-dropdown-separator { height: 1px; background: #444; margin: 8px 0; }
    
    .layout { display: flex; margin-top: var(--header-height); height: calc(100vh - var(--header-height)); }
    .sidebar { width: var(--sidebar-width); padding: 12px; overflow-y: auto; flex-shrink: 0; }
    .nav-item {
        display: flex; align-items: center; gap: 16px; padding: 10px 12px; border-radius: 8px;
        color: var(--text-main); text-decoration: none; font-size: 14px; margin-bottom: 4px; transition: background 0.2s;
    }
    .nav-item:hover, .nav-item.active { background: var(--bg-elevated); font-weight: 700; }
    .nav-item i { font-size: 22px; }
    .nav-separator { height: 1px; background: #333; margin: 12px 0; }
    
    .main-content { flex: 1; overflow-y: auto; padding: 24px; }
    
    /* HERO */
    .hero-banner { position: relative; width: 100%; height: 300px; border-radius: 12px; overflow: hidden; margin-bottom: 32px; background: #000; }
    .hero-banner img { width: 100%; height: 100%; object-fit: cover; opacity: 0.7; }
    .hero-content { position: absolute; bottom: 0; left: 0; padding: 30px; background: linear-gradient(to top, rgba(15,15,15,1) 0%, rgba(15,15,15,0) 100%); width: 100%; }
    .hero-content h1 { margin: 8px 0; font-size: 32px; }
    .hero-content p { margin: 0 0 16px; color: var(--text-muted); font-size: 14px; }
    .btn-watch { background: #fff; color: #000; padding: 10px 24px; border-radius: 20px; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; }
    
    /* GRID */
    .filters { display: flex; gap: 12px; margin-bottom: 24px; overflow-x: auto; }
    .chip { background: var(--bg-elevated); color: var(--text-main); border: none; padding: 8px 16px; border-radius: 8px; cursor: pointer; white-space: nowrap; }
    .chip.active { background: #fff; color: #000; font-weight: 600; }
    .video-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 24px 16px; }
    .video-card { cursor: pointer; text-decoration: none; color: inherit; display: flex; flex-direction: column; gap: 12px; }
    .thumbnail-container { position: relative; width: 100%; aspect-ratio: 16/9; border-radius: 12px; overflow: hidden; background: #222; }
    .thumbnail-container img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.2s; }
    .video-card:hover .thumbnail-container img { transform: scale(1.03); }
    .duration-badge { position: absolute; bottom: 8px; right: 8px; background: rgba(0,0,0,0.8); color: #fff; font-size: 12px; padding: 2px 6px; border-radius: 4px; }
    .video-info { display: flex; gap: 12px; }
    .channel-avatar { width: 36px; height: 36px; border-radius: 50%; object-fit: cover; }
    .video-title { font-size: 16px; font-weight: 600; margin: 0 0 4px; line-height: 1.3; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    .video-meta { font-size: 14px; color: var(--text-muted); margin: 0; }
    
    @media (max-width: 900px) { .sidebar { display: none; } }
</style>
</head>
<body>

    <header class="topbar">
        <a href="index.php?p=home" class="brand"><i class="ph-fill ph-play-circle"></i> KAZURI</a>
        <div class="search-bar">
            <input type="text" placeholder="Rechercher des films, séries, musique...">
            <button><i class="ph ph-magnifying-glass"></i></button>
        </div>
        <div class="top-actions">
            <button style="background:none;border:none;color:#fff;font-size:24px;"><i class="ph ph-video-camera"></i></button>
            <button style="background:none;border:none;color:#fff;font-size:24px;"><i class="ph ph-bell"></i></button>
            
            <!-- ✅ MENU DÉROULANT COMPTE -->
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
                            <div class="user-dropdown-name"><?= $userName ?></div>
                            <div class="user-dropdown-email"><?= $userEmail ?></div>
                            <a href="index.php?p=account" class="user-dropdown-link">Gérer mon compte</a>
                        </div>
                        <div class="user-dropdown-body">
                            <a href="#" class="user-dropdown-item"><i class="ph ph-user"></i> Profil</a>
                            <a href="#" class="user-dropdown-item"><i class="ph ph-video-camera"></i> Studio Kazuri</a>
                            <?php if ($isAdmin): ?>
                                <a href="index.php?p=admin" class="user-dropdown-item"><i class="ph ph-shield-check"></i> Administration</a>
                            <?php endif; ?>
                            <div class="user-dropdown-separator"></div>
                            <a href="index.php?p=logout" class="user-dropdown-item"><i class="ph ph-sign-out"></i> Se déconnecter</a>
                        </div>
                    <?php else: ?>
                        <div class="user-dropdown-body" style="text-align: center;">
                            <a href="index.php?p=account" class="user-dropdown-item" style="justify-content: center;"><i class="ph ph-sign-in"></i> Se connecter</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </header>

    <div class="layout">
        <aside class="sidebar">
            <a href="index.php?p=home" class="nav-item active"><i class="ph-fill ph-house"></i> Accueil</a>
            <a href="#" class="nav-item"><i class="ph ph-film-strip"></i> Films</a>
            <a href="#" class="nav-item"><i class="ph ph-monitor-play"></i> Séries</a>
            <a href="#" class="nav-item"><i class="ph ph-music-notes"></i> Musique</a>
            <a href="#" class="nav-item"><i class="ph ph-device-mobile-camera"></i> Reels</a>
            <div class="nav-separator"></div>
            <a href="#" class="nav-item"><i class="ph ph-books"></i> Bibliothèque</a>
            <a href="#" class="nav-item"><i class="ph ph-heart"></i> Vidéos aimées</a>
            <?php if ($isAdmin): ?>
            <div class="nav-separator"></div>
            <a href="index.php?p=admin" class="nav-item" style="color: var(--accent);"><i class="ph ph-shield-check"></i> Administration</a>
            <?php endif; ?>
        </aside>

        <main class="main-content">
            <?= $content ?> <!-- Le contenu de home.php est injecté ici -->
        </main>
    </div>

    <!-- ✅ JAVASCRIPT POUR LE MENU DÉROULANT -->
    <script>
        function toggleUserMenu(event) {
            event.stopPropagation(); // Empêche la fermeture immédiate
            const dropdown = document.getElementById('userDropdown');
            dropdown.classList.toggle('show');
        }

        // Ferme le menu si on clique en dehors
        window.addEventListener('click', function(event) {
            const dropdown = document.getElementById('userDropdown');
            const trigger = document.querySelector('.user-trigger');
            
            if (dropdown && dropdown.classList.contains('show')) {
                // Si le clic n'est pas sur le trigger ni dans le dropdown
                if (!trigger.contains(event.target) && !dropdown.contains(event.target)) {
                    dropdown.classList.remove('show');
                }
            }
        });
    </script>

</body>
</html>