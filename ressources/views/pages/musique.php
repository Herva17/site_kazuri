<h2 class="section-title">Musique</h2>
<div class="video-grid">
    <?php for($i=1; $i<=6; $i++): ?>
    <a href="index.php?p=detail&id=m<?= $i ?>" class="video-card">
        <div class="thumbnail-container">
            <img src="https://picsum.photos/seed/music<?= $i ?>/600/338" alt="Musique">
            <span class="duration-badge">4:12</span>
        </div>
        <div class="video-info">
            <img src="https://ui-avatars.com/api/?name=M+<?= $i ?>&background=333&color=fff" class="channel-avatar">
            <div class="video-text">
                <h3 class="video-title">Clip Officiel - Rumba Moderne <?= $i ?></h3>
                <p class="video-meta">Amani Beats</p>
                <p class="video-meta">125 k vues · il y a 5 jours</p>
            </div>
        </div>
    </a>
    <?php endfor; ?>
</div>