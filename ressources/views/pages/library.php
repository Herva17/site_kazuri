<h2 class="section-title">Ma Bibliothèque</h2>
<p style="color: #aaa; margin-bottom: 24px;">Retrouvez ici vos vidéos enregistrées, votre historique et vos playlists.</p>

<h3 style="color: #fff; margin-bottom: 16px;">Reprendre la lecture</h3>
<div class="video-grid">
    <?php for($i=1; $i<=3; $i++): ?>
    <a href="index.php?p=detail&id=lib<?= $i ?>" class="video-card">
        <div class="thumbnail-container">
            <img src="https://picsum.photos/seed/lib<?= $i ?>/600/338" alt="Historique">
            <div style="position:absolute; bottom:0; left:0; width:100%; height:4px; background:#333;">
                <div style="width:45%; height:100%; background:var(--accent);"></div>
            </div>
        </div>
        <div class="video-info">
            <div class="video-text">
                <h3 class="video-title">Vidéo regardée récemment <?= $i ?></h3>
                <p class="video-meta">45% de visionnage</p>
            </div>
        </div>
    </a>
    <?php endfor; ?>
</div>