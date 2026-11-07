<?php
// On s'assure que la classe Video est chargée
if (!class_exists('Video')) {
    require_once dirname(__DIR__, 3) . '/Classes/VideoClass.php';
}

// ==========================================
// TRAITEMENT DES ACTIONS
// ==========================================
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $action = $_GET['action'];
    $msg = '';

    if ($action === 'approve') {
        Video::approveVideo($id);
        $msg = 'approved';
    } elseif ($action === 'feature') {
        Video::featureVideo($id);
        $msg = 'featured';
    } elseif ($action === 'reject') {
        // La raison est passée via une alerte JS
        $reason = $_GET['reason'] ?? 'Non conforme aux règles.';
        Video::rejectVideo($id, $reason);
        $msg = 'rejected';
    }

    echo "<script>window.location.href='index.php?p=admin_videos&msg=" . $msg . "';</script>";
    exit;
}

// Récupération des vidéos
$pendingVideos = Video::getPendingVideos();
?>

<!-- Messages de succès -->
<?php if (isset($_GET['msg'])): ?>
    <div style="background: rgba(74, 222, 128, 0.1); color: #4ade80; padding: 12px; border-radius: 8px; margin-bottom: 20px; border: 1px solid rgba(74, 222, 128, 0.3);">
        <?php 
            if ($_GET['msg'] === 'approved') echo "✅ La vidéo a été approuvée et publiée.";
            if ($_GET['msg'] === 'featured') echo "⭐ La vidéo a été approuvée et mise à la une !";
            if ($_GET['msg'] === 'rejected') echo "❌ La vidéo a été rejetée.";
        ?>
    </div>
<?php endif; ?>

<div class="card">
    <h2 class="card-title">File de modération (Vidéos en attente)</h2>
    <p style="color: #aaa; font-size: 14px; margin-bottom: 20px;">Examinez ces vidéos avant de les publier sur la plateforme.</p>
    
    <div style="overflow-x: auto;">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="min-width: 300px;">Vidéo</th>
                    <th>Contributeur</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($pendingVideos)): ?>
                    <tr>
                        <td colspan="4" style="text-align: center; color: #aaa; padding: 30px;">
                            🎉 Aucune vidéo en attente de modération pour le moment.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($pendingVideos as $v): ?>
                    <tr>
                        <td>
                            <div style="display: flex; gap: 16px; align-items: center;">
                                <img src="<?= htmlspecialchars($v['thumbnail_url'] ?: 'https://picsum.photos/seed/vid/200/112') ?>" style="width: 100px; aspect-ratio: 16/9; border-radius: 6px; object-fit: cover;" alt="Aperçu">
                                <div>
                                    <div style="font-weight: 600; color: #fff; margin-bottom: 4px;"><?= htmlspecialchars($v['title']) ?></div>
                                    <div style="font-size: 12px; color: #aaa;"><i class="ph ph-clock"></i> <?= gmdate("i:s", $v['duration_seconds'] ?? 0) ?> min</div>
                                </div>
                            </div>
                        </td>
                        <td><?= htmlspecialchars($v['author_name']) ?></td>
                        <td style="color: #aaa;"><?= date('d/m/Y H:i', strtotime($v['created_at'])) ?></td>
                        <td>
                            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                                <!-- Bouton Détails -->
                                <button class="btn btn-outline" onclick='showDetails(<?= json_encode($v) ?>)'>
                                    <i class="ph ph-eye"></i> Détails
                                </button>
                                
                                <!-- Bouton Approuver -->
                                <button class="btn btn-primary" onclick="window.location.href='index.php?p=admin_videos&action=approve&id=<?= $v['id'] ?>'">
                                    <i class="ph ph-check"></i> Approuver
                                </button>

                                <!-- Bouton À la une -->
                                <button class="btn" style="background:#facc15; color:#000;" onclick="window.location.href='index.php?p=admin_videos&action=feature&id=<?= $v['id'] ?>'">
                                    <i class="ph ph-star"></i> À la une
                                </button>
                                
                                <!-- Bouton Rejeter -->
                                <button class="btn btn-danger" onclick="rejectVideo(<?= $v['id'] ?>)">
                                    <i class="ph ph-x"></i> Rejeter
                                </button>
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
<!-- MODALE POUR AFFICHER LES DÉTAILS           -->
<!-- ========================================== -->
<div id="detailsModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 1000; justify-content: center; align-items: center;">
    <div style="background: #1e1e1e; padding: 32px; border-radius: 12px; width: 90%; max-width: 600px; border: 1px solid #333; position: relative;">
        <button onclick="closeModal()" style="position: absolute; top: 16px; right: 16px; background: none; border: none; color: #aaa; font-size: 24px; cursor: pointer;">&times;</button>
        
        <h2 id="modalTitle" style="color: #fff; margin-top: 0;">Titre de la vidéo</h2>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px;">
            <div>
                <div style="color: #aaa; font-size: 12px; margin-bottom: 4px;">Auteur</div>
                <div id="modalAuthor" style="color: #fff;">Nom</div>
            </div>
            <div>
                <div style="color: #aaa; font-size: 12px; margin-bottom: 4px;">Date de soumission</div>
                <div id="modalDate" style="color: #fff;">Date</div>
            </div>
        </div>

        <div style="margin-bottom: 20px;">
            <div style="color: #aaa; font-size: 12px; margin-bottom: 4px;">Description</div>
            <div id="modalDesc" style="color: #ddd; font-size: 14px; line-height: 1.5; background: #121212; padding: 12px; border-radius: 6px; max-height: 150px; overflow-y: auto;">
                Description...
            </div>
        </div>

        <div style="margin-bottom: 20px;">
            <div style="color: #aaa; font-size: 12px; margin-bottom: 4px;">Lien de la vidéo</div>
            <a id="modalUrl" href="#" target="_blank" style="color: #3ea6ff; font-size: 14px; word-break: break-all;">Lien</a>
        </div>

        <div style="text-align: right;">
            <button class="btn btn-outline" onclick="closeModal()">Fermer</button>
        </div>
    </div>
</div>

<script>
    // Fonction pour afficher la modale avec les détails
    function showDetails(video) {
        document.getElementById('modalTitle').innerText = video.title;
        document.getElementById('modalAuthor').innerText = video.author_name;
        document.getElementById('modalDate').innerText = video.created_at;
        document.getElementById('modalDesc').innerText = video.description || 'Aucune description fournie.';
        
        const urlLink = document.getElementById('modalUrl');
        urlLink.href = video.video_url;
        urlLink.innerText = video.video_url;

        // Afficher la modale
        document.getElementById('detailsModal').style.display = 'flex';
    }

    // Fermer la modale
    function closeModal() {
        document.getElementById('detailsModal').style.display = 'none';
    }

    // Fonction pour rejeter avec une raison
    function rejectVideo(id) {
        const reason = prompt("Pourquoi rejetez-vous cette vidéo ? (Droits d'auteur, qualité, etc.)");
        if (reason !== null && reason.trim() !== "") {
            window.location.href = 'index.php?p=admin_videos&action=reject&id=' + id + '&reason=' + encodeURIComponent(reason);
        }
    }

    // Fermer la modale si on clique en dehors
    window.onclick = function(event) {
        const modal = document.getElementById('detailsModal');
        if (event.target === modal) {
            closeModal();
        }
    }
</script>