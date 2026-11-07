<?php
// Sécurité : on s'assure que la classe User et Conn sont chargées
if (!class_exists('User')) {
    require_once dirname(__DIR__, 3) . '/Classes/UserClass.php';
}

$currentUser = User::current();
if (!$currentUser) {
    header('Location: index.php?p=account');
    exit;
}

// ==========================================
// RÉCUPÉRATION DES VRAIES VIDÉOS DU CRÉATEUR
// ==========================================
$videos = [];
try {
    $pdo = Conn::getInstance()->getPdo();
    $stmt = $pdo->prepare("SELECT * FROM videos WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$currentUser->id()]);
    $videos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $errorMsg = "Erreur de chargement : " . $e->getMessage();
}

// Fonction pour formater la durée (secondes -> mm:ss ou hh:mm:ss)
function formatDuration(?int $seconds): string {
    if (!$seconds) return '0:00';
    $h = floor($seconds / 3600);
    $m = floor(($seconds % 3600) / 60);
    $s = $seconds % 60;
    return $h > 0 ? sprintf('%d:%02d:%02d', $h, $m, $s) : sprintf('%d:%02d', $m, $s);
}
?>

<div class="dashboard-card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2 class="dashboard-title" style="margin: 0;">Tous mes contenus (<?= count($videos) ?>)</h2>
        <a href="index.php?p=studio_upload" class="btn-create" style="background: var(--accent); color: #fff; padding: 8px 16px; border-radius: 8px; text-decoration: none; font-weight: bold; display: inline-flex; align-items: center; gap: 6px;">
            <i class="ph ph-plus"></i> Nouvelle vidéo
        </a>
    </div>
    
    <?php if (isset($errorMsg)): ?>
        <div style="background: rgba(255,75,92,0.1); color: #ff4b5c; padding: 12px; border-radius: 8px; margin-bottom: 20px;">
            <?= htmlspecialchars($errorMsg) ?>
        </div>
    <?php endif; ?>

    <div style="overflow-x: auto;">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Vidéo</th>
                    <th>Visibilité</th>
                    <th>Date</th>
                    <th>Vues</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($videos)): ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 40px; color: #aaa;">
                            <i class="ph ph-film-strip" style="font-size: 48px; color: #444; margin-bottom: 12px; display: block;"></i>
                            <h3 style="margin: 0 0 8px; color: #fff;">Aucun contenu pour le moment</h3>
                            <p style="margin: 0 0 16px;">Commencez par publier votre première vidéo sur Kazuri.</p>
                            <a href="index.php?p=studio_upload" style="background: var(--accent); color: #fff; padding: 10px 20px; border-radius: 6px; text-decoration: none; font-weight: bold;">Uploader une vidéo</a>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($videos as $v): 
                        // Gestion de l'image miniature
                        $thumb = !empty($v['thumbnail_url']) ? $v['thumbnail_url'] : 'https://picsum.photos/seed/' . urlencode($v['title']) . '/200/112';
                        
                        // Gestion du badge de statut
                        $badgeClass = 'badge-warning';
                        $statusLabel = 'En attente';
                        if ($v['status'] === 'published') {
                            $badgeClass = 'badge-success';
                            $statusLabel = 'Public';
                        } elseif ($v['status'] === 'rejected') {
                            $badgeClass = 'badge-danger';
                            $statusLabel = 'Rejeté';
                        }
                    ?>
                    <tr>
                        <td>
                            <div style="display: flex; gap: 16px; align-items: center;">
                                <img src="<?= htmlspecialchars($thumb) ?>" class="video-thumb-sm" alt="Miniature" onerror="this.src='https://picsum.photos/seed/error/200/112'">
                                <div>
                                    <div style="font-weight: 600; color: #fff;"><?= htmlspecialchars($v['title']) ?></div>
                                    <div style="font-size: 12px; color: #aaa;"><?= formatDuration((int)$v['duration_seconds']) ?></div>
                                </div>
                            </div>
                        </td>
                        <td><span class="badge <?= $badgeClass ?>"><?= $statusLabel ?></span></td>
                        <td style="color: #aaa;"><?= date('d/m/Y', strtotime($v['created_at'])) ?></td>
                        <td>—</td> <!-- À remplacer plus tard par le vrai compteur de vues -->
                        <td>
                            <div style="display: flex; gap: 8px;">
                                <!-- ✅ BOUTON PLAY FONCTIONNEL -->
                                <button onclick='playVideo(<?= json_encode($v['video_url']) ?>, <?= json_encode($v['title']) ?>)' 
                                        style="background:rgba(74,222,128,0.1); border:1px solid #4ade80; color:#4ade80; padding:6px 10px; border-radius:6px; cursor:pointer;" title="Lire la vidéo">
                                    <i class="ph-fill ph-play"></i>
                                </button>
                                
                                <button style="background:none; border:1px solid #444; color:#fff; padding:6px 10px; border-radius:6px; cursor:pointer;" title="Modifier">
                                    <i class="ph ph-pencil"></i>
                                </button>
                                
                                <?php if ($v['status'] !== 'published'): ?>
                                    <button style="background:none; border:1px solid #ff4b5c; color:#ff4b5c; padding:6px 10px; border-radius:6px; cursor:pointer;" title="Supprimer">
                                        <i class="ph ph-trash"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ========================================== -->
<!-- MODALE DU LECTEUR VIDÉO (POPUP)            -->
<!-- ========================================== -->
<div id="videoModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.9); z-index: 9999; justify-content: center; align-items: center; flex-direction: column;">
    <!-- Titre de la vidéo -->
    <h3 id="videoModalTitle" style="color: #fff; margin-bottom: 16px; font-size: 20px; font-weight: 500;">Titre de la vidéo</h3>
    
    <!-- Lecteur vidéo -->
    <video id="videoPlayer" controls style="max-width: 90%; max-height: 75vh; border-radius: 12px; outline: none; box-shadow: 0 4px 30px rgba(0,0,0,0.8);" controlsList="nodownload">
        Votre navigateur ne supporte pas la lecture de vidéos.
    </video>
    
    <!-- Bouton Fermer -->
    <button onclick="closeVideoModal()" style="margin-top: 24px; background: #333; color: #fff; border: 1px solid #444; padding: 10px 24px; border-radius: 8px; cursor: pointer; font-weight: bold; transition: 0.2s;">
        <i class="ph ph-x"></i> Fermer le lecteur
    </button>
</div>

<!-- ========================================== -->
<!-- SCRIPT POUR GÉRER LE LECTEUR               -->
<!-- ========================================== -->
<script>
    function playVideo(videoUrl, title) {
        const modal = document.getElementById('videoModal');
        const video = document.getElementById('videoPlayer');
        const titleEl = document.getElementById('videoModalTitle');
        
        // On s'assure que l'URL de la vidéo commence par un slash si elle est locale
        // (Ex: uploads/videos/xyz.mp4 -> /KazuriApp/uploads/videos/xyz.mp4)
        // Si le chemin est déjà absolu (http://...), on le laisse tel quel.
        
        titleEl.textContent = title;
        video.src = videoUrl;
        
        modal.style.display = 'flex';
        video.play().catch(e => console.log("Lecture automatique bloquée par le navigateur :", e));
    }

    function closeVideoModal() {
        const modal = document.getElementById('videoModal');
        const video = document.getElementById('videoPlayer');
        
        video.pause();
        video.src = ''; 
        modal.style.display = 'none';
    }

    // Fermer avec la touche Échap
    window.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') closeVideoModal();
    });
</script>