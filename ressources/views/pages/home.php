<?php
// On récupère l'utilisateur connecté pour les avatars dans la grille
if (class_exists('User')) {
    $currentUser = User::current();
}

// Données de démonstration avec des images FIABLES (Picsum Photos)
$videos = [
    [
        'id' => 1, 'title' => 'Kinshasa Nights - Le film', 'author' => 'Kazuri Studio',
        'views' => '412 k', 'time' => 'il y a 2 jours', 'duration' => '1:45:20',
        'thumbnail' => 'https://picsum.photos/seed/kazuri1/600/338',
        'avatar' => 'https://ui-avatars.com/api/?name=K+S&background=ff2d3a&color=fff'
    ],
    [
        'id' => 2, 'title' => 'Amani Beats - Rumba Moderne (Clip Officiel)', 'author' => 'Amani Beats',
        'views' => '125 k', 'time' => 'il y a 5 jours', 'duration' => '4:12',
        'thumbnail' => 'https://picsum.photos/seed/kazuri2/600/338',
        'avatar' => 'https://ui-avatars.com/api/?name=A+B&background=272727&color=fff'
    ],
    [
        'id' => 3, 'title' => 'Documentaire : Le fleuve Congo', 'author' => 'Grace M.',
        'views' => '89 k', 'time' => 'il y a 1 semaine', 'duration' => '52:30',
        'thumbnail' => 'https://picsum.photos/seed/kazuri3/600/338',
        'avatar' => 'https://ui-avatars.com/api/?name=G+M&background=272727&color=fff'
    ],
    [
        'id' => 4, 'title' => 'Reel : Danse Afro à Lagos', 'author' => 'Adeola Films',
        'views' => '1.2 M', 'time' => 'il y a 3 jours', 'duration' => '0:45',
        'thumbnail' => 'https://picsum.photos/seed/kazuri4/600/338',
        'avatar' => 'https://ui-avatars.com/api/?name=A+F&background=272727&color=fff'
    ],
    [
        'id' => 5, 'title' => 'Les Secrets du Cinéma Africain', 'author' => 'Fiston I.',
        'views' => '34 k', 'time' => 'il y a 12 heures', 'duration' => '15:00',
        'thumbnail' => 'https://picsum.photos/seed/kazuri5/600/338',
        'avatar' => 'https://ui-avatars.com/api/?name=F+I&background=272727&color=fff'
    ],
    [
        'id' => 6, 'title' => 'Concert Live : Gospel à Nairobi', 'author' => 'Wanjiru K.',
        'views' => '210 k', 'time' => 'il y a 1 mois', 'duration' => '1:12:00',
        'thumbnail' => 'https://picsum.photos/seed/kazuri6/600/338',
        'avatar' => 'https://ui-avatars.com/api/?name=W+K&background=272727&color=fff'
    ]
];
?>

<!-- BANNIÈRE HERO -->
<div class="hero-banner">
    <img src="https://picsum.photos/seed/kazuri_hero/1200/600" alt="Hero">
    <div class="hero-content">
        <span style="background:var(--accent); color:#fff; padding:4px 8px; border-radius:4px; font-size:12px; font-weight:bold;">À LA UNE</span>
        <h1>Kinshasa Nights</h1>
        <p>Plongez au cœur de la nuit de la capitale. Un voyage visuel et sonore unique. <br> Réalisé par Kazuri Studio.</p>
        <a href="index.php?p=detail&id=1" class="btn-watch"><i class="ph-fill ph-play"></i> Regarder</a>
    </div>
</div>

<!-- FILTRES -->
<div class="filters">
    <button class="chip active">Tout</button>
    <button class="chip">Films</button>
    <button class="chip">Séries</button>
    <button class="chip">Musique</button>
    <button class="chip">Documentaires</button>
</div>

<!-- GRILLE DE VIDÉOS -->
<div class="video-grid">
    <?php foreach ($videos as $video): ?>
    <a href="index.php?p=detail&id=<?= $video['id'] ?>" class="video-card">
        <div class="thumbnail-container">
            <img src="<?= $video['thumbnail'] ?>" 
                 alt="<?= htmlspecialchars($video['title']) ?>"
                 onerror="this.style.display='none'; this.parentElement.classList.add('no-image');">
            <span class="duration-badge"><?= $video['duration'] ?></span>
        </div>
        <div class="video-info">
            <img src="<?= $video['avatar'] ?>" alt="Avatar" class="channel-avatar">
            <div class="video-text">
                <h3 class="video-title"><?= htmlspecialchars($video['title']) ?></h3>
                <p class="video-meta"><?= htmlspecialchars($video['author']) ?></p>
                <p class="video-meta"><?= $video['views'] ?> vues · <?= $video['time'] ?></p>
            </div>
        </div>
    </a>
    <?php endforeach; ?>
</div>